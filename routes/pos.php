<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PosController as WebPosController;
use App\Http\Controllers\PosAuthController;
use App\Http\Controllers\TimeEntryController;
use App\Http\Controllers\Admin\EmployeeController as WebEmployeeController;
use App\Http\Controllers\AnalyticsController as WebAnalyticsController;
use App\Http\Controllers\InventoryController as WebInventoryController;
use App\Http\Controllers\ReportController as WebReportController;

/*
|--------------------------------------------------------------------------
| Employee / POS Routes
|--------------------------------------------------------------------------
*/

// ── POS Interface (auth + POS access) ─────────────────────────────────── 
Route::middleware(['auth', 'pos.access'])->group(function () {
    Route::get('/pos', [WebPosController::class, 'index'])->name('pos');
    Route::get('/pos/tables', [WebPosController::class, 'tables'])->name('pos.tables');
    Route::get('/pos/orders', [WebPosController::class, 'orders'])->name('pos.orders');
    Route::get('/pos/payments', [WebPosController::class, 'payments'])->name('pos.payments');
    Route::post('/pos/verify-token', [App\Http\Controllers\Api\PosController::class, 'verifyToken'])->name('pos.verify-token');
});

// ── POS API (web session authenticated) ───────────────────────────────── 
Route::middleware(['auth', 'pos.access'])->prefix('api/pos')->group(function () {
    Route::get('/products', [App\Http\Controllers\Api\PosController::class, 'products']);
    Route::get('/tables', [App\Http\Controllers\Api\PosController::class, 'tables']);
    Route::get('/user-info', [App\Http\Controllers\Api\PosController::class, 'userInfo']);
    Route::get('/orders', [App\Http\Controllers\Api\PosOrderController::class, 'index']);
    Route::post('/pos-orders', [App\Http\Controllers\Api\PosOrderController::class, 'store']);
    Route::get('/orders/{order}', [App\Http\Controllers\Api\PosOrderController::class, 'show']);
    Route::put('/orders/{order}', [App\Http\Controllers\Api\PosOrderController::class, 'update']);
    Route::delete('/orders/{order}', [App\Http\Controllers\Api\PosOrderController::class, 'destroy']);
    Route::post('/payments', [App\Http\Controllers\Api\PosPaymentController::class, 'store']);
});

// ── Employee clock-in / clock-out (role: employee|admin) ─────────────── 
Route::middleware(['auth', 'role:employee|admin'])->group(function () {
    Route::get('/employee/dashboard', [WebEmployeeController::class, 'dashboard'])->name('employee.dashboard');
    Route::get('/employee/time-entries', [TimeEntryController::class, 'index'])->name('employee.time-entries');
    Route::post('/employee/clock-in', [TimeEntryController::class, 'clockIn'])->name('employee.clock-in');
    Route::post('/employee/clock-out', [TimeEntryController::class, 'clockOut'])->name('employee.clock-out');

    // Employee wallet scanner (top-up on behalf of customer)
    Route::get('/employee/wallet/scanner', [App\Http\Controllers\Employee\WalletTopUpController::class, 'scanner'])->name('employee.wallet.scanner');
    Route::post('/employee/wallet/process-qr', [App\Http\Controllers\Employee\WalletTopUpController::class, 'processQR'])->name('employee.wallet.process-qr');
    Route::post('/employee/wallet/topup', [App\Http\Controllers\Employee\WalletTopUpController::class, 'topUp'])->name('employee.wallet.topup');
    Route::post('/employee/wallet/balance-by-barcode', [App\Http\Controllers\Employee\WalletTopUpController::class, 'getBalanceByBarcode'])->name('employee.wallet.balance-by-barcode');

    // Employee credits scanner
    Route::get('/employee/credits/scanner', [App\Http\Controllers\Employee\CreditsTopUpController::class, 'scanner'])->name('employee.credits.scanner');
    Route::post('/employee/credits/process-qr', [App\Http\Controllers\Employee\CreditsTopUpController::class, 'processQR'])->name('employee.credits.process-qr');
    Route::post('/employee/credits/topup', [App\Http\Controllers\Employee\CreditsTopUpController::class, 'topUp'])->name('employee.credits.topup');
    Route::post('/employee/credits/balance-by-barcode', [App\Http\Controllers\Employee\CreditsTopUpController::class, 'balanceByBarcode'])->name('employee.credits.balance-by-barcode');
});

// ── Manager routes (admin | main_manager) ─────────────────────────────── 
Route::middleware(['auth', 'role:admin|main_manager'])->group(function () {
    Route::get('/manager/dashboard', [WebAnalyticsController::class, 'dashboard'])->name('manager.dashboard');
    Route::get('/manager/inventory', [WebInventoryController::class, 'index'])->name('manager.inventory');
    Route::get('/manager/reports', [WebReportController::class, 'index'])->name('manager.reports');
});

// ── API routes (Sanctum) ──────────────────────────────────────────────── 
Route::prefix('api')->group(function () {
    Route::post('/employee/verify', [WebEmployeeController::class, 'verify']);

    Route::middleware(['auth:sanctum'])->group(function () {
        Route::middleware(['role:admin'])->prefix('admin')->group(function () {
            Route::get('/dashboard', [App\Http\Controllers\User\DashboardController::class, 'getDashboardData']);
            Route::get('/analytics/dashboard', [App\Http\Controllers\Api\AnalyticsController::class, 'getDashboardKPIs']);
        });
        Route::middleware(['role:admin|main_manager'])->prefix('manager')->group(function () {
            Route::get('/reports', [App\Http\Controllers\Api\AnalyticsController::class, 'reports']);
            Route::get('/inventory', [App\Http\Controllers\Api\ProductController::class, 'inventory']);
        });
    });
});

Route::get('/api/admin/cash-drawer/status', [App\Http\Controllers\Admin\CashDrawerController::class, 'getStatus']);
