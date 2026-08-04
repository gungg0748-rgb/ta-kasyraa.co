<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Controller untuk mengelola data produk.
 */
class ProductController extends Controller
{
    /**
     * Menampilkan daftar produk dengan filter kategori dan pencarian.
     */
    public function index(Request $request)
    {
        // Menampilkan daftar produk dengan filter kategori dan pencarian.
        $query = Product::with(['category', 'unit', 'variants']);

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('barcode', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('category')) {
            $query->where('category_id', $request->category);
        }

        $products    = $query->latest('id')->paginate(15)->withQueryString(); // produk terbaru di atas
        $categories  = Category::orderBy('name')->get(); // dropdown filter tetap alfabetis

        // Harga beli = harga dari transaksi Pembelian TERAKHIR (tanpa menyimpan field baru).
        $lastCost = DB::table('purchase_items')
            ->join('product_variants', 'purchase_items.variant_id', '=', 'product_variants.id')
            ->join('purchases', 'purchase_items.purchase_id', '=', 'purchases.id')
            ->whereIn('product_variants.product_id', $products->pluck('id'))
            ->orderBy('purchases.date', 'desc')
            ->orderBy('purchases.id', 'desc')
            ->select('product_variants.product_id', 'purchase_items.price')
            ->get()
            ->groupBy('product_id')
            ->map(fn ($rows) => $rows->first()->price); // baris pertama = pembelian terbaru

        return view('products.index', compact('products', 'categories', 'lastCost'));
    }

    /**
     * Menampilkan form tambah produk baru.
     */
    public function create()
    {
        // Menampilkan form tambah produk baru.
        $categories = Category::orderBy('name')->get();
        $units      = Unit::orderBy('name')->get();
        return view('products.create', compact('categories', 'units'));
    }

    /**
     * Menyimpan produk baru ke database beserta generate barcode otomatis.
     */
    public function store(Request $request)
    {
        // Menyimpan produk baru ke database beserta generate barcode otomatis.
        $request->validate([
            'name'          => 'required|string|max:255',
            'category_id'   => 'required|exists:categories,id',
            'unit_id'       => 'required|exists:units,id',
            'price'         => 'required|numeric|min:0',
            'reorder_level' => 'required|integer|min:0',
            'description'   => 'nullable|string',
            'image'         => 'nullable|image|max:2048',
            'purchase_price'=> 'nullable|numeric|min:0',
        ]);

        $barcode = 'KSR-' . strtoupper(Str::random(8));
        while (Product::where('barcode', $barcode)->exists()) {
            $barcode = 'KSR-' . strtoupper(Str::random(8));
        }

        $imagePath = $request->hasFile('image')
            ? $request->file('image')->store('products', 'public')
            : null;

        $product = Product::create([
            ...$request->only('name', 'category_id', 'unit_id', 'price', 'reorder_level', 'description'),
            'barcode' => $barcode,
            'image'   => $imagePath,
        ]);

        // Simpan harga beli: buat varian default dulu, lalu catat transaksi pembelian dummy.
        if ($request->filled('purchase_price')) {
            $variantBarcode = 'KSR-' . strtoupper(Str::random(8));
            while (ProductVariant::where('barcode', $variantBarcode)->exists()) {
                $variantBarcode = 'KSR-' . strtoupper(Str::random(8));
            }

            $product->variants()->create([
                'model'   => 'Default',
                'stock'   => 0,
                'barcode' => $variantBarcode,
            ]);

            $this->updateLastPurchasePrice($product, $request->purchase_price);
        }

        return redirect()->route('products.index')->with('success', 'Produk berhasil ditambahkan.');
    }

    /**
     * Menampilkan detail produk beserta varian-variannya.
     */
    public function show(Product $product)
    {
        // Menampilkan detail produk beserta varian-variannya.
        $product->load(['category', 'unit', 'variants']);
        return view('products.show', compact('product'));
    }

    /**
     * Menampilkan form edit produk yang sudah ada.
     */
    public function edit(Product $product)
    {
        // Menampilkan form edit produk yang sudah ada.
        $categories = Category::orderBy('name')->get();
        $units      = Unit::orderBy('name')->get();

        // Harga beli yang tampil = harga pembelian terakhir produk ini.
        $lastCost = $this->lastPurchase($product);

        return view('products.edit', compact('product', 'categories', 'units', 'lastCost'));
    }

    /**
     * Memperbarui data produk di database.
     */
    public function update(Request $request, Product $product)
    {
        // Memperbarui data produk di database.
        $request->validate([
            'name'          => 'required|string|max:255',
            'category_id'   => 'required|exists:categories,id',
            'unit_id'       => 'required|exists:units,id',
            'price'         => 'required|numeric|min:0',
            'reorder_level' => 'required|integer|min:0',
            'description'   => 'nullable|string',
            'image'         => 'nullable|image|max:2048',
            // Harga beli (opsional) — kalau diisi, update harga pembelian terakhir.
            'purchase_price'=> 'nullable|numeric|min:0',
        ]);

        $data = $request->only('name', 'category_id', 'unit_id', 'price', 'reorder_level', 'description');

        if ($request->hasFile('image')) {
            // Hapus foto lama kalau ada
            if ($product->image) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($product->image);
            }
            $data['image'] = $request->file('image')->store('products', 'public');
        }

        $product->update($data);

        // Update harga beli: langsung ubah item pembelian terakhir produk ini.
        if ($request->filled('purchase_price')) {
            $this->updateLastPurchasePrice($product, $request->purchase_price);
        }

        return redirect()->route('products.show', $product)->with('success', 'Produk berhasil diperbarui.');
    }

    /**
     * Menghapus produk dari database. Gagal jika masih ada varian dengan riwayat transaksi.
     */
    public function destroy(Product $product)
    {
        // Cek apakah ada varian produk yang punya riwayat transaksi
        $variantIds = $product->variants()->pluck('id');

        $hasPurchases = DB::table('purchase_items')->whereIn('variant_id', $variantIds)->exists();
        $hasSales     = DB::table('sale_items')->whereIn('variant_id', $variantIds)->exists();
        $hasReturns   = DB::table('return_items')->whereIn('variant_id', $variantIds)->exists();
        $hasOpnames   = DB::table('opname_items')->whereIn('variant_id', $variantIds)->exists();

        if ($hasPurchases || $hasSales || $hasReturns || $hasOpnames) {
            return back()->with('error', 'Produk tidak dapat dihapus karena masih memiliki riwayat transaksi (pembelian/penjualan/return/opname). Hapus dulu varian terkait atau nonaktifkan produk.');
        }

        $product->delete();
        return redirect()->route('products.index')->with('success', 'Produk berhasil dihapus.');
    }

    /**
     * Harga beli = harga item pembelian TERAKHIR untuk produk ini.
     * Prioritas: pembelian dengan tanggal terbaru, lalu id terbesar.
     */
    private function lastPurchase(Product $product): ?float
    {
        $item = DB::table('purchase_items')
            ->join('product_variants', 'purchase_items.variant_id', '=', 'product_variants.id')
            ->join('purchases', 'purchase_items.purchase_id', '=', 'purchases.id')
            ->where('product_variants.product_id', $product->id)
            ->orderBy('purchases.date', 'desc')
            ->orderBy('purchases.id', 'desc')
            ->select('purchase_items.id', 'purchase_items.price')
            ->first();

        return $item ? (float) $item->price : null;
    }

    /**
     * Ubah harga beli: update harga item pembelian terakhir produk ini,
     * lalu hitung ulang total transaksi pembelian tersebut.
     * Kalau belum ada riwayat pembelian, buat transaksi pembelian baru.
     */
    private function updateLastPurchasePrice(Product $product, $newPrice): void
    {
        $item = DB::table('purchase_items')
            ->join('product_variants', 'purchase_items.variant_id', '=', 'product_variants.id')
            ->join('purchases', 'purchase_items.purchase_id', '=', 'purchases.id')
            ->where('product_variants.product_id', $product->id)
            ->orderBy('purchases.date', 'desc')
            ->orderBy('purchases.id', 'desc')
            ->select('purchase_items.id', 'purchase_items.purchase_id', 'purchase_items.price', 'purchase_items.qty')
            ->first();

        if ($item) {
            // Sudah ada riwayat pembelian — update harga item terakhir.
            DB::table('purchase_items')->where('id', $item->id)->update(['price' => $newPrice]);

            // Hitung ulang total pembelian dari semua item-nya.
            $total = DB::table('purchase_items')
                ->where('purchase_id', $item->purchase_id)
                ->sum(DB::raw('qty * price'));

            DB::table('purchases')->where('id', $item->purchase_id)->update(['total' => $total]);
            return;
        }

        // Belum pernah dibeli — buat transaksi pembelian baru sebagai catatan harga beli.
        $variant = $product->variants()->first();
        if (! $variant) {
            return; // tidak ada varian, tidak bisa buat pembelian
        }

        $supplier = \App\Models\Supplier::first();
        if (! $supplier) {
            return; // tidak ada supplier
        }

        $invoiceNumber = 'PO-' . now()->format('Ymd') . '-HBL' . strtoupper(Str::random(4));
        while (DB::table('purchases')->where('invoice_number', $invoiceNumber)->exists()) {
            $invoiceNumber = 'PO-' . now()->format('Ymd') . '-HBL' . strtoupper(Str::random(4));
        }

        $purchaseId = DB::table('purchases')->insertGetId([
            'invoice_number' => $invoiceNumber,
            'supplier_id'    => $supplier->id,
            'user_id'        => auth()->id() ?? 1,
            'total'          => $newPrice,
            'date'           => now()->toDateString(),
            'created_at'     => now(),
            'updated_at'     => now(),
        ]);

        DB::table('purchase_items')->insert([
            'purchase_id' => $purchaseId,
            'variant_id'  => $variant->id,
            'qty'         => 0,
            'price'       => $newPrice,
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);
    }
}
