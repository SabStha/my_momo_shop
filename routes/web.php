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
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
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
    Route::middleware(['pos.access'])->group(function () {
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
});

// Admin routes
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
    
    // Wallet Management
    Route::prefix('wallet')->name('wallet.')->group(function () {
        Route::get('/', [WalletController::class, 'index'])->name('index');
        Route::get('/manage', [WalletController::class, 'manage'])->name('manage');
        Route::get('/transactions', [WalletController::class, 'transactions'])->name('transactions');
        Route::post('/generate-qr', [WalletController::class, 'generateTopUpQR'])->name('generate-qr');
        Route::post('/topup', [WalletController::class, 'adminTopup'])->name('topup');
        Route::post('/deposit', [WalletController::class, 'deposit'])->name('deposit');
        Route::post('/withdraw', [WalletController::class, 'withdraw'])->name('withdraw');
        Route::get('/balance', [WalletController::class, 'balance'])->name('balance');
        Route::get('/export', [WalletController::class, 'export'])->name('export');
    });

    Route::resource('products', ProductController::class);
    // Route::resource('categories', CategoryController::class); // Commented out because the controller does not exist
    Route::prefix('inventory')->name('inventory.')->group(function () {
        Route::get('manage', [InventoryController::class, 'manage'])->name('manage');
        Route::get('categories', [InventoryController::class, 'categories'])->name('categories');
        Route::post('categories/store', [InventoryController::class, 'storeCategory'])->name('store-category');
        Route::put('categories/{category}', [InventoryController::class, 'updateCategory'])->name('update-category');
        Route::delete('categories/{category}', [InventoryController::class, 'deleteCategory'])->name('delete-category');
        
        Route::get('checks', [InventoryCheckController::class, 'index'])->name('checks.index');
        Route::get('checks/create', [InventoryCheckController::class, 'create'])->name('checks.create');
        Route::post('checks', [InventoryCheckController::class, 'store'])->name('checks.store');
        Route::get('checks/{check}', [InventoryCheckController::class, 'show'])->name('checks.show');
        Route::get('checks/{check}/edit', [InventoryCheckController::class, 'edit'])->name('checks.edit');
        Route::put('checks/{check}', [InventoryCheckController::class, 'update'])->name('checks.update');
        Route::delete('checks/{check}', [InventoryCheckController::class, 'destroy'])->name('checks.destroy');
        
        Route::resource('', InventoryController::class)->parameters(['' => 'item']);
        Route::post('{item}/lock', [InventoryController::class, 'lock'])->name('lock');
        Route::post('{item}/unlock', [InventoryController::class, 'unlock'])->name('unlock');
        Route::post('{item}/adjust', [InventoryController::class, 'adjust'])->name('adjust');
        Route::get('count', [InventoryController::class, 'count'])->name('count');
        Route::get('forecast', [InventoryController::class, 'forecast'])->name('forecast');

        // Inventory Orders
        Route::resource('orders', InventoryOrderController::class);
    });

    // User Management
    Route::resource('users', AdminUserController::class);
    Route::resource('roles', AdminRoleController::class);
    
    // Employee Management
    Route::resource('employees', \App\Http\Controllers\Admin\EmployeeController::class);
    Route::get('/employees/{employee}/schedule', [\App\Http\Controllers\Admin\EmployeeController::class, 'schedule'])->name('employees.schedule');
    Route::get('/employees/{employee}/attendance', [\App\Http\Controllers\Admin\EmployeeController::class, 'attendance'])->name('employees.attendance');
    Route::get('/employees/{employee}/payroll', [\App\Http\Controllers\Admin\EmployeeController::class, 'payroll'])->name('employees.payroll');
    
    // POS Access Logs
    Route::get('/pos-access-logs', [PosAccessLogController::class, 'index'])->name('pos-access-logs');
    Route::get('/pos-access-logs/export', [PosAccessLogController::class, 'export'])->name('pos-access-logs.export');
    Route::get('/pos-access-logs/{log}', [PosAccessLogController::class, 'show'])->name('pos-access-logs.show');
    Route::delete('/pos-access-logs/{log}', [PosAccessLogController::class, 'destroy'])->name('pos-access-logs.destroy');
    
    // Clock Management
    Route::get('/clock', [\App\Http\Controllers\Admin\AdminClockController::class, 'index'])->name('clock.index');
    Route::post('/clock/in', [\App\Http\Controllers\Admin\AdminClockController::class, 'clockIn'])->name('clock.in');
    Route::post('/clock/out', [\App\Http\Controllers\Admin\AdminClockController::class, 'clockOut'])->name('clock.out');
    Route::post('/clock/report', [\App\Http\Controllers\Admin\AdminClockController::class, 'report'])->name('clock.report');
    Route::get('/clock/search', [\App\Http\Controllers\Admin\AdminClockController::class, 'search'])->name('clock.search');
    
    // Reports & Analytics
    Route::get('/reports', [AdminReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/sales', [AdminReportController::class, 'sales'])->name('reports.sales');
    Route::get('/reports/orders', [AdminReportController::class, 'orders'])->name('reports.orders');
    Route::get('/reports/products', [AdminReportController::class, 'products'])->name('reports.products');
    Route::get('/reports/employees', [AdminReportController::class, 'employees'])->name('reports.employees');
    Route::get('/reports/export/{type}', [AdminReportController::class, 'export'])->name('reports.export');

    // Analytics
    Route::get('/analytics', [AdminAnalyticsController::class, 'index'])->name('analytics.index');
    Route::get('/analytics/sales', [AdminAnalyticsController::class, 'sales'])->name('analytics.sales');
    Route::get('/analytics/products', [AdminAnalyticsController::class, 'products'])->name('analytics.products');
    Route::get('/analytics/employees', [AdminAnalyticsController::class, 'employees'])->name('analytics.employees');
    
    // Product Management
    Route::resource('products', AdminProductController::class);
    Route::post('products/import', [AdminProductController::class, 'import'])->name('products.import');
    Route::get('products/export', [AdminProductController::class, 'export'])->name('products.export');
    
    // Order Management
    Route::resource('orders', AdminOrderController::class);
    Route::put('orders/{order}/status', [AdminOrderController::class, 'updateStatus'])->name('orders.status');
    
    // Supplier Management
    Route::resource('suppliers', SupplierController::class);
    
    // Supply Orders
    Route::prefix('supply')->name('supply.')->group(function () {
        Route::resource('orders', SupplyOrderController::class);
        Route::post('orders/{order}/send', [SupplyOrderController::class, 'sendToSupplier'])->name('orders.send');
    });
    
    // Settings
    Route::get('/settings', [AdminSettingsController::class, 'index'])->name('settings');
    Route::post('/settings/update', [AdminSettingsController::class, 'update'])->name('settings.update');

    // Creator Dashboard Routes
    Route::prefix('creator-dashboard')->name('creator-dashboard.')->group(function () {
        Route::get('/', [CreatorDashboardController::class, 'index'])->name('index');
        Route::get('/creators', [CreatorDashboardController::class, 'creators'])->name('creators');
        Route::get('/creators/{creator}', [CreatorDashboardController::class, 'show'])->name('creators.show');
        Route::post('/creators/{creator}/approve', [CreatorDashboardController::class, 'approve'])->name('creators.approve');
        Route::post('/creators/{creator}/reject', [CreatorDashboardController::class, 'reject'])->name('creators.reject');
        Route::get('/analytics', [CreatorDashboardController::class, 'analytics'])->name('analytics');
        Route::get('/reports', [CreatorDashboardController::class, 'reports'])->name('reports');
    });

    // Role Management
    Route::prefix('roles')->name('roles.')->group(function () {
        Route::get('/', [AdminRoleController::class, 'index'])->name('index');
        Route::get('/create', [AdminRoleController::class, 'create'])->name('create');
        Route::post('/', [AdminRoleController::class, 'store'])->name('store');
        Route::get('/{role}', [AdminRoleController::class, 'show'])->name('show');
        Route::get('/{role}/edit', [AdminRoleController::class, 'edit'])->name('edit');
        Route::put('/{role}', [AdminRoleController::class, 'update'])->name('update');
        Route::patch('/{role}', [AdminRoleController::class, 'update'])->name('update');
        Route::delete('/{role}', [AdminRoleController::class, 'destroy'])->name('destroy');
    });
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
            Route::get('/orders', [ApiOrderController::class, 'index']);
            Route::post('/orders', [ApiOrderController::class, 'store']);
            Route::get('/orders/{order}', [ApiOrderController::class, 'show']);
            Route::put('/orders/{order}', [ApiOrderController::class, 'update']);
            Route::put('/orders/{order}/status', [ApiOrderController::class, 'updateStatus']);
            Route::delete('/orders/{order}', [ApiOrderController::class, 'destroy']);
            
            // Payments
            Route::post('/payments', [ApiPaymentController::class, 'store']);
            Route::get('/payments/{payment}', [ApiPaymentController::class, 'show']);
        });

        // Admin only routes
        Route::middleware(['role:admin'])->prefix('admin')->group(function () {
            Route::get('/dashboard', [AdminDashboardController::class, 'getDashboardData']);
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

