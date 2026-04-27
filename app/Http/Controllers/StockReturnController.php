<?php

namespace App\Http\Controllers;

use App\Mail\ReturnNotification;
use App\Models\Purchase;
use App\Models\ProductVariant;
use App\Models\StockReturn;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

/**
 * Controller untuk mengelola retur stok ke supplier.
 */
class StockReturnController extends Controller
{
    /**
     * Menampilkan daftar retur stok dengan filter supplier dan tanggal.
     */
    public function index(Request $request)
    {
        // Menampilkan daftar retur stok dengan filter supplier dan tanggal.
        $query = StockReturn::with(['supplier', 'purchase', 'user'])->latest('date');

        if ($request->filled('supplier')) {
            $query->where('supplier_id', $request->supplier);
        }
        if ($request->filled('from')) {
            $query->whereDate('date', '>=', $request->from);
        }
        if ($request->filled('to')) {
            $query->whereDate('date', '<=', $request->to);
        }

        $returns   = $query->paginate(15)->withQueryString();
        $suppliers = \App\Models\Supplier::orderBy('name')->get();

        return view('returns.index', compact('returns', 'suppliers'));
    }

    /**
     * Menampilkan form buat retur stok baru berdasarkan pembelian yang ada.
     */
    public function create()
    {
        // Menampilkan form buat retur stok baru berdasarkan pembelian yang ada.
        // Hanya tampilkan pembelian yang punya items
        $purchases = Purchase::with(['supplier', 'items.variant.product'])
            ->has('items')
            ->latest('date')
            ->get();

        return view('returns.create', compact('purchases'));
    }

    /**
     * Menyimpan retur stok, kurangi stok, dan kirim notifikasi email ke supplier.
     */
    public function store(Request $request)
    {
        // Menyimpan retur stok, kurangi stok, dan kirim notifikasi email ke supplier.
        $request->validate([
            'purchase_id'        => 'required|exists:purchases,id',
            'date'               => 'required|date',
            'notes'              => 'nullable|string',
            'items'              => 'required|array|min:1',
            'items.*.variant_id' => 'required|exists:product_variants,id',
            'items.*.qty'        => 'required|integer|min:1',
            'items.*.reason'     => 'nullable|string|max:255',
        ]);

        // Validasi qty tidak melebihi stok
        foreach ($request->items as $item) {
            $variant = ProductVariant::find($item['variant_id']);
            if ($variant->stock < $item['qty']) {
                return back()->withInput()->withErrors([
                    'items' => "Stok {$variant->product->name} tidak cukup untuk di-return. Tersedia: {$variant->stock}",
                ]);
            }
        }

        // Menjalankan proses simpan retur dan pengurangan stok dalam transaksi database.
        DB::transaction(function () use ($request) {
            $purchase    = Purchase::find($request->purchase_id);
            $returnNumber = 'RT-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -5));

            $stockReturn = StockReturn::create([
                'return_number' => $returnNumber,
                'purchase_id'   => $request->purchase_id,
                'supplier_id'   => $purchase->supplier_id,
                'user_id'       => auth()->id(),
                'date'          => $request->date,
                'notes'         => $request->notes,
            ]);

            foreach ($request->items as $item) {
                $stockReturn->items()->create([
                    'variant_id' => $item['variant_id'],
                    'qty'        => $item['qty'],
                    'reason'     => $item['reason'] ?? null,
                ]);

                // Kurangi stok
                ProductVariant::find($item['variant_id'])->decrement('stock', $item['qty']);
            }

            // Kirim email ke supplier
            $stockReturn->load(['supplier', 'purchase', 'user', 'items.variant.product']);
            if ($stockReturn->supplier->email) {
                Mail::to($stockReturn->supplier->email)->send(new ReturnNotification($stockReturn));
            }

            session()->flash('success', "Return #{$stockReturn->return_number} berhasil disimpan. Stok telah dikurangi.");
        });

        return redirect()->route('returns.index');
    }

    /**
     * Menampilkan detail retur stok beserta item-itemnya.
     */
    public function show(StockReturn $return)
    {
        // Menampilkan detail retur stok beserta item-itemnya.
        $return->load(['supplier', 'purchase', 'user', 'items.variant.product']);
        return view('returns.show', compact('return'));
    }

    /**
     * AJAX: ambil daftar item dari pembelian tertentu untuk keperluan form retur.
     */
    public function getPurchaseItems(Purchase $purchase)
    {
        // AJAX: ambil daftar item dari pembelian tertentu untuk keperluan form retur.
        $purchase->load('items.variant.product');
        return response()->json($purchase->items->map(fn($item) => [
            'variant_id' => $item->variant_id,
            'label'      => $item->variant->product->name . ' (' .
                            collect([$item->variant->model, $item->variant->color, $item->variant->size])
                                ->filter()->implode(', ') . ')',
            'qty_bought' => $item->qty,
            'stock'      => $item->variant->stock,
        ]));
    }
}
