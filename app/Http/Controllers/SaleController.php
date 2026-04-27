<?php

namespace App\Http\Controllers;

use App\Mail\ReorderAlert;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

/**
 * Controller untuk mengelola transaksi penjualan.
 */
class SaleController extends Controller
{
    /**
     * Menampilkan daftar penjualan dengan filter tanggal.
     */
    public function index(Request $request)
    {
        // Menampilkan daftar penjualan dengan filter tanggal.
        $query = Sale::with(['user'])->latest('date');

        if ($request->filled('from')) {
            $query->whereDate('date', '>=', $request->from);
        }
        if ($request->filled('to')) {
            $query->whereDate('date', '<=', $request->to);
        }

        $sales = $query->paginate(15)->withQueryString();

        return view('sales.index', compact('sales'));
    }

    /**
     * Menampilkan form buat transaksi penjualan baru.
     */
    public function create()
    {
        // Menampilkan form buat transaksi penjualan baru.
        $products = Product::with('variants')->orderBy('name')->get();
        return view('sales.create', compact('products'));
    }

    /**
     * Menyimpan transaksi penjualan, kurangi stok, dan kirim alert reorder ke admin jika stok menipis.
     */
    public function store(Request $request)
    {
        // Menyimpan transaksi penjualan, kurangi stok, dan kirim alert reorder ke admin jika stok menipis.
        $request->validate([
            'date'               => 'required|date',
            'notes'              => 'nullable|string',
            'items'              => 'required|array|min:1',
            'items.*.variant_id' => 'required|exists:product_variants,id',
            'items.*.qty'        => 'required|integer|min:1',
            'items.*.price'      => 'required|numeric|min:0',
        ]);

        // Validasi stok cukup
        foreach ($request->items as $item) {
            $variant = ProductVariant::find($item['variant_id']);
            if ($variant->stock < $item['qty']) {
                return back()->withInput()->withErrors([
                    'items' => "Stok {$variant->product->name} tidak cukup. Tersedia: {$variant->stock}",
                ]);
            }
        }

        // Menjalankan proses simpan penjualan dan pengurangan stok dalam transaksi database.
        DB::transaction(function () use ($request) {
            $invoice = 'SO-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -5));
            $total   = collect($request->items)->sum(fn($i) => $i['qty'] * $i['price']);

            $sale = Sale::create([
                'invoice_number' => $invoice,
                'user_id'        => auth()->id(),
                'date'           => $request->date,
                'notes'          => $request->notes,
                'total'          => $total,
            ]);

            foreach ($request->items as $item) {
                $sale->items()->create([
                    'variant_id' => $item['variant_id'],
                    'qty'        => $item['qty'],
                    'price'      => $item['price'],
                ]);

                // Kurangi stok
                $variant = ProductVariant::find($item['variant_id']);
                $variant->decrement('stock', $item['qty']);
                $variant->refresh();

                // Cek reorder level → kirim email ke semua admin
                if ($variant->stock <= $variant->product->reorder_level) {
                    $admins = User::where('role', 'admin')->where('is_active', true)->get();
                    foreach ($admins as $admin) {
                        Mail::to($admin->email)->send(new ReorderAlert($variant));
                    }
                }
            }

            session()->flash('success', "Penjualan #{$sale->invoice_number} berhasil disimpan.");
        });

        return redirect()->route('sales.index');
    }

    /**
     * Menampilkan detail transaksi penjualan beserta item-itemnya.
     */
    public function show(Sale $sale)
    {
        // Menampilkan detail transaksi penjualan beserta item-itemnya.
        $sale->load(['user', 'items.variant.product']);
        return view('sales.show', compact('sale'));
    }

    /**
     * Lookup varian by barcode untuk keperluan scan barcode di form penjualan.
     */
    public function lookupBarcode(Request $request)
    {
        // Lookup varian by barcode untuk keperluan scan barcode di form penjualan.
        $variant = ProductVariant::with('product')
            ->where('barcode', $request->barcode)
            ->first();

        if (!$variant) {
            return response()->json(['error' => 'Barcode tidak ditemukan.'], 404);
        }

        return response()->json([
            'id'    => $variant->id,
            'label' => $variant->product->name . ' (' . collect([$variant->model, $variant->color, $variant->size])->filter()->implode(', ') . ')',
            'price' => $variant->product->price,
            'stock' => $variant->stock,
        ]);
    }
}
