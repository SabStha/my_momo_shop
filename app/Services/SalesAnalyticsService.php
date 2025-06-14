<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class SalesAnalyticsService
{
    protected $openAIService;

    public function __construct(OpenAIService $openAIService)
    {
        $this->openAIService = $openAIService;
    }

    /**
     * Get total sales for a given period
     */
    public function getTotalSales($startDate = null, $endDate = null)
    {
        $query = Order::where('status', 'completed');
        
        if ($startDate) {
            $query->where('created_at', '>=', $startDate);
        }
        if ($endDate) {
            $query->where('created_at', '<=', $endDate);
        }
        
        return $query->sum('total_amount');
    }

    /**
     * Get total number of orders for a given period
     */
    public function getTotalOrders($startDate = null, $endDate = null)
    {
        $query = Order::where('status', 'completed');
        
        if ($startDate) {
            $query->where('created_at', '>=', $startDate);
        }
        if ($endDate) {
            $query->where('created_at', '<=', $endDate);
        }
        
        return $query->count();
    }

    /**
     * Get average order value
     */
    public function getAverageOrderValue($startDate = null, $endDate = null)
    {
        $totalSales = $this->getTotalSales($startDate, $endDate);
        $totalOrders = $this->getTotalOrders($startDate, $endDate);
        
        return $totalOrders > 0 ? $totalSales / $totalOrders : 0;
    }

    /**
     * Get top selling products
     */
    public function getTopSellingProducts($limit = 5, $startDate = null, $endDate = null)
    {
        $query = OrderItem::select('product_id', DB::raw('SUM(quantity) as total_quantity'))
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->where('orders.status', 'completed')
            ->groupBy('product_id')
            ->orderBy('total_quantity', 'desc')
            ->limit($limit);

        if ($startDate) {
            $query->where('orders.created_at', '>=', $startDate);
        }
        if ($endDate) {
            $query->where('orders.created_at', '<=', $endDate);
        }

        return $query->get()->map(function ($item) {
            $product = Product::find($item->product_id);
            return [
                'product' => $product,
                'total_quantity' => $item->total_quantity
            ];
        });
    }

    /**
     * Get sales by day for the last 30 days
     */
    public function getDailySales($days = 30)
    {
        $startDate = Carbon::now()->subDays($days);
        
        return Order::where('status', 'completed')
            ->where('created_at', '>=', $startDate)
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('SUM(total_amount) as total'))
            ->groupBy('date')
            ->orderBy('date')
            ->get();
    }

    /**
     * Get all dashboard KPIs
     */
    public function getDashboardKPIs()
    {
        $today = Carbon::today();
        $thisMonth = Carbon::now()->startOfMonth();
        $lastMonth = Carbon::now()->subMonth()->startOfMonth();
        
        return [
            'today' => [
                'sales' => $this->getTotalSales($today),
                'orders' => $this->getTotalOrders($today),
                'average_order_value' => $this->getAverageOrderValue($today),
            ],
            'this_month' => [
                'sales' => $this->getTotalSales($thisMonth),
                'orders' => $this->getTotalOrders($thisMonth),
                'average_order_value' => $this->getAverageOrderValue($thisMonth),
            ],
            'last_month' => [
                'sales' => $this->getTotalSales($lastMonth, $thisMonth),
                'orders' => $this->getTotalOrders($lastMonth, $thisMonth),
                'average_order_value' => $this->getAverageOrderValue($lastMonth, $thisMonth),
            ],
            'top_products' => $this->getTopSellingProducts(5),
            'daily_sales' => $this->getDailySales(),
        ];
    }

    /**
     * Get comprehensive sales overview
     *
     * @param string $period (daily, weekly, monthly, yearly)
     * @param string|null $startDate
     * @param string|null $endDate
     * @return array
     */
    public function getSalesOverview(string $period = 'monthly', ?string $startDate = null, ?string $endDate = null)
    {
        $dateRange = $this->getDateRange($period, $startDate, $endDate);
        
        $salesData = [
            'summary' => $this->getSalesSummary($dateRange['start'], $dateRange['end']),
            'trends' => $this->getSalesTrends($period, $dateRange['start'], $dateRange['end']),
            'top_products' => $this->getTopProducts($dateRange['start'], $dateRange['end']),
            'payment_methods' => $this->getPaymentMethodDistribution($dateRange['start'], $dateRange['end']),
            'sales_growth' => $this->getSalesGrowth($dateRange['start'], $dateRange['end']),
            'best_performing_periods' => $this->getBestPerformingPeriods($dateRange['start'], $dateRange['end']),
            'customer_metrics' => $this->getCustomerMetrics($dateRange['start'], $dateRange['end']),
            'category_analysis' => $this->getCategoryAnalysis($dateRange['start'], $dateRange['end']),
            'ai_analysis' => null
        ];

        // Generate AI analysis
        $salesData['ai_analysis'] = $this->generateAIAnalysis($salesData);

        return $salesData;
    }

    /**
     * Get basic sales summary
     */
    protected function getSalesSummary($startDate, $endDate)
    {
        $summary = Order::whereBetween('created_at', [$startDate, $endDate])
            ->select([
                DB::raw('COUNT(*) as total_orders'),
                DB::raw('COALESCE(SUM(total), 0) as total_sales'),
                DB::raw('COALESCE(AVG(total), 0) as average_order_value'),
                DB::raw('COALESCE(MIN(total), 0) as smallest_order'),
                DB::raw('COALESCE(MAX(total), 0) as largest_order'),
                DB::raw('COUNT(DISTINCT user_id) as unique_customers')
            ])
            ->first();

        return [
            'total_orders' => $summary->total_orders ?? 0,
            'total_sales' => number_format($summary->total_sales ?? 0, 2),
            'average_order_value' => number_format($summary->average_order_value ?? 0, 2),
            'smallest_order' => number_format($summary->smallest_order ?? 0, 2),
            'largest_order' => number_format($summary->largest_order ?? 0, 2),
            'unique_customers' => $summary->unique_customers ?? 0
        ];
    }

    /**
     * Get sales trends
     */
    protected function getSalesTrends($period, $startDate, $endDate)
    {
        $format = $this->getDateFormat($period);
        
        $trends = Order::whereBetween('created_at', [$startDate, $endDate])
            ->select([
                DB::raw("DATE_FORMAT(created_at, '$format') as period"),
                DB::raw('COUNT(*) as order_count'),
                DB::raw('COALESCE(SUM(total), 0) as total_sales')
            ])
            ->groupBy('period')
            ->orderBy('period')
            ->get();

        // If no data, return empty array
        if ($trends->isEmpty()) {
            return [];
        }

        return $trends->toArray();
    }

    /**
     * Get top selling products
     */
    protected function getTopProducts($startDate, $endDate)
    {
        $products = OrderItem::join('orders', 'order_items.order_id', '=', 'orders.id')
            ->whereBetween('orders.created_at', [$startDate, $endDate])
            ->select([
                'order_items.product_id',
                'order_items.item_name',
                DB::raw('SUM(order_items.quantity) as total_quantity'),
                DB::raw('COALESCE(SUM(order_items.quantity * order_items.price), 0) as total_revenue')
            ])
            ->groupBy('order_items.product_id', 'order_items.item_name')
            ->orderByDesc('total_revenue')
            ->limit(5)
            ->get();

        // If no data, return empty array
        if ($products->isEmpty()) {
            return [];
        }

        return $products->toArray();
    }

    /**
     * Get payment method distribution
     */
    protected function getPaymentMethodDistribution($startDate, $endDate)
    {
        $methods = Order::whereBetween('created_at', [$startDate, $endDate])
            ->select([
                'payment_method',
                DB::raw('COUNT(*) as count'),
                DB::raw('COALESCE(SUM(total), 0) as total_amount')
            ])
            ->groupBy('payment_method')
            ->get();

        // If no data, return empty array
        if ($methods->isEmpty()) {
            return [];
        }

        return $methods->toArray();
    }

    /**
     * Get sales growth rate
     */
    protected function getSalesGrowth($startDate, $endDate)
    {
        $currentPeriod = Order::whereBetween('created_at', [$startDate, $endDate])
            ->select(DB::raw('COALESCE(SUM(total), 0) as total_sales'))
            ->first();

        $previousStartDate = Carbon::parse($startDate)->subDays(Carbon::parse($startDate)->diffInDays($endDate));
        $previousEndDate = Carbon::parse($startDate)->subDay();

        $previousPeriod = Order::whereBetween('created_at', [$previousStartDate, $previousEndDate])
            ->select(DB::raw('COALESCE(SUM(total), 0) as total_sales'))
            ->first();

        $currentSales = $currentPeriod->total_sales ?? 0;
        $previousSales = $previousPeriod->total_sales ?? 0;

        $growthRate = $previousSales > 0 
            ? (($currentSales - $previousSales) / $previousSales) * 100 
            : 0;

        return [
            'current_period_sales' => number_format($currentSales, 2),
            'previous_period_sales' => number_format($previousSales, 2),
            'growth_rate' => number_format($growthRate, 2) . '%',
            'is_growing' => $growthRate > 0
        ];
    }

    /**
     * Get best performing periods
     */
    protected function getBestPerformingPeriods($startDate, $endDate)
    {
        $bestDays = Order::whereBetween('created_at', [$startDate, $endDate])
            ->select([
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as order_count'),
                DB::raw('COALESCE(SUM(total), 0) as total_sales')
            ])
            ->groupBy('date')
            ->orderByDesc('total_sales')
            ->limit(3)
            ->get();

        $bestHours = Order::whereBetween('created_at', [$startDate, $endDate])
            ->select([
                DB::raw('HOUR(created_at) as hour'),
                DB::raw('COUNT(*) as order_count'),
                DB::raw('COALESCE(SUM(total), 0) as total_sales')
            ])
            ->groupBy('hour')
            ->orderByDesc('total_sales')
            ->limit(3)
            ->get();

        return [
            'best_days' => $bestDays->isEmpty() ? [] : $bestDays->toArray(),
            'best_hours' => $bestHours->isEmpty() ? [] : $bestHours->toArray()
        ];
    }

    /**
     * Get customer metrics
     */
    protected function getCustomerMetrics($startDate, $endDate)
    {
        $metrics = Order::whereBetween('created_at', [$startDate, $endDate])
            ->select([
                DB::raw('COUNT(DISTINCT user_id) as total_customers'),
                DB::raw('COUNT(*) / NULLIF(COUNT(DISTINCT user_id), 0) as average_orders_per_customer'),
                DB::raw('COALESCE(AVG(total), 0) as average_customer_spend')
            ])
            ->first();

        return [
            'total_customers' => $metrics->total_customers ?? 0,
            'average_orders_per_customer' => number_format($metrics->average_orders_per_customer ?? 0, 2),
            'average_customer_spend' => number_format($metrics->average_customer_spend ?? 0, 2)
        ];
    }

    /**
     * Get category analysis
     */
    protected function getCategoryAnalysis($startDate, $endDate)
    {
        $categories = OrderItem::join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->whereBetween('orders.created_at', [$startDate, $endDate])
            ->select([
                'products.category',
                DB::raw('COUNT(*) as total_items'),
                DB::raw('COALESCE(SUM(order_items.quantity), 0) as total_quantity'),
                DB::raw('COALESCE(SUM(order_items.quantity * order_items.price), 0) as total_revenue')
            ])
            ->groupBy('products.category')
            ->orderByDesc('total_revenue')
            ->get();

        return $categories->isEmpty() ? [] : $categories->toArray();
    }

    /**
     * Generate AI analysis of sales data
     */
    protected function generateAIAnalysis(array $salesData)
    {
        $hasData = $salesData['summary']['total_orders'] > 0;

        if (!$hasData) {
            return "No sales data available for the selected period. This could be due to:\n" .
                   "1. No orders have been placed yet\n" .
                   "2. The selected date range doesn't contain any orders\n" .
                   "3. The system is newly set up\n\n" .
                   "Recommendations:\n" .
                   "1. Verify the date range selection\n" .
                   "2. Check if orders are being properly recorded\n" .
                   "3. Consider implementing marketing strategies to drive sales";
        }

        $prompt = "Analyze the following sales data and provide insights:\n" . 
                 "Total Orders: {$salesData['summary']['total_orders']}\n" .
                 "Total Sales: $" . $salesData['summary']['total_sales'] . "\n" .
                 "Average Order Value: $" . $salesData['summary']['average_order_value'] . "\n" .
                 "Unique Customers: {$salesData['summary']['unique_customers']}\n" .
                 "Sales Growth: {$salesData['sales_growth']['growth_rate']}\n" .
                 "Best Performing Days:\n" . json_encode($salesData['best_performing_periods']['best_days'], JSON_PRETTY_PRINT) . "\n" .
                 "Customer Metrics:\n" . json_encode($salesData['customer_metrics'], JSON_PRETTY_PRINT) . "\n" .
                 "Category Analysis:\n" . json_encode($salesData['category_analysis'], JSON_PRETTY_PRINT) . "\n" .
                 "Top Products:\n" . json_encode($salesData['top_products'], JSON_PRETTY_PRINT) . "\n" .
                 "Payment Methods:\n" . json_encode($salesData['payment_methods'], JSON_PRETTY_PRINT);

        return $this->openAIService->generateCompletion($prompt);
    }

    /**
     * Get date range based on period
     */
    protected function getDateRange($period, $startDate, $endDate)
    {
        $end = $endDate ? Carbon::parse($endDate) : Carbon::now();
        $start = $startDate ? Carbon::parse($startDate) : $this->getDefaultStartDate($period, $end);

        return [
            'start' => $start,
            'end' => $end
        ];
    }

    /**
     * Get default start date based on period
     */
    protected function getDefaultStartDate($period, $endDate)
    {
        return match($period) {
            'daily' => $endDate->copy()->subDay(),
            'weekly' => $endDate->copy()->subWeek(),
            'monthly' => $endDate->copy()->subMonth(),
            'yearly' => $endDate->copy()->subYear(),
            default => $endDate->copy()->subMonth(),
        };
    }

    /**
     * Get date format for grouping
     */
    protected function getDateFormat($period)
    {
        return match($period) {
            'daily' => '%Y-%m-%d',
            'weekly' => '%Y-%u',
            'monthly' => '%Y-%m',
            'yearly' => '%Y',
            default => '%Y-%m',
        };
    }
} 