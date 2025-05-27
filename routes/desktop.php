<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\EmployeeController;
use App\Http\Controllers\Admin\EmployeeTimeLogController;
use App\Http\Controllers\Admin\InventoryController;
use App\Http\Controllers\Admin\OrderController as InventoryOrderController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;

// Desktop Admin routes
Route::middleware(['auth', 'is.admin'])->prefix('admin')->name('admin.')->group(function () {
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
        Route::get('/', [InventoryController::class, 'dashboard'])->name('inventory.dashboard');
        Route::get('count', [InventoryController::class, 'count'])->name('inventory.count');
        Route::post('count', [InventoryController::class, 'storeCount']);
        Route::get('forecast', [InventoryController::class, 'forecast'])->name('inventory.forecast');
        Route::post('forecast/update', [InventoryController::class, 'updateForecast'])->name('inventory.forecast.update');
        Route::post('forecast/override', [InventoryController::class, 'overrideForecast']);
        
        // Inventory Orders
        Route::get('orders', [InventoryOrderController::class, 'index'])->name('inventory.orders');
        Route::get('orders/create', [InventoryOrderController::class, 'create'])->name('inventory.orders.create');
        Route::post('orders', [InventoryOrderController::class, 'store'])->name('inventory.orders.store');
        Route::get('orders/{id}', [InventoryOrderController::class, 'show'])->name('inventory.orders.show');
        Route::post('orders/{id}/confirm', [InventoryOrderController::class, 'confirm'])->name('inventory.orders.confirm');
        Route::post('orders/{id}/cancel', [InventoryOrderController::class, 'cancel'])->name('inventory.orders.cancel');
        Route::get('orders/export', [InventoryOrderController::class, 'export'])->name('inventory.orders.export');
    });
}); 