<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\BulkController;
use App\Http\Controllers\CreatorController;
use App\Http\Controllers\LeaderboardController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\AIPopupController;
use App\Http\Controllers\PublicInvestmentController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\Admin\CategoryController;

/*
|--------------------------------------------------------------------------
| Public / Customer-Facing Routes
|--------------------------------------------------------------------------
*/

// ── Informational pages ──────────────────────────────────────────────────
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/about', [HomeController::class, 'about'])->name('about');
Route::get('/contact', [HomeController::class, 'contact'])->name('contact');
Route::get('/terms', [HomeController::class, 'terms'])->name('terms');
Route::get('/privacy', [HomeController::class, 'privacy'])->name('privacy');
Route::get('/privacy-policy', fn() => view('privacy-policy'))->name('privacy-policy');
Route::get('/help', [HomeController::class, 'help'])->name('help');
Route::get('/new-user-guide', [HomeController::class, 'newUserGuide'])->name('new-user-guide');
Route::get('/beta', fn() => view('beta-testing'))->name('beta-testing');
Route::get('/statistics', [HomeController::class, 'getStatistics'])->name('statistics');
Route::get('/offers', [HomeController::class, 'offers'])->name('offers');
Route::get('/roadmap', fn() => view('pages.roadmap'))->name('public.roadmap');

// ── Menu ─────────────────────────────────────────────────────────────────
Route::get('/menu', [MenuController::class, 'showMenu'])->name('menu');
Route::get('/menu/food', [MenuController::class, 'showFood'])->name('menu.food');
Route::get('/menu/drinks', [MenuController::class, 'showDrinks'])->name('menu.drinks');
Route::get('/menu/desserts', [MenuController::class, 'showDesserts'])->name('menu.desserts');
Route::get('/menu/combos', [MenuController::class, 'showCombos'])->name('menu.combos');
Route::get('/menu/featured', [MenuController::class, 'featured'])->name('menu.featured');

// ── Products ─────────────────────────────────────────────────────────────
Route::get('/products', [ProductController::class, 'index'])->name('products.index');
Route::get('/products/{product}', [ProductController::class, 'show'])->name('products.show');
Route::get('/products/category/{category}', [ProductController::class, 'category'])->name('products.category');
Route::get('/products/search', [ProductController::class, 'search'])->name('products.search');
Route::get('/products/{product}/qr', [ProductController::class, 'generateQRCode'])->name('products.qr');
Route::get('/search', [ProductController::class, 'search'])->name('search');
Route::get('/api/products/autocomplete', [ProductController::class, 'autocomplete'])->name('products.autocomplete');
Route::get('/categories/{category}', [CategoryController::class, 'show'])->name('categories.show');

// ── Cart (public add-to-cart, view, server calculations) ──────────────── 
Route::get('/cart', [CartController::class, 'index'])->name('cart');
Route::post('/debug/cart-status', [CartController::class, 'debugCartStatus']);
Route::post('/api/cart/calculate', [App\Http\Controllers\Api\CartController::class, 'calculateTotals'])->name('api.cart.calculate');
Route::post('/api/cart/validate', [App\Http\Controllers\Api\CartController::class, 'validateCart'])->name('api.cart.validate');

// ── Checkout (public — guest allowed) ─────────────────────────────────── 
Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout');
Route::post('/checkout', [CartController::class, 'checkout'])->name('checkout.post');
Route::post('/checkout/branches', [CheckoutController::class, 'getAvailableBranches'])->name('checkout.branches');
Route::get('/checkout/all-branches', [CheckoutController::class, 'getAllBranches'])->name('checkout.all-branches');

// ── Orders (public placement) ─────────────────────────────────────────── 
Route::post('/orders', [OrderController::class, 'store'])->name('orders.store');

// ── Payment callbacks & public payment pages ─────────────────────────── 
Route::get('/payment', [PaymentController::class, 'index'])->name('payment')->middleware('no.cache.html');
Route::get('/payment/success', fn() => view('payment.success'))->name('payment.success');
Route::get('/payment/esewa/success', [App\Http\Controllers\PaymentController::class, 'esewaSuccess'])->name('payment.esewa.success');
Route::get('/payment/esewa/failure', fn() => view('payment.esewa.failure'))->name('payment.esewa.failure');
Route::post('/webhooks/payment-status', [App\Http\Controllers\WebhookController::class, 'paymentStatus'])->name('webhooks.payment-status');

// ── Bulk orders ───────────────────────────────────────────────────────── 
Route::get('/bulk', [BulkController::class, 'index'])->name('bulk');
Route::get('/bulk/custom-builder', [BulkController::class, 'customBuilder'])->name('bulk.custom-builder');
Route::get('/api/bulk-packages/{packageKey}', [BulkController::class, 'getPackageByKey']);

// ── Finds feed ────────────────────────────────────────────────────────── 
Route::get('/finds', [App\Http\Controllers\FindsController::class, 'index'])->name('finds');
Route::get('/finds/data', [App\Http\Controllers\FindsController::class, 'data']);

// ── Public leaderboard ────────────────────────────────────────────────── 
Route::get('/leaderboard', [CreatorController::class, 'leaderboard'])->name('public.leaderboard');

// ── Supplier public confirmation (email link, no auth needed) ─────────── 
Route::get('/supplier/order/{order}/confirm', [App\Http\Controllers\SupplierController::class, 'confirmOrder'])->name('supplier.order.confirm');
Route::get('/supplier/order/{order}/view', [App\Http\Controllers\SupplierController::class, 'viewOrder'])->name('supplier.order.view');
Route::post('/supplier/orders/{order}/confirm', [App\Http\Controllers\SupplierController::class, 'confirmFullOrder'])->name('supplier.orders.confirm');
Route::post('/supplier/orders/{order}/partial-confirm', [App\Http\Controllers\SupplierController::class, 'confirmPartialOrder'])->name('supplier.orders.partial-confirm');
Route::post('/supplier/orders/{order}/reject', [App\Http\Controllers\SupplierController::class, 'rejectOrder'])->name('supplier.orders.reject');

// ── Customer payment viewer (no auth required) ────────────────────────── 
Route::get('/customer/payment-viewer', [App\Http\Controllers\Customer\CustomerPaymentController::class, 'showPaymentViewer'])
    ->name('payment.viewer')
    ->middleware('web');
Route::get('/api/customer/active-order', [App\Http\Controllers\Customer\CustomerPaymentController::class, 'getActiveOrder'])
    ->name('api.orders.active')
    ->middleware('web');

// ── Receipts (public print) ───────────────────────────────────────────── 
Route::get('/receipts/print/{id}', [App\Http\Controllers\ReceiptController::class, 'print'])->name('receipts.print');

// ── Reviews ───────────────────────────────────────────────────────────── 
Route::post('/reviews', [ReviewController::class, 'store'])->name('reviews.store')->middleware('auth');

// ── AI Popup (customer-facing) ────────────────────────────────────────── 
Route::get('/ai-popup/decision', [AIPopupController::class, 'getPopupDecision'])->name('ai-popup.decision');
Route::post('/ai-popup/track', [AIPopupController::class, 'trackInteraction'])->name('ai-popup.track');
Route::get('/ai-popup/analytics', [AIPopupController::class, 'getAnalytics'])->name('ai-popup.analytics')->middleware(['auth', 'admin']);
Route::post('/ai-popup/reset', [AIPopupController::class, 'resetPopupState'])->name('ai-popup.reset');
Route::get('/ai-popup/reset-frequency', [AIPopupController::class, 'resetFrequency'])->name('ai-popup.reset-frequency');

// ── Public investment registration ────────────────────────────────────── 
Route::get('/invest', [PublicInvestmentController::class, 'index'])->name('public.investment.index');
Route::post('/invest/register', [PublicInvestmentController::class, 'register'])->name('public.investment.register');
Route::get('/invest/leaderboard', [PublicInvestmentController::class, 'leaderboard'])->name('public.investment.leaderboard');

// ── Mobile API helpers ────────────────────────────────────────────────── 
Route::get('/mobile-api/content/app-config', function (Request $request) {
    return response()->json([
        'success' => true,
        'data'    => [
            'app_name'              => 'Amako Shop',
            'app_tagline'           => 'From our kitchen to your heart',
            'hero_default_cta'      => 'Add to Cart',
            'empty_hero_message'    => 'No featured items available',
            'product_default_subtitle' => 'Delicious and authentic',
        ],
    ]);
});

Route::get('/mobile-api/content/section/{section}/array', function (Request $request, $section) {
    return response()->json(['success' => true, 'data' => []]);
});

// ── Misc API (branches public lookup) ────────────────────────────────── 
Route::get('/api/branches/{id}', function ($id) {
    $branch = \App\Models\Branch::find($id);
    if ($branch) {
        return response()->json([
            'success' => true,
            'branch'  => [
                'id'           => $branch->id,
                'name'         => $branch->name,
                'address'      => $branch->address,
                'phone'        => $branch->phone,
                'latitude'     => $branch->latitude,
                'longitude'    => $branch->longitude,
                'delivery_fee' => $branch->delivery_fee,
            ],
        ]);
    }
    return response()->json(['success' => false, 'message' => 'Branch not found'], 404);
})->name('api.branches.show');

// ── Log checking ──────────────────────────────────────────────────────── 
Route::get('/check-logs', [App\Http\Controllers\LogController::class, 'checkLogs'])->name('check.logs');

Route::get('/registration-logs', function () {
    $logFile = storage_path('logs/registration.log');
    if (file_exists($logFile)) {
        return response()->json(['success' => true, 'logs' => file_get_contents($logFile)]);
    }
    return response()->json(['success' => false, 'message' => 'No registration logs found']);
})->name('registration.logs');

Route::get('/api/sales/overview', [App\Http\Controllers\SalesAnalyticsController::class, 'getSalesOverview']);
Route::get('/test-openai', [App\Http\Controllers\OpenAITestController::class, 'testBasicCompletion']);
Route::get('/test-json', fn() => response()->json(['message' => 'JSON response working', 'timestamp' => now()]));
