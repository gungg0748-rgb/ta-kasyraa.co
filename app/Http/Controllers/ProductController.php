<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\Unit;
use Illuminate\Http\Request;
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

        $products    = $query->orderBy('name')->paginate(15)->withQueryString();
        $categories  = Category::orderBy('name')->get();

        return view('products.index', compact('products', 'categories'));
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
        return view('products.edit', compact('product', 'categories', 'units'));
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
}
