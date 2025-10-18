<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\ChurnPrediction;
use App\Services\ChurnPredictionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Employee;
use App\Models\Branch;
use App\Models\Campaign;
use App\Models\Customer;
use App\Models\ActivityLog;
use Carbon\Carbon;
use App\Models\Rule;

class DashboardController extends Controller
{
    protected $churnPredictionService;

    public function __construct(ChurnPredictionService $churnPredictionService)
    {
        $this->churnPredictionService = $churnPredictionService;
    }

    public function index(Request $request)
    {
        $currentBranch = $request->session()->get('current_branch');
        
        // Get all branches for the branch selector
        $branches = Branch::all();
        
        // Get metrics for the current branch
        $totalCustomers = DB::table('users')
            ->join('orders', 'users.id', '=', 'orders.user_id')
            ->where('orders.branch_id', $currentBranch->id)
            ->whereIn('orders.status', ['completed', 'delivered'])
            ->distinct('users.id')
            ->count('users.id');
            
        $totalOrders = Order::where('branch_id', $currentBranch->id)
            ->whereIn('status', ['completed', 'delivered'])
            ->count();
            
        $totalRevenue = Order::where('branch_id', $currentBranch->id)
            ->whereIn('status', ['completed', 'delivered'])
            ->sum('total');
            
        // Get active campaigns
        $activeCampaigns = Campaign::where('branch_id', $currentBranch->id)
            ->where('status', 'active')
            ->count();
            
        // Get campaign metrics
        $campaignMetrics = [
            'total_redemptions' => 0,
            'average_open_rate' => 0,
            'average_engagement_rate' => 0,
            'average_roi' => 0
        ];
        
        // Get recent activity
        $recentActivity = Order::where('branch_id', $currentBranch->id)
            ->with(['customer', 'items.product'])
            ->latest()
            ->take(5)
            ->get();
            
        // Get sales trend
        $salesTrend = Order::where('branch_id', $currentBranch->id)
            ->whereIn('status', ['completed', 'delivered'])
            ->where('created_at', '>=', now()->subDays(30))
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('SUM(total_amount) as total')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get();
            
        // Get order trend
        $orderTrend = Order::where('branch_id', $currentBranch->id)
            ->whereIn('status', ['completed', 'delivered'])
            ->where('created_at', '>=', now()->subDays(30))
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as count')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Get rules for the current branch
        $rules = Rule::where('branch_id', $currentBranch->id)
            ->orderBy('priority')
            ->get();
        
        return view('admin.dashboard', compact(
            'currentBranch',
            'branches',
            'totalCustomers',
            'totalOrders',
            'totalRevenue',
            'activeCampaigns',
            'campaignMetrics',
            'recentActivity',
            'salesTrend',
            'orderTrend',
            'rules'
        ));
    }

    private function getCampaignMetrics($branch)
    {
        $campaigns = Campaign::where('branch_id', $branch->id)->get();

        $totalRedemptions = 0;
        $totalOpenRate = 0;
        $totalEngagementRate = 0;
        $totalROI = 0;
        $campaignCount = $campaigns->count();

        foreach ($campaigns as $campaign) {
            // Calculate redemptions
            $redemptions = $campaign->triggers()
                ->where('status', 'completed')
                ->where('action_taken', 'redeemed')
                ->count();
            $totalRedemptions += $redemptions;

            // Calculate open rate
            $totalSent = $campaign->triggers()->where('status', 'completed')->count();
            if ($totalSent > 0) {
                $totalOpened = $campaign->triggers()
                    ->where('status', 'completed')
                    ->where('opened_at', '!=', null)
                    ->count();
                $totalOpenRate += ($totalOpened / $totalSent) * 100;
            }

            // Calculate engagement rate
            if ($totalSent > 0) {
                $totalEngaged = $campaign->triggers()
                    ->where('status', 'completed')
                    ->where(function ($query) {
                        $query->where('clicked_at', '!=', null)
                            ->orWhere('action_taken', '!=', null);
                    })
                    ->count();
                $totalEngagementRate += ($totalEngaged / $totalSent) * 100;
            }

            // Calculate ROI
            $cost = $campaign->cost ?? 0;
            if ($cost > 0) {
                $revenue = $campaign->triggers()
                    ->where('status', 'completed')
                    ->where('action_taken', 'redeemed')
                    ->sum('revenue_generated');
                $totalROI += (($revenue - $cost) / $cost) * 100;
            }
        }

        return [
            'total_redemptions' => $totalRedemptions,
            'average_open_rate' => $campaignCount > 0 ? $totalOpenRate / $campaignCount : 0,
            'average_engagement_rate' => $campaignCount > 0 ? $totalEngagementRate / $campaignCount : 0,
            'average_roi' => $campaignCount > 0 ? $totalROI / $campaignCount : 0,
        ];
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