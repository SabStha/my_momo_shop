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

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Debug route to check auth status
Route::get('/analytics/dashboard', [SalesAnalyticsController::class, 'getDashboardKPIs']);


// Admin API routes
Route::middleware(['auth:sanctum', 'admin'])->prefix('admin')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'getDashboardData']);
});

// API routes with rate limiting
Route::middleware(['throttle:60,1'])->group(function () {
    // POS API Endpoints
    Route::prefix('pos')->group(function () {
        Route::get('products', [\App\Http\Controllers\Api\PosProductController::class, 'index']);
        Route::get('tables', [\App\Http\Controllers\Api\PosTableController::class, 'index']);
        Route::post('orders', [\App\Http\Controllers\Api\PosOrderController::class, 'store']);
        Route::get('orders', [\App\Http\Controllers\Api\PosOrderController::class, 'index']);
        Route::get('orders/{order}', [\App\Http\Controllers\Api\PosOrderController::class, 'show']);
        Route::put('orders/{order}', [\App\Http\Controllers\Api\PosOrderController::class, 'update']);
        Route::put('orders/{order}/status', [\App\Http\Controllers\Api\PosOrderController::class, 'updateStatus']);
        Route::delete('orders/{order}', [\App\Http\Controllers\Api\PosOrderController::class, 'destroy']);
        Route::post('payments', [\App\Http\Controllers\Api\PosPaymentController::class, 'store']);
    });

    // Report routes
    Route::prefix('reports')->group(function () {
        Route::post('/generate', [ReportController::class, 'generate']);
        Route::post('/export', [ReportController::class, 'export']);
    });

    Route::get('/leaderboard', function () {
        $creators = \App\Models\Creator::with('user')
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

// Employee verification with stricter rate limiting for authentication
Route::middleware(['throttle:10,1'])->post('/employee/verify', function(Request $request) {
    $request->validate([
        'identifier' => 'required', // can be user_id or email
        'password' => 'required',
    ]);
    $user = User::where('id', $request->identifier)
        ->orWhere('email', $request->identifier)
        ->first();
    if ($user && \Hash::check($request->password, $user->password)) {
        // Check if user is either an admin, cashier, or employee
        if ($user->isAdmin() || $user->hasRole('cashier') || $user->hasRole('employee')) {
            return response()->json([
                'success' => true, 
                'name' => $user->name,
                'is_admin' => $user->isAdmin(),
                'is_cashier' => $user->hasRole('cashier')
            ]);
        }
    }
    return response()->json(['success' => false, 'message' => 'Invalid credentials'], 401);
});
