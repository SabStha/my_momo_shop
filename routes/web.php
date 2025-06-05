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
use App\Http\Controllers\Employee\DashboardController as EmployeeDashboardController;
use App\Http\Controllers\Employee\TimeLogController;
use App\Http\Controllers\Employee\SalaryController;
use App\Http\Controllers\ReceiptController;
use App\Http\Controllers\Employee\EmployeeScheduleController;
use App\Http\Controllers\ScheduleController;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\CreatorController;
use App\Http\Controllers\LeaderboardController;
use App\Http\Controllers\CreatorDashboardController;
use App\Http\Controllers\CouponController;
use App\Http\Controllers\TestController;
use App\Http\Controllers\CreatorRewardController;
use App\Http\Controllers\PayoutRequestController;
use App\Http\Controllers\AdminPayoutController;
use App\Http\Controllers\CreatorCouponController;
use App\Http\Controllers\Admin\InventoryController;
use App\Http\Controllers\Admin\AdminRoleController;
use App\Http\Controllers\CreatorCashoutController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PosAuthController;
use App\Http\Controllers\Admin\PosAccessLogController;
use App\Http\Controllers\PaymentManagerAuthController;
use App\Http\Controllers\AccessVerificationController;
use Illuminate\Http\Request;
use App\Http\Controllers\MenuController;


// Public routes
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/offers', [HomeController::class, 'offers'])->name('offers');
Route::get('/cart', [CartController::class, 'index'])->name('cart');
Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications');
Route::post('/cart/add/{product}', [CartController::class, 'add'])->name('cart.add');
Route::post('/clear-referral-discount', [HomeController::class, 'clearReferralDiscount'])->name('clear.referral.discount');

// Navigation routes
Route::get('/bulk-orders', [HomeController::class, 'bulkOrders'])->name('bulk.orders');
Route::get('/search', [HomeController::class, 'search'])->name('search');
Route::get('/account', [HomeController::class, 'account'])->name('account');

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
    Route::delete('/cart/remove/{product}', [CartController::class, 'remove'])->name('cart.remove');
    Route::get('/checkout', [CartController::class, 'checkout'])->name('checkout');
    Route::post('/checkout', [CartController::class, 'checkoutSubmit'])->name('checkout.submit');
    Route::get('/checkout/confirmation/{order}', [CartController::class, 'confirmation'])->name('checkout.confirmation');

    // Product rating
    Route::post('/products/{product}/rate', [ProductRatingController::class, 'store'])->name('products.rate');

    // Employee Schedule Management
    Route::get('/schedules', [EmployeeScheduleController::class, 'index'])->name('employee-schedules.index');
    Route::post('/schedules', [EmployeeScheduleController::class, 'store'])->name('employee-schedules.store');
    Route::put('/schedules/{employeeSchedule}', [EmployeeScheduleController::class, 'update'])->name('employee-schedules.update');
    Route::delete('/schedules/{employeeSchedule}', [EmployeeScheduleController::class, 'destroy'])->name('employee-schedules.destroy');
    Route::get('/schedules/export', [EmployeeScheduleController::class, 'export'])->name('employee-schedules.export');

    // Creator Rewards Routes
    Route::get('/creator/rewards', [CreatorRewardController::class, 'index'])->name('creator.rewards.index');
    Route::post('/creator/rewards/{id}/claim', [CreatorRewardController::class, 'claim'])->name('creator.rewards.claim');
});

// Creator Payout Routes
Route::middleware(['auth'])->group(function () {
    Route::get('/creator/payouts', [PayoutRequestController::class, 'index'])->name('creator.payouts.index');
    Route::post('/creator/payouts/request', [PayoutRequestController::class, 'requestPayout'])->name('creator.payouts.request');
});

// Employee routes
Route::middleware(['auth', 'role:employee'])->prefix('employee')->name('employee.')->group(function () {
    Route::get('/dashboard', [EmployeeDashboardController::class, 'index'])->name('dashboard');
    Route::get('/time-logs', [TimeLogController::class, 'index'])->name('time-logs');
    Route::get('/salary', [SalaryController::class, 'index'])->name('salary');
});

// POS Authentication Routes
Route::middleware(['auth', 'role:admin|cashier|employee'])->group(function () {
    Route::get('/pos/login', [PosAuthController::class, 'showLoginForm'])->name('pos.login');
    Route::post('/pos/login', [PosAuthController::class, 'login'])->name('pos.login.submit');
    Route::post('/pos/logout', [PosAuthController::class, 'logout'])->name('pos.logout');
});

// Payment Manager Authentication Routes
Route::middleware(['auth', 'role:admin|cashier'])->group(function () {
    Route::get('/payment-manager/login', [PaymentManagerAuthController::class, 'showLoginForm'])->name('payment-manager.login');
    Route::post('/payment-manager/login', [PaymentManagerAuthController::class, 'login'])->name('payment-manager.login.submit');
    Route::post('/payment-manager/logout', [PaymentManagerAuthController::class, 'logout'])->name('payment-manager.logout');
});

// POS routes - require admin, cashier, or employee role and POS verification
Route::middleware(['auth', 'role:admin|cashier|employee'])->group(function () {
    Route::get('/pos', function () {
        if (!session()->has('pos_verified')) {
            return redirect()->route('pos.login');
        }
        return view('desktop.admin.pos');
    })->name('pos');

    // Order management routes for POS
    Route::get('/pos/orders', [OrderController::class, 'index'])->name('pos.orders.index');
    Route::get('/pos/orders/{order}', [OrderController::class, 'show'])->name('pos.orders.show');
    Route::post('/pos/orders', [OrderController::class, 'store'])->name('pos.orders.store');
    Route::post('/pos/orders/{order}/pay', [OrderController::class, 'pay'])->name('pos.orders.pay');
    Route::patch('/pos/orders/{order}/payment', [OrderController::class, 'updatePaymentStatus'])->name('pos.orders.update-payment');
    Route::get('/pos/orders/{order}/receipt', [OrderController::class, 'receipt'])->name('pos.orders.receipt');
    Route::get('/pos/orders/{order}/kitchen-receipt', [OrderController::class, 'kitchenReceipt'])->name('pos.orders.kitchen-receipt');
    Route::get('/pos/orders/report', [OrderController::class, 'report'])->name('pos.orders.report');
});

// Payment Manager routes - require admin or cashier role and Payment Manager verification
Route::middleware(['auth', 'role:admin|cashier'])->group(function () {
    Route::get('/payment-manager', function () {
        if (!session()->has('payment_verified')) {
            return redirect()->route('payment-manager.login');
        }
        return view('desktop.admin.payment-manager');
    })->name('payment-manager');

    // Payment management routes
    Route::get('/payment-manager/orders', [OrderController::class, 'index'])->name('payment-manager.orders.index');
    Route::get('/payment-manager/orders/{order}', [OrderController::class, 'show'])->name('payment-manager.orders.show');
    Route::post('/payment-manager/orders/{order}/pay', [OrderController::class, 'pay'])->name('payment-manager.orders.pay');
    Route::patch('/payment-manager/orders/{order}/payment', [OrderController::class, 'updatePaymentStatus'])->name('payment-manager.orders.update-payment');
});

// Public product routes
Route::get('/products', [ProductController::class, 'index'])->name('products.index');
Route::get('/products/{product}', [ProductController::class, 'show'])->name('products.show');
Route::get('/products/{product}/edit', [ProductController::class, 'edit'])->name('products.edit');
Route::delete('/products/{product}', [ProductController::class, 'destroy'])->name('products.destroy');

Route::get('/receipt/print/{id}', [ReceiptController::class, 'print'])->name('receipt.print');
Route::get('/menu', [\App\Http\Controllers\MenuController::class, 'showMenu'])->name('menu');

// Test route for role middleware
Route::get('/test-role', function () {
    return 'Role middleware is working! You have admin access.';
})->middleware(['web', 'auth', 'role:admin']);

Route::resource('schedules', ScheduleController::class);

// Mobile routes
Route::get('/mobile', function () {
    return view('mobile.home');
});

// Public leaderboard route
Route::get('/leaderboard', [CreatorController::class, 'leaderboard'])->name('public.leaderboard');

// Admin-only creators management
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/creators', [CreatorController::class, 'index'])->name('creators.index');
    Route::get('/creators/create', [CreatorController::class, 'create'])->name('creators.create');
    Route::post('/creators', [CreatorController::class, 'store'])->name('creators.store');
    Route::get('/creators/{code}', [CreatorController::class, 'show'])->name('creators.show');
    // ... other admin creator management routes ...
});

// Creator-only dashboard and leaderboard
Route::middleware(['auth', 'role:creator'])->group(function () {
    Route::get('/creator-dashboard', [CreatorDashboardController::class, 'index'])->name('creator-dashboard.index');
    Route::post('/creator-dashboard/generate-referral', [CreatorDashboardController::class, 'generateReferral'])->name('creator-dashboard.generate-referral');
    Route::get('/creator-leaderboard', [CreatorController::class, 'creatorLeaderboard'])->name('creator.leaderboard');
    Route::post('/update-profile-photo', [CreatorDashboardController::class, 'updateProfilePhoto'])->name('creator-dashboard.update-profile-photo');
    Route::get('/logout', [CreatorDashboardController::class, 'logout'])->name('creator-dashboard.logout');
    Route::get('/home', [CreatorDashboardController::class, 'home'])->name('creator-dashboard.home');
});

Route::post('/coupon/apply', [CouponController::class, 'apply'])->name('coupon.apply');

// Test Panel Routes
Route::view('/test-panel', 'test.devpanel')->name('test.panel');
Route::post('/assign-monthly-rewards', [TestController::class, 'assignMonthlyRewards'])->name('test.assign-monthly-rewards');

// Admin Payout Routes
Route::middleware(['auth', 'role:admin'])->prefix('admin/payouts')->name('admin.payouts.')->group(function () {
    Route::get('/', [AdminPayoutController::class, 'index'])->name('index');
    Route::post('/{payout}/approve', [AdminPayoutController::class, 'approve'])->name('approve');
    Route::post('/{payout}/reject', [AdminPayoutController::class, 'reject'])->name('reject');
    Route::post('/{payout}/mark-paid', [AdminPayoutController::class, 'markAsPaid'])->name('mark-paid');
});

// Admin Coupon Routes
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/admin/coupons/create', [CouponController::class, 'create'])->name('admin.coupons.create');
    Route::post('/admin/coupons', [CouponController::class, 'store'])->name('admin.coupons.store');
    
    // Admin Inventory Routes
    Route::prefix('admin/inventory')->name('admin.inventory.')->group(function () {
        Route::get('/', [InventoryController::class, 'index'])->name('index');
        Route::get('/dashboard', [InventoryController::class, 'index'])->name('dashboard');
        Route::get('/{id}/edit', [InventoryController::class, 'edit'])->name('edit');
        Route::put('/{id}', [InventoryController::class, 'update'])->name('update');
        Route::delete('/{id}', [InventoryController::class, 'destroy'])->name('destroy');
        Route::post('/forecast/update', [InventoryController::class, 'updateForecast'])->name('forecast.update');
        Route::post('/order/create', [InventoryController::class, 'createOrder'])->name('order.create');
    });

    // Admin Role Routes
    Route::get('/admin/roles', [AdminRoleController::class, 'index'])->name('roles.index');
    Route::post('/admin/roles/{user}', [AdminRoleController::class, 'update'])->name('roles.update');
});

// Creator Coupon Routes
Route::middleware(['auth', 'is_creator'])->group(function () {
    Route::match(['get', 'post'], '/creator/coupons/generate', [CreatorCouponController::class, 'generate'])->name('creator.coupons.generate');
});

// Creator Registration Routes
Route::middleware(['guest'])->group(function () {
    Route::get('/creator/register', [CreatorController::class, 'showRegistrationForm'])->name('creator.register');
    Route::post('/creator/register', [CreatorController::class, 'register'])->name('creator.register.submit');
});

// Creator Cashout Routes
Route::middleware(['auth', 'role:creator'])->prefix('creator')->name('creator.')->group(function () {
    Route::get('/cashouts', [\App\Http\Controllers\CreatorCashoutController::class, 'index'])->name('cashouts.index');
    Route::get('/cashouts/create', [\App\Http\Controllers\CreatorCashoutController::class, 'create'])->name('cashouts.create');
    Route::post('/cashouts', [\App\Http\Controllers\CreatorCashoutController::class, 'store'])->name('cashouts.store');
});

// Admin Cashout Routes
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/cashouts', [\App\Http\Controllers\CreatorCashoutController::class, 'adminIndex'])->name('cashouts.index');
    Route::post('/cashouts/{id}/status', [\App\Http\Controllers\CreatorCashoutController::class, 'updateStatus'])->name('cashouts.updateStatus');
});

// Creator Dashboard Routes
Route::middleware(['auth', 'role:creator'])->prefix('creator-dashboard')->name('creator-dashboard.')->group(function () {
    Route::get('/', [CreatorDashboardController::class, 'index'])->name('index');
    Route::post('/update-profile-photo', [CreatorDashboardController::class, 'updateProfilePhoto'])->name('update-profile-photo');
    Route::get('/logout', [CreatorDashboardController::class, 'logout'])->name('logout');
});

Route::post('/cart/update/{id}', [CartController::class, 'update'])->name('cart.update');
Route::post('/cart/remove/{id}', [CartController::class, 'remove'])->name('cart.remove');

// Admin Access Logs Route
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/admin/pos-access-logs', [PosAccessLogController::class, 'index'])->name('admin.pos-access-logs');
});
Route::get('/menu', [MenuController::class, 'showMenu'])->name('menu');
Route::get('/menu/featured', [MenuController::class, 'featured'])->name('menu.featured');

