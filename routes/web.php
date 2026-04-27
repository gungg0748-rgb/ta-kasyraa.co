<?php

use App\Http\Controllers\ScannerController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductVariantController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\StockOpnameController;
use App\Http\Controllers\StockReturnController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\UnitController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/dashboard', function () {
    $totalSkus     = \App\Models\ProductVariant::count();
    $criticalStock = \App\Models\Product::with('variants')
        ->get()
        ->filter(fn($p) => $p->variants->count() > 0 && $p->variants->sum('stock') <= $p->reorder_level);
    $criticalCount = $criticalStock->count();
    $salesToday    = \App\Models\Sale::whereDate('date', today())->sum('total');
    $purchasesToday = \App\Models\Purchase::whereDate('date', today())->sum('total');
    $recentSales   = \App\Models\Sale::with('user')->latest('date')->limit(5)->get();
    $recentPurchases = \App\Models\Purchase::with('supplier')->latest('date')->limit(5)->get();
    return view('dashboard', compact(
        'totalSkus', 'criticalCount', 'criticalStock',
        'salesToday', 'purchasesToday', 'recentSales', 'recentPurchases'
    ));
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Manajemen Akun - Admin only
    Route::middleware('role:admin')->group(function () {
        Route::resource('users', UserController::class)->except(['show', 'destroy']);
        Route::patch('users/{user}/toggle-active', [UserController::class, 'toggleActive'])->name('users.toggle-active');

        // Data Master
        Route::resource('categories', CategoryController::class)->except(['show']);
        Route::resource('units', UnitController::class)->except(['show']);
        Route::resource('suppliers', SupplierController::class);
    });

    // Produk & Varian - Admin + Gudang
    Route::middleware('role:admin,gudang')->group(function () {
        Route::resource('products', ProductController::class);
        Route::post('products/{product}/variants', [ProductVariantController::class, 'store'])->name('products.variants.store');
        Route::put('products/{product}/variants/{variant}', [ProductVariantController::class, 'update'])->name('products.variants.update');
        Route::delete('products/{product}/variants/{variant}', [ProductVariantController::class, 'destroy'])->name('products.variants.destroy');

        // FR-03: Pembelian
        Route::resource('purchases', PurchaseController::class)->only(['index', 'create', 'store', 'show']);

        // FR-05: Return Pembelian
        Route::resource('returns', StockReturnController::class)->only(['index', 'create', 'store', 'show']);
        Route::get('returns/purchase/{purchase}/items', [StockReturnController::class, 'getPurchaseItems'])->name('returns.purchase-items');

        // FR-06: Stok Opname
        Route::resource('stock-opnames', StockOpnameController::class)->only(['index', 'create', 'store', 'show']);
        Route::post('stock-opnames/{stockOpname}/confirm', [StockOpnameController::class, 'confirm'])->name('stock-opnames.confirm');
        Route::get('stock-opnames/{stockOpname}/edit', [StockOpnameController::class, 'edit'])->name('stock-opnames.edit');
        Route::post('stock-opnames/init-draft', [StockOpnameController::class, 'initDraft'])->name('stock-opnames.init-draft');
        Route::patch('stock-opnames/{stockOpname}/save-item', [StockOpnameController::class, 'saveItem'])->name('stock-opnames.save-item');
    });

    // FR-04: Penjualan - Admin + Kasir
    Route::middleware('role:admin,kasir')->group(function () {
        Route::resource('sales', SaleController::class)->only(['index', 'create', 'store', 'show']);
        Route::get('sales/barcode-lookup', [SaleController::class, 'lookupBarcode'])->name('sales.barcode-lookup');
    });

    // Scanner HP — semua role yang login bisa pakai
    Route::post('scanner/token', [ScannerController::class, 'generateToken'])->name('scanner.token');
    Route::get('scanner/poll/{token}', [ScannerController::class, 'poll'])->name('scanner.poll');
    Route::get('scanner/devices', [ScannerController::class, 'activeDevices'])->name('scanner.devices');
    Route::get('scanner/connected/{token}', [ScannerController::class, 'checkConnected'])->name('scanner.connected');
    Route::post('scanner/disconnect/{token}', [ScannerController::class, 'disconnect'])->name('scanner.disconnect');

    // FR-08: Laporan - per role
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/', [ReportController::class, 'index'])->name('index');
        Route::get('/stock', [ReportController::class, 'stock'])->name('stock');
        Route::get('/stock/export', [ReportController::class, 'exportStock'])->name('stock.export');

        // Admin + Gudang
        Route::middleware('role:admin,gudang')->group(function () {
            Route::get('/purchases', [ReportController::class, 'purchases'])->name('purchases');
            Route::get('/purchases/export', [ReportController::class, 'exportPurchases'])->name('purchases.export');
            Route::get('/returns', [ReportController::class, 'returns'])->name('returns');
            Route::get('/returns/export', [ReportController::class, 'exportReturns'])->name('returns.export');
            Route::get('/opnames', [ReportController::class, 'opnames'])->name('opnames');
            Route::get('/opnames/export', [ReportController::class, 'exportOpnames'])->name('opnames.export');
        });

        // Admin + Kasir
        Route::middleware('role:admin,kasir')->group(function () {
            Route::get('/sales', [ReportController::class, 'sales'])->name('sales');
            Route::get('/sales/export', [ReportController::class, 'exportSales'])->name('sales.export');
        });
    });
});

// HP scanner — tanpa auth, akses via token
Route::get('scanner/{token}', [ScannerController::class, 'mobile'])->name('scanner.mobile');
Route::post('scanner/{token}/push', [ScannerController::class, 'push'])->name('scanner.push');
Route::post('scanner/{token}/ping', [ScannerController::class, 'ping'])->name('scanner.ping');
Route::get('scanner/{token}/connected', [ScannerController::class, 'checkConnected'])->name('scanner.connected');

require __DIR__.'/auth.php';
