<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Campaign;
use App\Models\Customer;
use App\Models\Order;
use App\Models\ActivityLog;
use App\Models\Rule;
use App\Models\Product;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Models\CampaignRedemption;
use App\Models\Activity;

class AdminDashboardController extends Controller
{
    public function index()
    {
        $branches = Branch::all();
        $selectedBranchId = session('selected_branch_id');

        if ($selectedBranchId) {
            // Basic metrics
            $totalCustomers = Customer::where('branch_id', $selectedBranchId)->count();
            $totalOrders = Order::where('branch_id', $selectedBranchId)
                ->whereMonth('created_at', now()->month)
                ->count();
            $totalRevenue = (float) Order::where('branch_id', $selectedBranchId)
                ->whereMonth('created_at', now()->month)
                ->sum('total_amount');
            $activeCampaigns = Campaign::where('branch_id', $selectedBranchId)
                ->where('status', 'active')
                ->count();

            // Enhanced Sales Analytics
            $salesAnalytics = $this->getSalesAnalytics($selectedBranchId);
            
            // Top Products
            $topProducts = $this->getTopProducts($selectedBranchId);
            
            // Payment Methods Breakdown
            $paymentMethods = $this->getPaymentMethodsBreakdown($selectedBranchId);
            
            // Sales by Hour
            $salesByHour = $this->getSalesByHour($selectedBranchId);

            // Product Categories Breakdown
            $productCategories = $this->getProductCategoriesBreakdown($selectedBranchId);
            
            // Customer Segments
            $customerSegments = $this->getCustomerSegments($selectedBranchId);
            
            // Revenue Distribution
            $revenueDistribution = $this->getRevenueDistribution($selectedBranchId);

            $rules = Rule::where('branch_id', $selectedBranchId)
                ->orderBy('priority')
                ->get();
            $campaigns = Campaign::where('branch_id', $selectedBranchId)
                ->orderBy('created_at', 'desc')
                ->get();

            // Get sales trend data
            $salesTrend = Order::where('branch_id', $selectedBranchId)
                ->where('created_at', '>=', now()->subDays(30))
                ->selectRaw('DATE(created_at) as date, SUM(total_amount) as amount, COUNT(*) as count')
                ->groupBy('date')
                ->get();

            // Get campaign metrics
            $campaignMetrics = [
                'total_redemptions' => 0,
                'average_open_rate' => 0,
                'average_engagement_rate' => 0,
                'average_roi' => 0
            ];

            return view('admin.dashboard', compact(
                'branches',
                'totalCustomers',
                'totalOrders',
                'totalRevenue',
                'activeCampaigns',
                'rules',
                'campaigns',
                'salesTrend',
                'campaignMetrics',
                'salesAnalytics',
                'topProducts',
                'paymentMethods',
                'salesByHour',
                'productCategories',
                'customerSegments',
                'revenueDistribution'
            ));
        }

        return view('admin.dashboard', compact('branches'));
    }

    private function getSalesAnalytics($branchId)
    {
        $currentMonth = now()->month;
        $currentYear = now()->year;
        $lastMonth = now()->subMonth()->month;
        $lastYear = now()->subMonth()->year;

        // Current month metrics
        $currentMonthOrders = Order::where('branch_id', $branchId)
            ->whereMonth('created_at', $currentMonth)
            ->whereYear('created_at', $currentYear);
        
        $currentMonthRevenue = (float) $currentMonthOrders->sum('total_amount');
        $currentMonthCount = $currentMonthOrders->count();
        $currentMonthAvgOrder = $currentMonthCount > 0 ? $currentMonthRevenue / $currentMonthCount : 0;

        // Last month metrics
        $lastMonthOrders = Order::where('branch_id', $branchId)
            ->whereMonth('created_at', $lastMonth)
            ->whereYear('created_at', $lastYear);
        
        $lastMonthRevenue = (float) $lastMonthOrders->sum('total_amount');
        $lastMonthCount = $lastMonthOrders->count();
        $lastMonthAvgOrder = $lastMonthCount > 0 ? $lastMonthRevenue / $lastMonthCount : 0;

        // Calculate growth percentages
        $revenueGrowth = $lastMonthRevenue > 0 ? (($currentMonthRevenue - $lastMonthRevenue) / $lastMonthRevenue) * 100 : 0;
        $ordersGrowth = $lastMonthCount > 0 ? (($currentMonthCount - $lastMonthCount) / $lastMonthCount) * 100 : 0;
        $avgOrderGrowth = $lastMonthAvgOrder > 0 ? (($currentMonthAvgOrder - $lastMonthAvgOrder) / $lastMonthAvgOrder) * 100 : 0;

        // Today's metrics
        $todayRevenue = (float) Order::where('branch_id', $branchId)
            ->whereDate('created_at', today())
            ->sum('total_amount');
        
        $todayOrders = Order::where('branch_id', $branchId)
            ->whereDate('created_at', today())
            ->count();

        // This week metrics
        $weekStart = now()->startOfWeek();
        $weekEnd = now()->endOfWeek();
        
        $thisWeekRevenue = (float) Order::where('branch_id', $branchId)
            ->whereBetween('created_at', [$weekStart, $weekEnd])
            ->sum('total_amount');
        
        $thisWeekOrders = Order::where('branch_id', $branchId)
            ->whereBetween('created_at', [$weekStart, $weekEnd])
            ->count();

        return [
            'current_month' => [
                'revenue' => $currentMonthRevenue,
                'orders' => $currentMonthCount,
                'avg_order' => $currentMonthAvgOrder
            ],
            'last_month' => [
                'revenue' => $lastMonthRevenue,
                'orders' => $lastMonthCount,
                'avg_order' => $lastMonthAvgOrder
            ],
            'growth' => [
                'revenue' => $revenueGrowth,
                'orders' => $ordersGrowth,
                'avg_order' => $avgOrderGrowth
            ],
            'today' => [
                'revenue' => $todayRevenue,
                'orders' => $todayOrders
            ],
            'this_week' => [
                'revenue' => $thisWeekRevenue,
                'orders' => $thisWeekOrders
            ]
        ];
    }

    private function getTopProducts($branchId)
    {
        return OrderItem::join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->where('orders.branch_id', $branchId)
            ->whereMonth('orders.created_at', now()->month)
            ->select(
                'products.name',
                'products.id',
                DB::raw('SUM(order_items.quantity) as total_quantity'),
                DB::raw('SUM(order_items.quantity * order_items.price) as total_revenue')
            )
            ->groupBy('products.id', 'products.name')
            ->orderBy('total_revenue', 'desc')
            ->limit(10)
            ->get();
    }

    private function getPaymentMethodsBreakdown($branchId)
    {
        return Order::where('branch_id', $branchId)
            ->whereMonth('created_at', now()->month)
            ->select('payment_method', 
                DB::raw('COUNT(*) as count'),
                DB::raw('SUM(total_amount) as total_amount')
            )
            ->groupBy('payment_method')
            ->get();
    }

    private function getSalesByHour($branchId)
    {
        return Order::where('branch_id', $branchId)
            ->whereMonth('created_at', now()->month)
            ->select(
                DB::raw('HOUR(created_at) as hour'),
                DB::raw('COUNT(*) as count'),
                DB::raw('SUM(total_amount) as total_amount')
            )
            ->groupBy('hour')
            ->orderBy('hour')
            ->get();
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

    private function getProductCategoriesBreakdown($branchId)
    {
        return OrderItem::join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->where('orders.branch_id', $branchId)
            ->whereMonth('orders.created_at', now()->month)
            ->select(
                'products.category',
                DB::raw('SUM(order_items.quantity * order_items.price) as total_revenue'),
                DB::raw('COUNT(DISTINCT orders.id) as order_count')
            )
            ->groupBy('products.category')
            ->orderBy('total_revenue', 'desc')
            ->get();
    }

    private function getCustomerSegments($branchId)
    {
        // Get customers with their order counts and total spent
        $customers = Customer::where('branch_id', $branchId)
            ->withCount(['orders' => function($query) {
                $query->whereMonth('created_at', now()->month);
            }])
            ->withSum(['orders' => function($query) {
                $query->whereMonth('created_at', now()->month);
            }], 'total_amount')
            ->get();

        // Segment customers based on spending
        $segments = [
            'High Value' => 0,
            'Medium Value' => 0,
            'Low Value' => 0,
            'New Customers' => 0
        ];

        foreach ($customers as $customer) {
            $totalSpent = (float) ($customer->orders_sum_total_amount ?? 0);
            $orderCount = $customer->orders_count ?? 0;

            if ($orderCount === 0) {
                $segments['New Customers']++;
            } elseif ($totalSpent >= 100) {
                $segments['High Value']++;
            } elseif ($totalSpent >= 50) {
                $segments['Medium Value']++;
            } else {
                $segments['Low Value']++;
            }
        }

        return collect($segments)->map(function($count, $segment) {
            return (object) [
                'segment' => $segment,
                'count' => $count
            ];
        });
    }

    private function getRevenueDistribution($branchId)
    {
        // Get revenue by day of week
        $revenueByDay = Order::where('branch_id', $branchId)
            ->whereMonth('created_at', now()->month)
            ->select(
                DB::raw('DAYOFWEEK(created_at) as day_of_week'),
                DB::raw('SUM(total_amount) as total_revenue'),
                DB::raw('COUNT(*) as order_count')
            )
            ->groupBy('day_of_week')
            ->get();

        $dayNames = [
            1 => 'Sunday',
            2 => 'Monday', 
            3 => 'Tuesday',
            4 => 'Wednesday',
            5 => 'Thursday',
            6 => 'Friday',
            7 => 'Saturday'
        ];

        return $revenueByDay->map(function($item) use ($dayNames) {
            return (object) [
                'day' => $dayNames[$item->day_of_week] ?? 'Unknown',
                'revenue' => (float) $item->total_revenue,
                'orders' => $item->order_count
            ];
        });
    }
} 