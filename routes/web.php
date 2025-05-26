<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\ProductRatingController;
use App\Http\Controllers\User\DashboardController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\User\ProfileController;
use App\Http\Controllers\Admin\EmployeeController;
use App\Http\Controllers\Employee\DashboardController as EmployeeDashboardController;
use App\Http\Controllers\Employee\TimeLogController;
use App\Http\Controllers\Employee\SalaryController;
use App\Http\Controllers\Admin\EmployeeTimeLogController;
use App\Http\Controllers\ReceiptController;
use App\Http\Controllers\Admin\InventoryController;
use App\Http\Controllers\Admin\OrderController as InventoryOrderController;
use Illuminate\Support\Facades\Auth;
// use App\Http\Controllers\Employee\KitchenInventoryOrderController;

// Public routes
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::post('/cart/add/{product}', [CartController::class, 'add'])->name('cart.add');

// Authentication routes
Auth::routes();

// Custom auth routes
Route::get('/password/reset', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
Route::post('/password/email', [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');
Route::get('/password/reset/{token}', [ResetPasswordController::class, 'showResetForm'])->name('password.reset');
Route::post('/password/reset', [ResetPasswordController::class, 'reset'])->name('password.update');

// Protected routes - require authentication
Route::middleware(['auth'])->group(function () {
    // Dashboard route
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // User dashboard routes
    Route::get('/dashboard/orders', [OrderController::class, 'index'])->name('dashboard.orders');
    Route::get('/dashboard/orders/{order}', [OrderController::class, 'show'])->name('dashboard.orders.show');
    
    // Profile management
    Route::get('/dashboard/profile', [ProfileController::class, 'edit'])->name('dashboard.profile');
    Route::post('/dashboard/profile', [ProfileController::class, 'update'])->name('dashboard.profile.update');
    Route::post('/dashboard/profile/password', [ProfileController::class, 'updatePassword'])->name('dashboard.profile.password');

    // Cart and checkout routes
    Route::post('/checkout/buy-now/{product}', [CartController::class, 'buyNow'])->name('checkout.buyNow');
    Route::get('/cart', [CartController::class, 'show'])->name('cart.show');
    Route::get('/checkout', [CartController::class, 'checkout'])->name('checkout');
    Route::delete('/cart/remove/{product}', [CartController::class, 'remove'])->name('cart.remove');
    Route::post('/checkout', [CartController::class, 'checkoutSubmit'])->name('checkout.submit');
    Route::get('/checkout/confirmation/{order}', [CartController::class, 'confirmation'])->name('checkout.confirmation');

    // Product rating
    Route::post('/products/{product}/rate', [ProductRatingController::class, 'store'])->name('products.rate');
});

// Admin routes
Route::middleware(['auth', 'is.admin'])->prefix('admin')->name('admin.')->group(function () {
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

        // Kitchen Inventory Orders
        // Route::prefix('kitchen')->group(function () {
        //     Route::get('orders', [KitchenInventoryOrderController::class, 'index'])->name('kitchen-inventory.orders');
        //     Route::get('orders/create', [KitchenInventoryOrderController::class, 'create'])->name('kitchen-inventory.orders.create');
        //     Route::post('orders', [KitchenInventoryOrderController::class, 'store'])->name('kitchen-inventory.orders.store');
        //     Route::get('orders/{id}', [KitchenInventoryOrderController::class, 'show'])->name('kitchen-inventory.orders.show');
        //     Route::get('orders/{id}/print', [KitchenInventoryOrderController::class, 'print'])->name('kitchen-inventory.orders.print');
        //     Route::post('orders/{id}/confirm', [KitchenInventoryOrderController::class, 'confirm'])->name('kitchen-inventory.orders.confirm');
        //     Route::post('orders/{id}/cancel', [KitchenInventoryOrderController::class, 'cancel'])->name('kitchen-inventory.orders.cancel');
        //     Route::get('orders/export', [KitchenInventoryOrderController::class, 'export'])->name('kitchen-inventory.orders.export');
        // });
    });
});

// Employee routes
Route::middleware(['auth', 'role:employee'])->prefix('employee')->name('employee.')->group(function () {
    Route::get('/dashboard', [EmployeeDashboardController::class, 'index'])->name('dashboard');
    Route::get('/time-logs', [TimeLogController::class, 'index'])->name('time-logs');
    Route::get('/salary', [SalaryController::class, 'index'])->name('salary');
});

// POS and Payment Manager routes - require admin or cashier role
Route::middleware(['auth', 'role:admin|cashier'])->group(function () {
    Route::get('/pos', function () {
        return view('pos');
    })->name('pos');

    Route::get('/payment-manager', function () {
        return view('payment-manager');
    })->name('payment-manager');

    // Order management routes
    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
    Route::post('/orders', [OrderController::class, 'store'])->name('orders.store');
    Route::post('/orders/{order}/pay', [OrderController::class, 'pay'])->name('orders.pay');
    Route::patch('/orders/{order}/payment', [OrderController::class, 'updatePaymentStatus'])->name('orders.update-payment');
    Route::get('/orders/{order}/receipt', [OrderController::class, 'receipt'])->name('orders.receipt');
    Route::get('/orders/{order}/kitchen-receipt', [OrderController::class, 'kitchenReceipt'])->name('orders.kitchen-receipt');
    Route::get('/orders/report', [OrderController::class, 'report'])->name('orders.report');
});

// Public product routes
Route::get('/products', [ProductController::class, 'index'])->name('products.index');
Route::get('/products/{product}', [ProductController::class, 'show'])->name('products.show');

// Include admin routes
require __DIR__.'/admin.php';

Route::get('/receipt/print/{id}', [ReceiptController::class, 'print'])->name('receipt.print');

Route::get('/menu', [ProductController::class, 'menu'])->name('menu');

// Test route for role middleware
Route::get('/test-role', function () {
    return 'Role middleware is working! You have admin access.';
})->middleware(['web', 'auth', 'role:admin']);
