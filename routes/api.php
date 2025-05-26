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

Route::get('/debug-test', function () {
    return response()->json(['status' => 'debug route working']);
});

// Debug route for database check
Route::get('/debug-db', function () {
    try {
        $dbName = DB::connection()->getDatabaseName();
        $orderCount = Order::count();
        $productCount = Product::count();
        $totalRevenue = Order::sum('total_amount');
        $sampleOrder = Order::with('user')->first();
        
        return response()->json([
            'database_name' => $dbName,
            'order_count' => $orderCount,
            'product_count' => $productCount,
            'total_revenue' => $totalRevenue,
            'sample_order' => $sampleOrder,
            'connection_status' => 'Connected successfully'
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'error' => $e->getMessage(),
            'connection_status' => 'Failed to connect'
        ], 500);
    }
});

// Admin API routes
Route::middleware(['auth:sanctum', 'admin'])->prefix('admin')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'getDashboardData']);
});

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

Route::post('/employee/verify', function(Request $request) {
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

// Report routes
Route::prefix('reports')->group(function () {
    Route::post('/generate', [ReportController::class, 'generate']);
    Route::post('/export', [ReportController::class, 'export']);
});
