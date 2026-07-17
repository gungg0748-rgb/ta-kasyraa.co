<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

/**
 * Controller untuk mengelola data kategori produk.
 */
class CategoryController extends Controller
{
    /**
     * Menampilkan daftar semua kategori beserta jumlah produknya.
     */
    public function index()
    {
        // Menampilkan daftar semua kategori beserta jumlah produknya.
        $categories = Category::withCount('products')->latest('id')->paginate(15)->withQueryString();
        return view('categories.index', compact('categories'));
    }

    /**
     * Menampilkan form tambah kategori baru.
     */
    public function create()
    {
        // Menampilkan form tambah kategori baru.
        return view('categories.create');
    }

    /**
     * Menyimpan kategori baru ke database.
     */
    public function store(Request $request)
    {
        // Menyimpan kategori baru ke database.
        $request->validate([
            'name'        => 'required|string|max:255|unique:categories,name',
            'description' => 'nullable|string|max:500',
        ]);

        Category::create($request->only('name', 'description'));

        return redirect()->route('categories.index')->with('success', 'Kategori berhasil ditambahkan.');
    }

    /**
     * Menampilkan form edit kategori yang sudah ada.
     */
    public function edit(Category $category)
    {
        // Menampilkan form edit kategori yang sudah ada.
        return view('categories.edit', compact('category'));
    }

    /**
     * Memperbarui data kategori di database.
     */
    public function update(Request $request, Category $category)
    {
        // Memperbarui data kategori di database.
        $request->validate([
            'name'        => 'required|string|max:255|unique:categories,name,' . $category->id,
            'description' => 'nullable|string|max:500',
        ]);

        $category->update($request->only('name', 'description'));

        return redirect()->route('categories.index')->with('success', 'Kategori berhasil diperbarui.');
    }

    /**
     * Menghapus kategori, tapi gagal kalau masih ada produk terkait.
     */
    public function destroy(Category $category)
    {
        // Menghapus kategori, tapi gagal kalau masih ada produk terkait.
        if ($category->products()->exists()) {
            return back()->with('error', 'Kategori tidak dapat dihapus karena masih memiliki produk.');
        }

        $category->delete();
        return redirect()->route('categories.index')->with('success', 'Kategori berhasil dihapus.');
    }
}
