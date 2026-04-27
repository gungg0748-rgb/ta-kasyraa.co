<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * Controller untuk mengelola varian produk.
 */
class ProductVariantController extends Controller
{
    /**
     * Menyimpan varian baru untuk produk tertentu beserta generate barcode otomatis.
     */
    public function store(Request $request, Product $product)
    {
        // Menyimpan varian baru untuk produk tertentu beserta generate barcode otomatis.
        $request->validate([
            'model' => 'nullable|string|max:100',
            'color' => 'nullable|string|max:100',
            'size'  => 'nullable|string|max:50',
        ]);

        $barcode = 'VAR-' . strtoupper(Str::random(8));
        while (ProductVariant::where('barcode', $barcode)->exists()) {
            $barcode = 'VAR-' . strtoupper(Str::random(8));
        }

        $product->variants()->create([
            'model'   => $request->model,
            'color'   => $request->color,
            'size'    => $request->size,
            'stock'   => 0,
            'barcode' => $barcode,
        ]);

        return redirect()->route('products.show', $product)->with('success', 'Varian berhasil ditambahkan.');
    }

    /**
     * Memperbarui data varian produk di database.
     */
    public function update(Request $request, Product $product, ProductVariant $variant)
    {
        // Memperbarui data varian produk di database.
        $request->validate([
            'model' => 'nullable|string|max:100',
            'color' => 'nullable|string|max:100',
            'size'  => 'nullable|string|max:50',
        ]);

        $variant->update($request->only('model', 'color', 'size'));

        return redirect()->route('products.show', $product)->with('success', 'Varian berhasil diperbarui.');
    }

    /**
     * Menghapus varian produk, tapi gagal kalau masih ada riwayat transaksi.
     */
    public function destroy(Product $product, ProductVariant $variant)
    {
        // Menghapus varian produk, tapi gagal kalau masih ada riwayat transaksi.
        if ($variant->purchaseItems()->exists() || $variant->saleItems()->exists()) {
            return back()->with('error', 'Varian tidak dapat dihapus karena memiliki riwayat transaksi.');
        }

        $variant->delete();
        return redirect()->route('products.show', $product)->with('success', 'Varian berhasil dihapus.');
    }
}
