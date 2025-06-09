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
use App\Http\Controllers\Admin\AdminAnalyticsController;
use App\Http\Controllers\Admin\AdminReportController;
use App\Http\Controllers\Admin\AdminSettingsController;
use App\Http\Controllers\Admin\AdminPaymentController;
use App\Http\Controllers\Api\PosController;
use App\Http\Controllers\Api\PaymentController as ApiPaymentController;
use App\Http\Controllers\Api\OrderController as ApiOrderController;
use App\Http\Controllers\Api\ProductController as ApiProductController;
use App\Http\Controllers\Api\AnalyticsController as ApiAnalyticsController;
use App\Http\Controllers\Api\ReportController as ApiReportController;
use App\Http\Controllers\PosController as WebPosController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\TimeEntryController;
use App\Http\Controllers\Admin\EmployeeController as WebEmployeeController;
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
use App\Http\Controllers\UserController;
use App\Http\Controllers\Admin\WalletTopUpController;
use App\Http\Controllers\Admin\BranchController;
use App\Http\Controllers\Admin\BranchProductController;
use App\Http\Controllers\Admin\TableController;
use App\Http\Controllers\Admin\BranchWalletController;
use App\Http\Controllers\Admin\BranchReportController;
use App\Http\Controllers\Admin\BranchAnalyticsController;

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
Route::prefix('admin')->middleware(['auth', 'role:admin'])->name('admin.')->group(function () {
    Route::get('/dashboard', [App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('dashboard');
    
    // Inventory Management Routes
    Route::prefix('inventory')->name('inventory.')->group(function () {
        // Specific routes first
        Route::get('/', [App\Http\Controllers\Admin\InventoryController::class, 'index'])->name('index');
        Route::get('/create', [App\Http\Controllers\Admin\InventoryController::class, 'create'])->name('create');
        Route::post('/', [App\Http\Controllers\Admin\InventoryController::class, 'store'])->name('store');
        Route::get('/categories', [App\Http\Controllers\Admin\InventoryController::class, 'categories'])->name('categories');
        Route::get('/daily-check', [App\Http\Controllers\Admin\InventoryController::class, 'dailyCheck'])->name('daily-check');
        Route::post('/daily-check', [App\Http\Controllers\Admin\InventoryController::class, 'submitDailyCheck'])->name('daily-check.submit');
        Route::get('/bulk-order', [App\Http\Controllers\Admin\InventoryController::class, 'bulkOrder'])->name('bulk-order');
        Route::post('/bulk-order', [App\Http\Controllers\Admin\InventoryController::class, 'submitBulkOrder'])->name('bulk-order.submit');
        Route::get('/lock', [App\Http\Controllers\Admin\InventoryController::class, 'lockInventory'])->name('lock');
        Route::post('/lock', [App\Http\Controllers\Admin\InventoryController::class, 'submitLockInventory'])->name('lock.submit');
        Route::post('/unlock', [App\Http\Controllers\Admin\InventoryController::class, 'unlockInventory'])->name('unlock');
        
        // Parameterized routes last
        Route::get('/{item}', [App\Http\Controllers\Admin\InventoryController::class, 'show'])->name('show');
        Route::get('/{item}/edit', [App\Http\Controllers\Admin\InventoryController::class, 'edit'])->name('edit');
        Route::put('/{item}', [App\Http\Controllers\Admin\InventoryController::class, 'update'])->name('update');
        Route::delete('/{item}', [App\Http\Controllers\Admin\InventoryController::class, 'destroy'])->name('destroy');

        // Inventory Orders Routes
        Route::prefix('orders')->name('orders.')->group(function () {
            Route::get('/', [App\Http\Controllers\Admin\InventoryOrderController::class, 'index'])->name('index');
            Route::get('/history', [App\Http\Controllers\Admin\InventoryOrderController::class, 'history'])->name('history');
            Route::get('/create', [App\Http\Controllers\Admin\InventoryOrderController::class, 'create'])->name('create');
            Route::post('/', [App\Http\Controllers\Admin\InventoryOrderController::class, 'store'])->name('store');
            Route::get('/{order}', [App\Http\Controllers\Admin\InventoryOrderController::class, 'show'])->name('show');
            Route::get('/{order}/edit', [App\Http\Controllers\Admin\InventoryOrderController::class, 'edit'])->name('edit');
            Route::put('/{order}', [App\Http\Controllers\Admin\InventoryOrderController::class, 'update'])->name('update');
            Route::delete('/{order}', [App\Http\Controllers\Admin\InventoryOrderController::class, 'destroy'])->name('destroy');
            Route::post('/{order}/confirm', [App\Http\Controllers\Admin\InventoryOrderController::class, 'confirm'])->name('confirm');
            Route::post('/{order}/cancel', [App\Http\Controllers\Admin\InventoryOrderController::class, 'cancel'])->name('cancel');
        });
    });

    Route::get('/orders', [App\Http\Controllers\Admin\AdminOrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [App\Http\Controllers\Admin\AdminOrderController::class, 'show'])->name('orders.show');
    Route::get('/products', [AdminProductController::class, 'index'])->name('products.index');
    Route::get('/payment-manager', [AdminPaymentController::class, 'index'])->name('payment-manager');
    Route::get('/pos-access-logs', [PosAccessLogController::class, 'index'])->name('pos-access-logs');

    // Clock System Routes
    Route::prefix('clock')->name('clock.')->group(function () {
        Route::get('/', [App\Http\Controllers\Admin\AdminClockController::class, 'index'])->name('index');
        Route::get('/report', [App\Http\Controllers\Admin\AdminClockController::class, 'report'])->name('report');
        Route::post('/action', [App\Http\Controllers\Admin\AdminClockController::class, 'handleAction'])->name('action');
        Route::get('/search', [App\Http\Controllers\Admin\AdminClockController::class, 'search'])->name('search');
        Route::post('/generate-report', [App\Http\Controllers\Admin\AdminClockController::class, 'generateReport'])->name('clock.report.generate.post');
    });

    // Employee Schedules Routes
    Route::prefix('employees/schedules')->name('employees.schedules.')->group(function () {
        Route::get('/', [App\Http\Controllers\Admin\EmployeeScheduleController::class, 'index'])->name('index');
        Route::get('/create', [App\Http\Controllers\Admin\EmployeeScheduleController::class, 'create'])->name('create');
        Route::post('/', [App\Http\Controllers\Admin\EmployeeScheduleController::class, 'store'])->name('store');
        Route::get('/{schedule}/edit', [App\Http\Controllers\Admin\EmployeeScheduleController::class, 'edit'])->name('edit');
        Route::put('/{schedule}', [App\Http\Controllers\Admin\EmployeeScheduleController::class, 'update'])->name('update');
        Route::delete('/{schedule}', [App\Http\Controllers\Admin\EmployeeScheduleController::class, 'destroy'])->name('destroy');
        Route::get('/export', [App\Http\Controllers\Admin\EmployeeScheduleController::class, 'export'])->name('export');
    });

    Route::get('/roles', [AdminRoleController::class, 'index'])->name('roles.index');
    Route::put('/roles/{user}', [AdminRoleController::class, 'update'])->name('roles.update');
    Route::get('/wallet', [WalletController::class, 'index'])->name('wallet.index');
    Route::get('/wallet/manage', [WalletController::class, 'manage'])->name('wallet.manage');
    Route::post('/wallet/topup', [WalletController::class, 'topUp'])->name('wallet.topup');
    Route::get('/wallet/search', [WalletController::class, 'search'])->name('wallet.search');
    Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
    Route::get('/coupons', [CouponController::class, 'index'])->name('coupons.index');
    Route::get('/analytics', [AdminAnalyticsController::class, 'index'])->name('analytics.index');
    Route::get('/reports', [AdminReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/sales', [AdminReportController::class, 'sales'])->name('reports.sales');
    Route::get('/reports/inventory', [AdminReportController::class, 'inventory'])->name('reports.inventory');
    Route::get('/reports/employees', [AdminReportController::class, 'employees'])->name('reports.employees');
    Route::get('/settings', [AdminSettingsController::class, 'index'])->name('settings.index');

    // Creator management routes
    Route::get('/creator-dashboard', [CreatorDashboardController::class, 'index'])->name('creator-dashboard.index');
    Route::get('/creators', [CreatorController::class, 'index'])->name('creators.index');
    Route::get('/creators/{creator}', [CreatorController::class, 'show'])->name('creators.show');
    Route::get('/creators/{creator}/edit', [CreatorController::class, 'edit'])->name('creators.edit');
    Route::put('/creators/{creator}', [CreatorController::class, 'update'])->name('creators.update');
    Route::delete('/creators/{creator}', [CreatorController::class, 'destroy'])->name('creators.destroy');

    // Add this line for admin employees resource
    Route::resource('employees', App\Http\Controllers\Admin\EmployeeController::class)->names('employees');

    // Add this line for admin clock index
    Route::get('/clock', [App\Http\Controllers\Admin\AdminClockController::class, 'index'])->name('clock.index');
    Route::get('/clock/report', [App\Http\Controllers\Admin\AdminClockController::class, 'report'])->name('clock.report');
    Route::post('/clock/report/generate', [App\Http\Controllers\Admin\AdminClockController::class, 'generateReport'])->name('clock.report.generate.post');
    Route::post('/clock/update', [App\Http\Controllers\Admin\AdminClockController::class, 'update'])->name('clock.update');
    Route::post('/clock/action', [App\Http\Controllers\Admin\AdminClockController::class, 'handleAction'])->name('clock.action');
    Route::get('/clock/search', [App\Http\Controllers\Admin\AdminClockController::class, 'search'])->name('clock.search');

    // Add this line for admin products resource
    Route::resource('products', App\Http\Controllers\Admin\AdminProductController::class)->names('products');

    // Add this line for admin suppliers resource
    Route::resource('suppliers', App\Http\Controllers\Admin\SupplierController::class)->names('suppliers');

    // Add this line for admin orders destroy
    Route::delete('/orders/{order}', [App\Http\Controllers\Admin\AdminOrderController::class, 'destroy'])->name('orders.destroy');

    // Add this line for admin employees schedules index
    Route::get('/employees/schedules', [App\Http\Controllers\Admin\EmployeeScheduleController::class, 'index'])->name('employees.schedules.index');

    // Admin User Routes
    Route::middleware(['auth', 'role:admin'])->prefix('users')->name('users.')->group(function () {
        Route::get('/search', [App\Http\Controllers\UserController::class, 'search'])->name('search');
    });

    // Supply Orders
    Route::prefix('supply/orders')->name('supply.orders.')->group(function () {
        Route::get('/', [SupplyOrderController::class, 'index'])->name('index');
        Route::get('/create', [SupplyOrderController::class, 'create'])->name('create');
        Route::post('/', [SupplyOrderController::class, 'store'])->name('store');
        Route::get('/{order}', [SupplyOrderController::class, 'show'])->name('show');
        Route::get('/{order}/edit', [SupplyOrderController::class, 'edit'])->name('edit');
        Route::put('/{order}', [SupplyOrderController::class, 'update'])->name('update');
        Route::delete('/{order}', [SupplyOrderController::class, 'destroy'])->name('destroy');
        Route::post('/{order}/send', [SupplyOrderController::class, 'sendToSupplier'])->name('send');
        Route::get('/{order}/items', [SupplyOrderController::class, 'getOrderItems'])->name('items');
    });

    // Branch Management Routes
    Route::prefix('branches')->name('branches.')->group(function () {
        Route::get('/', [BranchController::class, 'index'])->name('index');
        Route::get('/create', [BranchController::class, 'create'])->name('create');
        Route::post('/', [BranchController::class, 'store'])->name('store');
        Route::get('/{branch}', [BranchController::class, 'show'])->name('show');
        Route::get('/{branch}/edit', [BranchController::class, 'edit'])->name('edit');
        Route::put('/{branch}', [BranchController::class, 'update'])->name('update');
        Route::delete('/{branch}', [BranchController::class, 'destroy'])->name('destroy');
    });

    // Table Management Routes
    Route::prefix('tables')->name('tables.')->group(function () {
        Route::get('/', [App\Http\Controllers\Admin\TableController::class, 'index'])->name('index');
        Route::get('/create', [App\Http\Controllers\Admin\TableController::class, 'create'])->name('create');
        Route::post('/', [App\Http\Controllers\Admin\TableController::class, 'store'])->name('store');
        Route::get('/{table}/edit', [App\Http\Controllers\Admin\TableController::class, 'edit'])->name('edit');
        Route::put('/{table}', [App\Http\Controllers\Admin\TableController::class, 'update'])->name('update');
        Route::delete('/{table}', [App\Http\Controllers\Admin\TableController::class, 'destroy'])->name('destroy');
    });

    // Wallet Management Routes
    Route::prefix('wallets')->name('wallets.')->group(function () {
        Route::get('/', [App\Http\Controllers\Admin\WalletController::class, 'index'])->name('index');
        Route::get('/create', [App\Http\Controllers\Admin\WalletController::class, 'create'])->name('create');
        Route::post('/', [App\Http\Controllers\Admin\WalletController::class, 'store'])->name('store');
        Route::get('/{wallet}/edit', [App\Http\Controllers\Admin\WalletController::class, 'edit'])->name('edit');
        Route::put('/{wallet}', [App\Http\Controllers\Admin\WalletController::class, 'update'])->name('update');
        Route::delete('/{wallet}', [App\Http\Controllers\Admin\WalletController::class, 'destroy'])->name('destroy');
        Route::get('/{wallet}/transactions', [App\Http\Controllers\Admin\WalletController::class, 'transactions'])->name('transactions');
        Route::post('/{wallet}/top-up', [App\Http\Controllers\Admin\WalletController::class, 'topUp'])->name('top-up');
    });
});

// API Routes
Route::prefix('api')->group(function () {
    // Public routes
    Route::post('/employee/verify', [WebEmployeeController::class, 'verify']);

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

// Wallet authentication routes (outside the middleware group)
Route::prefix('wallet')->name('wallet.')->group(function () {
    Route::get('/topup/login', [\App\Http\Controllers\Admin\WalletTopUpController::class, 'showLogin'])->name('topup.login');
    Route::post('/topup/login', [\App\Http\Controllers\Admin\WalletTopUpController::class, 'login'])->name('topup.login.submit');
    Route::get('/topup/logout', [\App\Http\Controllers\Admin\WalletTopUpController::class, 'logout'])->name('topup.logout');
});

// All wallet routes (with second authentication)
Route::prefix('wallet')->name('wallet.')->middleware(['auth', 'role:admin', 'wallet.auth'])->group(function () {
    // Main wallet routes
    Route::get('/', [WalletController::class, 'index'])->name('index');
    Route::get('/qr-generator', [WalletController::class, 'qrGenerator'])->name('qr-generator');
    Route::post('/generate-qr', [WalletController::class, 'generateQRCode'])->name('generate-qr');
    Route::get('/manage', [WalletController::class, 'manage'])->name('manage');
    Route::get('/export', [WalletController::class, 'export'])->name('export');

    // Top-up routes
    Route::get('/topup', [\App\Http\Controllers\Admin\WalletTopUpController::class, 'showTopUpForm'])->name('topup.form');
    Route::post('/topup/process', [\App\Http\Controllers\Admin\WalletTopUpController::class, 'processTopUp'])->name('topup.process');
    Route::post('/topup/generate-qr', [\App\Http\Controllers\Admin\WalletTopUpController::class, 'generateQR'])->name('topup.generate-qr');
});

// Branch Management Routes
Route::middleware(['auth', 'role:admin'])->prefix('admin/branches')->name('admin.branches.')->group(function () {
    // Main branch routes
    Route::get('/', [BranchController::class, 'index'])->name('index');
    Route::post('/', [BranchController::class, 'store'])->name('store');
    Route::get('/create', [BranchController::class, 'create'])->name('create');
    Route::get('/{branch}', [BranchController::class, 'show'])->name('show');
    Route::get('/{branch}/edit', [BranchController::class, 'edit'])->name('edit');
    Route::put('/{branch}', [BranchController::class, 'update'])->name('update');
    Route::delete('/{branch}', [BranchController::class, 'destroy'])->name('destroy');

    // Branch-specific routes
    Route::prefix('{branch}')->group(function () {
        // Products
        Route::get('/products', [App\Http\Controllers\Admin\ProductController::class, 'index'])->name('products.index');
        Route::get('/products/create', [App\Http\Controllers\Admin\ProductController::class, 'create'])->name('products.create');
        Route::post('/products', [App\Http\Controllers\Admin\ProductController::class, 'store'])->name('products.store');
        Route::get('/products/{product}/edit', [App\Http\Controllers\Admin\ProductController::class, 'edit'])->name('products.edit');
        Route::put('/products/{product}', [App\Http\Controllers\Admin\ProductController::class, 'update'])->name('products.update');
        Route::delete('/products/{product}', [App\Http\Controllers\Admin\ProductController::class, 'destroy'])->name('products.destroy');

        // Inventory
        Route::get('/inventory', [App\Http\Controllers\Admin\InventoryController::class, 'index'])->name('inventory.index');
        Route::get('/inventory/bulk-order', [App\Http\Controllers\Admin\InventoryController::class, 'bulkOrder'])->name('inventory.bulk-order');
        Route::post('/inventory/bulk-order', [App\Http\Controllers\Admin\InventoryController::class, 'submitBulkOrder'])->name('inventory.bulk-order.submit');
        Route::get('/inventory/daily-check', [App\Http\Controllers\Admin\InventoryController::class, 'dailyCheck'])->name('inventory.daily-check');
        Route::post('/inventory/daily-check', [App\Http\Controllers\Admin\InventoryController::class, 'submitDailyCheck'])->name('inventory.daily-check.submit');
        Route::get('/inventory/lock', [App\Http\Controllers\Admin\InventoryController::class, 'lockInventory'])->name('inventory.lock');
        Route::post('/inventory/lock', [App\Http\Controllers\Admin\InventoryController::class, 'submitLockInventory'])->name('inventory.lock.submit');
        Route::post('/inventory/unlock', [App\Http\Controllers\Admin\InventoryController::class, 'unlockInventory'])->name('inventory.unlock');

        // Orders
        Route::get('/orders', [App\Http\Controllers\Admin\AdminOrderController::class, 'index'])->name('orders.index');
        Route::get('/orders/create', [App\Http\Controllers\Admin\AdminOrderController::class, 'create'])->name('orders.create');
        Route::post('/orders', [App\Http\Controllers\Admin\AdminOrderController::class, 'store'])->name('orders.store');
        Route::get('/orders/{order}', [App\Http\Controllers\Admin\AdminOrderController::class, 'show'])->name('orders.show');
        Route::get('/orders/{order}/edit', [App\Http\Controllers\Admin\AdminOrderController::class, 'edit'])->name('orders.edit');
        Route::put('/orders/{order}', [App\Http\Controllers\Admin\AdminOrderController::class, 'update'])->name('orders.update');
        Route::delete('/orders/{order}', [App\Http\Controllers\Admin\AdminOrderController::class, 'destroy'])->name('orders.destroy');

        // Employees
        Route::get('/employees', [App\Http\Controllers\Admin\EmployeeController::class, 'index'])->name('employees.index');
        Route::get('/employees/create', [App\Http\Controllers\Admin\EmployeeController::class, 'create'])->name('employees.create');
        Route::post('/employees', [App\Http\Controllers\Admin\EmployeeController::class, 'store'])->name('employees.store');
        Route::get('/employees/{employee}/edit', [App\Http\Controllers\Admin\EmployeeController::class, 'edit'])->name('employees.edit');
        Route::put('/employees/{employee}', [App\Http\Controllers\Admin\EmployeeController::class, 'update'])->name('employees.update');
        Route::delete('/employees/{employee}', [App\Http\Controllers\Admin\EmployeeController::class, 'destroy'])->name('employees.destroy');

        // Tables
        Route::get('/tables', [App\Http\Controllers\Admin\TableController::class, 'index'])->name('tables.index');
        Route::get('/tables/create', [App\Http\Controllers\Admin\TableController::class, 'create'])->name('tables.create');
        Route::post('/tables', [App\Http\Controllers\Admin\TableController::class, 'store'])->name('tables.store');
        Route::get('/tables/{table}/edit', [App\Http\Controllers\Admin\TableController::class, 'edit'])->name('tables.edit');
        Route::put('/tables/{table}', [App\Http\Controllers\Admin\TableController::class, 'update'])->name('tables.update');
        Route::delete('/tables/{table}', [App\Http\Controllers\Admin\TableController::class, 'destroy'])->name('tables.destroy');

        // Wallets
        Route::get('/wallets', [App\Http\Controllers\Admin\WalletController::class, 'index'])->name('wallets.index');
        Route::get('/wallets/create', [App\Http\Controllers\Admin\WalletController::class, 'create'])->name('wallets.create');
        Route::post('/wallets', [App\Http\Controllers\Admin\WalletController::class, 'store'])->name('wallets.store');
        Route::get('/wallets/{wallet}', [App\Http\Controllers\Admin\WalletController::class, 'show'])->name('wallets.show');
        Route::get('/wallets/{wallet}/edit', [App\Http\Controllers\Admin\WalletController::class, 'edit'])->name('wallets.edit');
        Route::put('/wallets/{wallet}', [App\Http\Controllers\Admin\WalletController::class, 'update'])->name('wallets.update');
        Route::delete('/wallets/{wallet}', [App\Http\Controllers\Admin\WalletController::class, 'destroy'])->name('wallets.destroy');

        // Reports & Analytics
        Route::get('/reports', [App\Http\Controllers\Admin\BranchReportController::class, 'index'])->name('reports.index');
        Route::get('/reports/sales', [App\Http\Controllers\Admin\BranchReportController::class, 'sales'])->name('reports.sales');
        Route::get('/reports/inventory', [App\Http\Controllers\Admin\BranchReportController::class, 'inventory'])->name('reports.inventory');
        Route::get('/reports/employees', [App\Http\Controllers\Admin\BranchReportController::class, 'employees'])->name('reports.employees');
        Route::get('/analytics', [App\Http\Controllers\Admin\BranchAnalyticsController::class, 'index'])->name('analytics.index');
    });
});


