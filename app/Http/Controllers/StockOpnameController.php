<?php

namespace App\Http\Controllers;

use App\Models\OpnameItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\StockOpname;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Controller untuk mengelola proses stock opname.
 */
class StockOpnameController extends Controller
{
    /**
     * Menampilkan daftar stock opname dengan filter status dan tanggal.
     */
    public function index(Request $request)
    {
        // Menampilkan daftar stock opname dengan filter status dan tanggal.
        // latest('id') sebagai tie-break: kolom date bertipe DATE (tanpa jam),
        // jadi transaksi di tanggal sama harus diurut id desc agar terbaru di atas.
        $query = StockOpname::with('user')->latest('date')->latest('id');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('from')) {
            $query->whereDate('date', '>=', $request->from);
        }
        if ($request->filled('to')) {
            $query->whereDate('date', '<=', $request->to);
        }

        $opnames = $query->paginate(15)->withQueryString();
        return view('stock-opnames.index', compact('opnames'));
    }

    /**
     * Menampilkan form buat opname baru, atau redirect ke draft yang belum selesai.
     */
    public function create()
    {
        // Menampilkan form buat opname baru, atau redirect ke draft yang belum selesai.
        // Kalau ada draft milik user ini, lanjutkan draft itu
        $existing = StockOpname::where('user_id', auth()->id())
            ->where('status', 'draft')
            ->latest()
            ->first();

        if ($existing) {
            return redirect()->route('stock-opnames.edit', $existing)
                ->with('info', 'Melanjutkan draft opname sebelumnya.');
        }

        $products = Product::with(['variants', 'unit', 'category'])
            ->has('variants')
            ->orderBy('name')
            ->get();

        return view('stock-opnames.create', compact('products'));
    }

    /**
     * Menyimpan data opname sebagai draft ke database.
     */
    public function store(Request $request)
    {
        // Menyimpan data opname sebagai draft ke database.
        $request->validate([
            'date'                      => 'required|date',
            'notes'                     => 'nullable|string',
            'items'                     => 'required|array|min:1',
            'items.*.variant_id'        => 'required|exists:product_variants,id',
            'items.*.physical_stock'    => 'required|integer|min:0',
        ]);

        // Menjalankan proses simpan draft opname dalam transaksi database.
        DB::transaction(function () use ($request) {
            $opname = StockOpname::create([
                'user_id' => auth()->id(),
                'date'    => $request->date,
                'notes'   => $request->notes,
                'status'  => 'draft',
            ]);

            foreach ($request->items as $item) {
                $variant = ProductVariant::find($item['variant_id']);
                $opname->items()->create([
                    'variant_id'     => $item['variant_id'],
                    'system_stock'   => $variant->stock,
                    'physical_stock' => $item['physical_stock'],
                ]);
            }

            session()->flash('success', 'Stok opname berhasil disimpan sebagai draft. Tinjau selisih dan konfirmasi untuk menyesuaikan stok.');
        });

        return redirect()->route('stock-opnames.index');
    }

    /**
     * Menampilkan detail stock opname beserta selisih stok tiap varian.
     */
    public function show(StockOpname $stockOpname)
    {
        // Menampilkan detail stock opname beserta selisih stok tiap varian.
        $stockOpname->load(['user', 'items.variant.product']);
        return view('stock-opnames.show', compact('stockOpname'));
    }

    /**
     * Lanjutkan draft opname yang sudah ada.
     */
    public function edit(StockOpname $stockOpname)
    {
        // Lanjutkan draft opname yang sudah ada.
        if ($stockOpname->status === 'confirmed') {
            return redirect()->route('stock-opnames.show', $stockOpname);
        }

        $products = Product::with(['variants', 'unit', 'category'])
            ->has('variants')
            ->orderBy('name')
            ->get();

        // Map existing saved items: variant_id => physical_stock
        $savedItems = $stockOpname->items->keyBy('variant_id');

        return view('stock-opnames.create', compact('products', 'stockOpname', 'savedItems'));
    }

    /**
     * Buat draft kosong saat halaman create dibuka untuk keperluan auto-save.
     */
    public function initDraft(Request $request)
    {
        // Buat draft kosong saat halaman create dibuka untuk keperluan auto-save.
        $request->validate(['date' => 'required|date', 'notes' => 'nullable|string']);

        // Cek apakah sudah ada draft aktif milik user ini
        $opname = StockOpname::where('user_id', auth()->id())
            ->where('status', 'draft')
            ->latest()
            ->first();

        if ($opname) {
            $opname->update(['date' => $request->date, 'notes' => $request->notes]);
        } else {
            $opname = StockOpname::create([
                'user_id' => auth()->id(),
                'date'    => $request->date,
                'notes'   => $request->notes,
                'status'  => 'draft',
            ]);
        }

        return response()->json(['id' => $opname->id]);
    }

    /**
     * Auto-save satu item opname ke draft yang sedang aktif.
     */
    public function saveItem(Request $request, StockOpname $stockOpname)
    {
        // Auto-save satu item opname ke draft yang sedang aktif.
        $request->validate([
            'variant_id'     => 'required|exists:product_variants,id',
            'physical_stock' => 'required|integer|min:0',
        ]);

        $variant = ProductVariant::find($request->variant_id);

        $stockOpname->items()->updateOrCreate(
            ['variant_id' => $request->variant_id],
            ['system_stock' => $variant->stock, 'physical_stock' => $request->physical_stock]
        );

        return response()->json(['ok' => true]);
    }

    /**
     * Konfirmasi opname dan sesuaikan stok sistem dengan stok fisik.
     */
    public function confirm(StockOpname $stockOpname)
    {
        // Konfirmasi opname dan sesuaikan stok sistem dengan stok fisik.
        if ($stockOpname->status === 'confirmed') {
            return back()->with('error', 'Opname ini sudah dikonfirmasi.');
        }

        // Menjalankan proses konfirmasi opname dan penyesuaian stok dalam transaksi database.
        DB::transaction(function () use ($stockOpname) {
            foreach ($stockOpname->items as $item) {
                // Update stok sistem sesuai stok fisik
                ProductVariant::find($item->variant_id)
                    ->update(['stock' => $item->physical_stock]);
            }

            $stockOpname->update(['status' => 'confirmed']);
            session()->flash('success', 'Stok opname dikonfirmasi. Stok sistem telah disesuaikan.');
        });

        return redirect()->route('stock-opnames.show', $stockOpname);
    }
}
