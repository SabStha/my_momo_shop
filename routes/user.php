<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\User\DashboardController;
use App\Http\Controllers\User\ProfileController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\CouponController;
use App\Http\Controllers\CreditsController;

/*
|--------------------------------------------------------------------------
| Authenticated User Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth'])->group(function () {

    // ── Account & Dashboard ────────────────────────────────────────────── 
    Route::get('/my-account', [\App\Http\Controllers\User\AccountController::class, 'index'])->name('account');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // ── User profile via API controller ───────────────────────────────── 
    Route::get('/user/profile', [App\Http\Controllers\Api\UserController::class, 'getProfile'])->name('user.profile.get');
    Route::put('/user/profile', [App\Http\Controllers\Api\UserController::class, 'updateProfile'])->name('user.profile.update');

    // ── Profile ───────────────────────────────────────────────────────── 
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::post('/profile/picture', [ProfileController::class, 'updatePicture'])->name('profile.picture');
    Route::post('/profile/topup', [ProfileController::class, 'topUp'])->name('profile.topup');

    // ── Notifications ─────────────────────────────────────────────────── 
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications');
    Route::post('/notifications/mark-as-read', [NotificationController::class, 'markAsRead'])->name('notifications.markAsRead');
    Route::post('/notifications/mark-all-as-read', [NotificationController::class, 'markAllAsRead'])->name('notifications.markAllAsRead');
    Route::delete('/notifications/{notification}', [NotificationController::class, 'destroy'])->name('notifications.destroy');

    // ── Orders ────────────────────────────────────────────────────────── 
    Route::get('/orders', [OrderController::class, 'index'])->name('orders');
    Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
    Route::get('/orders/{order}/receipt', [OrderController::class, 'receipt'])->name('orders.receipt');
    Route::get('/orders/{order}/success', [OrderController::class, 'success'])->name('orders.success');

    // ── Cart (AJAX) ───────────────────────────────────────────────────── 
    Route::post('/cart/add', [CartController::class, 'addToCart'])->name('cart.add');
    Route::put('/cart/update', [CartController::class, 'updateQuantity'])->name('cart.update');
    Route::delete('/cart/remove', [CartController::class, 'removeFromCart'])->name('cart.remove');
    Route::post('/cart/clear', [CartController::class, 'clearCart'])->name('cart.clear');
    Route::get('/cart/get-cart', [CartController::class, 'getCart'])->name('cart.get-cart');
    Route::get('/cart/suggestions', [CartController::class, 'getSuggestions'])->name('cart.suggestions');
    Route::post('/cart/sync', [CartController::class, 'syncCart'])->name('cart.sync');

    // ── Coupons ───────────────────────────────────────────────────────── 
    Route::post('/coupon/apply', [CouponController::class, 'apply'])->name('coupon.apply');
    Route::post('/coupon/remove', [CouponController::class, 'remove'])->name('coupon.remove');

    // ── Checkout (authenticated) ──────────────────────────────────────── 
    Route::post('/checkout/submit', [CheckoutController::class, 'submit'])->name('checkout.submit');
    Route::post('/checkout/process/{product}', [CheckoutController::class, 'process'])->name('checkout.process');
    Route::post('/checkout/{product}/quick', [CheckoutController::class, 'quickCheckout'])->name('checkout.quick');
    Route::get('/checkout/complete/{order}', [CheckoutController::class, 'complete'])->name('checkout.complete');
    Route::get('/checkout/thankyou', [CheckoutController::class, 'thankyou'])->name('checkout.thankyou');

    // ── User Payments ─────────────────────────────────────────────────── 
    Route::post('/payments/initialize', [PaymentController::class, 'initialize'])->name('payments.initialize');
    Route::post('/payments/{payment}/process', [PaymentController::class, 'process'])->name('payments.process');
    Route::get('/payments/{payment}/verify', [PaymentController::class, 'verify'])->name('payments.verify');
    Route::post('/payments/{payment}/cancel', [PaymentController::class, 'cancel'])->name('payments.cancel');
    Route::get('/payments/{payment}/receipt', [PaymentController::class, 'receipt'])->name('payments.receipt');

    // ── Offers (authenticated) ────────────────────────────────────────── 
    Route::post('/offers/claim', [App\Http\Controllers\OfferController::class, 'claim'])->name('offers.claim');
    Route::get('/offers/my-claims', [App\Http\Controllers\OfferController::class, 'myClaims'])->name('offers.my-claims');
    Route::post('/offers/apply', [App\Http\Controllers\OfferController::class, 'apply'])->name('offers.apply');
    Route::post('/offers/remove', [App\Http\Controllers\OfferController::class, 'remove'])->name('offers.remove');
    Route::get('/offers/available', [App\Http\Controllers\OfferController::class, 'available'])->name('offers.available');

    // ── Amako Credits (user-facing wallet balance APIs) ───────────────── 
    Route::get('/api/user/wallet/balance', [App\Http\Controllers\Api\UserController::class, 'getWalletBalance'])
        ->middleware(['auth:sanctum'])
        ->name('api.user.wallet.balance');
    Route::get('/api/user/wallet/balance-fresh', [App\Http\Controllers\Api\UserController::class, 'getWalletBalancePost'])
        ->middleware(['auth:sanctum'])
        ->name('api.user.wallet.balance.fresh');
    Route::get('/api/user/credits/balance', [App\Http\Controllers\Api\UserController::class, 'getCreditsBalance'])
        ->middleware(['auth:sanctum'])
        ->name('api.user.credits.balance');

    // ── Credits (loyalty) ─────────────────────────────────────────────── 
    Route::get('/credits', [CreditsController::class, 'index'])->name('user.credits.index');
    Route::get('/credits/transactions', [CreditsController::class, 'transactions'])->name('user.credits.transactions');
    Route::post('/credits/generate-qr', [CreditsController::class, 'generateQR'])->name('user.credits.generate-qr');
    Route::get('/credits/balance', [CreditsController::class, 'getBalance'])->name('user.credits.balance');

    // ── Clear referral discount session ───────────────────────────────── 
    Route::post('/clear-referral-discount', [App\Http\Controllers\HomeController::class, 'clearReferralDiscount']);

    // ── User themes ───────────────────────────────────────────────────── 
    Route::get('/user/themes', [App\Http\Controllers\UserThemeController::class, 'index'])->name('user.themes.index');
    Route::post('/user/themes/activate', [App\Http\Controllers\UserThemeController::class, 'activate'])->name('user.themes.activate');
    Route::post('/user/themes/sync', [App\Http\Controllers\UserThemeController::class, 'sync'])->name('user.themes.sync');

    // ── Delivery driver routes ────────────────────────────────────────── 
    Route::prefix('delivery')->name('delivery.')->group(function () {
        Route::get('/', [App\Http\Controllers\DeliveryController::class, 'index'])->name('dashboard');
        Route::post('/orders/{orderId}/accept', [App\Http\Controllers\DeliveryController::class, 'acceptOrder'])->name('orders.accept');
        Route::post('/orders/{orderId}/start', [App\Http\Controllers\DeliveryController::class, 'startDelivery'])->name('orders.start');
        Route::post('/orders/{orderId}/location', [App\Http\Controllers\DeliveryController::class, 'updateLocation'])->name('orders.update-location');
        Route::post('/orders/{orderId}/delivered', [App\Http\Controllers\DeliveryController::class, 'markAsDelivered'])->name('orders.delivered');
        Route::get('/orders/{orderId}/tracking', [App\Http\Controllers\DeliveryController::class, 'getTracking'])->name('orders.tracking');
    });

    // ── Order management shared (accept/decline/ready for payment staff) ─ 
    Route::get('/admin/orders/json', [App\Http\Controllers\Admin\AdminOrderController::class, 'getOrdersJson'])->name('admin.orders.json.public');
    Route::post('/admin/orders/{orderId}/accept', [App\Http\Controllers\Admin\AdminOrderController::class, 'acceptOrder'])->name('admin.orders.accept');
    Route::post('/admin/orders/{orderId}/decline', [App\Http\Controllers\Admin\AdminOrderController::class, 'declineOrder'])->name('admin.orders.decline');
    Route::post('/admin/orders/{orderId}/mark-as-ready', [App\Http\Controllers\Admin\AdminOrderController::class, 'markAsReady'])->name('admin.orders.mark-as-ready');
    Route::post('/admin/orders/{orderId}/reset-status', [App\Http\Controllers\Admin\AdminOrderController::class, 'resetOrderStatus'])->name('admin.orders.reset-status');
    Route::get('/admin/orders/{orderId}/kitchen-print', [App\Http\Controllers\Admin\AdminOrderController::class, 'kitchenPrint'])->name('admin.orders.kitchen-print');

    // ── Amako Credits (user wallet - wallet.auth secondary auth required) ─ 
    Route::get('/amako-credits', [App\Http\Controllers\Admin\WalletController::class, 'index'])->name('wallet')->middleware('wallet.auth');
    Route::get('/amako-credits/scan', [App\Http\Controllers\Admin\WalletController::class, 'scan'])->name('wallet.scan')->middleware('wallet.auth');
    Route::post('/amako-credits/top-up', [App\Http\Controllers\Admin\WalletController::class, 'topUp'])->name('wallet.top-up')->middleware('wallet.auth');
    Route::post('/amako-credits/process-code', [App\Http\Controllers\Admin\WalletController::class, 'processCode'])->name('wallet.process-code')->middleware('wallet.auth');
    Route::get('/amako-credits/transactions', [App\Http\Controllers\Admin\WalletController::class, 'transactions'])->name('wallet.transactions')->middleware('wallet.auth');
    Route::get('/amako-credits/transactions/{user}', [App\Http\Controllers\Admin\WalletController::class, 'getTransactions'])->name('user-transactions')->middleware('wallet.auth');
    Route::get('/amako-credits/api/transactions', [App\Http\Controllers\Admin\WalletController::class, 'getTransactionsByDate'])->name('wallet.api.transactions')->middleware('wallet.auth');
});
