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
use App\Http\Controllers\Admin\SupplierController;
use App\Http\Controllers\Admin\InventoryCheckController;
use App\Http\Controllers\Admin\InventoryOrderController;
use App\Http\Controllers\Admin\SupplyOrderController;
use App\Http\Controllers\ReferralController;
use App\Http\Controllers\User\AccountController;
use App\Http\Controllers\WalletController;
use App\Http\Controllers\AdminController;

// Public routes
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/offers', [HomeController::class, 'offers'])->name('offers');
Route::get('/cart', [CartController::class, 'index'])->name('cart');
Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications');
Route::post('/cart/add/{product}', [CartController::class, 'add'])->name('cart.add');
Route::post('/clear-referral-discount', [HomeController::class, 'clearReferralDiscount'])->name('clear.referral.discount');
Route::get('/bulk-orders', [HomeController::class, 'bulkOrders'])->name('bulk.orders');
Route::get('/search', [HomeController::class, 'search'])->name('search');
Route::get('/account', [HomeController::class, 'account'])->name('account');
Route::get('/ref/{code}', [ReferralController::class, 'showInstallPrompt'])->name('referral.install');

// Authentication routes with rate limiting
Route::middleware(['throttle:5,1'])->group(function () {
    Route::post('/login', [LoginController::class, 'login'])->name('login');
    Route::post('/register', [RegisterController::class, 'register'])->name('register');
    Route::post('/password/email', [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');
    Route::post('/password/reset', [ResetPasswordController::class, 'reset'])->name('password.update');
});

// Other auth routes without rate limiting
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
Route::get('/password/reset', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
Route::get('/password/reset/{token}', [ResetPasswordController::class, 'showResetForm'])->name('password.reset');

// Protected routes - require authentication
Route::middleware(['auth'])->group(function () {
    // Dashboard routes
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
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

    // My Account routes
    Route::get('/my-account', [\App\Http\Controllers\AccountController::class, 'show'])->name('my-account');
    Route::post('/my-account', [\App\Http\Controllers\AccountController::class, 'update'])->name('my-account.update');
    Route::post('/my-account/password', [\App\Http\Controllers\AccountController::class, 'updatePassword'])->name('my-account.password.update');
    Route::post('/my-account/settings', [\App\Http\Controllers\AccountController::class, 'updateSettings'])->name('my-account.settings.update');
    Route::get('/my-account/referral', [\App\Http\Controllers\AccountController::class, 'getReferralCode'])->name('my-account.referral');
    Route::get('/my-account/orders/{order}', [\App\Http\Controllers\OrderController::class, 'show'])->name('my-account.orders.show');
    Route::post('/my-account/profile-picture', [\App\Http\Controllers\AccountController::class, 'updateProfilePicture'])->name('my-account.update-profile-picture');

    // Wallet routes
    Route::get('/wallet', [WalletController::class, 'index'])->name('wallet.index');
    Route::get('/wallet/topup', [WalletController::class, 'showTopUp'])->name('wallet.topup');
    Route::get('/wallet/scan', [WalletController::class, 'showScanner'])->name('wallet.scan');
    Route::post('/wallet/process-topup', [WalletController::class, 'processTopUp'])->name('wallet.process-topup');
});

// Admin routes - These routes are now handled in routes/admin.php via RouteServiceProvider
// Keeping only essential admin routes that aren't in admin.php
Route::middleware(['auth', 'role:admin'])->group(function () {
    // These admin routes are handled by AdminController (different from Admin\DashboardController)
    Route::get('/admin/users', [AdminController::class, 'users'])->name('admin.users');
    Route::get('/admin/orders', [AdminController::class, 'orders'])->name('admin.orders');
    Route::get('/admin/settings', [AdminController::class, 'settings'])->name('admin.settings');
    Route::get('/admin/transactions', [AdminController::class, 'transactions'])->name('admin.transactions');
    Route::get('/admin/wallet-transactions', [AdminController::class, 'walletTransactions'])->name('admin.wallet-transactions');
    Route::get('/admin/notifications', [AdminController::class, 'notifications'])->name('admin.notifications');
    Route::get('/admin/profile', [AdminController::class, 'profile'])->name('admin.profile');
    Route::get('/admin/help', [AdminController::class, 'help'])->name('admin.help');
    Route::get('/admin/logout', [AdminController::class, 'logout'])->name('admin.logout');

    // Admin Suppliers
    Route::prefix('admin/suppliers')->name('admin.suppliers.')->group(function () {
        Route::get('/', [SupplierController::class, 'index'])->name('index');
        Route::get('/create', [SupplierController::class, 'create'])->name('create');
        Route::post('/', [SupplierController::class, 'store'])->name('store');
        Route::get('/{supplier}', [SupplierController::class, 'show'])->name('show');
        Route::get('/{supplier}/edit', [SupplierController::class, 'edit'])->name('edit');
        Route::put('/{supplier}', [SupplierController::class, 'update'])->name('update');
        Route::delete('/{supplier}', [SupplierController::class, 'destroy'])->name('destroy');
    });

    // Admin Coupons
    Route::prefix('admin/coupons')->name('admin.coupons.')->group(function () {
        Route::get('/create', [CouponController::class, 'create'])->name('create');
        Route::post('/', [CouponController::class, 'store'])->name('store');
    });

    // Admin Roles
    Route::prefix('admin/roles')->name('admin.roles.')->group(function () {
        Route::get('/', [AdminRoleController::class, 'index'])->name('index');
        Route::post('/{user}', [AdminRoleController::class, 'update'])->name('update');
    });

    // Admin Payouts
    Route::prefix('admin/payouts')->name('admin.payouts.')->group(function () {
        Route::get('/', [AdminPayoutController::class, 'index'])->name('index');
        Route::post('/{payout}/approve', [AdminPayoutController::class, 'approve'])->name('approve');
        Route::post('/{payout}/reject', [AdminPayoutController::class, 'reject'])->name('reject');
        Route::post('/{payout}/mark-paid', [AdminPayoutController::class, 'markAsPaid'])->name('mark-paid');
    });

    // Admin POS Access Logs
    Route::get('/admin/pos-access-logs', [PosAccessLogController::class, 'index'])->name('admin.pos-access-logs');

    // Admin Supply Orders
    Route::prefix('admin/supply/orders')->name('admin.supply.orders.')->group(function () {
        Route::get('/', [SupplyOrderController::class, 'index'])->name('index');
        Route::get('/create', [SupplyOrderController::class, 'create'])->name('create');
        Route::post('/', [SupplyOrderController::class, 'store'])->name('store');
        Route::get('/{order}', [SupplyOrderController::class, 'show'])->name('show');
        Route::get('/{order}/edit', [SupplyOrderController::class, 'edit'])->name('edit');
        Route::put('/{order}', [SupplyOrderController::class, 'update'])->name('update');
        Route::delete('/{order}', [SupplyOrderController::class, 'destroy'])->name('destroy');
        Route::post('/{order}/send', [SupplyOrderController::class, 'sendToSupplier'])->name('send');
        Route::get('/{order}/items', [SupplyOrderController::class, 'getOrderItems'])->name('items');
        Route::post('/{order}/partial-receive', [SupplyOrderController::class, 'partialReceive'])->name('partial-receive');
    });

    // Admin Inventory
    Route::prefix('admin/inventory')->name('admin.inventory.')->group(function () {
        Route::get('/manage', [InventoryController::class, 'manage'])->name('manage');
        Route::post('/{item}/lock', [InventoryController::class, 'lock'])->name('lock');
        Route::post('/{item}/unlock', [InventoryController::class, 'unlock'])->name('unlock');
        Route::post('/order-locked', [InventoryController::class, 'orderLockedItems'])->name('order-locked');
        // ... existing inventory routes ...
    });

    // Admin Wallet Routes
    Route::prefix('admin/wallet')->name('admin.wallet.')->group(function () {
        Route::get('/', [WalletController::class, 'index'])->name('index');
        Route::get('/create', [WalletController::class, 'create'])->name('create');
        Route::post('/', [WalletController::class, 'store'])->name('store');
        Route::get('/{transaction}/edit', [WalletController::class, 'edit'])->name('edit');
        Route::put('/{transaction}', [WalletController::class, 'update'])->name('update');
        Route::delete('/{transaction}', [WalletController::class, 'destroy'])->name('destroy');
        Route::get('/manage', [WalletController::class, 'manage'])->name('manage');
        Route::post('/top-up', [WalletController::class, 'topUp'])->name('top-up');
        Route::post('/withdraw', [WalletController::class, 'withdraw'])->name('withdraw');
        Route::get('/search', [WalletController::class, 'search'])->name('search');
    });

    // Wallet routes
    Route::get('/wallet', [WalletController::class, 'index'])->name('wallet.index');
});

// Employee routes
Route::middleware(['auth', 'role:employee'])->prefix('employee')->name('employee.')->group(function () {
    Route::get('/dashboard', [EmployeeDashboardController::class, 'index'])->name('dashboard');
    Route::get('/time-logs', [TimeLogController::class, 'index'])->name('time-logs');
    Route::get('/salary', [SalaryController::class, 'index'])->name('salary');
});

// POS routes
Route::middleware(['auth', 'role:admin|cashier|employee'])->group(function () {
    Route::get('/pos/login', [PosAuthController::class, 'showLoginForm'])->name('pos.login');
    Route::post('/pos/login', [PosAuthController::class, 'login'])->name('pos.login.submit');
    Route::post('/pos/logout', [PosAuthController::class, 'logout'])->name('pos.logout');

    Route::get('/pos', function () {
        if (!session()->has('pos_verified')) {
            return redirect()->route('pos.login');
        }
        return view('desktop.admin.pos');
    })->name('pos');

    Route::prefix('pos/orders')->name('pos.orders.')->group(function () {
        Route::get('/', [OrderController::class, 'index'])->name('index');
        Route::post('/', [OrderController::class, 'store'])->name('store');
        Route::get('/{order}', [OrderController::class, 'show'])->name('show');
        Route::post('/{order}/pay', [OrderController::class, 'pay'])->name('pay');
        Route::patch('/{order}/payment', [OrderController::class, 'updatePaymentStatus'])->name('update-payment');
        Route::get('/{order}/receipt', [OrderController::class, 'receipt'])->name('receipt');
        Route::get('/{order}/kitchen-receipt', [OrderController::class, 'kitchenReceipt'])->name('kitchen-receipt');
        Route::get('/report', [OrderController::class, 'report'])->name('report');
    });
});

// Payment Manager routes
Route::middleware(['auth', 'role:admin|cashier'])->group(function () {
    Route::get('/payment-manager/login', [PaymentManagerAuthController::class, 'showLoginForm'])->name('payment-manager.login');
    Route::post('/payment-manager/login', [PaymentManagerAuthController::class, 'login'])->name('payment-manager.login.submit');
    Route::post('/payment-manager/logout', [PaymentManagerAuthController::class, 'logout'])->name('payment-manager.logout');

    Route::middleware(['payment.manager'])->group(function () {
        Route::get('/payment-manager', function () {
            return view('desktop.admin.payment-manager');
        })->name('payment-manager');

        Route::prefix('payment-manager/orders')->name('payment-manager.orders.')->group(function () {
            Route::get('/', [OrderController::class, 'index'])->name('index');
            Route::get('/{order}', [OrderController::class, 'show'])->name('show');
            Route::post('/{order}/pay', [OrderController::class, 'pay'])->name('pay');
            Route::patch('/{order}/payment', [OrderController::class, 'updatePaymentStatus'])->name('update-payment');
        });

        // Add payment route
        Route::post('/payment-manager/orders/{order}/process-payment', [OrderController::class, 'pay'])->name('payment-manager.orders.process-payment');
    });
});

// Public product routes
Route::get('/products', [ProductController::class, 'index'])->name('products.index');
Route::get('/products/{product}', [ProductController::class, 'show'])->name('products.show');
Route::get('/products/{product}/qr', [ProductController::class, 'generateQRCode'])->name('products.qr');
Route::post('/products/qr-scan', [ProductController::class, 'showFromQR'])->name('products.qr-scan');
Route::get('/products/{product}/edit', [ProductController::class, 'edit'])->name('products.edit');
Route::delete('/products/{product}', [ProductController::class, 'destroy'])->name('products.destroy');

// Creator routes
Route::middleware(['auth', 'role:creator'])->group(function () {
    Route::get('/creator-dashboard', [CreatorDashboardController::class, 'index'])->name('creator-dashboard.index');
    Route::post('/creator-dashboard/generate-referral', [CreatorDashboardController::class, 'generateReferral'])->name('creator-dashboard.generate-referral');
    Route::get('/creator-leaderboard', [CreatorController::class, 'creatorLeaderboard'])->name('creator.leaderboard');
    Route::post('/update-profile-photo', [CreatorDashboardController::class, 'updateProfilePhoto'])->name('creator-dashboard.update-profile-photo');
    Route::get('/logout', [CreatorDashboardController::class, 'logout'])->name('creator-dashboard.logout');
    Route::get('/home', [CreatorDashboardController::class, 'home'])->name('creator-dashboard.home');
});

// Guest creator routes
Route::middleware(['guest'])->group(function () {
    Route::get('/creator/register', [CreatorController::class, 'showRegistrationForm'])->name('creator.register');
    Route::post('/creator/register', [CreatorController::class, 'register'])->name('creator.register.submit');
});

// Misc routes
Route::get('/receipt/print/{id}', [ReceiptController::class, 'print'])->name('receipt.print');
Route::get('/menu', [\App\Http\Controllers\MenuController::class, 'showMenu'])->name('menu');
Route::get('/leaderboard', [CreatorController::class, 'leaderboard'])->name('public.leaderboard');
Route::post('/coupon/apply', [CouponController::class, 'apply'])->name('coupon.apply');
Route::view('/test-panel', 'test.devpanel')->name('test.panel');
Route::post('/assign-monthly-rewards', [TestController::class, 'assignMonthlyRewards'])->name('test.assign-monthly-rewards');
Route::post('/referral/installed', [ReferralController::class, 'markAsInstalled'])->name('referral.installed');

// Wallet Routes
Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('/wallet/topup', [WalletController::class, 'showTopUp'])->name('wallet.topup');
    Route::post('/wallet/generate-qr', [WalletController::class, 'generateTopUpQR'])->name('wallet.generate-qr');
    Route::get('/wallet/generate-pwa-qr', [WalletController::class, 'generatePWAQR'])->name('wallet.generate-pwa-qr');
    Route::post('/wallet/generate-product-qr', [WalletController::class, 'generateProductQR'])->name('wallet.generate-product-qr');
    Route::get('/wallet/order-details', [WalletController::class, 'showOrderDetails'])->name('wallet.order-details');
});

// Wallet scanner route (available to all authenticated users)
Route::middleware(['auth'])->group(function () {
    Route::get('/wallet/scan', [WalletController::class, 'showScanner'])->name('wallet.scan');
    Route::post('/wallet/process-topup', [WalletController::class, 'processTopUp'])->name('wallet.process-topup');
});

// PWA QR Code Route
Route::get('/pwa/qr', [ProductController::class, 'generatePWAQRCode'])->name('pwa.qr');

// PWA Routes
Route::get('/pwa/install', function () {
    return view('desktop.pwa.install');
})->name('pwa.install');

/*
 * Route Names Summary:
 * 
 * Admin Inventory Routes:
 * - admin.inventory.index
 * - admin.inventory.create
 * - admin.inventory.store
 * - admin.inventory.show
 * - admin.inventory.edit
 * - admin.inventory.update
 * - admin.inventory.destroy
 * - admin.inventory.adjust
 * - admin.inventory.categories
 * - admin.inventory.store-category
 * - admin.inventory.update-category
 * - admin.inventory.delete-category
 * - admin.inventory.checks.index
 * - admin.inventory.checks.store
 * 
 * Admin Inventory Orders Routes:
 * - admin.inventory.orders.index
 * - admin.inventory.orders.create
 * - admin.inventory.orders.store
 * - admin.inventory.orders.show
 * - admin.inventory.orders.edit
 * - admin.inventory.orders.update
 * - admin.inventory.orders.destroy
 * - admin.inventory.orders.confirm
 * - admin.inventory.orders.cancel
 * - admin.inventory.orders.export
 * 
 * Admin Suppliers Routes:
 * - admin.suppliers.index
 * - admin.suppliers.create
 * - admin.suppliers.store
 * - admin.suppliers.show
 * - admin.suppliers.edit
 * - admin.suppliers.update
 * - admin.suppliers.destroy
 */
