<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\AdminClockController;
use App\Http\Controllers\Admin\SupplyOrderController;

// Routes are already prefixed with 'admin' and have 'admin.' name prefix in RouteServiceProvider

// Admin Dashboard
Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

// Product routes
Route::get('/products', [ProductController::class, 'index'])->name('products.index');
Route::get('/products/create', [ProductController::class, 'create'])->name('products.create');
Route::post('/products', [ProductController::class, 'store'])->name('products.store');
Route::get('/products/{product}/edit', [ProductController::class, 'edit'])->name('products.edit');
Route::put('/products/{product}', [ProductController::class, 'update'])->name('products.update');
Route::delete('/products/{product}', [ProductController::class, 'destroy'])->name('products.destroy');

// Admin Clock Routes
Route::prefix('clock')->name('clock.')->group(function () {
    Route::get('/', [AdminClockController::class, 'index'])->name('index');
    Route::get('/report', [AdminClockController::class, 'report'])->name('report');
    Route::post('/search', [AdminClockController::class, 'search'])->name('search');
    Route::post('/in', [AdminClockController::class, 'clockIn'])->name('in');
    Route::post('/out', [AdminClockController::class, 'clockOut'])->name('out');
    Route::post('/break/start', [AdminClockController::class, 'startBreak'])->name('break.start');
    Route::post('/break/end', [AdminClockController::class, 'endBreak'])->name('break.end');
    Route::put('/{timeLog}', [AdminClockController::class, 'edit'])->name('edit');
});

Route::get('/reports', function() {
    return view('admin.reports');
})->name('reports');

Route::get('/simple-report', [DashboardController::class, 'simpleReport'])->name('simple-report');

// Supply Orders Routes
Route::prefix('supply')->name('supply.')->group(function () {
    Route::get('orders', [SupplyOrderController::class, 'index'])->name('orders.index');
    Route::get('orders/create', [SupplyOrderController::class, 'create'])->name('orders.create');
    Route::post('orders', [SupplyOrderController::class, 'store'])->name('orders.store');
    Route::get('orders/{order}', [SupplyOrderController::class, 'show'])->name('orders.show')->where('order', '[0-9]+');
    Route::get('orders/{order}/edit', [SupplyOrderController::class, 'edit'])->name('orders.edit')->where('order', '[0-9]+');
    Route::put('orders/{order}', [SupplyOrderController::class, 'update'])->name('orders.update')->where('order', '[0-9]+');
    Route::delete('orders/{order}', [SupplyOrderController::class, 'destroy'])->name('orders.destroy')->where('order', '[0-9]+');
});