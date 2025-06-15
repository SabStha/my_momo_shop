<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Order;
use App\Models\Product;
use App\Models\TimeEntry;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminDashboardController extends Controller
{
    public function index(Branch $branch)
    {
        // Check if user has access to this branch
        if (!auth()->user()->isAdmin()) {
            return redirect()->route('admin.branches.index')
                ->with('error', 'You do not have access to this branch.');
        }

        // Store the branch in session for other operations
        session(['selected_branch' => $branch]);
        session(['selected_branch_id' => $branch->id]);

        // Get total orders
        $totalOrders = Order::where('branch_id', $branch->id)->count();
        
        // Get total products
        $totalProducts = Product::where('branch_id', $branch->id)->count();
        
        // Get pending orders
        $pendingOrders = Order::where('branch_id', $branch->id)
            ->where('status', 'pending')
            ->count();
        
        // Get recent orders
        $recentOrders = Order::where('branch_id', $branch->id)
            ->with(['user', 'items.product'])
            ->latest()
            ->take(5)
            ->get();
        
        // Get top selling products
        $topProducts = Product::where('branch_id', $branch->id)
            ->withCount(['orderItems as total_sold' => function($query) {
                $query->select(DB::raw('SUM(quantity)'));
            }])
            ->orderBy('total_sold', 'desc')
            ->take(5)
            ->get();
        
        // Get total sales for the current month
        $totalSales = Order::where('branch_id', $branch->id)
            ->whereMonth('created_at', Carbon::now()->month)
            ->where('status', 'completed')
            ->sum('total_amount');
        
        // Get total orders for the current month
        $totalOrdersReport = Order::where('branch_id', $branch->id)
            ->whereMonth('created_at', Carbon::now()->month)
            ->count();
        
        // Get total profit for the current month
        $totalProfit = Order::where('branch_id', $branch->id)
            ->whereMonth('created_at', Carbon::now()->month)
            ->where('status', 'completed')
            ->sum('profit');
        
        // Get employee hours for the current month
        $employeeHours = TimeEntry::where('branch_id', $branch->id)
            ->whereMonth('clock_in', Carbon::now()->month)
            ->select('user_id', DB::raw('SUM(TIMESTAMPDIFF(HOUR, clock_in, COALESCE(clock_out, NOW()))) as total_hours'))
            ->groupBy('user_id')
            ->with('user')
            ->get();
        
        // Get profit analysis for the last 7 days
        $profitAnalysis = Order::where('branch_id', $branch->id)
            ->where('status', 'completed')
            ->where('created_at', '>=', Carbon::now()->subDays(7))
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('SUM(total_amount) as revenue'),
                DB::raw('SUM(total_amount - profit) as cost'),
                DB::raw('SUM(profit) as profit'),
                DB::raw('(SUM(profit) / SUM(total_amount)) * 100 as margin')
            )
            ->groupBy('date')
            ->get();
        
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
            'profitAnalysis'
        ));
    }
} 