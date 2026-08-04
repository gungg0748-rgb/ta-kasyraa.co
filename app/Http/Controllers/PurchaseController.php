<?php

namespace App\Http\Controllers;

use App\Mail\PurchaseConfirmation;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

/**
 * Controller untuk mengelola transaksi pembelian.
 */
class PurchaseController extends Controller
{
    /**
     * Menampilkan daftar pembelian dengan filter supplier dan tanggal.
     */
    public function index(Request $request)
    {
        // Menampilkan daftar pembelian dengan filter supplier dan tanggal.
        // latest('id') sebagai tie-break: kolom date bertipe DATE (tanpa jam),
        // jadi transaksi di tanggal sama harus diurut id desc agar terbaru di atas.
        $query = Purchase::with(['supplier', 'user'])->latest('date')->latest('id');

        if ($request->filled('supplier')) {
            $query->where('supplier_id', $request->supplier);
        }
        if ($request->filled('from')) {
            $query->whereDate('date', '>=', $request->from);
        }
        if ($request->filled('to')) {
            $query->whereDate('date', '<=', $request->to);
        }

        $purchases = $query->paginate(15)->withQueryString();
        $suppliers  = Supplier::orderBy('name')->get();

        return view('purchases.index', compact('purchases', 'suppliers'));
    }

    /**
     * Menampilkan form buat pembelian baru.
     */
    public function create()
    {
        // Menampilkan form buat pembelian baru.
        $suppliers = Supplier::orderBy('name')->get();
        $products  = Product::with('variants')->orderBy('name')->get();

        // Harga beli terakhir per varian (untuk prefill harga di form)
        $variantCosts = DB::table('purchase_items')
            ->join('purchases', 'purchase_items.purchase_id', '=', 'purchases.id')
            ->orderBy('purchases.date', 'desc')
            ->orderBy('purchases.id', 'desc')
            ->select('purchase_items.variant_id', 'purchase_items.price')
            ->get()
            ->groupBy('variant_id')
            ->map(fn ($rows) => (float) $rows->first()->price);

        return view('purchases.create', compact('suppliers', 'products', 'variantCosts'));
    }

    /**
     * Menyimpan transaksi pembelian, update stok, dan kirim email konfirmasi ke supplier.
     */
    public function store(Request $request)
    {
        // Menyimpan transaksi pembelian, update stok, dan kirim email konfirmasi ke supplier.
        $request->validate([
            'supplier_id'       => 'required|exists:suppliers,id',
            'date'              => 'required|date',
            'notes'             => 'nullable|string',
            'items'             => 'required|array|min:1',
            'items.*.variant_id'=> 'required|exists:product_variants,id',
            'items.*.qty'       => 'required|integer|min:1',
            'items.*.price'     => 'required|numeric|min:0',
        ]);

        // Menjalankan proses simpan pembelian dan update stok dalam transaksi database.
        DB::transaction(function () use ($request) {
            $invoice = 'PO-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -5));

            $total = collect($request->items)->sum(fn($i) => $i['qty'] * $i['price']);

            $purchase = Purchase::create([
                'invoice_number' => $invoice,
                'supplier_id'    => $request->supplier_id,
                'user_id'        => auth()->id(),
                'date'           => $request->date,
                'notes'          => $request->notes,
                'total'          => $total,
            ]);

            foreach ($request->items as $item) {
                $purchase->items()->create([
                    'variant_id' => $item['variant_id'],
                    'qty'        => $item['qty'],
                    'price'      => $item['price'],
                ]);

                // Tambah stok
                \App\Models\ProductVariant::find($item['variant_id'])
                    ->increment('stock', $item['qty']);
            }

            // Kirim email ke supplier jika ada email
            $purchase->load(['supplier', 'user', 'items.variant.product']);
            if ($purchase->supplier->email) {
                Mail::to($purchase->supplier->email)->send(new PurchaseConfirmation($purchase));
            }

            session()->flash('success', "Pembelian #{$purchase->invoice_number} berhasil disimpan. Stok telah diperbarui.");
        });

        return redirect()->route('purchases.index');
    }

    /**
     * Menampilkan detail transaksi pembelian beserta item-itemnya.
     */
    public function show(Purchase $purchase)
    {
        // Menampilkan detail transaksi pembelian beserta item-itemnya.
        $purchase->load(['supplier', 'user', 'items.variant.product']);
        return view('purchases.show', compact('purchase'));
    }
}
