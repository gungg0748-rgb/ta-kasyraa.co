<?php

namespace App\Http\Controllers;

use App\Models\Unit;
use Illuminate\Http\Request;

/**
 * Controller untuk mengelola data satuan produk.
 */
class UnitController extends Controller
{
    /**
     * Menampilkan daftar satuan produk.
     */
    public function index()
    {
        // Menampilkan daftar satuan produk.
        $units = Unit::withCount('products')->orderBy('name')->get();
        return view('units.index', compact('units'));
    }

    /**
     * Menampilkan form tambah satuan baru.
     */
    public function create()
    {
        // Menampilkan form tambah satuan baru.
        return view('units.create');
    }

    /**
     * Menyimpan satuan baru ke database.
     */
    public function store(Request $request)
    {
        // Menyimpan satuan baru ke database.
        $request->validate([
            'name' => 'required|string|max:100|unique:units,name',
        ]);

        Unit::create($request->only('name'));

        return redirect()->route('units.index')->with('success', 'Satuan berhasil ditambahkan.');
    }

    /**
     * Menampilkan form edit satuan.
     */
    public function edit(Unit $unit)
    {
        // Menampilkan form edit satuan.
        return view('units.edit', compact('unit'));
    }

    /**
     * Memperbarui data satuan di database.
     */
    public function update(Request $request, Unit $unit)
    {
        // Memperbarui data satuan di database.
        $request->validate([
            'name' => 'required|string|max:100|unique:units,name,' . $unit->id,
        ]);

        $unit->update($request->only('name'));

        return redirect()->route('units.index')->with('success', 'Satuan berhasil diperbarui.');
    }

    /**
     * Menghapus satuan jika belum digunakan produk.
     */
    public function destroy(Unit $unit)
    {
        // Menghapus satuan jika belum digunakan produk.
        if ($unit->products()->exists()) {
            return back()->with('error', 'Satuan tidak dapat dihapus karena masih digunakan produk.');
        }

        $unit->delete();
        return redirect()->route('units.index')->with('success', 'Satuan berhasil dihapus.');
    }
}
