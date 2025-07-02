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
use App\Http\Controllers\Admin\AIAssistantController;
use App\Http\Controllers\Admin\ChurnExportController;
// use App\Http\Controllers\Customer\CustomerSegmentController;
use App\Http\Controllers\Admin\CampaignTriggerController;
use App\Http\Controllers\Admin\IntegrationController;
use App\Http\Controllers\Admin\ChurnPredictionController;
use App\Http\Controllers\Admin\CampaignPerformanceController;
use App\Http\Controllers\Admin\RuleController;
use App\Http\Controllers\Admin\CampaignController;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Admin\SessionController;
use App\Http\Controllers\WebhookController;
use App\Http\Controllers\Admin\CashDrawerController;
use App\Http\Controllers\Admin\CashDrawerAlertController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\Admin\InventoryCategoryController;
use App\Http\Controllers\Admin\WeeklyStockCheckController;
use App\Http\Controllers\Admin\MonthlyStockCheckController;
use App\Http\Controllers\Admin\AuditReportController;
use App\Http\Controllers\Admin\InvestorController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\AIPopupController;
use App\Http\Controllers\PublicInvestmentController;
use App\Http\Controllers\CreditsController;
use App\Http\Controllers\Admin\UserBadgeController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/




// Public routes
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/statistics', [HomeController::class, 'getStatistics'])->name('statistics');
Route::get('/about', [HomeController::class, 'about'])->name('about');
Route::get('/contact', [HomeController::class, 'contact'])->name('contact');
Route::get('/terms', [HomeController::class, 'terms'])->name('terms');
Route::get('/privacy', [HomeController::class, 'privacy'])->name('privacy');
Route::get('/menu', function () {
    return view('pages.menu');
})->name('menu');
Route::get('/notifications', [\App\Http\Controllers\NotificationController::class, 'index'])->name('notifications');
Route::get('/finds', [\App\Http\Controllers\FindsController::class, 'index'])->name('finds');
Route::get('/finds/data', [\App\Http\Controllers\FindsController::class, 'data']);
Route::get('/leaderboard', [CreatorController::class, 'leaderboard'])->name('public.leaderboard');
Route::get('/offers', [HomeController::class, 'offers'])->name('offers');
Route::get('/search', [App\Http\Controllers\ProductController::class, 'search'])->name('search');
Route::get('/api/products/autocomplete', [App\Http\Controllers\ProductController::class, 'autocomplete'])->name('products.autocomplete');
Route::get('/categories/{category}', [CategoryController::class, 'show'])->name('categories.show');
Route::get('/bulk', [\App\Http\Controllers\BulkController::class, 'index'])->name('bulk');

// Public checkout routes (accessible without authentication)
Route::get('/cart', [CartController::class, 'index'])->name('cart');
Route::get('/checkout', [App\Http\Controllers\CheckoutController::class, 'index'])->name('checkout');
Route::get('/payment', [App\Http\Controllers\PaymentController::class, 'index'])->name('payment');
Route::post('/checkout', [CartController::class, 'checkout'])->name('checkout.post');

// Branch selection for checkout (public routes)
Route::post('/checkout/branches', [App\Http\Controllers\CheckoutController::class, 'getAvailableBranches'])->name('checkout.branches');
Route::get('/checkout/all-branches', [App\Http\Controllers\CheckoutController::class, 'getAllBranches'])->name('checkout.all-branches');

// User profile routes (web-based, session authenticated)
Route::middleware(['auth'])->group(function () {
    Route::get('/user/profile', [App\Http\Controllers\Api\UserController::class, 'getProfile'])->name('user.profile.get');
    Route::put('/user/profile', [App\Http\Controllers\Api\UserController::class, 'updateProfile'])->name('user.profile.update');
});

// Debug route for cart status logging
Route::post('/debug/cart-status', [CartController::class, 'debugCartStatus']);

// Authentication routes
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', function (Request $request) {
    try {
        // Delete all tokens for the user
        if (Auth::check()) {
            Auth::user()->tokens()->delete();
        }
        
        // Clear the session
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        // Log the logout
        \Log::info('User logged out successfully', [
            'user_id' => Auth::id(),
            'ip' => $request->ip()
        ]);
        
        return response()->json(['message' => 'Logged out successfully']);
    } catch (\Exception $e) {
        \Log::error('Logout failed', [
            'error' => $e->getMessage(),
            'user_id' => Auth::id()
        ]);
        return response()->json(['message' => 'Logout failed'], 500);
    }
})->name('logout')->middleware(['web', 'auth']);

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

    // Wallet balance API route
    Route::get('/api/user/wallet/balance', [App\Http\Controllers\Api\UserController::class, 'getWalletBalance'])
        ->middleware(['auth:sanctum'])
        ->name('api.user.wallet.balance');

    // Credits balance API route
    Route::get('/api/user/credits/balance', [App\Http\Controllers\Api\UserController::class, 'getCreditsBalance'])
        ->middleware(['auth:sanctum'])
        ->name('api.user.credits.balance');

    // Orders
    Route::get('/orders', [OrderController::class, 'index'])->name('orders');
    Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
    Route::get('/orders/{order}/success', [App\Http\Controllers\OrderController::class, 'success'])->name('orders.success');
    
    // Additional Cart AJAX Routes
    Route::post('/cart/add-to-cart', [CartController::class, 'addToCart'])->name('cart.add-to-cart');
    Route::post('/cart/update-quantity', [CartController::class, 'updateQuantity'])->name('cart.update-quantity');
    Route::post('/cart/remove-from-cart', [CartController::class, 'removeFromCart'])->name('cart.remove-from-cart');
    Route::post('/cart/clear', [CartController::class, 'clearCart'])->name('cart.clear');
    Route::get('/cart/get-cart', [CartController::class, 'getCart'])->name('cart.get-cart');
    Route::get('/cart/suggestions', [CartController::class, 'getSuggestions'])->name('cart.suggestions');
    Route::post('/cart/sync', [CartController::class, 'syncCart'])->name('cart.sync');

    // Coupon Routes
    Route::post('/coupon/apply', [CouponController::class, 'apply'])->name('coupon.apply');
    Route::post('/coupon/remove', [CouponController::class, 'remove'])->name('coupon.remove');

    // Checkout Routes - Remove the conflicting route that overrides CartController
    // Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout');
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
        
        // Employee Wallet Routes
        Route::get('/employee/wallet/scanner', [App\Http\Controllers\Employee\WalletTopUpController::class, 'scanner'])->name('employee.wallet.scanner');
        Route::post('/employee/wallet/process-qr', [App\Http\Controllers\Employee\WalletTopUpController::class, 'processQR'])->name('employee.wallet.process-qr');
        Route::post('/employee/wallet/topup', [App\Http\Controllers\Employee\WalletTopUpController::class, 'topUp'])->name('employee.wallet.topup');
        Route::post('/employee/wallet/balance-by-barcode', [App\Http\Controllers\Employee\WalletTopUpController::class, 'getBalanceByBarcode'])->name('employee.wallet.balance-by-barcode');

        // Employee Credits Routes
        Route::get('/employee/credits/scanner', [App\Http\Controllers\Employee\CreditsTopUpController::class, 'scanner'])->name('employee.credits.scanner');
        Route::post('/employee/credits/process-qr', [App\Http\Controllers\Employee\CreditsTopUpController::class, 'processQR'])->name('employee.credits.process-qr');
        Route::post('/employee/credits/topup', [App\Http\Controllers\Employee\CreditsTopUpController::class, 'topUp'])->name('employee.credits.topup');
        Route::post('/employee/credits/balance-by-barcode', [App\Http\Controllers\Employee\CreditsTopUpController::class, 'balanceByBarcode'])->name('employee.credits.balance-by-barcode');
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
    Route::get('/wallet/transactions/{user}', [WalletController::class, 'getTransactions'])->name('user-transactions');

    // Credits Routes
    Route::get('/credits', [CreditsController::class, 'index'])->name('user.credits.index');
    Route::get('/credits/transactions', [CreditsController::class, 'transactions'])->name('user.credits.transactions');
    Route::post('/credits/generate-qr', [CreditsController::class, 'generateQR'])->name('user.credits.generate-qr');
    Route::get('/credits/balance', [CreditsController::class, 'getBalance'])->name('user.credits.balance');

    // Payment routes
    Route::post('/payments/initialize', [App\Http\Controllers\PaymentController::class, 'initialize'])->name('payments.initialize');
    Route::post('/payments/{payment}/process', [App\Http\Controllers\PaymentController::class, 'process'])->name('payments.process');
    Route::get('/payments/{payment}/verify', [App\Http\Controllers\PaymentController::class, 'verify'])->name('payments.verify');
    Route::post('/payments/{payment}/cancel', [App\Http\Controllers\PaymentController::class, 'cancel'])->name('payments.cancel');
    Route::get('/payments/{payment}/receipt', [App\Http\Controllers\PaymentController::class, 'receipt'])->name('payments.receipt');

    // Offer claiming routes
    Route::post('/offers/claim', [App\Http\Controllers\OfferController::class, 'claim'])->name('offers.claim');
    Route::get('/offers/my-claims', [App\Http\Controllers\OfferController::class, 'myClaims'])->name('offers.my-claims');
    Route::post('/offers/apply', [App\Http\Controllers\OfferController::class, 'apply'])->name('offers.apply');
    Route::post('/offers/remove', [App\Http\Controllers\OfferController::class, 'remove'])->name('offers.remove');
    Route::get('/offers/available', [App\Http\Controllers\OfferController::class, 'available'])->name('offers.available');
});

// Admin routes
Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin'])->group(function () {
    // Dashboard - redirect route (no branch parameter)
    Route::get('/dashboard', function() {
        // Check if user has a selected branch in session
        $selectedBranchId = session('selected_branch_id');
        
        if ($selectedBranchId) {
            // Redirect to the selected branch dashboard
            return redirect()->route('admin.dashboard.branch', ['branch' => $selectedBranchId]);
        }
        
        // Check if there's a main branch
        $mainBranch = \App\Models\Branch::where('is_main', true)->first();
        
        if ($mainBranch) {
            // Redirect to main branch dashboard
            return redirect()->route('admin.dashboard.branch', ['branch' => $mainBranch->id]);
        }
        
        // If no main branch, redirect to branches selection
        return redirect()->route('admin.branches.index');
    })->name('dashboard');
    
    // Dashboard with branch parameter
    Route::get('/dashboard/{branch}', [AdminDashboardController::class, 'index'])->name('dashboard.branch');
    
    Route::get('/', function() {
        return redirect()->route('admin.branches.index');
    });

    // Campaigns
    Route::get('/campaigns', [CampaignController::class, 'index'])->name('campaigns.index');
    Route::get('/campaigns/create', [CampaignController::class, 'create'])->name('campaigns.create');
    Route::post('/campaigns', [CampaignController::class, 'store'])->name('campaigns.store');
    Route::get('/campaigns/{campaign}', [CampaignController::class, 'show'])->name('campaigns.show');
    Route::get('/campaigns/{campaign}/edit', [CampaignController::class, 'edit'])->name('campaigns.edit');
    Route::put('/campaigns/{campaign}', [CampaignController::class, 'update'])->name('campaigns.update');
    Route::delete('/campaigns/{campaign}', [CampaignController::class, 'destroy'])->name('campaigns.destroy');
    Route::put('/campaigns/{campaign}/status', [CampaignController::class, 'updateStatus'])->name('campaigns.status');

    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications');
    Route::post('/notifications/mark-as-read', [NotificationController::class, 'markAsRead'])->name('notifications.markAsRead');
    Route::post('/notifications/mark-all-as-read', [NotificationController::class, 'markAllAsRead'])->name('notifications.markAllAsRead');
    Route::delete('/notifications/{notification}', [NotificationController::class, 'destroy'])->name('notifications.destroy');

    // Sales Analytics
    Route::get('/sales/overview', [SalesAnalyticsController::class, 'index'])->name('sales.overview');

    // Products
    Route::resource('products', AdminProductController::class)->names([
        'index' => 'products.index',
        'create' => 'products.create',
        'store' => 'products.store',
        'show' => 'products.show',
        'edit' => 'products.edit',
        'update' => 'products.update',
        'destroy' => 'products.destroy',
    ]);

    // Test route
    Route::post('/test-product', function() {
        return response()->json(['success' => true, 'message' => 'Test route working']);
    })->name('test.product');

    // Orders
    Route::get('/orders', [AdminOrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [AdminOrderController::class, 'show'])->name('orders.show.details');
    Route::delete('/orders/{order}', [AdminOrderController::class, 'destroy'])->name('orders.destroy');
    Route::get('/orders/pending', [AdminOrderController::class, 'pending'])->name('orders.pending');
    Route::get('/orders/json', [AdminOrderController::class, 'getOrdersJson'])->name('orders.json');
    Route::post('/orders/process-payment', [AdminOrderController::class, 'processPayment'])->name('orders.process-payment');

    // Settings
    Route::get('/settings', [AdminSettingsController::class, 'index'])->name('settings');

    // Activity Log Routes
    Route::get('/activity-logs', [App\Http\Controllers\Admin\ActivityLogController::class, 'index'])->name('activity-logs.index');
    Route::get('/activity-logs/{activityLog}', [App\Http\Controllers\Admin\ActivityLogController::class, 'show'])->name('activity-logs.show');

    // Clock Routes
    Route::get('/clock', [App\Http\Controllers\Admin\ClockController::class, 'index'])->name('clock.index');
    Route::get('/clock/report', [App\Http\Controllers\Admin\ClockController::class, 'report'])->name('clock.report');
    Route::post('/clock/in', [App\Http\Controllers\Admin\ClockController::class, 'clockIn'])->name('clock.in');
    Route::post('/clock/out', [App\Http\Controllers\Admin\ClockController::class, 'clockOut'])->name('clock.out');
    Route::post('/clock/break/start', [App\Http\Controllers\Admin\ClockController::class, 'startBreak'])->name('clock.break.start');
    Route::post('/clock/break/end', [App\Http\Controllers\Admin\ClockController::class, 'endBreak'])->name('clock.break.end');
    Route::get('/clock/employees/search', [App\Http\Controllers\Admin\ClockController::class, 'searchEmployees'])->name('clock.employees.search');
    Route::get('/clock/logs', [App\Http\Controllers\Admin\ClockController::class, 'getTimeLogs'])->name('clock.logs');
    
    // Debug route
    Route::get('/debug-employees', function() {
        $employees = \App\Models\Employee::with('user')->get();
        $data = [];
        foreach($employees as $emp) {
            $data[] = [
                'id' => $emp->id,
                'name' => $emp->user->name,
                'branch_id' => $emp->branch_id
            ];
        }
        return response()->json($data);
    });

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
    
    // Inventory Categories (must come before inventory/{item} routes)
    Route::get('/inventory/categories', [App\Http\Controllers\Admin\InventoryCategoryController::class, 'index'])->name('inventory.categories.index');
    Route::get('/inventory/categories/create', [App\Http\Controllers\Admin\InventoryCategoryController::class, 'create'])->name('inventory.categories.create');
    Route::post('/inventory/categories', [App\Http\Controllers\Admin\InventoryCategoryController::class, 'store'])->name('inventory.categories.store');
    Route::get('/inventory/categories/{category}', [App\Http\Controllers\Admin\InventoryCategoryController::class, 'show'])->name('inventory.categories.show');
    Route::get('/inventory/categories/{category}/edit', [App\Http\Controllers\Admin\InventoryCategoryController::class, 'edit'])->name('inventory.categories.edit');
    Route::put('/inventory/categories/{category}', [App\Http\Controllers\Admin\InventoryCategoryController::class, 'update'])->name('inventory.categories.update');
    Route::delete('/inventory/categories/{category}', [App\Http\Controllers\Admin\InventoryCategoryController::class, 'destroy'])->name('inventory.categories.destroy');
    
    // Inventory Manage
    Route::get('/inventory/manage', [App\Http\Controllers\Admin\InventoryController::class, 'manage'])->name('inventory.manage');
    
    // Inventory Stock Check
    Route::get('/inventory/stock-check', [App\Http\Controllers\Admin\InventoryController::class, 'stockCheck'])->name('inventory.stock-check');
    
    // Daily Stock Check Routes
    Route::get('/inventory/checks', [App\Http\Controllers\Admin\InventoryCheckController::class, 'index'])->name('inventory.checks.index');
    Route::post('/inventory/checks', [App\Http\Controllers\Admin\InventoryCheckController::class, 'store'])->name('inventory.checks.store');
    
    // Weekly Stock Check Routes
    Route::get('/inventory/weekly-checks', [App\Http\Controllers\Admin\InventoryController::class, 'weeklyChecks'])->name('inventory.weekly-checks.index');
    Route::post('/inventory/weekly-checks', [App\Http\Controllers\Admin\InventoryController::class, 'storeWeeklyChecks'])->name('inventory.weekly-checks.store');
    
    // Monthly Stock Check Routes
    Route::get('/inventory/monthly-checks', [App\Http\Controllers\Admin\InventoryController::class, 'monthlyChecks'])->name('inventory.monthly-checks.index');
    Route::post('/inventory/monthly-checks', [App\Http\Controllers\Admin\InventoryController::class, 'storeMonthlyChecks'])->name('inventory.monthly-checks.store');
    
    // Audit Report Routes
    Route::get('/inventory/audit-reports', [App\Http\Controllers\Admin\AuditReportController::class, 'index'])->name('inventory.audit-reports.index');
    Route::get('/inventory/audit-reports/detailed', [App\Http\Controllers\Admin\AuditReportController::class, 'detailedReport'])->name('inventory.audit-reports.detailed');
    Route::get('/inventory/audit-reports/sessions', [App\Http\Controllers\Admin\AuditReportController::class, 'auditSessions'])->name('inventory.audit-reports.sessions');
    Route::get('/inventory/audit-reports/export-pdf', [App\Http\Controllers\Admin\AuditReportController::class, 'exportPdf'])->name('inventory.audit-reports.export-pdf');
    Route::get('/inventory/audit-reports/export-excel', [App\Http\Controllers\Admin\AuditReportController::class, 'exportExcel'])->name('inventory.audit-reports.export-excel');
    
    // Inventory Order Routes (must come before inventory/{item} routes)
    Route::get('/inventory/orders', [App\Http\Controllers\Admin\InventoryOrderController::class, 'index'])->name('inventory.orders.index');
    Route::get('/inventory/orders/list', [App\Http\Controllers\Admin\InventoryOrderController::class, 'ordersList'])->name('inventory.orders.list');
    Route::get('/inventory/orders/create', [App\Http\Controllers\Admin\InventoryOrderController::class, 'create'])->name('inventory.orders.create');
    Route::post('/inventory/orders', [App\Http\Controllers\Admin\InventoryOrderController::class, 'store'])->name('inventory.orders.store');
    Route::post('/inventory/forecast', [App\Http\Controllers\Admin\InventoryOrderController::class, 'generateForecast'])->name('inventory.forecast');
    Route::post('/inventory/orders/bulk-status-update', [App\Http\Controllers\Admin\InventoryOrderController::class, 'bulkStatusUpdate'])->name('inventory.orders.bulk-status-update');
    Route::get('/inventory/orders/supplier-view', [App\Http\Controllers\Admin\InventoryOrderController::class, 'supplierView'])->name('inventory.orders.supplier-view');
    Route::get('/inventory/orders/supplier-view-debug', function() {
        $branch = \App\Models\Branch::find(session('selected_branch_id'));
        $user = auth()->user();
        
        return response()->json([
            'authenticated' => auth()->check(),
            'user_role' => $user ? $user->roles->pluck('name')->first() : 'none',
            'selected_branch' => $branch ? [
                'id' => $branch->id,
                'name' => $branch->name,
                'is_main' => $branch->is_main
            ] : null,
            'all_branches' => \App\Models\Branch::all()->map(function($b) {
                return [
                    'id' => $b->id,
                    'name' => $b->name,
                    'is_main' => $b->is_main
                ];
            })
        ]);
    })->name('inventory.orders.supplier-view-debug');
    Route::get('/inventory/orders/history', [App\Http\Controllers\Admin\InventoryOrderController::class, 'history'])->name('inventory.orders.history');
    Route::get('/inventory/orders/export', [App\Http\Controllers\Admin\InventoryOrderController::class, 'export'])->name('inventory.orders.export');
    Route::get('/inventory/orders/{order}', [App\Http\Controllers\Admin\InventoryOrderController::class, 'show'])->name('inventory.orders.show');
    Route::get('/inventory/orders/{order}/edit', [App\Http\Controllers\Admin\InventoryOrderController::class, 'edit'])->name('inventory.orders.edit');
    Route::put('/inventory/orders/{order}', [App\Http\Controllers\Admin\InventoryOrderController::class, 'update'])->name('inventory.orders.update');
    Route::delete('/inventory/orders/{order}', [App\Http\Controllers\Admin\InventoryOrderController::class, 'destroy'])->name('inventory.orders.destroy');
    Route::post('/inventory/orders/{order}/confirm', [App\Http\Controllers\Admin\InventoryOrderController::class, 'confirm'])->name('inventory.orders.confirm');
    Route::post('/inventory/orders/{order}/detailed-confirm', [App\Http\Controllers\Admin\InventoryOrderController::class, 'detailedConfirm'])->name('inventory.orders.detailed-confirm');
    Route::post('/inventory/orders/{order}/cancel', [App\Http\Controllers\Admin\InventoryOrderController::class, 'cancel'])->name('inventory.orders.cancel');
    Route::post('/inventory/orders/{order}/status', [App\Http\Controllers\Admin\InventoryOrderController::class, 'updateStatus'])->name('inventory.orders.status');
    Route::post('/inventory/orders/{order}/process-branch-order', [App\Http\Controllers\Admin\InventoryOrderController::class, 'processBranchOrder'])->name('inventory.orders.process-branch-order');
    Route::post('/inventory/orders/{order}/distribute', [App\Http\Controllers\Admin\InventoryOrderController::class, 'distribute'])->name('inventory.orders.distribute');
    
    // Inventory Bulk Operations
    Route::post('/inventory/bulk-update', [App\Http\Controllers\Admin\InventoryController::class, 'bulkUpdate'])->name('inventory.bulk-update');
    Route::get('/inventory/export', [App\Http\Controllers\Admin\InventoryController::class, 'export'])->name('inventory.export');
    Route::post('/inventory/import', [App\Http\Controllers\Admin\InventoryController::class, 'import'])->name('inventory.import');
    
    // Inventory Item Routes (must come after specific routes)
    Route::get('/inventory/{item}', [App\Http\Controllers\Admin\InventoryController::class, 'show'])->name('inventory.show');
    Route::get('/inventory/{item}/edit', [App\Http\Controllers\Admin\InventoryController::class, 'edit'])->name('inventory.edit');
    Route::put('/inventory/{item}', [App\Http\Controllers\Admin\InventoryController::class, 'update'])->name('inventory.update');
    Route::delete('/inventory/{item}', [App\Http\Controllers\Admin\InventoryController::class, 'destroy'])->name('inventory.destroy');
    Route::post('/inventory/{item}/adjust', [App\Http\Controllers\Admin\InventoryController::class, 'adjust'])->name('inventory.adjust');

    // Supply Order Routes
    Route::get('/supply/orders', [App\Http\Controllers\Admin\SupplyOrderController::class, 'index'])->name('supply.orders.index');
    Route::get('/supply/orders/create', [App\Http\Controllers\Admin\SupplyOrderController::class, 'create'])->name('supply.orders.create');
    Route::post('/supply/orders', [App\Http\Controllers\Admin\SupplyOrderController::class, 'store'])->name('supply.orders.store');
    Route::get('/supply/orders/{order}', [App\Http\Controllers\Admin\SupplyOrderController::class, 'show'])->name('supply.orders.show');
    Route::get('/supply/orders/{order}/edit', [App\Http\Controllers\Admin\SupplyOrderController::class, 'edit'])->name('supply.orders.edit');
    Route::put('/supply/orders/{order}', [App\Http\Controllers\Admin\SupplyOrderController::class, 'update'])->name('supply.orders.update');
    Route::delete('/supply/orders/{order}', [App\Http\Controllers\Admin\SupplyOrderController::class, 'destroy'])->name('supply.orders.destroy');
    Route::post('/supply/orders/{order}/send', [App\Http\Controllers\Admin\SupplyOrderController::class, 'sendToSupplier'])->name('supply.orders.send');
    Route::post('/supply/orders/{order}/receive', [App\Http\Controllers\Admin\SupplyOrderController::class, 'partialReceive'])->name('supply.orders.receive');

    // Customer Analytics Routes
    Route::get('/analytics', [CustomerAnalyticsController::class, 'index'])->name('analytics');
    Route::get('/analytics/journey-insights', [CustomerAnalyticsController::class, 'getJourneyInsights'])->name('analytics.journey-insights');

    // Analytics Routes
    Route::prefix('analytics')->middleware(['auth', 'admin'])->group(function () {
        Route::get('/', [CustomerAnalyticsController::class, 'index'])->name('admin.analytics.index');
        Route::get('/segments', [CustomerAnalyticsController::class, 'segments'])->name('admin.analytics.segments');
        Route::get('/churn', [CustomerAnalyticsController::class, 'churn'])->name('admin.analytics.churn');
        Route::get('/segment-evolution', [CustomerAnalyticsController::class, 'getSegmentEvolution'])->name('admin.analytics.segment-evolution');
        Route::get('/segment-suggestions', [CustomerAnalyticsController::class, 'getSegmentSuggestions'])->name('admin.analytics.segment-suggestions');
        Route::post('/generate-campaign', [CustomerAnalyticsController::class, 'generateRetentionCampaign'])->name('admin.analytics.generate-campaign');
        Route::get('/export-segment/{segment}', [CustomerAnalyticsController::class, 'exportSegment'])->name('admin.analytics.export-segment');
        Route::get('/journey-analysis', [CustomerAnalyticsController::class, 'journeyAnalysis'])->name('admin.analytics.journey-analysis');
        Route::get('/journey-insights', [CustomerAnalyticsController::class, 'getJourneyInsights'])->name('admin.analytics.journey-insights');
        Route::get('/trend-explanation', [CustomerAnalyticsController::class, 'getTrendExplanation'])->name('admin.analytics.trend-explanation');
        Route::get('/weekly-digest', [WeeklyDigestController::class, 'index'])->name('admin.analytics.weekly-digest');
    });

    // Roles Routes
    Route::get('/roles', [App\Http\Controllers\Admin\RoleController::class, 'index'])->name('roles.index');
    Route::post('/roles', [App\Http\Controllers\Admin\RoleController::class, 'store'])->name('roles.store');
    Route::put('/roles/{role}', [App\Http\Controllers\Admin\RoleController::class, 'update'])->name('roles.update');
    Route::delete('/roles/{role}', [App\Http\Controllers\Admin\RoleController::class, 'destroy'])->name('roles.destroy');

    // Referral Settings Routes
    Route::get('/referral-settings', [App\Http\Controllers\Admin\ReferralSettingsController::class, 'index'])->name('referral-settings.index');
    Route::post('/referral-settings', [App\Http\Controllers\Admin\ReferralSettingsController::class, 'update'])->name('referral-settings.update');

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
    Route::get('/analytics/segment-evolution', [App\Http\Controllers\Admin\CustomerAnalyticsController::class, 'getSegmentEvolution'])->name('admin.analytics.segment-evolution');
    Route::post('/analytics/explain-trend', [App\Http\Controllers\Admin\CustomerAnalyticsController::class, 'explainTrend'])->name('analytics.explain-trend');
    Route::get('/analytics/segments', [App\Http\Controllers\Admin\CustomerAnalyticsController::class, 'segments'])->name('admin.analytics.segments');
    Route::get('/analytics/churn', [App\Http\Controllers\Admin\CustomerAnalyticsController::class, 'churn'])->name('admin.analytics.churn');
    Route::get('/analytics/segment-suggestions', [App\Http\Controllers\Admin\CustomerAnalyticsController::class, 'getSegmentSuggestions'])->name('admin.analytics.segment-suggestions');
    Route::post('/analytics/generate-campaign', [App\Http\Controllers\Admin\CustomerAnalyticsController::class, 'generateCampaign'])->name('admin.analytics.generate-campaign');
    Route::get('/analytics/export-segment/{segment}', [App\Http\Controllers\Admin\CustomerAnalyticsController::class, 'exportSegment'])->name('admin.analytics.export-segment');
    Route::post('/analytics/journey-analysis', [App\Http\Controllers\Admin\CustomerAnalyticsController::class, 'analyzeJourney'])->name('admin.analytics.journey-analysis.post');
    Route::get('/analytics/retention-campaign/{customerId}', [App\Http\Controllers\Admin\CustomerAnalyticsController::class, 'generateRetentionCampaign'])->name('admin.analytics.retention-campaign');

    Route::get('/notifications/churn-risks', [NotificationController::class, 'getChurnRisks'])->name('notifications.churn-risks');

    // Churn Data Export
    Route::get('/churn/export', [ChurnExportController::class, 'exportChurnData'])->name('churn.export');

    // Churn Prediction Routes
    Route::get('/churn', [ChurnPredictionController::class, 'index'])->name('churn.index');
    Route::post('/churn/update', [ChurnPredictionController::class, 'updatePredictions'])->name('churn.update');
    Route::get('/churn/{customer}', [ChurnPredictionController::class, 'show'])->name('churn.show');
    Route::get('/churn/export', [ChurnPredictionController::class, 'export'])->name('churn.export');

    // Campaign Performance Routes
    Route::get('/campaigns/performance', [CampaignPerformanceController::class, 'index'])->name('campaigns.performance');
    Route::get('/campaigns/{campaign}/performance', [CampaignPerformanceController::class, 'show'])->name('campaigns.performance.show');

    // Session Management Routes
    Route::post('/sessions/open', [SessionController::class, 'open'])->name('sessions.open');
    Route::post('/sessions/{session}/close', [SessionController::class, 'close'])->name('sessions.close');
    Route::get('/sessions', [SessionController::class, 'index'])->name('sessions.index');
    Route::get('/sessions/{session}', [SessionController::class, 'show'])->name('sessions.show');

    // Cash Denominations Management
    Route::get('/cash-denominations', [App\Http\Controllers\Admin\CashDenominationController::class, 'index'])->name('cash-denominations.index');
    Route::put('/cash-denominations/{denomination}', [App\Http\Controllers\Admin\CashDenominationController::class, 'update'])->name('cash-denominations.update');
    Route::get('/cash-denominations/{denomination}/history', [App\Http\Controllers\Admin\CashDenominationController::class, 'history'])->name('cash-denominations.history');
    Route::get('/cash-denominations/total', [App\Http\Controllers\Admin\CashDenominationController::class, 'getTotalCash'])->name('cash-denominations.total');

    // Admin Analytics Index Route
    Route::get('/analytics', [App\Http\Controllers\Admin\CustomerAnalyticsController::class, 'index'])->name('analytics.index');

    // Admin Analytics Weekly Digest Route
    Route::get('/analytics/weekly-digest', [App\Http\Controllers\Admin\WeeklyDigestController::class, 'index'])->name('analytics.weekly-digest');

    // Payment Management Routes
    Route::prefix('payments')->name('payments.')->group(function () {
        Route::get('/', [AdminPaymentController::class, 'index'])->name('index');
        Route::get('/order/{order}', [AdminPaymentController::class, 'showOrder'])->name('order.show');
        Route::post('/order/{order}/process', [AdminPaymentController::class, 'processPayment'])->name('order.process');
        Route::post('/cash-drawer/open', [AdminPaymentController::class, 'openCashDrawer'])->name('cash-drawer.open');
        Route::post('/cash-drawer/close', [AdminPaymentController::class, 'closeCashDrawer'])->name('cash-drawer.close');
        Route::get('/cash-drawer/status', [AdminPaymentController::class, 'getCashDrawerStatus'])->name('cash-drawer.status');
        Route::get('/wallet/{order}/balance', [AdminPaymentController::class, 'getWalletBalance'])->name('wallet.balance');
        Route::get('/wallet/number/{number}', [AdminPaymentController::class, 'getWalletBalanceByNumber'])->name('wallet.balance.number');
    });

    // Cash drawer routes
    Route::post('/admin/cash-drawer/open', [CashDrawerController::class, 'openDrawer'])->name('admin.cash-drawer.open');
    Route::post('/admin/cash-drawer/close', [CashDrawerController::class, 'closeDrawer'])->name('admin.cash-drawer.close');
    Route::get('/admin/cash-drawer/status', [CashDrawerController::class, 'getStatus'])->name('admin.cash-drawer.status');
    Route::get('/cash-drawer/session-sales', [App\Http\Controllers\Admin\CashDrawerController::class, 'getSessionSales'])->name('admin.cash-drawer.session-sales');
    
    // Cash Drawer Alert Routes
    Route::get('/cash-drawer/alerts', [App\Http\Controllers\Admin\CashDrawerAlertController::class, 'index'])->name('admin.cash-drawer.alerts.index');
    Route::post('/cash-drawer/alerts/update', [App\Http\Controllers\Admin\CashDrawerAlertController::class, 'update'])->name('admin.cash-drawer.alerts.update');
    Route::post('/cash-drawer/alerts/toggle', [App\Http\Controllers\Admin\CashDrawerAlertController::class, 'toggle'])->name('admin.cash-drawer.alerts.toggle');
    Route::post('/cash-drawer/alerts/current', [App\Http\Controllers\Admin\CashDrawerAlertController::class, 'getCurrentAlerts'])->name('admin.cash-drawer.alerts.current');
    // Cash Drawer Actions
    Route::post('/cash-drawer/open-physical', [App\Http\Controllers\Admin\CashDrawerController::class, 'openPhysicalDrawer'])->name('admin.cash-drawer.open-physical');

    // Suppliers
    Route::get('/suppliers', [App\Http\Controllers\Admin\SupplierController::class, 'index'])->name('suppliers.index');
    Route::get('/suppliers/create', [App\Http\Controllers\Admin\SupplierController::class, 'create'])->name('suppliers.create');
    Route::post('/suppliers', [App\Http\Controllers\Admin\SupplierController::class, 'store'])->name('suppliers.store');
    Route::get('/suppliers/{supplier}', [App\Http\Controllers\Admin\SupplierController::class, 'show'])->name('suppliers.show');
    Route::get('/suppliers/{supplier}/edit', [App\Http\Controllers\Admin\SupplierController::class, 'edit'])->name('suppliers.edit');
    Route::put('/suppliers/{supplier}', [App\Http\Controllers\Admin\SupplierController::class, 'update'])->name('suppliers.update');
    Route::delete('/suppliers/{supplier}', [App\Http\Controllers\Admin\SupplierController::class, 'destroy'])->name('suppliers.destroy');

    Route::resource('investors', App\Http\Controllers\Admin\InvestorController::class)->names([
        'index' => 'investors.index',
        'create' => 'investors.create',
        'store' => 'investors.store',
        'show' => 'investors.show',
        'edit' => 'investors.edit',
        'update' => 'investors.update',
        'destroy' => 'investors.destroy',
    ]);
    Route::get('investors/{investor}/dashboard', [App\Http\Controllers\Admin\InvestorController::class, 'dashboard'])->name('investors.dashboard');
    Route::get('investors/{investor}/investments', [App\Http\Controllers\Admin\InvestorController::class, 'investments'])->name('investors.investments');
    Route::post('investors/{investor}/investments', [App\Http\Controllers\Admin\InvestorController::class, 'storeInvestment'])->name('investors.investments.store');
    Route::get('investors/{investor}/payouts', [App\Http\Controllers\Admin\InvestorController::class, 'payouts'])->name('investors.payouts');
    Route::post('investors/{investor}/payouts', [App\Http\Controllers\Admin\InvestorController::class, 'storePayout'])->name('investors.payouts.store');
    Route::get('investors/{investor}/reports', [App\Http\Controllers\Admin\InvestorController::class, 'reports'])->name('investors.reports');
    Route::post('investors/{investor}/reports', [App\Http\Controllers\Admin\InvestorController::class, 'generateReport'])->name('investors.reports.generate');
});

// Public supplier routes for order confirmation (no authentication required)
Route::get('/supplier/order/{order}/confirm', [App\Http\Controllers\SupplierController::class, 'confirmOrder'])
    ->name('supplier.order.confirm');
Route::get('/supplier/order/{order}/view', [App\Http\Controllers\SupplierController::class, 'viewOrder'])
    ->name('supplier.order.view');

// New supplier order management routes
Route::post('/supplier/orders/{order}/confirm', [App\Http\Controllers\SupplierController::class, 'confirmFullOrder'])
    ->name('supplier.orders.confirm');
Route::post('/supplier/orders/{order}/partial-confirm', [App\Http\Controllers\SupplierController::class, 'confirmPartialOrder'])
    ->name('supplier.orders.partial-confirm');
Route::post('/supplier/orders/{order}/reject', [App\Http\Controllers\SupplierController::class, 'rejectOrder'])
    ->name('supplier.orders.reject');

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
Route::middleware(['auth', 'role:admin'])->prefix('wallet')->name('wallet.')->group(function () {
    // Main wallet routes
    Route::get('/', [WalletController::class, 'index'])->name('index');
    Route::get('/qr-generator', [WalletController::class, 'qrGenerator'])->name('qr-generator');
    Route::post('/generate-qr', [WalletController::class, 'generateQR'])->name('generate-qr');
    Route::get('/manage', [WalletController::class, 'manage'])->name('manage');
    Route::get('/export', [WalletController::class, 'export'])->name('export');
    Route::get('/transactions', [WalletController::class, 'transactions'])->name('transactions');
    Route::get('/transactions/{user}', [WalletController::class, 'getTransactions'])->name('user-transactions');

    // Top-up routes
    Route::get('/topup', [\App\Http\Controllers\Admin\WalletTopUpController::class, 'showTopUpForm'])->name('topup.form');
    Route::post('/topup/process', [\App\Http\Controllers\Admin\WalletTopUpController::class, 'processTopUp'])->name('topup.process');
    Route::post('/topup/generate-qr', [\App\Http\Controllers\Admin\WalletTopUpController::class, 'generateQR'])->name('topup.generate-qr');

    // Activity Logs
    Route::get('/activity-logs', [ActivityLogController::class, 'index'])->name('activity-logs.index');
    Route::get('/activity-logs/{activityLog}', [ActivityLogController::class, 'show'])->name('activity-logs.show');
});

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

// Cash Drawer Routes for Admin
Route::middleware(['auth', 'role:admin'])->prefix('admin')->group(function () {
    Route::post('/cash-drawer/open', [App\Http\Controllers\Admin\CashDrawerController::class, 'openSession'])->name('admin.cash-drawer.open');
    Route::post('/cash-drawer/close', [App\Http\Controllers\Admin\CashDrawerController::class, 'closeSession'])->name('admin.cash-drawer.close');
    Route::get('/cash-drawer/status', [App\Http\Controllers\Admin\CashDrawerController::class, 'getStatus'])->name('admin.cash-drawer.status');
    Route::get('/cash-drawer/session-sales', [App\Http\Controllers\Admin\CashDrawerController::class, 'getSessionSales'])->name('admin.cash-drawer.session-sales');
    Route::post('/cash-drawer/adjust', [App\Http\Controllers\Admin\CashDrawerController::class, 'adjustDenominations'])->name('admin.cash-drawer.adjust');
    Route::post('/cash-drawer/update-denominations', [App\Http\Controllers\Admin\CashDrawerController::class, 'updateDenominationsWithPassword'])->name('admin.cash-drawer.update-denominations');
    // Cash Drawer Actions
    Route::post('/cash-drawer/open-physical', [App\Http\Controllers\Admin\CashDrawerController::class, 'openPhysicalDrawer'])->name('admin.cash-drawer.open-physical');
    // Cash Drawer Alert Routes
    Route::get('/cash-drawer/alerts', [App\Http\Controllers\Admin\CashDrawerAlertController::class, 'index'])->name('admin.cash-drawer.alerts.index');
    Route::post('/cash-drawer/alerts/update', [App\Http\Controllers\Admin\CashDrawerAlertController::class, 'update'])->name('admin.cash-drawer.alerts.update');
    Route::post('/cash-drawer/alerts/toggle', [App\Http\Controllers\Admin\CashDrawerAlertController::class, 'toggle'])->name('admin.cash-drawer.alerts.toggle');
    Route::post('/cash-drawer/alerts/current', [App\Http\Controllers\Admin\CashDrawerAlertController::class, 'getCurrentAlerts'])->name('admin.cash-drawer.alerts.current');
});

// Creator management routes
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::resource('creators', App\Http\Controllers\Admin\AdminCreatorController::class)->names('admin.creators');
});

Route::get('/test-openai', [OpenAITestController::class, 'testBasicCompletion']);

Route::get('/api/sales/overview', [SalesAnalyticsController::class, 'getSalesOverview']);

// AI Assistant Routes
Route::post('/api/customer-analytics/ai-assistant', [App\Http\Controllers\Admin\AIAssistantController::class, 'handleRequest'])
    ->name('admin.analytics.ai-assistant')
    ->middleware(['auth', 'admin']);

// Campaign Triggers
Route::middleware(['auth', 'admin'])->prefix('admin/campaigns/triggers')->name('admin.campaigns.triggers.')->group(function () {
    Route::get('/', [CampaignTriggerController::class, 'index'])->name('index');
    Route::get('/create', [CampaignTriggerController::class, 'create'])->name('create');
    Route::post('/', [CampaignTriggerController::class, 'store'])->name('store');
    Route::get('/{trigger}/edit', [CampaignTriggerController::class, 'edit'])->name('edit');
    Route::put('/{trigger}', [CampaignTriggerController::class, 'update'])->name('update');
    Route::delete('/{trigger}', [CampaignTriggerController::class, 'destroy'])->name('destroy');
    Route::post('/{trigger}/toggle', [CampaignTriggerController::class, 'toggleStatus'])->name('toggle');
    Route::post('/{trigger}/test', [CampaignTriggerController::class, 'testTrigger'])->name('test');
});

// Integration Routes
Route::prefix('integrations')->group(function () {
    // Mailchimp Routes
    Route::get('/mailchimp/lists', [IntegrationController::class, 'getMailchimpLists'])->name('admin.integrations.mailchimp.lists');
    Route::post('/mailchimp/sync', [IntegrationController::class, 'syncWithMailchimp'])->name('admin.integrations.mailchimp.sync');
    
    // Twilio Routes
    Route::get('/twilio/groups', [IntegrationController::class, 'getTwilioGroups'])->name('admin.integrations.twilio.groups');
    Route::post('/twilio/sync', [IntegrationController::class, 'syncWithTwilio'])->name('admin.integrations.twilio.sync');
});

// Branch Selection Route
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::post('/branches/select', [BranchController::class, 'select'])->name('branches.select');
});

// Rules Builder Routes
Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('/admin/rules', [RuleController::class, 'index'])->name('admin.rules.index');
    Route::get('/admin/rules/create', [RuleController::class, 'create'])->name('admin.rules.create');
    Route::post('/admin/rules', [RuleController::class, 'store'])->name('admin.rules.store');
    Route::get('/admin/rules/{rule}/edit', [RuleController::class, 'edit'])->name('admin.rules.edit');
    Route::put('/admin/rules/{rule}', [RuleController::class, 'update'])->name('admin.rules.update');
    Route::delete('/admin/rules/{rule}', [RuleController::class, 'destroy'])->name('admin.rules.destroy');
    Route::patch('/admin/rules/{rule}/toggle', [RuleController::class, 'toggle'])->name('admin.rules.toggle');
});

// Payment Management Routes
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/admin/payments', [App\Http\Controllers\Admin\PaymentController::class, 'index'])->name('admin.payments.index');
    Route::get('/admin/payments/{payment}', [App\Http\Controllers\Admin\PaymentController::class, 'show'])->name('admin.payments.show');
    Route::post('/admin/payments/{payment}/cancel', [App\Http\Controllers\Admin\PaymentController::class, 'cancel'])->name('admin.payments.cancel');
    Route::get('/admin/payments/methods', [App\Http\Controllers\Admin\PaymentController::class, 'methods'])->name('admin.payments.methods');
    Route::get('/admin/payments/sessions', [App\Http\Controllers\Admin\PaymentController::class, 'sessions'])->name('admin.payments.sessions');
});

// Payment gateway webhook endpoint
Route::post('/webhooks/payment-status', [WebhookController::class, 'paymentStatus'])->name('webhooks.payment-status');

// Session Management Routes
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/sessions', [App\Http\Controllers\Admin\SessionController::class, 'index'])->name('sessions.index');
    Route::post('/sessions', [App\Http\Controllers\Admin\SessionController::class, 'store'])->name('sessions.store');
    Route::get('/sessions/{session}', [App\Http\Controllers\Admin\SessionController::class, 'show'])->name('sessions.show');
    Route::put('/sessions/{session}/close', [App\Http\Controllers\Admin\SessionController::class, 'close'])->name('sessions.close');
});

// Add this route for admin payment processing
Route::middleware(['auth', 'role:admin'])->post('/admin/payments', [AdminPaymentController::class, 'store'])->name('admin.payments.store');

// Investor Dashboard Routes
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/investor-dashboard', [App\Http\Controllers\Admin\InvestorDashboardController::class, 'index'])->name('investor.dashboard');
});

// Test route for debugging
Route::get('/test-orders', function() {
    try {
        $orders = \App\Models\Order::where('branch_id', 1)
            ->where('status', '!=', 'completed')
            ->with(['items.product', 'table', 'user'])
            ->orderBy('created_at', 'desc')
            ->get();
        
        return response()->json([
            'success' => true,
            'count' => $orders->count(),
            'orders' => $orders->take(5)->toArray()
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ], 500);
    }
});

// Test route for admin layout
Route::get('/admin/test', function() {
    return view('admin.investors.test');
})->middleware(['auth', 'admin'])->name('admin.test');

// Investor Routes
Route::middleware(['auth', 'role:investor'])->prefix('investor')->name('investor.')->group(function () {
    Route::get('/dashboard', [App\Http\Controllers\Investor\DashboardController::class, 'dashboard'])->name('dashboard');
    Route::get('/investments', [App\Http\Controllers\Investor\DashboardController::class, 'investments'])->name('investments');
    Route::get('/payouts', [App\Http\Controllers\Investor\DashboardController::class, 'payouts'])->name('payouts');
    Route::get('/reports', [App\Http\Controllers\Investor\DashboardController::class, 'reports'])->name('reports');
    Route::put('/profile', [App\Http\Controllers\Investor\DashboardController::class, 'updateProfile'])->name('profile.update');
});

// Admin Offer Management
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::resource('offers', App\Http\Controllers\Admin\OfferController::class);
});

// Admin Bulk Package Management
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::resource('bulk-packages', App\Http\Controllers\Admin\BulkPackageController::class);
    Route::post('bulk-packages/{bulkPackage}/toggle-status', [App\Http\Controllers\Admin\BulkPackageController::class, 'toggleStatus'])->name('bulk-packages.toggle-status');
});

// AI Offers Management
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/ai-offers', [App\Http\Controllers\Admin\AIOfferController::class, 'index'])->name('ai-offers.index');
    Route::post('/ai-offers/generate', [App\Http\Controllers\Admin\AIOfferController::class, 'generate'])->name('ai-offers.generate');
    Route::post('/ai-offers/personalized', [App\Http\Controllers\Admin\AIOfferController::class, 'generatePersonalized'])->name('ai-offers.personalized');
    Route::get('/ai-offers/{offer}', [App\Http\Controllers\Admin\AIOfferController::class, 'show'])->name('ai-offers.show');
    Route::get('/ai-offers/{offer}/edit', [App\Http\Controllers\Admin\AIOfferController::class, 'edit'])->name('ai-offers.edit');
    Route::put('/ai-offers/{offer}', [App\Http\Controllers\Admin\AIOfferController::class, 'update'])->name('ai-offers.update');
    Route::delete('/ai-offers/{offer}', [App\Http\Controllers\Admin\AIOfferController::class, 'destroy'])->name('ai-offers.destroy');
    Route::post('/ai-offers/{offer}/toggle-status', [App\Http\Controllers\Admin\AIOfferController::class, 'toggleStatus'])->name('ai-offers.toggle-status');
    Route::get('/ai-offers/analytics/overview', [App\Http\Controllers\Admin\AIOfferController::class, 'analytics'])->name('ai-offers.analytics');
    Route::get('/ai-offers/users/search', [App\Http\Controllers\Admin\AIOfferController::class, 'getUsers'])->name('ai-offers.users');
});

Route::post('/reviews', [ReviewController::class, 'store'])->name('reviews.store')->middleware('auth');

// AI Popup Routes
Route::get('/ai-popup/decision', [App\Http\Controllers\AIPopupController::class, 'getPopupDecision'])->name('ai-popup.decision');
Route::post('/ai-popup/track', [App\Http\Controllers\AIPopupController::class, 'trackInteraction'])->name('ai-popup.track');
Route::get('/ai-popup/analytics', [App\Http\Controllers\AIPopupController::class, 'getAnalytics'])->name('ai-popup.analytics')->middleware(['auth', 'admin']);
Route::post('/ai-popup/reset', [App\Http\Controllers\AIPopupController::class, 'resetPopupState'])->name('ai-popup.reset'); // Temporary for testing
Route::get('/ai-popup/reset-frequency', [App\Http\Controllers\AIPopupController::class, 'resetFrequency'])->name('ai-popup.reset-frequency'); // Reset frequency limits

// AI Popup Admin Routes
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/ai-popup', function() {
        return view('admin.ai-popup.index');
    })->name('ai-popup.index');
});

// Site Settings Routes
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/site-settings', [App\Http\Controllers\Admin\SiteSettingsController::class, 'index'])->name('site-settings.index');
    Route::put('/site-settings', [App\Http\Controllers\Admin\SiteSettingsController::class, 'update'])->name('site-settings.update');
    Route::patch('/site-settings/{setting}/toggle', [App\Http\Controllers\Admin\SiteSettingsController::class, 'toggle'])->name('site-settings.toggle');
});

// Public investment registration and leaderboard
Route::get('/invest', [\App\Http\Controllers\PublicInvestmentController::class, 'index'])->name('public.investment.index');
Route::post('/invest/register', [\App\Http\Controllers\PublicInvestmentController::class, 'register'])->name('public.investment.register');
Route::get('/invest/leaderboard', [\App\Http\Controllers\PublicInvestmentController::class, 'leaderboard'])->name('public.investment.leaderboard');

// User Theme Routes
Route::middleware(['auth'])->group(function () {
    Route::get('/user/themes', [App\Http\Controllers\UserThemeController::class, 'index'])->name('user.themes.index');
    Route::post('/user/themes/activate', [App\Http\Controllers\UserThemeController::class, 'activate'])->name('user.themes.activate');
    Route::post('/user/themes/sync', [App\Http\Controllers\UserThemeController::class, 'sync'])->name('user.themes.sync');
});

// Test route for QR code generation
Route::get('/test-qr', function() {
    $user = \App\Models\User::find(31); // User with credits account
    if ($user && $user->creditsAccount) {
        $qrCode = $user->creditsAccount->generateQRCode();
        return response()->json([
            'success' => true,
            'qr_code' => $qrCode,
            'is_url' => filter_var($qrCode, FILTER_VALIDATE_URL),
            'account_number' => $user->creditsAccount->account_number
        ]);
    }
    return response()->json(['success' => false, 'message' => 'No credits account found']);
});

// Admin badge management
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('badges', [\App\Http\Controllers\Admin\UserBadgeController::class, 'index'])->name('badges.index');
    Route::get('badges/{user}', [\App\Http\Controllers\Admin\UserBadgeController::class, 'show'])->name('badges.show');
});

// Test route for debugging admin access
Route::get('/test-admin', function() {
    if (!auth()->check()) {
        return 'Not logged in';
    }
    
    $user = auth()->user();
    $isAdmin = $user->hasRole('admin');
    
    return response()->json([
        'logged_in' => true,
        'user_email' => $user->email,
        'is_admin' => $isAdmin,
        'user_roles' => $user->getRoleNames()->toArray()
    ]);
})->middleware('auth');

// Admin login helper (for testing only - remove in production)
Route::get('/admin-login-helper', function() {
    $adminUser = \App\Models\User::role('admin')->first();
    if (!$adminUser) {
        return 'No admin user found';
    }
    
    return view('admin-login-helper', [
        'admin_email' => $adminUser->email,
        'admin_password' => 'password' // Default password
    ]);
});


