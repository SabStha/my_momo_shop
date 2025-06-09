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
    public function index(Request $request)
    {
        $branchId = $request->query('branch');
        $branch = null;

        if ($branchId) {
            $branch = \App\Models\Branch::findOrFail($branchId);
            session(['branch_id' => $branchId]);
        }

        $query = Order::query();
        $productQuery = Product::query();

        if ($branch) {
            $query->where('branch_id', $branch->id);
            $productQuery->where('products.branch_id', $branch->id);
        }

        $totalOrders = $query->count();
        $totalProducts = $productQuery->count();
        $pendingOrders = $query->where('status', 'pending')->count();

        // Get 5 most recent orders with user info
        $recentOrders = $query->with('user')
            ->latest()
            ->take(5)
            ->get();

        // Get top 5 selling products
        $topProducts = DB::table('products')
            ->join('order_items', 'products.id', '=', 'order_items.product_id')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->when($branch, function($q) use ($branch) {
                return $q->where('orders.branch_id', $branch->id)
                        ->where('products.branch_id', $branch->id);
            })
            ->select('products.name', DB::raw('SUM(order_items.quantity) as sold_count'))
            ->groupBy('products.name')
            ->orderByDesc('sold_count')
            ->limit(5)
            ->get();

        // --- Reports & Analytics Data ---
        $totalSales = $query->where('status', 'completed')->sum('total_amount');
        $totalOrdersReport = $query->where('status', 'completed')->count();
        $totalRevenue = $totalSales;
        $totalCost = \App\Models\OrderItem::join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->when($branch, function($q) use ($branch) {
                return $q->where('orders.branch_id', $branch->id);
            })
            ->where('orders.status', 'completed')
            ->sum(DB::raw('order_items.quantity * products.cost_price'));
        $totalProfit = $totalRevenue - $totalCost;

        // Employee hours for the branch
        $employeeHours = \App\Models\TimeLog::select('employee_id',
                DB::raw('SUM(TIMESTAMPDIFF(HOUR, clock_in, COALESCE(clock_out, NOW()))) as totalHours'),
                DB::raw('SUM(CASE WHEN TIMESTAMPDIFF(HOUR, clock_in, COALESCE(clock_out, NOW())) > 8 THEN TIMESTAMPDIFF(HOUR, clock_in, COALESCE(clock_out, NOW())) - 8 ELSE 0 END) as overtime')
            )
            ->when($branch, function($q) use ($branch) {
                return $q->whereHas('employee', function($q) use ($branch) {
                    $q->where('branch_id', $branch->id);
                });
            })
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
            $revenue = $query->where('status', 'completed')
                ->whereDate('created_at', $date)
                ->sum('total_amount');
            $cost = \App\Models\OrderItem::join('orders', 'order_items.order_id', '=', 'orders.id')
                ->join('products', 'order_items.product_id', '=', 'products.id')
                ->when($branch, function($q) use ($branch) {
                    return $q->where('orders.branch_id', $branch->id);
                })
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

        return view('admin.dashboard', compact(
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
            'branch'
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

    public function simpleReport()
    {
        // Total sales and orders (completed only)
        $totalSales = \App\Models\Order::where('status', 'completed')->sum('total_amount');
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
                ->sum('total_amount');
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

        return view('admin.simple-report', compact('totalSales', 'totalOrders', 'totalProfit', 'employeeHours', 'profitAnalysis'));
    }
} 