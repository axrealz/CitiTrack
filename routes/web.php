<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;

/*
|--------------------------------------------------------------------------
| Redirect root to login
|--------------------------------------------------------------------------
*/
Route::get('/', function () {
    return redirect()->route('login');
});

/*
|--------------------------------------------------------------------------
| Public / Guest routes
|--------------------------------------------------------------------------
| Handled by Breeze (routes/auth.php) — login, forgot password, etc.
*/

/*
|--------------------------------------------------------------------------
| Shared authenticated routes (all roles)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->group(function () {

    // Dashboard dispatcher — sends each user to their own dashboard
    Route::get('/dashboard', function () {
        return match (auth()->user()->role) {
            'admin'   => redirect()->route('admin.dashboard'),
            'cashier' => redirect()->route('cashier.dashboard'),
            'stocker' => redirect()->route('stocker.dashboard'),
            default   => abort(403),
        };
    })->name('dashboard');

    // Shared profile routes (Breeze)
    Route::get('/profile',    [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile',  [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Change password (separate page — Tier 2 requirement)
    Route::get('/password',   [ProfileController::class, 'editPassword'])->name('password.edit');
    Route::put('/password',   [ProfileController::class, 'updatePassword'])->name('password.update.custom');
});

/*
|--------------------------------------------------------------------------
| ADMIN routes
|--------------------------------------------------------------------------
*/
Route::prefix('admin')
    ->name('admin.')
    ->middleware(['auth', 'role:admin'])
    ->group(function () {

    Route::get('/dashboard', [\App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('dashboard');

    /* ── Products ── */
    Route::resource('products', \App\Http\Controllers\Admin\ProductController::class);

    /* ── Categories ── */
    Route::resource('categories', \App\Http\Controllers\Admin\CategoryController::class)
        ->except(['show']);

    /* ── Suppliers ── */
    Route::resource('suppliers', \App\Http\Controllers\Admin\SupplierController::class);

    /* ── Users (staff management) ── */
    Route::resource('users', \App\Http\Controllers\Admin\UserController::class);

    /* ── Void requests ── */
    Route::get('/voids',                    [\App\Http\Controllers\Admin\VoidController::class, 'index'])->name('voids.index');
    Route::get('/voids/{void}',             [\App\Http\Controllers\Admin\VoidController::class, 'show'])->name('voids.show');
    Route::post('/voids/{void}/approve',    [\App\Http\Controllers\Admin\VoidController::class, 'approve'])->name('voids.approve');
    Route::post('/voids/{void}/reject',     [\App\Http\Controllers\Admin\VoidController::class, 'reject'])->name('voids.reject');

    /* ── Stock adjustment requests ── */
    Route::get('/adjustments',                    [\App\Http\Controllers\Admin\AdjustmentController::class, 'index'])->name('adjustments.index');
    Route::get('/adjustments/{adjustment}',       [\App\Http\Controllers\Admin\AdjustmentController::class, 'show'])->name('adjustments.show');
    Route::post('/adjustments/{adjustment}/approve',[\App\Http\Controllers\Admin\AdjustmentController::class, 'approve'])->name('adjustments.approve');
    Route::post('/adjustments/{adjustment}/reject', [\App\Http\Controllers\Admin\AdjustmentController::class, 'reject'])->name('adjustments.reject');

    /* ── Audit logs ── */
    Route::get('/audit-logs', [\App\Http\Controllers\Admin\AuditLogController::class, 'index'])->name('audit-logs.index');

    /* ── Reports ── */
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/',                  [\App\Http\Controllers\Admin\ReportController::class, 'index'])->name('index');
        Route::get('/products',          [\App\Http\Controllers\Admin\ReportController::class, 'products'])->name('products');
        Route::get('/staff',             [\App\Http\Controllers\Admin\ReportController::class, 'staff'])->name('staff');
        Route::get('/financial',         [\App\Http\Controllers\Admin\ReportController::class, 'financial'])->name('financial');
        Route::get('/daily-sales',       [\App\Http\Controllers\Admin\ReportController::class, 'dailySales'])->name('daily-sales');
        Route::get('/item-sales',        [\App\Http\Controllers\Admin\ReportController::class, 'itemSales'])->name('item-sales');
        Route::get('/inventory-summary', [\App\Http\Controllers\Admin\ReportController::class, 'inventorySummary'])->name('inventory-summary');
        Route::get('/low-stock',         [\App\Http\Controllers\Admin\ReportController::class, 'lowStock'])->name('low-stock');
    });

    /* ── Exports (CSV / PDF) ── */
    Route::prefix('export')->name('export.')->group(function () {
        Route::get('/sales',       [\App\Http\Controllers\Admin\ExportController::class, 'sales'])->name('sales');
        Route::get('/inventory',   [\App\Http\Controllers\Admin\ExportController::class, 'inventory'])->name('inventory');
        Route::get('/staff',       [\App\Http\Controllers\Admin\ExportController::class, 'staff'])->name('staff');
        Route::get('/financial',   [\App\Http\Controllers\Admin\ExportController::class, 'financial'])->name('financial');
    });
});

/*
|--------------------------------------------------------------------------
| CASHIER routes
|--------------------------------------------------------------------------
*/
Route::prefix('cashier')
    ->name('cashier.')
    ->middleware(['auth', 'role:cashier'])
    ->group(function () {

    Route::get('/dashboard', [\App\Http\Controllers\Cashier\DashboardController::class, 'index'])->name('dashboard');

    /* ── POS (session-based cart) ── */
    Route::get('/pos',                  [\App\Http\Controllers\Cashier\POSController::class, 'index'])->name('pos');
    Route::post('/pos/add/{product}',   [\App\Http\Controllers\Cashier\POSController::class, 'add'])->name('pos.add');
    Route::post('/pos/update/{product}',[\App\Http\Controllers\Cashier\POSController::class, 'update'])->name('pos.update');
    Route::post('/pos/remove/{product}',[\App\Http\Controllers\Cashier\POSController::class, 'remove'])->name('pos.remove');
    Route::post('/pos/clear',           [\App\Http\Controllers\Cashier\POSController::class, 'clear'])->name('pos.clear');
    Route::post('/pos/checkout',        [\App\Http\Controllers\Cashier\POSController::class, 'checkout'])->name('pos.checkout');

    /* ── Receipt ── */
    Route::get('/receipt/{transaction}', [\App\Http\Controllers\Cashier\ReceiptController::class, 'show'])->name('receipt');

    /* ── Returns ── */
    Route::get('/returns',                 [\App\Http\Controllers\Cashier\ReturnController::class, 'index'])->name('returns.index');
    Route::get('/returns/create/{transaction}', [\App\Http\Controllers\Cashier\ReturnController::class, 'create'])->name('returns.create');
    Route::post('/returns/{transaction}',  [\App\Http\Controllers\Cashier\ReturnController::class, 'store'])->name('returns.store');

    /* ── Void requests ── */
    Route::get('/voids',                    [\App\Http\Controllers\Cashier\VoidRequestController::class, 'index'])->name('voids.index');
    Route::get('/voids/create/{transaction}', [\App\Http\Controllers\Cashier\VoidRequestController::class, 'create'])->name('voids.create');
    Route::post('/voids/{transaction}',     [\App\Http\Controllers\Cashier\VoidRequestController::class, 'store'])->name('voids.store');

    /* ── Sales history ── */
    Route::get('/sales-history',       [\App\Http\Controllers\Cashier\SalesHistoryController::class, 'index'])->name('sales-history');
    Route::get('/sales-history/{transaction}', [\App\Http\Controllers\Cashier\SalesHistoryController::class, 'show'])->name('sales-history.show');
});

/*
|--------------------------------------------------------------------------
| STOCKER routes
|--------------------------------------------------------------------------
*/
Route::prefix('stocker')
    ->name('stocker.')
    ->middleware(['auth', 'role:stocker'])
    ->group(function () {

    Route::get('/dashboard', [\App\Http\Controllers\Stocker\DashboardController::class, 'index'])->name('dashboard');

    /* ── Stock-in ── */
    Route::get('/stock-in',  [\App\Http\Controllers\Stocker\StockInController::class, 'index'])->name('stock-in.index');
    Route::post('/stock-in', [\App\Http\Controllers\Stocker\StockInController::class, 'store'])->name('stock-in.store');

    /* ── Products (view + edit stock only) ── */
    Route::get('/products',           [\App\Http\Controllers\Stocker\ProductController::class, 'index'])->name('products.index');
    Route::get('/products/{product}', [\App\Http\Controllers\Stocker\ProductController::class, 'show'])->name('products.show');

    /* ── Low stock ── */
    Route::get('/low-stock', [\App\Http\Controllers\Stocker\LowStockController::class, 'index'])->name('low-stock');

    /* ── Adjustment requests ── */
    Route::get('/adjustments',             [\App\Http\Controllers\Stocker\AdjustmentRequestController::class, 'index'])->name('adjustments.index');
    Route::get('/adjustments/create',      [\App\Http\Controllers\Stocker\AdjustmentRequestController::class, 'create'])->name('adjustments.create');
    Route::post('/adjustments',            [\App\Http\Controllers\Stocker\AdjustmentRequestController::class, 'store'])->name('adjustments.store');

    /* ── Shipment logs ── */
    Route::get('/shipment-logs', [\App\Http\Controllers\Stocker\ShipmentLogController::class, 'index'])->name('shipment-logs');
});

/*
|--------------------------------------------------------------------------
| Breeze auth routes
|--------------------------------------------------------------------------
*/
require __DIR__.'/auth.php';