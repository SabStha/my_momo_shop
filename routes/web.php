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
Route::middleware(['auth', 'role:admin|cashier'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
    Route::get('/payment-manager', [PaymentManagerController::class, 'index'])->name('payment-manager.index');
    Route::post('/payment-manager/process', [PaymentManagerController::class, 'processPayment'])->name('payment-manager.process');
    Route::get('/payment-manager/history', [PaymentManagerController::class, 'history'])->name('payment-manager.history');
    
    // Product Management Routes
    Route::get('/products', [ProductController::class, 'index'])->name('products.index');
    Route::get('/products/create', [ProductController::class, 'create'])->name('products.create');
    Route::post('/products', [ProductController::class, 'store'])->name('products.store');
    Route::get('/products/{product}', [ProductController::class, 'show'])->name('products.show');
    Route::get('/products/{product}/edit', [ProductController::class, 'edit'])->name('products.edit');
    Route::put('/products/{product}', [ProductController::class, 'update'])->name('products.update');
    Route::delete('/products/{product}', [ProductController::class, 'destroy'])->name('products.destroy');
    
    // Inventory Management Routes
    Route::get('/inventory', [InventoryController::class, 'index'])->name('inventory.index');
    Route::get('/inventory/create', [InventoryController::class, 'create'])->name('inventory.create');
    Route::post('/inventory', [InventoryController::class, 'store'])->name('inventory.store');
    Route::get('/inventory/{item}', [InventoryController::class, 'show'])->name('inventory.show');
    Route::get('/inventory/{item}/edit', [InventoryController::class, 'edit'])->name('inventory.edit');
    Route::put('/inventory/{item}', [InventoryController::class, 'update'])->name('inventory.update');
    Route::delete('/inventory/{item}', [InventoryController::class, 'destroy'])->name('inventory.destroy');
    Route::get('/inventory/manage', [InventoryController::class, 'manage'])->name('inventory.manage');
    Route::post('/inventory/bulk-update', [InventoryController::class, 'bulkUpdate'])->name('inventory.bulk-update');

    // Inventory Categories Routes
    Route::prefix('inventory/categories')->name('admin.inventory.categories.')->group(function () {
        Route::get('/', [CategoryController::class, 'index'])->name('index');
        Route::get('/create', [CategoryController::class, 'create'])->name('create');
        Route::post('/', [CategoryController::class, 'store'])->name('store');
        Route::get('/{category}', [CategoryController::class, 'show'])->name('show');
        Route::get('/{category}/edit', [CategoryController::class, 'edit'])->name('edit');
        Route::put('/{category}', [CategoryController::class, 'update'])->name('update');
        Route::delete('/{category}', [CategoryController::class, 'destroy'])->name('destroy');
    });
    
    // Category Management Routes
    Route::get('/inventory/categories', [CategoryController::class, 'index'])->name('admin.inventory.categories.index');
    Route::post('/inventory/categories', [CategoryController::class, 'store'])->name('admin.inventory.categories.store');
    Route::get('/inventory/categories/{id}/edit', [CategoryController::class, 'edit'])->name('admin.inventory.categories.edit');
    Route::put('/inventory/categories/{id}', [CategoryController::class, 'update'])->name('admin.inventory.categories.update');
    Route::delete('/inventory/categories/{id}', [CategoryController::class, 'destroy'])->name('admin.inventory.categories.destroy');
    
    // Supplier Management Routes
    Route::get('/suppliers', [SupplierController::class, 'index'])->name('suppliers.index');
    Route::get('/suppliers/create', [SupplierController::class, 'create'])->name('suppliers.create');
    Route::post('/suppliers', [SupplierController::class, 'store'])->name('suppliers.store');
    Route::get('/suppliers/{supplier}', [SupplierController::class, 'show'])->name('suppliers.show');
    Route::get('/suppliers/{supplier}/edit', [SupplierController::class, 'edit'])->name('suppliers.edit');
    Route::put('/suppliers/{supplier}', [SupplierController::class, 'update'])->name('suppliers.update');
    Route::delete('/suppliers/{supplier}', [SupplierController::class, 'destroy'])->name('suppliers.destroy');
    
    // Employee Management Routes
    Route::get('/employees', [EmployeeController::class, 'index'])->name('employees.index');
    Route::get('/employees/create', [EmployeeController::class, 'create'])->name('employees.create');
    Route::post('/employees', [EmployeeController::class, 'store'])->name('employees.store');
    Route::get('/employees/{employee}', [EmployeeController::class, 'show'])->name('employees.show');
    Route::get('/employees/{employee}/edit', [EmployeeController::class, 'edit'])->name('employees.edit');
    Route::put('/employees/{employee}', [EmployeeController::class, 'update'])->name('employees.update');
    Route::delete('/employees/{employee}', [EmployeeController::class, 'destroy'])->name('employees.destroy');
    
    // Clock In/Out Routes
    Route::get('/clock', [ClockController::class, 'index'])->name('clock.index');
    Route::post('/clock/in', [ClockController::class, 'clockIn'])->name('clock.in');
    Route::post('/clock/out', [ClockController::class, 'clockOut'])->name('clock.out');
    Route::post('/clock/break/start', [ClockController::class, 'startBreak'])->name('clock.break.start');
    Route::post('/clock/break/end', [ClockController::class, 'endBreak'])->name('clock.break.end');
    Route::get('/clock/search', [ClockController::class, 'searchEmployees'])->name('clock.search');
    Route::get('/clock/logs', [ClockController::class, 'getTimeLogs'])->name('clock.logs');
    Route::get('/clock/report', function() {
        $branch = session('selected_branch');
        $employees = $branch ? Employee::with('user')->where('branch_id', $branch->id)->get() : collect();
        return view('admin.clock.report', compact('employees', 'branch'));
    })->name('clock.report');
    Route::post('/clock/report/generate', function() {
        // Placeholder: You can implement report generation logic here
        return back()->with('success', 'Report generated (placeholder).');
    })->name('clock.report.generate');

    // Wallet Routes
    Route::get('/wallet', [WalletController::class, 'index'])->name('wallet.index');
    Route::get('/wallet/transactions', [WalletController::class, 'transactions'])->name('wallet.transactions');
    Route::post('/wallet/deposit', [WalletController::class, 'deposit'])->name('wallet.deposit');
    Route::post('/wallet/withdraw', [WalletController::class, 'withdraw'])->name('wallet.withdraw');
    Route::get('/wallet/statement', [WalletController::class, 'statement'])->name('wallet.statement');
    
    // Admin-only routes
    Route::middleware(['role:admin'])->group(function () {
        Route::get('/creators', [AdminCreatorController::class, 'index'])->name('creators.index');
        Route::get('/creators/create', [AdminCreatorController::class, 'create'])->name('creators.create');
        Route::post('/creators', [AdminCreatorController::class, 'store'])->name('creators.store');
        Route::get('/creators/{creator}', [AdminCreatorController::class, 'show'])->name('creators.show');
        Route::get('/creators/{creator}/edit', [AdminCreatorController::class, 'edit'])->name('creators.edit');
        Route::put('/creators/{creator}', [AdminCreatorController::class, 'update'])->name('creators.update');
        Route::delete('/creators/{creator}', [AdminCreatorController::class, 'destroy'])->name('creators.destroy');
        
        Route::get('/referral-settings', [ReferralSettingsController::class, 'index'])->name('referral-settings.index');
        Route::put('/referral-settings', [ReferralSettingsController::class, 'update'])->name('referral-settings.update');
        
        Route::get('/roles', [RoleController::class, 'index'])->name('roles.index');
        Route::get('/roles/create', [RoleController::class, 'create'])->name('roles.create');
        Route::post('/roles', [RoleController::class, 'store'])->name('roles.store');
        Route::get('/roles/{role}', [RoleController::class, 'show'])->name('roles.show');
        Route::get('/roles/{role}/edit', [RoleController::class, 'edit'])->name('roles.edit');
        Route::put('/roles/{role}', [RoleController::class, 'update'])->name('roles.update');
        Route::delete('/roles/{role}', [RoleController::class, 'destroy'])->name('roles.destroy');
        
        Route::get('/branches', [BranchController::class, 'index'])->name('branches.index');
        Route::get('/branches/create', [BranchController::class, 'create'])->name('branches.create');
        Route::post('/branches', [BranchController::class, 'store'])->name('branches.store');
        Route::get('/branches/{branch}', [BranchController::class, 'show'])->name('branches.show');
        Route::get('/branches/{branch}/edit', [BranchController::class, 'edit'])->name('branches.edit');
        Route::put('/branches/{branch}', [BranchController::class, 'update'])->name('branches.update');
        Route::delete('/branches/{branch}', [BranchController::class, 'destroy'])->name('branches.destroy');
        Route::post('/branches/{branch}/switch', [BranchController::class, 'switch'])->name('branches.switch');
    });

    // Order Management Routes
    Route::prefix('orders')->name('orders.')->group(function () {
        Route::get('/', [OrderController::class, 'index'])->name('index');
        Route::get('/create', [OrderController::class, 'create'])->name('create');
        Route::post('/', [OrderController::class, 'store'])->name('store');
        Route::get('/{order}', [OrderController::class, 'show'])->name('show');
        Route::get('/{order}/edit', [OrderController::class, 'edit'])->name('edit');
        Route::put('/{order}', [OrderController::class, 'update'])->name('update');
        Route::delete('/{order}', [OrderController::class, 'destroy'])->name('destroy');
        Route::post('/{order}/status', [OrderController::class, 'updateStatus'])->name('status.update');
        Route::post('/{order}/delivery', [OrderController::class, 'updateDelivery'])->name('delivery.update');
    });

    // Wallet Topup Routes
    Route::get('/wallet/topup/login', [WalletController::class, 'topupLogin'])->name('wallet.topup.login');
    Route::post('/wallet/topup/login/process', [WalletController::class, 'processTopupLogin'])->name('wallet.topup.login.process');
    Route::get('/wallet/topup/verify', [WalletController::class, 'topupVerify'])->name('wallet.topup.verify');
    Route::get('/wallet/topup/logout', [WalletController::class, 'topupLogout'])->name('wallet.topup.logout');
    Route::get('/wallet/qr-generator', [WalletController::class, 'qrGenerator'])->name('wallet.qr-generator');
    Route::post('/wallet/qr-generator', [WalletController::class, 'generateQr'])->name('wallet.qr-generator');
    Route::post('/wallet/topup', [WalletController::class, 'topup'])->name('wallet.topup');
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


