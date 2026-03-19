<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\InvestorController;

/*
|--------------------------------------------------------------------------
| Investor Portal Routes
|--------------------------------------------------------------------------
*/

// ── Investor self-service (role: admin | investor) ────────────────────── 
Route::middleware(['auth', 'role:admin|investor'])->prefix('investor')->name('investor.')->group(function () {
    Route::get('/dashboard/{investor?}', [App\Http\Controllers\Investor\DashboardController::class, 'dashboard'])->name('dashboard');
    Route::get('/investments', [App\Http\Controllers\Investor\DashboardController::class, 'investments'])->name('investments');
    Route::get('/payouts', [App\Http\Controllers\Investor\DashboardController::class, 'payouts'])->name('payouts');
    Route::get('/reports', [App\Http\Controllers\Investor\DashboardController::class, 'reports'])->name('reports');
    Route::get('/statement', [App\Http\Controllers\Investor\DashboardController::class, 'generateStatement'])->name('statement');
    Route::get('/profile', [App\Http\Controllers\Investor\DashboardController::class, 'profile'])->name('profile');
    Route::put('/profile', [App\Http\Controllers\Investor\DashboardController::class, 'updateProfile'])->name('profile.update');
});

// ── Admin investor dashboard ──────────────────────────────────────────── 
Route::middleware(['auth', 'role:admin|investor'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/investor-dashboard', [App\Http\Controllers\Admin\InvestorDashboardController::class, 'index'])->name('investor.dashboard');
});

// ── Admin investor CRUD (full management, role: admin) ────────────────── 
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::resource('investors', InvestorController::class)->names([
        'index'   => 'investors.index',
        'create'  => 'investors.create',
        'store'   => 'investors.store',
        'show'    => 'investors.show',
        'edit'    => 'investors.edit',
        'update'  => 'investors.update',
        'destroy' => 'investors.destroy',
    ]);
    Route::get('investors/{investor}/dashboard', [InvestorController::class, 'dashboard'])->name('investors.dashboard');
    Route::get('investors/{investor}/investments', [InvestorController::class, 'investments'])->name('investors.investments');
    Route::post('investors/{investor}/investments', [InvestorController::class, 'storeInvestment'])->name('investors.investments.store');
    Route::get('investors/{investor}/payouts', [InvestorController::class, 'payouts'])->name('investors.payouts');
    Route::post('investors/{investor}/payouts', [InvestorController::class, 'storePayout'])->name('investors.payouts.store');
    Route::get('investors/{investor}/reports', [InvestorController::class, 'reports'])->name('investors.reports');
    Route::post('investors/{investor}/reports', [InvestorController::class, 'generateReport'])->name('investors.reports.generate');
});

// ── Accounting (role: admin | investor) ───────────────────────────────── 
Route::middleware(['auth', 'role:admin|investor'])->prefix('accounting')->name('accounting.')->group(function () {
    Route::get('/dashboard', [App\Http\Controllers\AccountingController::class, 'dashboard'])->name('dashboard');
    Route::get('/spreadsheet', [App\Http\Controllers\AccountingController::class, 'spreadsheet'])->name('spreadsheet');
    Route::get('/', [App\Http\Controllers\AccountingController::class, 'index'])->name('index');
    Route::get('/create', [App\Http\Controllers\AccountingController::class, 'create'])->name('create');
    Route::post('/', [App\Http\Controllers\AccountingController::class, 'store'])->name('store');
    Route::get('/{expense}', [App\Http\Controllers\AccountingController::class, 'show'])->name('show');
    Route::get('/{expense}/edit', [App\Http\Controllers\AccountingController::class, 'edit'])->name('edit');
    Route::put('/{expense}', [App\Http\Controllers\AccountingController::class, 'update'])->name('update');
    Route::delete('/{expense}', [App\Http\Controllers\AccountingController::class, 'destroy'])->name('destroy');
    Route::post('/{expense}/approve', [App\Http\Controllers\AccountingController::class, 'approve'])->name('approve');
    Route::post('/{expense}/reject', [App\Http\Controllers\AccountingController::class, 'reject'])->name('reject');
});
