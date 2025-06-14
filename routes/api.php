<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\SalesAnalyticsController;
use App\Http\Controllers\Admin\DashboardController;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\PosController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\EmployeeController;
use App\Http\Controllers\Api\AnalyticsController;
use App\Http\Controllers\Api\PosAuthController;
use App\Http\Controllers\Api\PosOrderController;
use App\Http\Controllers\Api\EmployeeAuthController;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Api\PosTableController;
use App\Http\Controllers\Admin\CashDrawerController;
use App\Http\Controllers\Admin\AdminPaymentController;
use App\Http\Controllers\Api\KhaltiController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Public routes
Route::middleware(['throttle:30,1'])->group(function () {
    Route::post('/login', function (Request $request) {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        if (Auth::attempt($credentials)) {
            $user = Auth::user();
            $token = $user->createToken('payment-manager')->plainTextToken;

            return response()->json([
                'success' => true,
                'token' => $token,
                'user' => $user->load('roles')
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Invalid credentials'
        ], 401);
    });

    Route::get('/leaderboard', function () {
        $creators = \App\Models\Creator::with('user:id,name')
            ->orderByDesc('points')
            ->take(10)
            ->get()
            ->map(function ($creator, $index) {
                $rank = $index + 1;
                $discount = match(true) {
                    $rank === 1 => 50,
                    $rank <= 3 => 40,
                    $rank <= 10 => 30,
                    default => 20
                };
                
                return [
                    'rank' => $rank,
                    'name' => $creator->user->name,
                    'points' => $creator->points,
                    'discount' => $discount,
                    'is_trending' => $creator->isTrending()
                ];
            });

        return response()->json($creators);
    });
});

// Cash Drawer routes (public)
Route::get('/cash-drawer', [App\Http\Controllers\Api\PaymentController::class, 'getCashDrawer']);
Route::get('/cash-drawer/balance', [App\Http\Controllers\Api\PaymentController::class, 'getCashDrawerBalance']);
Route::post('/cash-drawer', [App\Http\Controllers\Api\PaymentController::class, 'updateCashDrawer']);

// Protected routes
Route::middleware(['auth:sanctum'])->group(function () {
    // Payment Manager routes
    Route::middleware(['role:admin'])->prefix('admin')->group(function () {
        Route::get('/orders', [OrderController::class, 'index']);
        Route::get('/orders/{id}', [OrderController::class, 'show']);
        Route::get('/payments', [PaymentController::class, 'index']);
        Route::get('/payments/{payment}', [PaymentController::class, 'show']);
        Route::post('/payments', [PaymentController::class, 'store']);
        Route::put('/payments/{payment}', [PaymentController::class, 'update']);
        Route::delete('/payments/{payment}', [PaymentController::class, 'destroy']);
        Route::get('/cash-drawer', [PaymentController::class, 'getCashDrawer']);
        Route::get('/cash-drawer/balance', [PaymentController::class, 'getCashDrawerBalance']);
        Route::post('/cash-drawer', [PaymentController::class, 'updateCashDrawer']);
        Route::post('/cash-drawer/update-denominations', [CashDrawerController::class, 'updateDenominations']);
        Route::post('/cash-drawer/adjust', [CashDrawerController::class, 'adjust']);
        Route::get('/cash-drawer/status', [CashDrawerController::class, 'getStatus']);
        Route::post('/cash-drawer/open', [CashDrawerController::class, 'openSession']);
        Route::post('/cash-drawer/close', [CashDrawerController::class, 'closeSession']);
    });

    // POS routes - require POS access
    Route::middleware(['pos.access'])->prefix('pos')->group(function () {
        Route::post('/verify-token', [PosAuthController::class, 'verifyToken']);
        Route::get('/tables', [PosController::class, 'tables']);
        Route::get('/products', [PosController::class, 'products']);
        Route::get('/orders', [PosOrderController::class, 'index']);
        
        // Orders
        Route::post('/orders', [PosOrderController::class, 'store']);
        Route::get('/orders/{order}', [PosOrderController::class, 'show']);
        Route::put('/orders/{order}', [PosOrderController::class, 'update']);
        Route::delete('/orders/{order}', [PosOrderController::class, 'destroy']);
        
        // Payments
        Route::post('/payments', [PaymentController::class, 'store']);
        Route::get('/payments/{payment}', [PaymentController::class, 'show']);

        // User info
        Route::get('/user-info', [PosController::class, 'userInfo']);
    });

    // Branch routes
    Route::get('/branches', [App\Http\Controllers\Api\BranchController::class, 'index']);

    // Admin only routes
    Route::middleware(['role:admin'])->prefix('admin')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'getDashboardData']);
        Route::get('/analytics', [AnalyticsController::class, 'index']);
        Route::get('/analytics/sales', [AnalyticsController::class, 'sales']);
        Route::get('/analytics/products', [AnalyticsController::class, 'products']);
        Route::get('/payments', [PaymentController::class, 'index']);
        Route::get('/payments/{payment}', [PaymentController::class, 'show']);
        Route::post('/payments', [PaymentController::class, 'store']);
        Route::put('/payments/{payment}', [PaymentController::class, 'update']);
        Route::delete('/payments/{payment}', [PaymentController::class, 'destroy']);
        Route::get('/orders/{id}', [PaymentController::class, 'getOrder']);
        Route::get('/cash-drawer', [PaymentController::class, 'getCashDrawer']);
        Route::get('/cash-drawer/balance', [PaymentController::class, 'getCashDrawerBalance']);
        Route::post('/cash-drawer', [PaymentController::class, 'updateCashDrawer']);
        Route::post('/cash-drawer/update-denominations', [CashDrawerController::class, 'updateDenominations']);
        
        // Cash Drawer Adjustment Routes
        Route::post('/cash-drawer/adjust', [CashDrawerController::class, 'adjust']);
        Route::get('/cash-drawer/balance', [CashDrawerController::class, 'getBalance']);
        Route::get('/cash-drawer/status', [CashDrawerController::class, 'getStatus']);
        Route::post('/cash-drawer/open', [CashDrawerController::class, 'openSession']);
        Route::post('/cash-drawer/close', [CashDrawerController::class, 'closeSession']);

        // Wallet routes
        Route::get('/wallets/{wallet_number}/balance', [AdminPaymentController::class, 'getWalletBalanceByNumber']);

        // Payment Manager Routes
        Route::post('/payment-manager/orders/{order}/process-payment', [App\Http\Controllers\Admin\AdminPaymentController::class, 'processPayment']);
    });

    // Manager routes (admin and main_manager)
    Route::middleware(['role:admin|main_manager'])->prefix('manager')->group(function () {
        Route::get('/reports', [AnalyticsController::class, 'reports']);
        Route::get('/inventory', [ProductController::class, 'inventory']);
    });

    // Report routes (protected - admin only)
    Route::middleware(['role:admin', 'throttle:60,1'])->prefix('reports')->group(function () {
        Route::post('/generate', [ReportController::class, 'generate']);
        Route::post('/export', [ReportController::class, 'export']);
    });

    // User authentication check
    Route::get('/user', function (Request $request) {
        return response()->json($request->user()->load('roles'));
    });

    // Orders API routes
    Route::get('/orders', [OrderController::class, 'index']);
    Route::post('/orders/{order}/process-payment', [OrderController::class, 'processPayment']);
    Route::post('/employee/verify', [EmployeeAuthController::class, 'verify']);

    // Khalti Payment Routes
    Route::post('/khalti/initiate', [KhaltiController::class, 'initiatePayment']);
    Route::post('/khalti/verify', [KhaltiController::class, 'verifyPayment']);
    Route::get('/khalti/return', [KhaltiController::class, 'handleReturn']);

    // Cash drawer routes
    Route::prefix('admin/cash-drawer')->group(function () {
        Route::get('/status', [CashDrawerController::class, 'status']);
        Route::post('/open', [CashDrawerController::class, 'openSession']);
        Route::post('/close', [CashDrawerController::class, 'closeSession']);
        Route::post('/update-denominations', [CashDrawerController::class, 'updateDenominations']);
    });
});

// POS Routes
Route::prefix('pos')->group(function () {
    // Temporarily remove auth middleware
    Route::post('/verify-token', [App\Http\Controllers\Api\PosController::class, 'verifyToken']);
    Route::get('/tables', [App\Http\Controllers\Api\PosController::class, 'tables']);
    Route::get('/orders', [App\Http\Controllers\Api\PosController::class, 'orders']);
    Route::get('/payments', [App\Http\Controllers\Api\PosController::class, 'payments']);
    Route::get('/access-logs', [App\Http\Controllers\Api\PosController::class, 'accessLogs']);
});

// Table API Routes
Route::prefix('admin')->group(function () {
    Route::put('/tables/{table}', [PosTableController::class, 'update']);
});

Route::middleware(['auth:sanctum', 'role:admin,cashier'])->group(function () {
    Route::post('/admin/cash-drawer/update-denominations', [App\Http\Controllers\Admin\CashDrawerController::class, 'updateDenominations']);
    Route::post('/admin/cash-drawer/adjust', [CashDrawerController::class, 'adjust']);
    Route::get('/admin/cash-drawer/balance', [CashDrawerController::class, 'getBalance']);
    Route::get('/admin/cash-drawer/status', [CashDrawerController::class, 'getStatus']);
    Route::post('/admin/cash-drawer/open', [CashDrawerController::class, 'openSession']);
    Route::post('/admin/cash-drawer/close', [CashDrawerController::class, 'closeSession']);
});
