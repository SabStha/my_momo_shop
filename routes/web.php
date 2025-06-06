<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\User\DashboardController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\User\ProfileController;
use App\Http\Controllers\Employee\DashboardController as EmployeeDashboardController;
use App\Http\Controllers\Employee\TimeLogController;
use App\Http\Controllers\Employee\SalaryController;
use App\Http\Controllers\ReceiptController;
use App\Http\Controllers\Employee\EmployeeScheduleController;
use App\Http\Controllers\CreatorController;
use App\Http\Controllers\LeaderboardController;
use App\Http\Controllers\CreatorDashboardController;
use App\Http\Controllers\CouponController;
use App\Http\Controllers\Admin\InventoryController;
use App\Http\Controllers\Admin\AdminRoleController;
use App\Http\Controllers\Admin\SupplierController;
use App\Http\Controllers\Admin\InventoryCheckController;
use App\Http\Controllers\Admin\InventoryOrderController;
use App\Http\Controllers\Admin\SupplyOrderController;
use App\Http\Controllers\ReferralController;
use App\Http\Controllers\User\AccountController;
use App\Http\Controllers\Admin\WalletController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Admin\AdminProductController;
use App\Http\Controllers\Admin\AdminOrderController;
use App\Http\Controllers\Admin\AdminCouponController;
use App\Http\Controllers\Admin\AdminAnalyticsController;
use App\Http\Controllers\Admin\AdminReportController;
use App\Http\Controllers\Admin\AdminSettingsController;
use App\Http\Controllers\Admin\AdminPaymentController;
use App\Http\Controllers\Api\PosController;
use App\Http\Controllers\Api\PaymentController as ApiPaymentController;
use App\Http\Controllers\Api\OrderController as ApiOrderController;
use App\Http\Controllers\Api\ProductController as ApiProductController;
use App\Http\Controllers\Api\EmployeeController;
use App\Http\Controllers\Api\AnalyticsController as ApiAnalyticsController;
use App\Http\Controllers\Api\ReportController as ApiReportController;
use App\Http\Controllers\PosController as WebPosController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\TimeEntryController;
use App\Http\Controllers\EmployeeController as WebEmployeeController;
use App\Http\Controllers\InventoryController as WebInventoryController;
use App\Http\Controllers\ReportController as WebReportController;
use App\Http\Controllers\AnalyticsController as WebAnalyticsController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PosAuthController;
use App\Http\Controllers\Admin\PosAccessLogController;
use App\Http\Controllers\PaymentManagerAuthController;
use App\Http\Controllers\AccessVerificationController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\BulkController;
use Illuminate\Http\Request;
use App\Http\Controllers\Api\PosOrderController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Public routes
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/about', [HomeController::class, 'about'])->name('about');
Route::get('/contact', [HomeController::class, 'contact'])->name('contact');
Route::get('/terms', [HomeController::class, 'terms'])->name('terms');
Route::get('/privacy', [HomeController::class, 'privacy'])->name('privacy');
Route::get('/menu', [\App\Http\Controllers\MenuController::class, 'showMenu'])->name('menu');
Route::get('/bulk', [\App\Http\Controllers\BulkController::class, 'index'])->name('bulk');
Route::get('/finds', [\App\Http\Controllers\FindsController::class, 'index'])->name('finds');
Route::get('/leaderboard', [CreatorController::class, 'leaderboard'])->name('public.leaderboard');
Route::get('/offers', [HomeController::class, 'offers'])->name('offers');
Route::get('/search', [App\Http\Controllers\ProductController::class, 'search'])->name('search');
Route::get('/api/products/autocomplete', [App\Http\Controllers\ProductController::class, 'autocomplete'])->name('products.autocomplete');

// POS Login routes
Route::get('/pos-login', [PosAuthController::class, 'showLoginForm'])->name('pos.login');
Route::post('/pos-login', [PosAuthController::class, 'login'])->name('pos.login.submit');

// Product routes
Route::get('/products', [ProductController::class, 'index'])->name('products.index');
Route::get('/products/{product}', [ProductController::class, 'show'])->name('products.show');
Route::get('/products/category/{category}', [ProductController::class, 'category'])->name('products.category');
Route::get('/products/search', [ProductController::class, 'search'])->name('products.search');
Route::get('/products/{product}/qr', [ProductController::class, 'generateQRCode'])->name('products.qr');

// Authentication routes
Route::middleware(['guest'])->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
    Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('/register', [RegisterController::class, 'register']);
    Route::get('/password/reset', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
    Route::post('/password/email', [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');
    Route::get('/password/reset/{token}', [ResetPasswordController::class, 'showResetForm'])->name('password.reset');
    Route::post('/password/reset', [ResetPasswordController::class, 'reset'])->name('password.update');
});

// Logout route
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// Protected routes
Route::middleware(['auth'])->group(function () {
    // Account
    Route::get('/my-account', [\App\Http\Controllers\User\AccountController::class, 'index'])->name('account');

    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications');
    Route::post('/notifications/mark-as-read', [NotificationController::class, 'markAsRead'])->name('notifications.markAsRead');
    Route::post('/notifications/mark-all-as-read', [NotificationController::class, 'markAllAsRead'])->name('notifications.markAllAsRead');
    Route::delete('/notifications/{notification}', [NotificationController::class, 'destroy'])->name('notifications.destroy');

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::post('/profile/picture', [ProfileController::class, 'updatePicture'])->name('profile.picture');

    // Cart & Orders
    Route::get('/cart', [CartController::class, 'index'])->name('cart');
    Route::post('/cart/add/{product}', [CartController::class, 'add'])->name('cart.add');
    Route::delete('/cart/remove/{product}', [CartController::class, 'remove'])->name('cart.remove');
    Route::get('/orders', [OrderController::class, 'index'])->name('orders');
    Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');

    // Coupon Routes
    Route::post('/coupon/apply', [CouponController::class, 'apply'])->name('coupon.apply');
    Route::post('/coupon/remove', [CouponController::class, 'remove'])->name('coupon.remove');

    // Checkout Routes
    Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout');
    Route::post('/checkout/submit', [CheckoutController::class, 'submit'])->name('checkout.submit');
    Route::post('/checkout/process/{product}', [CheckoutController::class, 'process'])->name('checkout.process');
    Route::post('/checkout/{product}/quick', [CheckoutController::class, 'quickCheckout'])->name('checkout.quick');
    Route::get('/checkout/complete/{order}', [CheckoutController::class, 'complete'])->name('checkout.complete');
    Route::get('/checkout/thankyou', [CheckoutController::class, 'thankyou'])->name('checkout.thankyou');

    // POS routes - require POS access
    Route::middleware(['auth', 'pos.access'])->group(function () {
        Route::get('/pos', [WebPosController::class, 'index'])->name('pos');
        Route::get('/pos/tables', [WebPosController::class, 'tables'])->name('pos.tables');
        Route::get('/pos/orders', [WebPosController::class, 'orders'])->name('pos.orders');
        Route::get('/pos/payments', [WebPosController::class, 'payments'])->name('pos.payments');
    });

    // Employee routes
    Route::middleware(['role:employee|admin'])->group(function () {
        Route::get('/employee/dashboard', [WebEmployeeController::class, 'dashboard'])->name('employee.dashboard');
        Route::get('/employee/time-entries', [TimeEntryController::class, 'index'])->name('employee.time-entries');
        Route::post('/employee/clock-in', [TimeEntryController::class, 'clockIn'])->name('employee.clock-in');
        Route::post('/employee/clock-out', [TimeEntryController::class, 'clockOut'])->name('employee.clock-out');
    });

    // Manager routes (admin and main_manager)
    Route::middleware(['role:admin|main_manager'])->group(function () {
        Route::get('/manager/dashboard', [WebAnalyticsController::class, 'dashboard'])->name('manager.dashboard');
        Route::get('/manager/inventory', [WebInventoryController::class, 'index'])->name('manager.inventory');
        Route::get('/manager/reports', [WebReportController::class, 'index'])->name('manager.reports');
    });

    // Wallet Routes
    Route::get('/wallet', [WalletController::class, 'index'])->name('wallet');
    Route::get('/wallet/scan', [WalletController::class, 'scan'])->name('wallet.scan');
    Route::post('/wallet/top-up', [WalletController::class, 'topUp'])->name('wallet.top-up');
    Route::post('/wallet/process-code', [WalletController::class, 'processCode'])->name('wallet.process-code');
    Route::get('/wallet/transactions', [WalletController::class, 'transactions'])->name('wallet.transactions');
});

// Creator Routes
Route::prefix('creator')->name('creator.')->group(function () {
    Route::get('/register', [CreatorController::class, 'showRegistrationForm'])->name('register');
    Route::post('/register', [CreatorController::class, 'register'])->name('register.submit');
});

// Admin routes
Route::middleware(['auth:web', 'role:admin,web'])->group(function () {
    Route::get('/admin/dashboard', [App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('admin.dashboard');
    Route::get('/admin/orders', [App\Http\Controllers\Admin\AdminOrderController::class, 'index'])->name('admin.orders.index');
    Route::get('/admin/orders/{order}', [App\Http\Controllers\Admin\AdminOrderController::class, 'show'])->name('admin.orders.show');
    Route::get('/admin/products', [AdminProductController::class, 'index'])->name('admin.products.index');
    Route::get('/admin/payment-manager', [AdminPaymentController::class, 'index'])->name('admin.payment-manager');
    Route::get('/admin/pos-access-logs', [PosAccessLogController::class, 'index'])->name('admin.pos-access-logs');

    // Employee Schedules Routes
    Route::prefix('admin/employees/schedules')->name('admin.employee-schedules.')->group(function () {
        Route::get('/', [App\Http\Controllers\Admin\EmployeeScheduleController::class, 'index'])->name('index');
        Route::get('/create', [App\Http\Controllers\Admin\EmployeeScheduleController::class, 'create'])->name('create');
        Route::post('/', [App\Http\Controllers\Admin\EmployeeScheduleController::class, 'store'])->name('store');
        Route::get('/{schedule}/edit', [App\Http\Controllers\Admin\EmployeeScheduleController::class, 'edit'])->name('edit');
        Route::put('/{schedule}', [App\Http\Controllers\Admin\EmployeeScheduleController::class, 'update'])->name('update');
        Route::delete('/{schedule}', [App\Http\Controllers\Admin\EmployeeScheduleController::class, 'destroy'])->name('destroy');
        Route::get('/export', [App\Http\Controllers\Admin\EmployeeScheduleController::class, 'export'])->name('export');
    });

    // Inventory Management Routes
    Route::prefix('admin/inventory')->name('admin.inventory.')->group(function () {
        Route::get('/', [InventoryController::class, 'index'])->name('index');
        Route::get('/create', [InventoryController::class, 'create'])->name('create');
        Route::post('/', [InventoryController::class, 'store'])->name('store');
        Route::get('/categories', [InventoryController::class, 'categories'])->name('categories');
        Route::post('/categories', [InventoryController::class, 'storeCategory'])->name('store-category');
        Route::put('/categories/{category}', [InventoryController::class, 'updateCategory'])->name('update-category');
        Route::delete('/categories/{category}', [InventoryController::class, 'deleteCategory'])->name('delete-category');
        Route::get('/manage', [InventoryController::class, 'manage'])->name('manage');
        Route::get('/checks', [InventoryCheckController::class, 'index'])->name('checks.index');
        Route::post('/checks', [InventoryCheckController::class, 'store'])->name('checks.store');

        // Dynamic routes should be last
        Route::get('/{item}', [InventoryController::class, 'show'])->name('show');
        Route::get('/{item}/edit', [InventoryController::class, 'edit'])->name('edit');
        Route::put('/{item}', [InventoryController::class, 'update'])->name('update');
        Route::delete('/{item}', [InventoryController::class, 'destroy'])->name('destroy');
    });

    Route::get('/admin/roles', [AdminRoleController::class, 'index'])->name('admin.roles.index');
    Route::put('/admin/roles/{user}', [AdminRoleController::class, 'update'])->name('admin.roles.update');
    Route::get('/admin/wallet', [WalletController::class, 'index'])->name('admin.wallet.index');
    Route::get('/admin/wallet/manage', [WalletController::class, 'manage'])->name('admin.wallet.manage');
    Route::post('/admin/wallet/topup', [WalletController::class, 'topUp'])->name('admin.wallet.topup');
    Route::get('/admin/wallet/search', [WalletController::class, 'search'])->name('admin.wallet.search');
    Route::get('/admin/users', [AdminUserController::class, 'index'])->name('admin.users.index');
    Route::get('/admin/coupons', [AdminCouponController::class, 'index'])->name('admin.coupons.index');
    Route::get('/admin/analytics', [AdminAnalyticsController::class, 'index'])->name('admin.analytics.index');
    Route::get('/admin/reports', [AdminReportController::class, 'index'])->name('admin.reports.index');
    Route::get('/admin/settings', [AdminSettingsController::class, 'index'])->name('admin.settings.index');

    // Creator management routes
    Route::get('/admin/creator-dashboard', [CreatorDashboardController::class, 'index'])->name('admin.creator-dashboard.index');
    Route::get('/admin/creators', [CreatorController::class, 'index'])->name('admin.creators.index');
    Route::get('/admin/creators/{creator}', [CreatorController::class, 'show'])->name('admin.creators.show');
    Route::get('/admin/creators/{creator}/edit', [CreatorController::class, 'edit'])->name('admin.creators.edit');
    Route::put('/admin/creators/{creator}', [CreatorController::class, 'update'])->name('admin.creators.update');
    Route::delete('/admin/creators/{creator}', [CreatorController::class, 'destroy'])->name('admin.creators.destroy');

    // Add this line for admin employees resource
    Route::resource('admin/employees', App\Http\Controllers\Admin\EmployeeController::class)->names('admin.employees');

    // Add this line for admin clock index
    Route::get('/admin/clock', [App\Http\Controllers\Admin\AdminClockController::class, 'index'])->name('admin.clock.index');
    Route::get('/admin/clock/report', [App\Http\Controllers\Admin\AdminClockController::class, 'report'])->name('admin.clock.report');
    Route::post('/admin/clock/report/generate', [App\Http\Controllers\Admin\AdminClockController::class, 'generateReport'])->name('admin.clock.report.generate');
    Route::post('/admin/clock/update', [App\Http\Controllers\Admin\AdminClockController::class, 'update'])->name('admin.clock.update');
    Route::post('/admin/clock/action', [App\Http\Controllers\Admin\AdminClockController::class, 'handleAction'])->name('admin.clock.action');
    Route::get('/admin/clock/search', [App\Http\Controllers\Admin\AdminClockController::class, 'search'])->name('admin.clock.search');

    // Add this line for admin products resource
    Route::resource('admin/products', App\Http\Controllers\Admin\AdminProductController::class)->names('admin.products');

    // Add this line for admin suppliers resource
    Route::resource('admin/suppliers', App\Http\Controllers\Admin\SupplierController::class)->names('admin.suppliers');

    // Add this line for admin orders destroy
    Route::delete('/admin/orders/{order}', [App\Http\Controllers\Admin\AdminOrderController::class, 'destroy'])->name('admin.orders.destroy');

    // Add this line for admin employees schedules index
    Route::get('/admin/employees/schedules', [App\Http\Controllers\Admin\EmployeeScheduleController::class, 'index'])->name('admin.employees.schedules.index');
});

// API Routes
Route::prefix('api')->group(function () {
    // Public routes
    Route::post('/employee/verify', [EmployeeController::class, 'verify']);

    // Protected routes
    Route::middleware(['auth:sanctum'])->group(function () {
        // POS routes - require POS access
        Route::middleware(['pos.access'])->prefix('pos')->group(function () {
            // Products
            Route::get('/products', [ApiProductController::class, 'index']);
            Route::get('/products/{product}', [ApiProductController::class, 'show']);

            // Orders
            Route::get('/orders', [PosOrderController::class, 'index']);
            Route::post('/orders', [PosOrderController::class, 'store']);
            Route::get('/orders/{order}', [PosOrderController::class, 'show']);
            Route::put('/orders/{order}', [PosOrderController::class, 'update']);
            Route::put('/orders/{order}/status', [PosOrderController::class, 'updateStatus']);
            Route::delete('/orders/{order}', [PosOrderController::class, 'destroy']);

            // Payments
            Route::post('/payments', [ApiPaymentController::class, 'store']);
            Route::get('/payments/{payment}', [ApiPaymentController::class, 'show']);
        });

        // Admin only routes
        Route::middleware(['role:admin'])->prefix('admin')->group(function () {
            Route::get('/dashboard', [DashboardController::class, 'getDashboardData']);
            Route::get('/analytics/dashboard', [ApiAnalyticsController::class, 'getDashboardKPIs']);
        });

        // Manager routes (admin and main_manager)
        Route::middleware(['role:admin|main_manager'])->prefix('manager')->group(function () {
            Route::get('/reports', [ApiAnalyticsController::class, 'reports']);
            Route::get('/inventory', [ApiProductController::class, 'inventory']);
        });
    });
});
Route::get('/menu', [MenuController::class, 'showMenu'])->name('menu');
Route::get('/menu/featured', [MenuController::class, 'featured'])->name('menu.featured');

// Creator Dashboard Routes
Route::middleware(['auth', 'role:creator'])->group(function () {
    Route::get('/creator-dashboard', [CreatorDashboardController::class, 'index'])->name('creator.dashboard');
    Route::post('/creator-dashboard/update-profile-photo', [CreatorDashboardController::class, 'updateProfilePhoto'])->name('creator-dashboard.update-profile-photo');
});

// Admin Creator Dashboard Routes
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/admin/creator-dashboard', [CreatorDashboardController::class, 'index'])->name('admin.creator-dashboard.index');
});

// Payment Manager Login Routes
Route::get('/payment-manager/login', [PaymentManagerAuthController::class, 'showLoginForm'])->name('payment-manager.login');
Route::post('/payment-manager/login', [PaymentManagerAuthController::class, 'login'])->name('payment-manager.login.submit');
Route::post('/payment-manager/logout', [PaymentManagerAuthController::class, 'logout'])->name('payment-manager.logout');

// Payment Manager routes
Route::middleware(['auth', 'role:admin|cashier'])->group(function () {
    Route::get('/payment-manager', [AdminPaymentController::class, 'index'])->name('payment-manager');
    Route::post('/payment-manager/orders/{order}/process-payment', [AdminPaymentController::class, 'processPayment'])->name('payment-manager.process-payment');
    Route::post('/payment-manager/close-day', [AdminPaymentController::class, 'closeDay'])->name('payment-manager.close-day');
    Route::post('/payment-manager/start-new-day', [AdminPaymentController::class, 'startNewDay'])->name('payment-manager.start-new-day');
});
