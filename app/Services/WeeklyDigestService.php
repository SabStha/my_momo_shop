<?php

namespace App\Services;

use App\Models\Order;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use PDF;
use OpenAI;

class WeeklyDigestService
{
    protected $customerAnalyticsService;
    protected $salesAnalyticsService;
    protected $openai;

    public function __construct(CustomerAnalyticsService $customerAnalyticsService, SalesAnalyticsService $salesAnalyticsService)
    {
        $this->customerAnalyticsService = $customerAnalyticsService;
        $this->salesAnalyticsService = $salesAnalyticsService;
        $this->openai = OpenAI::client(config('services.openai.api_key'));
    }

    /**
     * Generate weekly digest for a specific branch
     */
    public function generateWeeklyDigest($branchId = 1, ?Carbon $startDate = null, ?Carbon $endDate = null)
    {
        $endDate = $endDate ?? Carbon::now();
        $startDate = $startDate ?? $endDate->copy()->subWeek();

        // Gather KPIs
        $kpis = $this->gatherKPIs($startDate, $endDate, $branchId);

        // Get behavioral triggers
        $behavioralTriggers = $this->getBehavioralTriggers($startDate, $endDate, $branchId);

        // Generate summary using OpenAI
        try {
            $summary = $this->generateSummary($kpis);
        } catch (\Exception $e) {
            // Fallback to a simple summary if OpenAI fails
            $summary = $this->generateFallbackSummary($kpis);
        }

        return [
            'kpis' => $kpis,
            'behavioral_triggers' => $behavioralTriggers,
            'summary' => $summary
        ];
    }

    /**
     * Get behavioral triggers for the period
     */
    protected function getBehavioralTriggers($startDate, $endDate, $branchId)
    {
        // Get churn risk customers
        $churnRiskCustomers = $this->getChurnRiskCustomers($startDate, $endDate, $branchId);

        // Get VIP customers
        $vipCustomers = $this->getVIPCustomers($startDate, $endDate, $branchId);

        return [
            'churn_risk' => $churnRiskCustomers,
            'vip' => $vipCustomers
        ];
    }

    /**
     * Get customers at risk of churning
     */
    protected function getChurnRiskCustomers($startDate, $endDate, $branchId)
    {
        // Customers who haven't ordered in the last 30 days
        $inactiveCustomers = DB::table('users')
            ->whereNotExists(function ($query) use ($startDate, $endDate, $branchId) {
                $query->select(DB::raw(1))
                    ->from('orders')
                    ->whereColumn('orders.user_id', 'users.id')
                    ->where('orders.branch_id', $branchId)
                    ->where('orders.created_at', '>=', $startDate->copy()->subDays(30))
                    ->whereNull('orders.deleted_at');
            })
            ->whereExists(function ($query) use ($branchId) {
                $query->select(DB::raw(1))
                    ->from('orders')
                    ->whereColumn('orders.user_id', 'users.id')
                    ->where('orders.branch_id', $branchId)
                    ->whereNull('orders.deleted_at');
            })
            ->select(
                'users.id',
                'users.name',
                'users.email',
                'users.phone',
                DB::raw('(SELECT MAX(created_at) FROM orders WHERE user_id = users.id AND branch_id = ' . $branchId . ' AND deleted_at IS NULL) as last_order_date'),
                DB::raw('(SELECT COUNT(*) FROM orders WHERE user_id = users.id AND branch_id = ' . $branchId . ' AND deleted_at IS NULL) as total_orders'),
                DB::raw('(SELECT SUM(total_amount) FROM orders WHERE user_id = users.id AND branch_id = ' . $branchId . ' AND deleted_at IS NULL) as total_spent')
            )
            ->get()
            ->map(function ($customer) {
                $customer->days_since_last_order = Carbon::parse($customer->last_order_date)->diffInDays(now());
                $customer->trigger_type = 'churn_risk';
                $customer->trigger_reason = 'No orders in the last 30 days';
                return $customer;
            });

        // Customers with declining order frequency
        $decliningCustomers = DB::table('users')
            ->join('orders', 'users.id', '=', 'orders.user_id')
            ->where('orders.branch_id', $branchId)
            ->whereNull('orders.deleted_at')
            ->where('orders.created_at', '>=', $startDate->copy()->subDays(90))
            ->groupBy('users.id', 'users.name', 'users.email', 'users.phone')
            ->having(DB::raw('COUNT(*)'), '>=', 3)
            ->select(
                'users.id',
                'users.name',
                'users.email',
                'users.phone',
                DB::raw('MAX(orders.created_at) as last_order_date'),
                DB::raw('COUNT(*) as total_orders'),
                DB::raw('SUM(orders.total_amount) as total_spent')
            )
            ->get()
            ->map(function ($customer) use ($startDate, $endDate) {
                $customer->days_since_last_order = Carbon::parse($customer->last_order_date)->diffInDays(now());
                $customer->trigger_type = 'churn_risk';
                $customer->trigger_reason = 'Declining order frequency';
                return $customer;
            });

        return $inactiveCustomers->concat($decliningCustomers)->unique('id');
    }

    /**
     * Get VIP customers
     */
    protected function getVIPCustomers($startDate, $endDate, $branchId)
    {
        // High-value customers (top 10% by spend)
        $highValueCustomers = DB::table('users')
            ->join('orders', 'users.id', '=', 'orders.user_id')
            ->where('orders.branch_id', $branchId)
            ->whereNull('orders.deleted_at')
            ->where('orders.created_at', '>=', $startDate->copy()->subDays(90))
            ->groupBy('users.id', 'users.name', 'users.email', 'users.phone')
            ->having(DB::raw('COUNT(*)'), '>=', 3)
            ->select(
                'users.id',
                'users.name',
                'users.email',
                'users.phone',
                DB::raw('MAX(orders.created_at) as last_order_date'),
                DB::raw('COUNT(*) as total_orders'),
                DB::raw('SUM(orders.total_amount) as total_spent')
            )
            ->orderByDesc('total_spent')
            ->limit(10)
            ->get()
            ->map(function ($customer) {
                $customer->days_since_last_order = Carbon::parse($customer->last_order_date)->diffInDays(now());
                $customer->trigger_type = 'vip';
                $customer->trigger_reason = 'High-value customer';
                return $customer;
            });

        // Loyal customers (frequent orders)
        $loyalCustomers = DB::table('users')
            ->join('orders', 'users.id', '=', 'orders.user_id')
            ->where('orders.branch_id', $branchId)
            ->whereNull('orders.deleted_at')
            ->where('orders.created_at', '>=', $startDate->copy()->subDays(90))
            ->groupBy('users.id', 'users.name', 'users.email', 'users.phone')
            ->having(DB::raw('COUNT(*)'), '>=', 10)
            ->select(
                'users.id',
                'users.name',
                'users.email',
                'users.phone',
                DB::raw('MAX(orders.created_at) as last_order_date'),
                DB::raw('COUNT(*) as total_orders'),
                DB::raw('SUM(orders.total_amount) as total_spent')
            )
            ->get()
            ->map(function ($customer) {
                $customer->days_since_last_order = Carbon::parse($customer->last_order_date)->diffInDays(now());
                $customer->trigger_type = 'vip';
                $customer->trigger_reason = 'Loyal customer';
                return $customer;
            });

        return $highValueCustomers->concat($loyalCustomers)->unique('id');
    }

    /**
     * Get weekly summary
     */
    protected function getWeeklySummary(Carbon $startDate, Carbon $endDate, int $branchId)
    {
        return [
            'total_orders' => $this->getTotalOrders($startDate, $endDate, $branchId),
            'total_revenue' => $this->getTotalRevenue($startDate, $endDate, $branchId),
            'average_order_value' => $this->getAverageOrderValue($startDate, $endDate, $branchId),
            'new_customers' => $this->getNewCustomers($startDate, $endDate, $branchId),
            'returning_customers' => $this->getReturningCustomers($startDate, $endDate, $branchId),
            'top_products' => $this->getTopProducts($startDate, $endDate, $branchId),
            'peak_hours' => $this->getPeakHours($startDate, $endDate, $branchId)
        ];
    }

    /**
     * Get customer insights
     */
    protected function getCustomerInsights(Carbon $startDate, Carbon $endDate, int $branchId)
    {
        return [
            'customer_segments' => $this->getCustomerSegments($startDate, $endDate, $branchId),
            'churn_risk' => $this->getChurnRiskAnalysis($startDate, $endDate, $branchId),
            'loyalty_metrics' => $this->getLoyaltyMetrics($startDate, $endDate, $branchId),
            'customer_satisfaction' => $this->getCustomerSatisfaction($startDate, $endDate, $branchId)
        ];
    }

    /**
     * Get sales analysis
     */
    protected function getSalesAnalysis(Carbon $startDate, Carbon $endDate, int $branchId)
    {
        return [
            'daily_sales' => $this->getDailySales($startDate, $endDate, $branchId),
            'category_performance' => $this->getCategoryPerformance($startDate, $endDate, $branchId),
            'payment_methods' => $this->getPaymentMethodDistribution($startDate, $endDate, $branchId),
            'profit_margins' => $this->getProfitMargins($startDate, $endDate, $branchId)
        ];
    }

    /**
     * Get weekly trends
     */
    protected function getWeeklyTrends(Carbon $startDate, Carbon $endDate, int $branchId)
    {
        return [
            'revenue_trend' => $this->getRevenueTrend($startDate, $endDate, $branchId),
            'customer_growth' => $this->getCustomerGrowth($startDate, $endDate, $branchId),
            'product_trends' => $this->getProductTrends($startDate, $endDate, $branchId),
            'seasonal_patterns' => $this->getSeasonalPatterns($startDate, $endDate, $branchId)
        ];
    }

    /**
     * Get weekly recommendations
     */
    protected function getWeeklyRecommendations(Carbon $startDate, Carbon $endDate, int $branchId)
    {
        $data = [
            'summary' => $this->getWeeklySummary($startDate, $endDate, $branchId),
            'customer_insights' => $this->getCustomerInsights($startDate, $endDate, $branchId),
            'sales_analysis' => $this->getSalesAnalysis($startDate, $endDate, $branchId),
            'trends' => $this->getWeeklyTrends($startDate, $endDate, $branchId)
        ];

        $prompt = "Based on the following weekly data, provide actionable recommendations:\n" .
                 json_encode($data, JSON_PRETTY_PRINT) . "\n" .
                 "Please provide:\n" .
                 "1. Key opportunities for growth\n" .
                 "2. Areas needing immediate attention\n" .
                 "3. Strategic recommendations for the coming week\n" .
                 "4. Customer retention strategies\n" .
                 "5. Product and pricing recommendations";

        return $this->openAIService->generateText($prompt);
    }

    /**
     * Generate AI analysis of the weekly data
     */
    protected function generateAIAnalysis(array $data)
    {
        $prompt = "Analyze this weekly business data and provide insights:\n" .
                 json_encode($data, JSON_PRETTY_PRINT) . "\n" .
                 "Please provide:\n" .
                 "1. Key performance highlights\n" .
                 "2. Notable trends and patterns\n" .
                 "3. Customer behavior insights\n" .
                 "4. Sales performance analysis\n" .
                 "5. Strategic implications";

        return $this->openAIService->generateText($prompt);
    }

    /**
     * Generate PDF from data
     */
    protected function generatePDF(array $data)
    {
        $view = view('admin.analytics.weekly-digest', $data);
        return PDF::loadHTML($view);
    }

    /**
     * Get total orders for the period
     */
    protected function getTotalOrders(Carbon $startDate, Carbon $endDate, int $branchId)
    {
        return Order::where('branch_id', $branchId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->whereNull('deleted_at')
            ->count();
    }

    /**
     * Get total revenue for the period
     */
    protected function getTotalRevenue(Carbon $startDate, Carbon $endDate, int $branchId)
    {
        return Order::where('branch_id', $branchId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->whereNull('deleted_at')
            ->sum('total');
    }

    /**
     * Get average order value
     */
    protected function getAverageOrderValue(Carbon $startDate, Carbon $endDate, int $branchId)
    {
        $totalRevenue = $this->getTotalRevenue($startDate, $endDate, $branchId);
        $totalOrders = $this->getTotalOrders($startDate, $endDate, $branchId);

        return $totalOrders > 0 ? $totalRevenue / $totalOrders : 0;
    }

    /**
     * Get new customers count
     */
    protected function getNewCustomers(Carbon $startDate, Carbon $endDate, int $branchId)
    {
        return User::whereHas('orders', function ($query) use ($startDate, $endDate, $branchId) {
            $query->where('branch_id', $branchId)
                ->whereBetween('created_at', [$startDate, $endDate])
                ->whereNull('deleted_at');
        })
        ->whereDoesntHave('orders', function ($query) use ($startDate, $branchId) {
            $query->where('branch_id', $branchId)
                ->where('created_at', '<', $startDate)
                ->whereNull('deleted_at');
        })
        ->count();
    }

    /**
     * Get returning customers count
     */
    protected function getReturningCustomers(Carbon $startDate, Carbon $endDate, int $branchId)
    {
        return User::whereHas('orders', function ($query) use ($startDate, $endDate, $branchId) {
            $query->where('branch_id', $branchId)
                ->whereBetween('created_at', [$startDate, $endDate])
                ->whereNull('deleted_at');
        })
        ->whereHas('orders', function ($query) use ($startDate, $branchId) {
            $query->where('branch_id', $branchId)
                ->where('created_at', '<', $startDate)
                ->whereNull('deleted_at');
        })
        ->count();
    }

    /**
     * Get top products
     */
    protected function getTopProducts(Carbon $startDate, Carbon $endDate, int $branchId)
    {
        return DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->where('orders.branch_id', $branchId)
            ->whereBetween('orders.created_at', [$startDate, $endDate])
            ->whereNull('orders.deleted_at')
            ->select(
                'order_items.product_id',
                'order_items.item_name',
                DB::raw('SUM(order_items.quantity) as total_quantity'),
                DB::raw('SUM(order_items.quantity * order_items.price) as total_revenue')
            )
            ->groupBy('order_items.product_id', 'order_items.item_name')
            ->orderByDesc('total_revenue')
            ->limit(5)
            ->get();
    }

    /**
     * Get peak hours
     */
    protected function getPeakHours(Carbon $startDate, Carbon $endDate, int $branchId)
    {
        return DB::table('orders')
            ->where('branch_id', $branchId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->whereNull('deleted_at')
            ->select(
                DB::raw('HOUR(created_at) as hour'),
                DB::raw('COUNT(*) as order_count')
            )
            ->groupBy('hour')
            ->orderByDesc('order_count')
            ->limit(3)
            ->get();
    }

    /**
     * Get customer segments
     */
    protected function getCustomerSegments(Carbon $startDate, Carbon $endDate, int $branchId)
    {
        return [
            'new' => $this->getNewCustomers($startDate, $endDate, $branchId),
            'regular' => $this->getRegularCustomers($startDate, $endDate, $branchId),
            'loyal' => $this->getLoyalCustomers($startDate, $endDate, $branchId),
            'vip' => $this->getVIPCustomers($startDate, $endDate, $branchId)
        ];
    }

    /**
     * Get churn risk analysis
     */
    protected function getChurnRiskAnalysis(Carbon $startDate, Carbon $endDate, int $branchId)
    {
        return [
            'high_risk' => $this->getHighRiskCustomers($startDate, $endDate, $branchId),
            'medium_risk' => $this->getMediumRiskCustomers($startDate, $endDate, $branchId),
            'low_risk' => $this->getLowRiskCustomers($startDate, $endDate, $branchId)
        ];
    }

    /**
     * Get loyalty metrics
     */
    protected function getLoyaltyMetrics(Carbon $startDate, Carbon $endDate, int $branchId)
    {
        return [
            'loyalty_score' => $this->getAverageLoyaltyScore($startDate, $endDate, $branchId),
            'retention_rate' => $this->getRetentionRate($startDate, $endDate, $branchId),
            'repeat_purchase_rate' => $this->getRepeatPurchaseRate($startDate, $endDate, $branchId)
        ];
    }

    /**
     * Get customer satisfaction metrics
     */
    protected function getCustomerSatisfaction(Carbon $startDate, Carbon $endDate, int $branchId)
    {
        return [
            'average_rating' => $this->getAverageRating($startDate, $endDate, $branchId),
            'satisfaction_trend' => $this->getSatisfactionTrend($startDate, $endDate, $branchId),
            'feedback_summary' => $this->getFeedbackSummary($startDate, $endDate, $branchId)
        ];
    }

    /**
     * Get daily sales breakdown
     */
    protected function getDailySales(Carbon $startDate, Carbon $endDate, int $branchId)
    {
        return DB::table('orders')
            ->where('branch_id', $branchId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->whereNull('deleted_at')
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as order_count'),
                DB::raw('SUM(total) as total_revenue')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get();
    }

    /**
     * Get category performance
     */
    protected function getCategoryPerformance(Carbon $startDate, Carbon $endDate, int $branchId)
    {
        return DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->where('orders.branch_id', $branchId)
            ->whereBetween('orders.created_at', [$startDate, $endDate])
            ->whereNull('orders.deleted_at')
            ->select(
                'products.category',
                DB::raw('COUNT(*) as total_orders'),
                DB::raw('SUM(order_items.quantity) as total_quantity'),
                DB::raw('SUM(order_items.quantity * order_items.price) as total_revenue')
            )
            ->groupBy('products.category')
            ->orderByDesc('total_revenue')
            ->get();
    }

    /**
     * Get payment method distribution
     */
    protected function getPaymentMethodDistribution(Carbon $startDate, Carbon $endDate, int $branchId)
    {
        return DB::table('orders')
            ->where('branch_id', $branchId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->whereNull('deleted_at')
            ->select(
                'payment_method',
                DB::raw('COUNT(*) as count'),
                DB::raw('SUM(total) as total_amount')
            )
            ->groupBy('payment_method')
            ->get();
    }

    /**
     * Get profit margins
     */
    protected function getProfitMargins(Carbon $startDate, Carbon $endDate, int $branchId)
    {
        $revenue = $this->getTotalRevenue($startDate, $endDate, $branchId);
        $cost = $this->getTotalCost($startDate, $endDate, $branchId);
        $profit = $revenue - $cost;
        $margin = $revenue > 0 ? ($profit / $revenue) * 100 : 0;

        return [
            'revenue' => $revenue,
            'cost' => $cost,
            'profit' => $profit,
            'margin' => $margin
        ];
    }

    /**
     * Get revenue trend
     */
    protected function getRevenueTrend(Carbon $startDate, Carbon $endDate, int $branchId)
    {
        return DB::table('orders')
            ->where('branch_id', $branchId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->whereNull('deleted_at')
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('SUM(total) as revenue')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get();
    }

    /**
     * Get customer growth trend
     */
    protected function getCustomerGrowth(Carbon $startDate, Carbon $endDate, int $branchId)
    {
        return DB::table('orders')
            ->where('branch_id', $branchId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->whereNull('deleted_at')
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(DISTINCT user_id) as customer_count')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get();
    }

    /**
     * Get product trends
     */
    protected function getProductTrends(Carbon $startDate, Carbon $endDate, int $branchId)
    {
        return DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->where('orders.branch_id', $branchId)
            ->whereBetween('orders.created_at', [$startDate, $endDate])
            ->whereNull('orders.deleted_at')
            ->select(
                'order_items.product_id',
                'order_items.item_name',
                DB::raw('DATE(orders.created_at) as date'),
                DB::raw('SUM(order_items.quantity) as quantity')
            )
            ->groupBy('order_items.product_id', 'order_items.item_name', 'date')
            ->orderBy('date')
            ->get();
    }

    /**
     * Get seasonal patterns
     */
    protected function getSeasonalPatterns(Carbon $startDate, Carbon $endDate, int $branchId)
    {
        return [
            'day_of_week' => $this->getDayOfWeekPattern($startDate, $endDate, $branchId),
            'hour_of_day' => $this->getHourOfDayPattern($startDate, $endDate, $branchId),
            'product_seasonality' => $this->getProductSeasonality($startDate, $endDate, $branchId)
        ];
    }

    /**
     * Get day of week pattern
     */
    protected function getDayOfWeekPattern(Carbon $startDate, Carbon $endDate, int $branchId)
    {
        return DB::table('orders')
            ->where('branch_id', $branchId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->whereNull('deleted_at')
            ->select(
                DB::raw('DAYOFWEEK(created_at) as day_of_week'),
                DB::raw('COUNT(*) as order_count'),
                DB::raw('SUM(total) as total_revenue')
            )
            ->groupBy('day_of_week')
            ->orderBy('day_of_week')
            ->get();
    }

    /**
     * Get hour of day pattern
     */
    protected function getHourOfDayPattern(Carbon $startDate, Carbon $endDate, int $branchId)
    {
        return DB::table('orders')
            ->where('branch_id', $branchId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->whereNull('deleted_at')
            ->select(
                DB::raw('HOUR(created_at) as hour'),
                DB::raw('COUNT(*) as order_count'),
                DB::raw('SUM(total) as total_revenue')
            )
            ->groupBy('hour')
            ->orderBy('hour')
            ->get();
    }

    /**
     * Get product seasonality
     */
    protected function getProductSeasonality(Carbon $startDate, Carbon $endDate, int $branchId)
    {
        return DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->where('orders.branch_id', $branchId)
            ->whereBetween('orders.created_at', [$startDate, $endDate])
            ->whereNull('orders.deleted_at')
            ->select(
                'order_items.product_id',
                'order_items.item_name',
                DB::raw('HOUR(orders.created_at) as hour'),
                DB::raw('DAYOFWEEK(orders.created_at) as day_of_week'),
                DB::raw('SUM(order_items.quantity) as total_quantity')
            )
            ->groupBy('order_items.product_id', 'order_items.item_name', 'hour', 'day_of_week')
            ->orderBy('total_quantity', 'desc')
            ->get();
    }

    /**
     * Gather all KPIs for the period
     */
    protected function gatherKPIs($startDate, $endDate, $branchId)
    {
        // Get total sales for the branch
        $totalSales = DB::table('orders')
            ->where('branch_id', $branchId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->whereNull('deleted_at')
            ->sum('total_amount');

        // Get new customers
        $newCustomers = DB::table('users')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->count();

        // Get churn rate using the analytics service
        $churnRate = $this->customerAnalyticsService->getChurnRate(
            $startDate->format('Y-m-d'),
            $endDate->format('Y-m-d'),
            $branchId
        );

        // Get average order value
        $averageOrderValue = DB::table('orders')
            ->where('branch_id', $branchId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->whereNull('deleted_at')
            ->avg('total_amount');

        // Get total orders
        $totalOrders = DB::table('orders')
            ->where('branch_id', $branchId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->whereNull('deleted_at')
            ->count();

        // Get top products
        $topProducts = DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->where('orders.branch_id', $branchId)
            ->whereBetween('orders.created_at', [$startDate, $endDate])
            ->whereNull('orders.deleted_at')
            ->select(
                'order_items.product_id',
                'order_items.item_name',
                DB::raw('SUM(order_items.quantity) as total_quantity'),
                DB::raw('SUM(order_items.quantity * order_items.price) as total_revenue')
            )
            ->groupBy('order_items.product_id', 'order_items.item_name')
            ->orderByDesc('total_revenue')
            ->limit(5)
            ->get();

        // Get peak hours
        $peakHours = DB::table('orders')
            ->where('branch_id', $branchId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->whereNull('deleted_at')
            ->select(
                DB::raw('HOUR(created_at) as hour'),
                DB::raw('COUNT(*) as order_count'),
                DB::raw('SUM(total_amount) as total_revenue')
            )
            ->groupBy('hour')
            ->orderByDesc('order_count')
            ->limit(3)
            ->get();

        return [
            'total_sales' => $totalSales,
            'new_customers' => $newCustomers,
            'churn_rate' => $churnRate,
            'average_order_value' => $averageOrderValue,
            'total_orders' => $totalOrders,
            'top_products' => $topProducts,
            'peak_hours' => $peakHours,
            'period' => [
                'start' => $startDate->format('Y-m-d'),
                'end' => $endDate->format('Y-m-d')
            ]
        ];
    }

    /**
     * Generate summary using OpenAI
     */
    protected function generateSummary($kpis)
    {
        $prompt = "Analyze the following weekly KPIs and provide a concise summary:\n" . json_encode($kpis, JSON_PRETTY_PRINT);
        
        $response = $this->openai->chat()->create([
            'model' => 'gpt-3.5-turbo',
            'messages' => [
                ['role' => 'system', 'content' => 'You are a helpful assistant that analyzes business metrics.'],
                ['role' => 'user', 'content' => $prompt]
            ],
            'max_tokens' => 150
        ]);

        return $response->choices[0]->message->content;
    }

    /**
     * Generate fallback summary if OpenAI fails
     */
    protected function generateFallbackSummary($kpis)
    {
        return sprintf(
            "Weekly Summary (%s to %s):\n" .
            "- Total Sales: $%.2f\n" .
            "- New Customers: %d\n" .
            "- Churn Rate: %.2f%%\n" .
            "- Average Order Value: $%.2f\n" .
            "- Total Orders: %d\n" .
            "- Top Products: %s\n" .
            "- Peak Hours: %s",
            $kpis['period']['start'],
            $kpis['period']['end'],
            $kpis['total_sales'],
            $kpis['new_customers'],
            $kpis['churn_rate'],
            $kpis['average_order_value'],
            $kpis['total_orders'],
            $kpis['top_products']->pluck('item_name')->implode(', '),
            $kpis['peak_hours']->pluck('hour')->implode(', ')
        );
    }
} 