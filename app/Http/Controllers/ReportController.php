<?php

namespace App\Http\Controllers;

use App\Exports\OpnamesExport;
use App\Exports\PurchasesExport;
use App\Exports\ReturnsExport;
use App\Exports\SalesExport;
use App\Exports\StockExport;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\StockOpname;
use App\Models\StockReturn;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

/**
 * Controller untuk mengelola tampilan dan ekspor laporan.
 */
class ReportController extends Controller
{
    /**
     * Menampilkan halaman utama laporan.
     */
    public function index()
    {
        // Menampilkan halaman utama laporan.
        return view('reports.index');
    }

    /**
     * Menampilkan laporan stok produk dengan filter kategori dan pencarian.
     */
    public function stock(Request $request)
    {
        // Menampilkan laporan stok produk dengan filter kategori dan pencarian.
        $query = Product::with(['category', 'unit', 'variants']);

        if ($request->filled('category')) {
            $query->where('category_id', $request->category);
        }
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $products   = $query->orderBy('name')->get();
        $categories = \App\Models\Category::orderBy('name')->get();

        return view('reports.stock', compact('products', 'categories'));
    }

    /**
     * Menampilkan laporan pembelian dengan filter supplier dan rentang tanggal.
     */
    public function purchases(Request $request)
    {
        // Menampilkan laporan pembelian dengan filter supplier dan rentang tanggal.
        $query = Purchase::with(['supplier', 'user', 'items']);

        if ($request->filled('supplier')) {
            $query->where('supplier_id', $request->supplier);
        }
        if ($request->filled('from')) {
            $query->whereDate('date', '>=', $request->from);
        }
        if ($request->filled('to')) {
            $query->whereDate('date', '<=', $request->to);
        }

        $purchases  = $query->latest('date')->get();
        $suppliers  = \App\Models\Supplier::orderBy('name')->get();
        $total      = $purchases->sum('total');

        return view('reports.purchases', compact('purchases', 'suppliers', 'total'));
    }

    /**
     * Menampilkan laporan penjualan dengan filter rentang tanggal.
     */
    public function sales(Request $request)
    {
        // Menampilkan laporan penjualan dengan filter rentang tanggal.
        $query = Sale::with(['user', 'items']);

        if ($request->filled('from')) {
            $query->whereDate('date', '>=', $request->from);
        }
        if ($request->filled('to')) {
            $query->whereDate('date', '<=', $request->to);
        }

        $sales = $query->latest('date')->get();
        $total = $sales->sum('total');

        return view('reports.sales', compact('sales', 'total'));
    }

    /**
     * Menampilkan laporan retur stok dengan filter supplier dan rentang tanggal.
     */
    public function returns(Request $request)
    {
        // Menampilkan laporan retur stok dengan filter supplier dan rentang tanggal.
        $query = StockReturn::with(['supplier', 'purchase', 'user', 'items']);

        if ($request->filled('supplier')) {
            $query->where('supplier_id', $request->supplier);
        }
        if ($request->filled('from')) {
            $query->whereDate('date', '>=', $request->from);
        }
        if ($request->filled('to')) {
            $query->whereDate('date', '<=', $request->to);
        }

        $returns   = $query->latest('date')->get();
        $suppliers = \App\Models\Supplier::orderBy('name')->get();

        return view('reports.returns', compact('returns', 'suppliers'));
    }

    /**
     * Menampilkan laporan stock opname dengan filter tanggal dan status.
     */
    public function opnames(Request $request)
    {
        // Menampilkan laporan stock opname dengan filter tanggal dan status.
        $query = StockOpname::with(['user', 'items.variant.product']);

        if ($request->filled('from')) {
            $query->whereDate('date', '>=', $request->from);
        }
        if ($request->filled('to')) {
            $query->whereDate('date', '<=', $request->to);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $opnames = $query->latest('date')->get();

        return view('reports.opnames', compact('opnames'));
    }

    // ── Export methods ──────────────────────────────────────────

    /**
     * Export laporan stok ke file Excel.
     */
    public function exportStock(Request $request)
    {
        // Export laporan stok ke file Excel.
        $filename = 'laporan-stok-' . date('Ymd') . '.xlsx';
        return Excel::download(
            new StockExport($request->category, $request->search),
            $filename
        );
    }

    /**
     * Export laporan pembelian ke file Excel.
     */
    public function exportPurchases(Request $request)
    {
        // Export laporan pembelian ke file Excel.
        $filename = 'laporan-pembelian-' . date('Ymd') . '.xlsx';
        return Excel::download(
            new PurchasesExport($request->supplier, $request->from, $request->to),
            $filename
        );
    }

    /**
     * Export laporan penjualan ke file Excel.
     */
    public function exportSales(Request $request)
    {
        // Export laporan penjualan ke file Excel.
        $filename = 'laporan-penjualan-' . date('Ymd') . '.xlsx';
        return Excel::download(
            new SalesExport($request->from, $request->to),
            $filename
        );
    }

    /**
     * Export laporan retur stok ke file Excel.
     */
    public function exportReturns(Request $request)
    {
        // Export laporan retur stok ke file Excel.
        $filename = 'laporan-return-' . date('Ymd') . '.xlsx';
        return Excel::download(
            new ReturnsExport($request->supplier, $request->from, $request->to),
            $filename
        );
    }

    /**
     * Export laporan stock opname ke file Excel.
     */
    public function exportOpnames(Request $request)
    {
        // Export laporan stock opname ke file Excel.
        $filename = 'laporan-opname-' . date('Ymd') . '.xlsx';
        return Excel::download(
            new OpnamesExport($request->from, $request->to, $request->status),
            $filename
        );
    }
}
