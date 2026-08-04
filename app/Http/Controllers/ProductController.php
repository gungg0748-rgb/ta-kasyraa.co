<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
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
        ]);

        $barcode = 'KSR-' . strtoupper(Str::random(8));
        while (Product::where('barcode', $barcode)->exists()) {
            $barcode = 'KSR-' . strtoupper(Str::random(8));
        }

        $imagePath = $request->hasFile('image')
            ? $request->file('image')->store('products', 'public')
            : null;

        Product::create([
            ...$request->only('name', 'category_id', 'unit_id', 'price', 'reorder_level', 'description'),
            'barcode' => $barcode,
            'image'   => $imagePath,
        ]);

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
     * Menghapus produk dari database.
     */
    public function destroy(Product $product)
    {
        // Menghapus produk dari database.
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

        if (! $item) {
            return; // belum pernah dibeli, tidak ada yang diubah
        }

        DB::table('purchase_items')->where('id', $item->id)->update(['price' => $newPrice]);

        // Hitung ulang total pembelian dari semua item-nya.
        $total = DB::table('purchase_items')
            ->where('purchase_id', $item->purchase_id)
            ->sum(DB::raw('qty * price'));

        DB::table('purchases')->where('id', $item->purchase_id)->update(['total' => $total]);
    }
}
