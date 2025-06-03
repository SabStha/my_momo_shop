<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\EmployeeController;
use App\Http\Controllers\Admin\EmployeeTimeLogController;
use App\Http\Controllers\Admin\InventoryController;
use App\Http\Controllers\Admin\OrderController as InventoryOrderController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\User\DashboardController;
use App\Http\Controllers\User\ProfileController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\Admin\AdminClockController;

// Desktop Admin routes
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    // Admin Dashboard
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
    Route::get('/reports', [AdminDashboardController::class, 'reports'])->name('reports');
    Route::get('/simple-report', [AdminDashboardController::class, 'simpleReport'])->name('simple-report');

    // Employee Management
    Route::resource('employees', EmployeeController::class);
    Route::get('employees/{employee}/time-logs', [EmployeeTimeLogController::class, 'index'])->name('employees.time-logs.index');
    Route::put('employees/{employee}/time-logs/{timeLog}', [EmployeeTimeLogController::class, 'update'])->name('employees.time-logs.update');

    // Inventory Management
    Route::prefix('inventory')->group(function () {
        Route::get('/', [InventoryController::class, 'index'])->name('inventory.index');
        Route::get('count', [InventoryController::class, 'count'])->name('inventory.count');
        Route::post('count', [InventoryController::class, 'storeCount']);
        Route::get('forecast', [InventoryController::class, 'forecast'])->name('inventory.forecast');
        Route::post('forecast/update', [InventoryController::class, 'updateForecast'])->name('inventory.forecast.update');
        Route::post('forecast/override', [InventoryController::class, 'overrideForecast']);
        Route::get('edit/{id}', [InventoryController::class, 'edit'])->name('inventory.edit');
        Route::get('add', [InventoryController::class, 'create'])->name('inventory.add');
        
        // Inventory Orders
        // Route::get('orders', [InventoryOrderController::class, 'index'])->name('inventory.orders');
        // Route::get('orders/create', [InventoryOrderController::class, 'create'])->name('inventory.orders.create');
        // Route::post('orders', [InventoryOrderController::class, 'store'])->name('inventory.orders.store');
        // Route::get('orders/{id}', [InventoryOrderController::class, 'show'])->name('inventory.orders.show');
        // Route::post('orders/{id}/confirm', [InventoryOrderController::class, 'confirm'])->name('inventory.orders.confirm');
        // Route::post('orders/{id}/cancel', [InventoryOrderController::class, 'cancel'])->name('inventory.orders.cancel');
        // Route::get('orders/export', [InventoryOrderController::class, 'export'])->name('inventory.orders.export');
    });

    // Product Management
    Route::resource('products', ProductController::class);

    // Orders
    // Route::resource('orders', \App\Http\Controllers\Admin\OrderController::class);

    // Clock
    Route::get('clock', [AdminClockController::class, 'index'])->name('clock.index');
    Route::get('clock/report', [AdminClockController::class, 'report'])->name('clock.report');
    Route::post('clock/search', [AdminClockController::class, 'search'])->name('clock.search');
    Route::post('clock/in', [AdminClockController::class, 'clockIn'])->name('clock.in');
    Route::post('clock/out', [AdminClockController::class, 'clockOut'])->name('clock.out');
    Route::post('clock/break/start', [AdminClockController::class, 'startBreak'])->name('clock.break.start');
    Route::post('clock/break/end', [AdminClockController::class, 'endBreak'])->name('clock.break.end');
});

// Desktop User routes
Route::middleware(['auth'])->prefix('desktop')->name('desktop.')->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile');
    Route::post('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::post('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');
    
    // Orders
    Route::get('/orders', [OrderController::class, 'index'])->name('orders');
    Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
    
    // Cart
    Route::get('/cart', [CartController::class, 'show'])->name('cart');
    Route::post('/cart/add/{product}', [CartController::class, 'add'])->name('cart.add');
    Route::delete('/cart/remove/{product}', [CartController::class, 'remove'])->name('cart.remove');
    
    // Schedules
    Route::resource('schedules', ScheduleController::class);
});

Route::get('/admin/clock', [AdminClockController::class, 'index'])->name('admin.clock.index'); 