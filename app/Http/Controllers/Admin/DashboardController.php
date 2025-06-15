<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Employee;
use App\Models\Branch;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $branchId = $request->user()->branch_id;
        $branch = Branch::findOrFail($branchId);

        // Get total orders for the branch
        $totalOrders = Order::when($branchId, function($query) use ($branchId) {
            return $query->where('branch_id', $branchId);
        })->count();

        // Get total products for the branch
        $totalProducts = Product::when($branchId, function($query) use ($branchId) {
            return $query->where('branch_id', $branchId);
        })->count();

        // Get pending orders for the branch
        $pendingOrders = Order::when($branchId, function($query) use ($branchId) {
            return $query->where('branch_id', $branchId);
        })->where('status', 'pending')->count();

        // Get recent orders for the branch
        $recentOrders = Order::with(['customer'])
            ->when($branchId, function($query) use ($branchId) {
                return $query->where('branch_id', $branchId);
            })
            ->latest()
            ->take(5)
            ->get();

        // Get top products for the branch
        $topProducts = Product::withCount(['orderItems' => function($query) use ($branchId) {
            $query->when($branchId, function($q) use ($branchId) {
                return $q->whereHas('order', function($q) use ($branchId) {
                    $q->where('branch_id', $branchId);
                });
            });
        }])
        ->when($branchId, function($query) use ($branchId) {
            return $query->where('branch_id', $branchId);
        })
        ->orderBy('order_items_count', 'desc')
        ->take(5)
        ->get();

        // Get total sales for the branch
        $totalSales = Order::when($branchId, function($query) use ($branchId) {
            return $query->where('branch_id', $branchId);
        })->where('status', 'completed')->sum('total_amount');

        // Get total orders report for the branch
        $totalOrdersReport = Order::when($branchId, function($query) use ($branchId) {
            return $query->where('branch_id', $branchId);
        })->count();

        // Get total profit for the branch
        $totalProfit = Order::when($branchId, function($query) use ($branchId) {
            return $query->where('branch_id', $branchId);
        })->where('status', 'completed')->sum('profit');

        // Get employee hours for the branch
        $employeeHours = Employee::when($branchId, function($query) use ($branchId) {
            return $query->where('branch_id', $branchId);
        })->get();

        // Get profit analysis for the branch
        $profitAnalysis = Order::when($branchId, function($query) use ($branchId) {
            return $query->where('branch_id', $branchId);
        })->where('status', 'completed')
        ->selectRaw('DATE(created_at) as date, SUM(profit) as total_profit')
        ->groupBy('date')
        ->orderBy('date', 'desc')
        ->take(30)
        ->get();

        // Prepare chart data
        $salesChart = [
            'labels' => $profitAnalysis->pluck('date')->reverse(),
            'data' => $profitAnalysis->pluck('total_profit')->reverse()
        ];

        $ordersChart = [
            'labels' => $profitAnalysis->pluck('date')->reverse(),
            'data' => Order::when($branchId, function($query) use ($branchId) {
                return $query->where('branch_id', $branchId);
            })
            ->where('status', 'completed')
            ->whereIn('created_at', $profitAnalysis->pluck('date'))
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->pluck('count')
        ];

        return view('admin.dashboard', compact(
            'branch',
            'totalOrders',
            'totalProducts',
            'pendingOrders',
            'recentOrders',
            'topProducts',
            'totalSales',
            'totalOrdersReport',
            'totalProfit',
            'employeeHours',
            'profitAnalysis',
            'salesChart',
            'ordersChart'
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

            $totalRevenue = Order::sum('total');
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

    public function simpleReport()
    {
        // Total sales and orders (completed only)
        $totalSales = \App\Models\Order::where('status', 'completed')->sum('total');
        $totalOrders = \App\Models\Order::where('status', 'completed')->count();

        // Total profit (sales - cost of goods sold)
        $totalRevenue = $totalSales;
        $totalCost = \App\Models\OrderItem::join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->where('orders.status', 'completed')
            ->sum(DB::raw('order_items.quantity * products.cost_price'));
        $totalProfit = $totalRevenue - $totalCost;

        // Employee working hours (sum of hours per user)
        $employeeHours = \App\Models\TimeLog::select('employee_id',
                DB::raw('SUM(TIMESTAMPDIFF(HOUR, clock_in, COALESCE(clock_out, NOW()))) as totalHours'),
                DB::raw('SUM(CASE WHEN TIMESTAMPDIFF(HOUR, clock_in, COALESCE(clock_out, NOW())) > 8 THEN TIMESTAMPDIFF(HOUR, clock_in, COALESCE(clock_out, NOW())) - 8 ELSE 0 END) as overtime')
            )
            ->groupBy('employee_id')
            ->get()
            ->map(function ($row) {
                $employee = \App\Models\Employee::with('user')->find($row->employee_id);
                $userName = $employee && $employee->user ? $employee->user->name : 'Unknown Employee';
                $hourlyRate = 500;
                $overtimeRate = $hourlyRate * 1.5;
                $regularHours = $row->totalHours - $row->overtime;
                $totalPay = ($regularHours * $hourlyRate) + ($row->overtime * $overtimeRate);
                return [
                    'name' => $userName,
                    'totalHours' => $row->totalHours,
                    'overtime' => $row->overtime,
                    'totalPay' => $totalPay
                ];
            });

        // Profit analysis for last 7 days
        $profitAnalysis = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i)->toDateString();
            $revenue = \App\Models\Order::where('status', 'completed')
                ->whereDate('created_at', $date)
                ->sum('total');
            $cost = \App\Models\OrderItem::join('orders', 'order_items.order_id', '=', 'orders.id')
                ->join('products', 'order_items.product_id', '=', 'products.id')
                ->where('orders.status', 'completed')
                ->whereDate('orders.created_at', $date)
                ->sum(DB::raw('order_items.quantity * products.cost_price'));
            $profit = $revenue - $cost;
            $margin = $revenue > 0 ? round(($profit / $revenue) * 100, 2) : 0;
            $profitAnalysis[] = [
                'date' => $date,
                'revenue' => $revenue,
                'cost' => $cost,
                'profit' => $profit,
                'margin' => $margin
            ];
        }

        return view('admin.reports.simple', compact(
            'totalSales',
            'totalOrders',
            'totalRevenue',
            'totalCost',
            'totalProfit',
            'employeeHours',
            'profitAnalysis'
        ));
    }
} 