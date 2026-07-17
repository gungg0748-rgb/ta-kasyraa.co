<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Http\Request;

/**
 * Controller untuk mengelola data supplier.
 */
class SupplierController extends Controller
{
    /**
     * Menampilkan daftar semua supplier beserta jumlah pembeliannya.
     */
    public function index()
    {
        // Menampilkan daftar semua supplier beserta jumlah pembeliannya.
        $suppliers = Supplier::withCount('purchases')->latest('id')->paginate(15)->withQueryString();
        return view('suppliers.index', compact('suppliers'));
    }

    /**
     * Menampilkan form tambah supplier baru.
     */
    public function create()
    {
        // Menampilkan form tambah supplier baru.
        return view('suppliers.create');
    }

    /**
     * Menyimpan supplier baru ke database.
     */
    public function store(Request $request)
    {
        // Menyimpan supplier baru ke database.
        $request->validate([
            'name'    => 'required|string|max:255',
            'email'   => 'nullable|email|max:255|unique:suppliers,email',
            'phone'   => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
        ]);

        Supplier::create($request->only('name', 'email', 'phone', 'address'));

        return redirect()->route('suppliers.index')->with('success', 'Supplier berhasil ditambahkan.');
    }

    /**
     * Menampilkan detail supplier beserta riwayat pembelian terakhir.
     */
    public function show(Supplier $supplier)
    {
        // Menampilkan detail supplier beserta riwayat pembelian terakhir.
        $supplier->load(['purchases' => fn($q) => $q->latest()->limit(5)]);
        return view('suppliers.show', compact('supplier'));
    }

    /**
     * Menampilkan form edit data supplier.
     */
    public function edit(Supplier $supplier)
    {
        // Menampilkan form edit data supplier.
        return view('suppliers.edit', compact('supplier'));
    }

    /**
     * Memperbarui data supplier di database.
     */
    public function update(Request $request, Supplier $supplier)
    {
        // Memperbarui data supplier di database.
        $request->validate([
            'name'    => 'required|string|max:255',
            'email'   => 'nullable|email|max:255|unique:suppliers,email,' . $supplier->id,
            'phone'   => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
        ]);

        $supplier->update($request->only('name', 'email', 'phone', 'address'));

        return redirect()->route('suppliers.index')->with('success', 'Supplier berhasil diperbarui.');
    }

    /**
     * Menghapus supplier, tapi gagal kalau masih ada riwayat pembelian.
     */
    public function destroy(Supplier $supplier)
    {
        // Menghapus supplier, tapi gagal kalau masih ada riwayat pembelian.
        if ($supplier->purchases()->exists()) {
            return back()->with('error', 'Supplier tidak dapat dihapus karena memiliki riwayat pembelian.');
        }

        $supplier->delete();
        return redirect()->route('suppliers.index')->with('success', 'Supplier berhasil dihapus.');
    }
}
