<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\InventoryController;
use App\Http\Controllers\Admin\InventoryCheckController;
use App\Http\Controllers\Admin\InventoryOrderController;

Route::middleware(['auth', 'role:admin'])
    ->prefix('admin/inventory')
    ->name('admin.inventory.')
    ->group(function () {
        // Categories
        Route::get('/categories', [InventoryController::class, 'categories'])->name('categories');
        Route::post('/categories/store', [InventoryController::class, 'storeCategory'])->name('store-category');
        Route::put('/categories/{category}', [InventoryController::class, 'updateCategory'])->name('update-category');
        Route::delete('/categories/{category}', [InventoryController::class, 'deleteCategory'])->name('delete-category');

        // Stock Checks
        Route::get('/checks', [InventoryCheckController::class, 'index'])->name('checks.index');
        Route::post('/checks', [InventoryCheckController::class, 'store'])->name('checks.store');

        // Inventory Items - CRUD
        Route::get('/', [InventoryController::class, 'index'])->name('index');
        Route::get('/create', [InventoryController::class, 'create'])->name('create');
        Route::post('/', [InventoryController::class, 'store'])->name('store');
        Route::get('/{item}/edit', [InventoryController::class, 'edit'])->name('edit');
        Route::put('/{item}', [InventoryController::class, 'update'])->name('update');
        Route::delete('/{item}', [InventoryController::class, 'destroy'])->name('destroy');
        Route::get('/{item}', [InventoryController::class, 'show'])->name('show');
        Route::post('/{item}/adjust', [InventoryController::class, 'adjust'])->name('adjust');
        Route::get('/count', [InventoryController::class, 'count'])->name('count');
        Route::get('/forecast', [InventoryController::class, 'forecast'])->name('forecast');
    })->where(['item' => '[0-9]+']);

// Inventory Orders
Route::middleware(['auth', 'role:admin'])
    ->prefix('admin/inventory/orders')
    ->name('admin.inventory.orders.')
    ->group(function () {
        Route::get('/', [InventoryOrderController::class, 'index'])->name('index');
        Route::get('/create', [InventoryOrderController::class, 'create'])->name('create');
        Route::post('/', [InventoryOrderController::class, 'store'])->name('store');
        Route::get('/{order}', [InventoryOrderController::class, 'show'])->name('show');
        Route::get('/{order}/edit', [InventoryOrderController::class, 'edit'])->name('edit');
        Route::put('/{order}', [InventoryOrderController::class, 'update'])->name('update');
        Route::delete('/{order}', [InventoryOrderController::class, 'destroy'])->name('destroy');
        Route::post('/{order}/confirm', [InventoryOrderController::class, 'confirm'])->name('confirm');
        Route::post('/{order}/cancel', [InventoryOrderController::class, 'cancel'])->name('cancel');
        Route::get('/export', [InventoryOrderController::class, 'export'])->name('export');
    }); 