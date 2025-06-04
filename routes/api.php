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

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Admin API routes (protected)
Route::middleware(['auth:sanctum', 'role:admin'])->prefix('admin')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'getDashboardData']);
    Route::get('/analytics/dashboard', [SalesAnalyticsController::class, 'getDashboardKPIs']);
});

// POS API routes (protected - requires admin/employee role)
Route::middleware(['auth:sanctum', 'pos.access'])->group(function () {
    // Products
    Route::get('/pos/products', [ProductController::class, 'index']);
    Route::get('/pos/products/{product}', [ProductController::class, 'show']);
    
    // Orders
    Route::get('/pos/orders', [OrderController::class, 'index']);
    Route::post('/pos/orders', [OrderController::class, 'store']);
    Route::get('/pos/orders/{order}', [OrderController::class, 'show']);
    Route::put('/pos/orders/{order}', [OrderController::class, 'update']);
    Route::delete('/pos/orders/{order}', [OrderController::class, 'destroy']);
    
    // Payments
    Route::post('/pos/payments', [PaymentController::class, 'store']);
    Route::get('/pos/payments/{payment}', [PaymentController::class, 'show']);
});

// Report routes (protected - admin only)
Route::middleware(['auth:sanctum', 'role:admin', 'throttle:60,1'])->prefix('reports')->group(function () {
    Route::post('/generate', [ReportController::class, 'generate']);
    Route::post('/export', [ReportController::class, 'export']);
});

// Public API routes (limited rate limiting)
Route::middleware(['throttle:30,1'])->group(function () {
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
Route::middleware(['throttle:10,1'])->post('/employee/verify', function(Request $request) {
    $request->validate([
        'identifier' => 'required', // can be user_id or email
        'password' => 'required',
    ]);

    $user = User::where('id', $request->identifier)
        ->orWhere('email', $request->identifier)
        ->first();

    if ($user && \Hash::check($request->password, $user->password)) {
        // Check if user is either an admin or employee
        if ($user->hasAnyRole(['admin', 'employee'])) {
            // Revoke any existing tokens
            $user->tokens()->delete();
            
            // Create a new token
            $token = $user->createToken('pos-token', ['pos-access'])->plainTextToken;
            
            return response()->json([
                'success' => true, 
                'name' => $user->name,
                'is_admin' => $user->isAdmin(),
                'token' => $token
            ]);
        }
    }
    return response()->json(['success' => false, 'message' => 'Invalid credentials'], 401);
});

// Public routes
Route::post('/employee/verify', [EmployeeController::class, 'verify']);

// Protected routes
Route::middleware(['auth:sanctum'])->group(function () {
    // POS routes - require POS access
    Route::middleware(['pos.access'])->group(function () {
        // Products
        Route::get('/pos/products', [ProductController::class, 'index']);
        Route::get('/pos/products/{product}', [ProductController::class, 'show']);
        
        // Orders
        Route::get('/pos/orders', [OrderController::class, 'index']);
        Route::post('/pos/orders', [OrderController::class, 'store']);
        Route::get('/pos/orders/{order}', [OrderController::class, 'show']);
        Route::put('/pos/orders/{order}', [OrderController::class, 'update']);
        Route::delete('/pos/orders/{order}', [OrderController::class, 'destroy']);
        
        // Payments
        Route::post('/pos/payments', [PaymentController::class, 'store']);
        Route::get('/pos/payments/{payment}', [PaymentController::class, 'show']);
    });

    // Admin only routes
    Route::middleware(['role:admin'])->group(function () {
        Route::get('/admin/analytics', [AnalyticsController::class, 'index']);
        Route::get('/admin/analytics/sales', [AnalyticsController::class, 'sales']);
        Route::get('/admin/analytics/products', [AnalyticsController::class, 'products']);
    });

    // Manager routes (admin and main_manager)
    Route::middleware(['role:admin|main_manager'])->group(function () {
        Route::get('/manager/reports', [AnalyticsController::class, 'reports']);
        Route::get('/manager/inventory', [ProductController::class, 'inventory']);
    });
});
