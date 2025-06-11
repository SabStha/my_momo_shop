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

// Employee verification
Route::middleware(['throttle:10,1'])->post('/employee/verify', [EmployeeAuthController::class, 'verify']);

// Protected routes
Route::middleware(['auth:sanctum'])->group(function () {
    // POS routes - require POS access
    Route::middleware(['pos.access'])->prefix('pos')->group(function () {
        Route::post('/verify-token', [PosAuthController::class, 'verifyToken']);
        Route::get('/tables', [PosController::class, 'tables']);
        Route::get('/products', [PosController::class, 'products']);
        Route::get('/orders', [PosOrderController::class, 'index']);
        
        // Orders
        Route::post('/orders', [OrderController::class, 'store']);
        Route::get('/orders/{order}', [OrderController::class, 'show']);
        Route::put('/orders/{order}', [OrderController::class, 'update']);
        Route::delete('/orders/{order}', [OrderController::class, 'destroy']);
        
        // Payments
        Route::post('/payments', [PaymentController::class, 'store']);
        Route::get('/payments/{payment}', [PaymentController::class, 'show']);
    });

    // Branch routes
    Route::get('/branches', [App\Http\Controllers\Api\BranchController::class, 'index']);

    // Admin only routes
    Route::middleware(['role:admin'])->prefix('admin')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'getDashboardData']);
        Route::get('/analytics', [AnalyticsController::class, 'index']);
        Route::get('/analytics/sales', [AnalyticsController::class, 'sales']);
        Route::get('/analytics/products', [AnalyticsController::class, 'products']);
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
});
