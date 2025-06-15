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

        // Store the selected branch in session
        session(['selected_branch' => $branch]);
        session(['selected_branch_id' => $branch->id]);

        // Get churn risk data
        $churnService = new \App\Services\ChurnRiskNotificationService();
        $churnData = $churnService->getCachedNotifications();

        // Calculate risk levels
        $highRiskCustomers = 0;
        $moderateRiskCustomers = 0;
        $lowRiskCustomers = 0;

        foreach ($churnData as $notification) {
            if ($notification['type'] === 'danger') {
                $highRiskCustomers++;
            } elseif ($notification['type'] === 'warning') {
                $moderateRiskCustomers++;
            } else {
                $lowRiskCustomers++;
            }
        }

        // Get other dashboard data
        $totalOrders = Order::where('branch_id', $branch->id)->count();
        $totalProducts = Product::where('branch_id', $branch->id)->count();
        $pendingOrders = Order::where('branch_id', $branch->id)
            ->where('status', 'pending')
            ->count();
        $recentOrders = Order::where('branch_id', $branch->id)
            ->with(['user', 'items.product'])
            ->latest()
            ->take(5)
            ->get();
        $topProducts = Product::where('branch_id', $branch->id)
            ->withCount(['orderItems as order_items_count' => function ($query) {
                $query->whereHas('order', function ($q) {
                    $q->where('status', 'completed');
                });
            }])
            ->orderByDesc('order_items_count')
            ->take(5)
            ->get();
        $totalSales = Order::where('branch_id', $branch->id)
            ->where('status', 'completed')
            ->sum('total_amount');
        $profitAnalysis = Order::where('branch_id', $branch->id)
            ->where('status', 'completed')
            ->where('created_at', '>=', now()->subDays(30))
            ->selectRaw('DATE(created_at) as date, SUM(total_amount) as total_profit')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return view('admin.dashboard', compact(
            'branch',
            'totalOrders',
            'totalProducts',
            'pendingOrders',
            'recentOrders',
            'topProducts',
            'totalSales',
            'profitAnalysis',
            'highRiskCustomers',
            'moderateRiskCustomers',
            'lowRiskCustomers'
        ));
    }
} 