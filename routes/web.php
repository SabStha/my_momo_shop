<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\CartController;

// Public routes
Route::get('/', [HomeController::class, 'index'])->name('home');

// Product routes - public
Route::get('/products', [ProductController::class, 'index'])->name('products.index');
Route::get('/products/{product}', [ProductController::class, 'show'])->name('products.show');

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

// Include admin routes
require __DIR__.'/admin.php';
