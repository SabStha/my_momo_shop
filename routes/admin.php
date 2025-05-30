<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\AdminClockController;
use App\Http\Controllers\Admin\InventoryController;
use App\Http\Middleware\IsAdmin;

Route::middleware(['auth', IsAdmin::class])->prefix('admin')->group(function () {
    // Admin Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('admin.dashboard');
    
    // Product routes
    Route::get('/products', [ProductController::class, 'index'])->name('admin.products.index');
    Route::get('/products/create', [ProductController::class, 'create'])->name('admin.products.create');
    Route::post('/products', [ProductController::class, 'store'])->name('admin.products.store');
    Route::get('/products/{product}/edit', [ProductController::class, 'edit'])->name('admin.products.edit');
    Route::put('/products/{product}', [ProductController::class, 'update'])->name('admin.products.update');
    Route::delete('/products/{product}', [ProductController::class, 'destroy'])->name('admin.products.destroy');
    
    // Order routes (index, show, update, destroy)
    Route::get('/orders', [OrderController::class, 'index'])->name('admin.orders.index');
    Route::get('/orders/{order}', [OrderController::class, 'show'])->name('admin.orders.show');
    Route::put('/orders/{order}', [OrderController::class, 'update'])->name('admin.orders.update');
    Route::delete('/orders/{order}', [OrderController::class, 'destroy'])->name('admin.orders.destroy');

    // Admin Clock Routes
    Route::prefix('clock')->name('admin.clock.')->group(function () {
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
    })->name('admin.reports');

    Route::get('/simple-report', [DashboardController::class, 'simpleReport'])->name('admin.simple-report');

    Route::prefix('inventory')->name('admin.inventory.')->group(function () {
        Route::get('/', [InventoryController::class, 'dashboard'])->name('dashboard');
        Route::get('count', [InventoryController::class, 'count'])->name('count');
        Route::get('add', [InventoryController::class, 'add'])->name('add');
        Route::post('add', [InventoryController::class, 'store'])->name('store');
        Route::get('edit/{id}', [InventoryController::class, 'edit'])->name('edit');
        Route::put('edit/{id}', [InventoryController::class, 'update'])->name('update');
        // ... other inventory routes ...
    });
}); 