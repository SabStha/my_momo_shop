<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DashboardController extends Controller
{
    public function index()
    {
        $totalOrders = Order::count();
        $totalProducts = Product::count();
        $pendingOrders = Order::where('status', 'pending')->count();

        // Get 5 most recent orders with user info
        $recentOrders = Order::with('user')
            ->latest()
            ->take(5)
            ->get();

        // Get top 5 selling products
        $topProducts = Product::select('products.name')
            ->join('order_items', 'products.id', '=', 'order_items.product_id')
            ->selectRaw('products.name, SUM(order_items.quantity) as sold_count')
            ->groupBy('products.name')
            ->orderByDesc('sold_count')
            ->take(5)
            ->get();

        return view('admin.dashboard', compact(
            'totalOrders',
            'totalProducts',
            'pendingOrders',
            'recentOrders',
            'topProducts'
        ));
    }

    public function getDashboardData()
    {
        try {
            // Log the database connection info
            Log::info('Database connection:', [
                'database' => DB::connection()->getDatabaseName(),
                'driver' => DB::connection()->getDriverName()
            ]);

            // Get counts with logging
            $totalOrders = Order::count();
            Log::info('Total orders count:', ['count' => $totalOrders]);

            $totalProducts = Product::count();
            Log::info('Total products count:', ['count' => $totalProducts]);

            $totalRevenue = Order::sum('total_amount');
            Log::info('Total revenue:', ['amount' => $totalRevenue]);

            // Get recent orders with eager loading
            $recentOrders = Order::with('user')
                ->latest()
                ->take(5)
                ->get();
            
            Log::info('Recent orders count:', ['count' => $recentOrders->count()]);

            // Debug query
            $lastQuery = DB::getQueryLog();
            Log::info('Last executed query:', ['query' => end($lastQuery)]);

            return response()->json([
                'orders_today' => $totalOrders,
                'total_sales_today' => $totalRevenue,
                'totalProducts' => $totalProducts, // optional
                'recentOrders' => $recentOrders,
                'debug_info' => [
                    'database' => DB::connection()->getDatabaseName(),
                    'queries_executed' => count($lastQuery)
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Dashboard data error:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'error' => 'Failed to fetch dashboard data',
                'message' => $e->getMessage()
            ], 500);
        }
    }
} 