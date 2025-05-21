<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\SalesAnalyticsController;
use App\Http\Controllers\Admin\DashboardController;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

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
