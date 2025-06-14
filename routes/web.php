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
use App\Http\Controllers\Admin\BranchPasswordController;
use App\Http\Controllers\LogController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\PaymentController;
use App\Http\Controllers\Customer\CustomerPaymentController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\PaymentManagerController;
use App\Http\Controllers\Admin\EmployeeController;
use App\Http\Controllers\Admin\ClockController;
use App\Http\Controllers\Admin\CreatorController as AdminCreatorController;
use App\Http\Controllers\Admin\ReferralSettingsController;
use App\Http\Controllers\Admin\RoleController;
use App\Models\Employee;
use App\Http\Controllers\Admin\ActivityLogController;
use App\Http\Controllers\OpenAITestController;
use App\Http\Controllers\SalesAnalyticsController;
use App\Http\Controllers\Admin\CustomerAnalyticsController;
use App\Http\Controllers\Admin\AdminOrderController;
use App\Http\Controllers\Admin\WeeklyDigestController;

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
Route::get('/menu', function () {
    return view('pages.menu');
})->name('menu');
Route::get('/bulk', [\App\Http\Controllers\BulkController::class, 'index'])->name('bulk');
Route::get('/profile', [\App\Http\Controllers\User\ProfileController::class, 'edit'])->name('profile.edit');
Route::get('/notifications', [\App\Http\Controllers\NotificationController::class, 'index'])->name('notifications');
Route::get('/cart', [\App\Http\Controllers\CartController::class, 'index'])->name('cart');
Route::get('/finds', [\App\Http\Controllers\FindsController::class, 'index'])->name('finds');
Route::get('/leaderboard', [CreatorController::class, 'leaderboard'])->name('public.leaderboard');
Route::get('/offers', [HomeController::class, 'offers'])->name('offers');
Route::get('/search', [App\Http\Controllers\ProductController::class, 'search'])->name('search');
Route::get('/api/products/autocomplete', [App\Http\Controllers\ProductController::class, 'autocomplete'])->name('products.autocomplete');
Route::get('/categories/{category}', [CategoryController::class, 'show'])->name('categories.show');

// Authentication routes
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// Registration routes
Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
Route::post('/register', [RegisterController::class, 'register'])->name('register.submit');

// Creator registration routes
Route::get('/creator/register', [CreatorController::class, 'showRegistrationForm'])->name('creator.register');
Route::post('/creator/register', [CreatorController::class, 'register'])->name('creator.register.submit');

// Password reset routes
Route::get('/password/reset', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
Route::post('/password/email', [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');
Route::get('/password/reset/{token}', [ResetPasswordController::class, 'showResetForm'])->name('password.reset');
Route::post('/password/reset', [ResetPasswordController::class, 'reset'])->name('password.update');

// POS routes - require POS access
Route::middleware(['auth', 'pos.access'])->group(function () {
    Route::get('/pos', [WebPosController::class, 'index'])->name('pos');
    Route::get('/pos/tables', [WebPosController::class, 'tables'])->name('pos.tables');
    Route::get('/pos/orders', [WebPosController::class, 'orders'])->name('pos.orders');
    Route::get('/pos/payments', [WebPosController::class, 'payments'])->name('pos.payments');
    Route::post('/pos/verify-token', [App\Http\Controllers\Api\PosController::class, 'verifyToken'])->name('pos.verify-token');
});

// API routes for POS - using web authentication
Route::middleware(['auth', 'pos.access'])->prefix('api/pos')->group(function () {
    Route::get('/products', [App\Http\Controllers\Api\PosController::class, 'products']);
    Route::get('/tables', [App\Http\Controllers\Api\PosController::class, 'tables']);
    Route::get('/orders', [App\Http\Controllers\Api\PosOrderController::class, 'index']);
    Route::post('/orders', [App\Http\Controllers\Api\PosOrderController::class, 'store']);
    Route::get('/orders/{order}', [App\Http\Controllers\Api\PosOrderController::class, 'show']);
    Route::put('/orders/{order}', [App\Http\Controllers\Api\PosOrderController::class, 'update']);
    Route::delete('/orders/{order}', [App\Http\Controllers\Api\PosOrderController::class, 'destroy']);
    Route::post('/payments', [App\Http\Controllers\Api\PosPaymentController::class, 'store']);
    Route::get('/user-info', [App\Http\Controllers\Api\PosController::class, 'userInfo']);
});

// POS Login routes
Route::get('/pos/login', [PosAuthController::class, 'showLoginForm'])->name('pos.login');
Route::post('/pos/login', [PosAuthController::class, 'login'])->name('pos.login.submit');

// Product routes
Route::get('/products', [ProductController::class, 'index'])->name('products.index');
Route::get('/products/{product}', [ProductController::class, 'show'])->name('products.show');
Route::get('/products/category/{category}', [ProductController::class, 'category'])->name('products.category');
Route::get('/products/search', [ProductController::class, 'search'])->name('products.search');
Route::get('/products/{product}/qr', [ProductController::class, 'generateQRCode'])->name('products.qr');

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

// Admin routes
Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin'])->group(function () {
    // ... existing admin routes ...

    // Activity Log Routes
    Route::get('/activity-logs', [App\Http\Controllers\Admin\ActivityLogController::class, 'index'])->name('activity-logs.index');
    Route::get('/activity-logs/{activityLog}', [App\Http\Controllers\Admin\ActivityLogController::class, 'show'])->name('activity-logs.show');

    // Clock Routes
    Route::get('/clock', [App\Http\Controllers\Admin\ClockController::class, 'index'])->name('clock.index');
    Route::post('/clock/in', [App\Http\Controllers\Admin\ClockController::class, 'clockIn'])->name('clock.in');
    Route::post('/clock/out', [App\Http\Controllers\Admin\ClockController::class, 'clockOut'])->name('clock.out');
    Route::post('/clock/break/start', [App\Http\Controllers\Admin\ClockController::class, 'startBreak'])->name('clock.break.start');
    Route::post('/clock/break/end', [App\Http\Controllers\Admin\ClockController::class, 'endBreak'])->name('clock.break.end');
    Route::get('/clock/employees/search', [App\Http\Controllers\Admin\ClockController::class, 'searchEmployees'])->name('clock.employees.search');
    Route::get('/clock/logs', [App\Http\Controllers\Admin\ClockController::class, 'getTimeLogs'])->name('clock.logs');

    // Employee Routes
    Route::get('/employees', [App\Http\Controllers\Admin\EmployeeController::class, 'index'])->name('employees.index');
    Route::get('/employees/create', [App\Http\Controllers\Admin\EmployeeController::class, 'create'])->name('employees.create');
    Route::post('/employees', [App\Http\Controllers\Admin\EmployeeController::class, 'store'])->name('employees.store');
    Route::get('/employees/{employee}', [App\Http\Controllers\Admin\EmployeeController::class, 'show'])->name('employees.show');
    Route::get('/employees/{employee}/edit', [App\Http\Controllers\Admin\EmployeeController::class, 'edit'])->name('employees.edit');
    Route::put('/employees/{employee}', [App\Http\Controllers\Admin\EmployeeController::class, 'update'])->name('employees.update');
    Route::delete('/employees/{employee}', [App\Http\Controllers\Admin\EmployeeController::class, 'destroy'])->name('employees.destroy');

    // Inventory Routes
    Route::get('/inventory', [App\Http\Controllers\Admin\InventoryController::class, 'index'])->name('inventory.index');
    Route::get('/inventory/create', [App\Http\Controllers\Admin\InventoryController::class, 'create'])->name('inventory.create');
    Route::post('/inventory', [App\Http\Controllers\Admin\InventoryController::class, 'store'])->name('inventory.store');
    Route::get('/inventory/{item}', [App\Http\Controllers\Admin\InventoryController::class, 'show'])->name('inventory.show');
    Route::get('/inventory/{item}/edit', [App\Http\Controllers\Admin\InventoryController::class, 'edit'])->name('inventory.edit');
    Route::put('/inventory/{item}', [App\Http\Controllers\Admin\InventoryController::class, 'update'])->name('inventory.update');
    Route::delete('/inventory/{item}', [App\Http\Controllers\Admin\InventoryController::class, 'destroy'])->name('inventory.destroy');
    Route::post('/inventory/{item}/adjust', [App\Http\Controllers\Admin\InventoryController::class, 'adjust'])->name('inventory.adjust');

    // Payment Manager Routes
    Route::get('/payment-manager', [App\Http\Controllers\Admin\PaymentManagerController::class, 'index'])->name('payment-manager.index');
    Route::get('/payment-manager/history', [App\Http\Controllers\Admin\PaymentManagerController::class, 'history'])->name('payment-manager.history');
    Route::post('/payment-manager/orders/{order}/process-payment', [App\Http\Controllers\Admin\PaymentManagerController::class, 'processPayment'])->name('payment-manager.order.process-payment');

    // Customer Analytics Routes
    Route::get('/analytics', [CustomerAnalyticsController::class, 'index'])->name('analytics');
    Route::get('/analytics/trend-explanation', [CustomerAnalyticsController::class, 'getTrendExplanation'])->name('analytics.trend-explanation');
    Route::get('/analytics/journey-insights', [CustomerAnalyticsController::class, 'getJourneyInsights'])->name('analytics.journey-insights');
    Route::get('/analytics/journey-analysis', [CustomerAnalyticsController::class, 'journeyAnalysis'])->name('analytics.journey-analysis');

    // Analytics Routes
    Route::prefix('analytics')->middleware(['auth', 'admin'])->group(function () {
        Route::get('/', [CustomerAnalyticsController::class, 'index'])->name('admin.analytics.index');
        Route::get('/segments', [CustomerAnalyticsController::class, 'segments'])->name('admin.analytics.segments');
        Route::get('/churn', [CustomerAnalyticsController::class, 'churn'])->name('admin.analytics.churn');
        Route::get('/segment-evolution', [CustomerAnalyticsController::class, 'getSegmentEvolution'])->name('admin.analytics.segment-evolution');
        Route::post('/explain-trend/{branch?}', [CustomerAnalyticsController::class, 'explainTrend'])->name('admin.analytics.explain-trend');
        Route::get('/segment-suggestions', [CustomerAnalyticsController::class, 'getSegmentSuggestions'])->name('admin.analytics.segment-suggestions');
        Route::post('/generate-campaign', [CustomerAnalyticsController::class, 'generateRetentionCampaign'])->name('admin.analytics.generate-campaign');
        Route::get('/export-segment/{segment}', [CustomerAnalyticsController::class, 'exportSegment'])->name('admin.analytics.export-segment');
        Route::get('/journey-analysis', [CustomerAnalyticsController::class, 'journeyAnalysis'])->name('admin.analytics.journey-analysis');
        Route::get('/journey-insights', [CustomerAnalyticsController::class, 'getJourneyInsights'])->name('admin.analytics.journey-insights');
        Route::get('/trend-explanation', [CustomerAnalyticsController::class, 'getTrendExplanation'])->name('admin.analytics.trend-explanation');
        Route::get('/weekly-digest', [WeeklyDigestController::class, 'index'])->name('admin.analytics.weekly-digest');
    });

    // Wallet Routes
    Route::get('/wallet', [App\Http\Controllers\Admin\WalletController::class, 'index'])->name('wallet.index');
    Route::post('/wallet', [App\Http\Controllers\Admin\WalletController::class, 'store'])->name('wallet.store');
    Route::post('/wallet/topup', [App\Http\Controllers\Admin\WalletController::class, 'topUp'])->name('wallet.topup');
    Route::post('/wallet/withdraw', [App\Http\Controllers\Admin\WalletController::class, 'withdraw'])->name('wallet.withdraw');
    Route::get('/wallet/manage', [App\Http\Controllers\Admin\WalletController::class, 'manage'])->name('wallet.manage');
    Route::get('/wallet/search', [App\Http\Controllers\Admin\WalletController::class, 'search'])->name('wallet.search');
    Route::get('/wallet/qr-generator', [App\Http\Controllers\Admin\WalletController::class, 'qrGenerator'])->name('wallet.qr-generator');
    Route::post('/wallet/generate-qr', [App\Http\Controllers\Admin\WalletController::class, 'generateQRCode'])->name('wallet.generate-qr');
    Route::post('/wallet/generate-topup-qr', [App\Http\Controllers\Admin\WalletController::class, 'generateTopUpQR'])->name('wallet.generate-topup-qr');
    Route::get('/wallet/transactions', [App\Http\Controllers\Admin\WalletController::class, 'transactions'])->name('wallet.transactions');
    Route::post('/wallet/admin-topup', [App\Http\Controllers\Admin\WalletController::class, 'adminTopup'])->name('wallet.admin-topup');
    Route::get('/wallet/balance', [App\Http\Controllers\Admin\WalletController::class, 'balance'])->name('wallet.balance');
    Route::get('/wallet/export', [App\Http\Controllers\Admin\WalletController::class, 'export'])->name('wallet.export');
    Route::get('/wallet/scan', [App\Http\Controllers\Admin\WalletController::class, 'scan'])->name('wallet.scan');
    Route::get('/wallet/transactions/{user}', [App\Http\Controllers\Admin\WalletController::class, 'getTransactions'])->name('wallet.user-transactions');
    Route::get('/wallet/topup/login', [App\Http\Controllers\Admin\WalletController::class, 'topupLogin'])->name('wallet.topup.login');
    Route::post('/wallet/topup/login', [App\Http\Controllers\Admin\WalletController::class, 'processTopupLogin'])->name('wallet.topup.login.process');
    Route::get('/wallet/topup/verify', [App\Http\Controllers\Admin\WalletController::class, 'topupVerify'])->name('wallet.topup.verify');
    Route::post('/wallet/topup/logout', [App\Http\Controllers\Admin\WalletController::class, 'topupLogout'])->name('wallet.topup.logout');

    // Referral Settings Routes
    Route::get('/referral-settings', [App\Http\Controllers\Admin\ReferralSettingsController::class, 'index'])->name('referral-settings.index');
    Route::post('/referral-settings', [App\Http\Controllers\Admin\ReferralSettingsController::class, 'update'])->name('referral-settings.update');

    // Roles Routes
    Route::get('/roles', [App\Http\Controllers\Admin\RoleController::class, 'index'])->name('roles.index');
    Route::post('/roles', [App\Http\Controllers\Admin\RoleController::class, 'store'])->name('roles.store');
    Route::put('/roles/{role}', [App\Http\Controllers\Admin\RoleController::class, 'update'])->name('roles.update');
    Route::delete('/roles/{role}', [App\Http\Controllers\Admin\RoleController::class, 'destroy'])->name('roles.destroy');

    // Branch Routes
    Route::get('/branches', [App\Http\Controllers\Admin\BranchController::class, 'index'])->name('branches.index');
    Route::get('/branches/create', [App\Http\Controllers\Admin\BranchController::class, 'create'])->name('branches.create');
    Route::post('/branches', [App\Http\Controllers\Admin\BranchController::class, 'store'])->name('branches.store');
    Route::get('/branches/{branch}', [App\Http\Controllers\Admin\BranchController::class, 'show'])->name('branches.show');
    Route::get('/branches/{branch}/edit', [App\Http\Controllers\Admin\BranchController::class, 'edit'])->name('branches.edit');
    Route::put('/branches/{branch}', [App\Http\Controllers\Admin\BranchController::class, 'update'])->name('branches.update');
    Route::delete('/branches/{branch}', [App\Http\Controllers\Admin\BranchController::class, 'destroy'])->name('branches.destroy');
    Route::post('/branches/{branch}/switch', [App\Http\Controllers\Admin\BranchController::class, 'switch'])->name('branches.switch');
    Route::post('/branches/{branch}/verify', [App\Http\Controllers\Admin\BranchController::class, 'verify'])->name('branches.verify');
    Route::post('/branches/{branch}/reset-password', [App\Http\Controllers\Admin\BranchController::class, 'resetPassword'])->name('branches.reset-password');

    // Analytics Routes
    Route::get('/analytics/segment-evolution', [App\Http\Controllers\Admin\CustomerAnalyticsController::class, 'getSegmentEvolution'])->name('analytics.segment-evolution');
    Route::post('/analytics/explain-trend', [App\Http\Controllers\Admin\CustomerAnalyticsController::class, 'explainTrend'])->name('analytics.explain-trend');
    Route::get('/analytics/segments', [App\Http\Controllers\Admin\CustomerAnalyticsController::class, 'segments'])->name('analytics.segments');
    Route::get('/analytics/churn', [App\Http\Controllers\Admin\CustomerAnalyticsController::class, 'churn'])->name('analytics.churn');
    Route::get('/analytics/segment-suggestions', [App\Http\Controllers\Admin\CustomerAnalyticsController::class, 'getSegmentSuggestions'])->name('analytics.segment-suggestions');
    Route::post('/analytics/generate-campaign', [App\Http\Controllers\Admin\CustomerAnalyticsController::class, 'generateCampaign'])->name('analytics.generate-campaign');
    Route::get('/analytics/export-segment/{segment}', [App\Http\Controllers\Admin\CustomerAnalyticsController::class, 'exportSegment'])->name('analytics.export-segment');
    Route::post('/analytics/journey-analysis', [App\Http\Controllers\Admin\CustomerAnalyticsController::class, 'analyzeJourney'])->name('analytics.journey-analysis');
    Route::get('/analytics/retention-campaign/{customerId}', [App\Http\Controllers\Admin\CustomerAnalyticsController::class, 'generateRetentionCampaign'])->name('analytics.retention-campaign');
});

// API Routes
Route::prefix('api')->group(function () {
    // Public routes
    Route::post('/employee/verify', [WebEmployeeController::class, 'verify']);

    // Protected routes
    Route::middleware(['auth:sanctum'])->group(function () {
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

// Creator routes
Route::middleware(['auth', 'role:creator'])->group(function () {
    Route::get('/creator/dashboard', [CreatorDashboardController::class, 'index'])->name('creator.dashboard');
    Route::get('/creator/profile', [CreatorController::class, 'profile'])->name('creator.profile');
    Route::get('/creator/referrals', [CreatorController::class, 'referrals'])->name('creator.referrals');
    Route::get('/creator/earnings', [CreatorController::class, 'earnings'])->name('creator.earnings');
});

// Admin Creator Dashboard Routes
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/admin/creator-dashboard', [CreatorDashboardController::class, 'index'])->name('admin.creator-dashboard.index');
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

    // Activity Logs
    Route::get('/activity-logs', [ActivityLogController::class, 'index'])->name('activity-logs.index');
    Route::get('/activity-logs/{activityLog}', [ActivityLogController::class, 'show'])->name('activity-logs.show');
});

Route::get('/admin/wallet/transactions/{user}', [App\Http\Controllers\Admin\WalletController::class, 'getTransactions'])->name('admin.wallet.transactions');

// Log checking route
Route::get('/check-logs', [LogController::class, 'checkLogs'])->name('check.logs');

// Registration logs route
Route::get('/registration-logs', function() {
    $logFile = storage_path('logs/registration.log');
    if (file_exists($logFile)) {
        $logs = file_get_contents($logFile);
        return response()->json([
            'success' => true,
            'logs' => $logs
        ]);
    }
    return response()->json([
        'success' => false,
        'message' => 'No registration logs found'
    ]);
})->name('registration.logs');

// Payment Manager Routes
Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('/api/payments', [App\Http\Controllers\Api\PaymentController::class, 'index'])->middleware('auth:sanctum');
    Route::get('/api/orders/{id}', [App\Http\Controllers\Api\PaymentController::class, 'getOrder'])->middleware('auth:sanctum');
    Route::post('/api/payments', [App\Http\Controllers\Api\PaymentController::class, 'store'])->middleware('auth:sanctum');
    Route::get('/api/cash-drawer', [App\Http\Controllers\Api\PaymentController::class, 'getCashDrawer'])->middleware('auth:sanctum');
    Route::get('/api/cash-drawer/balance', [App\Http\Controllers\Api\PaymentController::class, 'getCashDrawerBalance'])->middleware('auth:sanctum');
    Route::post('/api/cash-drawer', [App\Http\Controllers\Api\PaymentController::class, 'updateCashDrawer'])->middleware('auth:sanctum');
    Route::get('/payments/{id}', [PaymentController::class, 'viewPayment'])->name('payments.show');
    Route::get('/payments/{id}/receipt', [PaymentController::class, 'printReceipt'])->name('payments.receipt');
});

// Receipt routes
Route::get('/receipts/print/{id}', [App\Http\Controllers\ReceiptController::class, 'print'])->name('receipts.print');

// Customer Payment Viewer Routes
Route::get('/customer/payment-viewer', [App\Http\Controllers\Customer\CustomerPaymentController::class, 'showPaymentViewer'])
    ->name('payment.viewer')
    ->middleware('web'); // Only use web middleware, no auth required

Route::get('/api/customer/active-order', [App\Http\Controllers\Customer\CustomerPaymentController::class, 'getActiveOrder'])
    ->name('api.orders.active')
    ->middleware('web'); // Only use web middleware, no auth required

Route::get('/api/admin/cash-drawer/status', [App\Http\Controllers\Admin\CashDrawerController::class, 'getStatus']);

// Creator management routes
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::resource('creators', App\Http\Controllers\Admin\AdminCreatorController::class)->names('admin.creators');
});

Route::get('/wallet/topup/login', [\App\Http\Controllers\Admin\WalletTopUpController::class, 'showLogin'])->name('admin.wallet.topup.login');

Route::get('/test-openai', [OpenAITestController::class, 'testBasicCompletion']);

Route::get('/api/sales/overview', [SalesAnalyticsController::class, 'getSalesOverview']);

// Admin Customer Analytics Routes
Route::prefix('admin/customer-analytics')->name('admin.customer-analytics.')->group(function () {
    Route::get('/', [CustomerAnalyticsController::class, 'index'])->name('index');
    Route::get('/segments', [CustomerAnalyticsController::class, 'segments'])->name('segments');
    Route::get('/churn', [CustomerAnalyticsController::class, 'churn'])->name('churn');
});

// Admin routes
Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin'])->group(function () {
    // Dashboard
    Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');

    // Customer Analytics Routes
    Route::prefix('customer-analytics')->name('customer-analytics.')->group(function () {
        Route::get('/', [CustomerAnalyticsController::class, 'index'])->name('index');
        Route::get('/segments', [CustomerAnalyticsController::class, 'segments'])->name('segments');
        Route::get('/churn', [CustomerAnalyticsController::class, 'churn'])->name('churn');
    });

    // Sales Analytics
    Route::get('/sales/overview', [SalesAnalyticsController::class, 'index'])->name('sales.overview');

    // Products
    Route::resource('products', AdminProductController::class);

    // Orders
    Route::get('/orders', [AdminOrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/pending', [AdminOrderController::class, 'pending'])->name('orders.pending');

    // Settings
    Route::get('/settings', [AdminSettingsController::class, 'index'])->name('settings');
});

// Customer Analytics Routes
Route::prefix('admin/customer-analytics')->middleware(['auth', 'admin'])->group(function () {
    Route::get('/', [CustomerAnalyticsController::class, 'index'])->name('admin.customer-analytics.index');
    Route::get('/segment-suggestions', [CustomerAnalyticsController::class, 'getSegmentSuggestions'])->name('admin.customer-analytics.segment-suggestions');
    Route::get('/retention-campaign/{customerId}', [CustomerAnalyticsController::class, 'generateRetentionCampaign'])->name('admin.customer-analytics.retention-campaign');
});

Route::get('/admin/analytics/journey-analysis', [CustomerAnalyticsController::class, 'journeyAnalysis'])
    ->name('admin.analytics.journey-analysis');

Route::middleware(['auth', 'verified'])->group(function () {
    // ... existing routes ...

    // Admin routes
    Route::prefix('admin')->group(function () {
        // ... existing admin routes ...

        // Customer Analytics Routes
        Route::get('/analytics', [CustomerAnalyticsController::class, 'index'])->name('admin.analytics');
        Route::get('/analytics/trend-explanation', [CustomerAnalyticsController::class, 'getTrendExplanation'])->name('admin.analytics.trend-explanation');
        Route::get('/analytics/journey-insights', [CustomerAnalyticsController::class, 'getJourneyInsights'])->name('admin.analytics.journey-insights');
        Route::get('/analytics/journey-analysis', [CustomerAnalyticsController::class, 'journeyAnalysis'])->name('admin.analytics.journey-analysis');
    });
});

// Admin Analytics Routes
Route::middleware(['auth', 'admin'])->prefix('admin/analytics')->name('admin.analytics.')->group(function () {
    Route::get('/customer-analytics', [CustomerAnalyticsController::class, 'index'])->name('customer-analytics.index');
    Route::get('/explain-trend', [CustomerAnalyticsController::class, 'explainTrend'])->name('explain-trend');
    Route::get('/weekly-digest', [WeeklyDigestController::class, 'index'])->name('weekly-digest');
    Route::get('/journey-insights', [CustomerAnalyticsController::class, 'getJourneyInsights'])->name('journey-insights');
    Route::get('/trend-explanation', [CustomerAnalyticsController::class, 'getTrendExplanation'])->name('trend-explanation');
});


