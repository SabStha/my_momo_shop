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
use App\Http\Controllers\User\OrderController;
use App\Http\Controllers\User\ProfileController;
use App\Http\Controllers\Admin\EmployeeController;
use App\Http\Controllers\Employee\DashboardController as EmployeeDashboardController;
use App\Http\Controllers\Employee\TimeLogController;
use App\Http\Controllers\Employee\SalaryController;
use App\Http\Controllers\Admin\EmployeeTimeLogController;

// Public routes
Route::get('/', [HomeController::class, 'index'])->name('home');

// Product routes - public
Route::get('/products', [ProductController::class, 'index'])->name('products.index');
Route::get('/products/{product}', [ProductController::class, 'show'])->name('products.show');
Route::post('/products/{product}/rate', [ProductRatingController::class, 'store'])->middleware('auth')->name('products.rate');

// Authentication routes
Auth::routes();

// Custom auth routes (only if needed to override default auth routes)
Route::get('/password/reset', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
Route::post('/password/email', [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');
Route::get('/password/reset/{token}', [ResetPasswordController::class, 'showResetForm'])->name('password.reset');
Route::post('/password/reset', [ResetPasswordController::class, 'reset'])->name('password.update');

// Cart and checkout routes
Route::post('/cart/add/{product}', [CartController::class, 'add'])->name('cart.add');
Route::post('/checkout/buy-now/{product}', [CartController::class, 'buyNow'])->name('checkout.buyNow');
Route::get('/cart', [CartController::class, 'show'])->name('cart.show');
Route::get('/checkout', [CartController::class, 'checkout'])->name('checkout');
Route::delete('/cart/remove/{product}', [CartController::class, 'remove'])->name('cart.remove');
Route::post('/checkout', [CartController::class, 'checkoutSubmit'])->name('checkout.submit');
Route::get('/checkout/confirmation/{order}', [CartController::class, 'confirmation'])->name('checkout.confirmation');

// User dashboard routes
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/orders', [OrderController::class, 'index'])->name('dashboard.orders');
    Route::get('/dashboard/orders/{order}', [OrderController::class, 'show'])->name('dashboard.orders.show');
    // Profile management
    Route::get('/dashboard/profile', [ProfileController::class, 'edit'])->name('dashboard.profile');
    Route::post('/dashboard/profile', [ProfileController::class, 'update'])->name('dashboard.profile.update');
    Route::post('/dashboard/profile/password', [ProfileController::class, 'updatePassword'])->name('dashboard.profile.password');
});

// Admin employee management routes
Route::middleware(['auth', 'is.admin'])->prefix('admin')->name('admin.')->group(function () {
    // Employee Management Routes
    Route::resource('employees', EmployeeController::class);
    
    // Employee Time Logs Routes
    Route::get('employees/{employee}/time-logs', [EmployeeTimeLogController::class, 'index'])->name('employees.time-logs.index');
    Route::put('employees/{employee}/time-logs/{timeLog}', [EmployeeTimeLogController::class, 'update'])->name('employees.time-logs.update');
});

// Employee routes
Route::middleware(['auth', 'role:employee'])->prefix('employee')->name('employee.')->group(function () {
    Route::get('/dashboard', [EmployeeDashboardController::class, 'index'])->name('dashboard');
    Route::get('/time-logs', [TimeLogController::class, 'index'])->name('time-logs');
    Route::get('/salary', [SalaryController::class, 'index'])->name('salary');
});

// Include admin routes
require __DIR__.'/admin.php';
