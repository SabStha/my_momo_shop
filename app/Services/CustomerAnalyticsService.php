<?php

namespace App\Services;

use App\Models\User;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Customer;
use App\Models\CustomerSegment;
use App\Models\CustomerFeedback;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class CustomerAnalyticsService
{
    protected $openAIService;

    public function __construct(OpenAIService $openAIService)
    {
        $this->openAIService = $openAIService;
    }

    /**
     * Get comprehensive customer analytics
     *
     * @param string|null $startDate
     * @param string|null $endDate
     * @param int|null $branchId
     * @return array
     */
    public function getCustomerAnalytics(?string $startDate = null, ?string $endDate = null, ?int $branchId = null)
    {
        $dateRange = $this->getDateRange($startDate, $endDate);

        return [
            'behavior_metrics' => [
                'total_customers' => $this->getTotalCustomers($branchId),
                'active_customers_30d' => $this->getActiveCustomers($dateRange['start'], $dateRange['end'], $branchId),
                'average_order_value' => $this->getAverageOrderValue($dateRange['start'], $dateRange['end'], $branchId),
                'retention_rate_30d' => $this->getRetentionRate($dateRange['start'], $dateRange['end'], $branchId),
                'repeat_purchase_rate' => $this->getRepeatPurchaseRate($dateRange['start'], $dateRange['end'], $branchId),
                'average_purchase_frequency' => $this->getAveragePurchaseFrequency($dateRange['start'], $dateRange['end'], $branchId),
                'top_categories' => $this->getTopCategories($dateRange['start'], $dateRange['end'], $branchId),
                'peak_hours' => $this->getPeakHours($dateRange['start'], $dateRange['end'], $branchId),
                'average_basket_size' => $this->getAverageBasketSize($dateRange['start'], $dateRange['end'], $branchId),
                'customer_satisfaction' => $this->getCustomerSatisfaction($dateRange['start'], $dateRange['end'], $branchId)
            ],
            'advanced_metrics' => [
                'clv' => $this->getCustomerLifetimeValue($dateRange['start'], $dateRange['end'], $branchId),
                'purchase_frequency' => $this->getPurchaseFrequency($dateRange['start'], $dateRange['end'], $branchId),
                'customer_lifespan' => $this->getCustomerLifespan($dateRange['start'], $dateRange['end'], $branchId)
            ],
            'journey_map' => [
                'new' => $this->getNewCustomers($dateRange['start'], $dateRange['end'], $branchId),
                'regular' => $this->getRegularCustomers($dateRange['start'], $dateRange['end'], $branchId),
                'loyal' => $this->getLoyalCustomers($dateRange['start'], $dateRange['end'], $branchId),
                'vip' => $this->getVIPCustomers($dateRange['start'], $dateRange['end'], $branchId),
                'churned' => $this->getChurnedCustomers($dateRange['start'], $dateRange['end'], $branchId),
                'conversion_rates' => [
                    'new_to_regular' => $this->getNewToRegularRate($dateRange['start'], $dateRange['end'], $branchId),
                    'regular_to_loyal' => $this->getRegularToLoyalRate($dateRange['start'], $dateRange['end'], $branchId),
                    'loyal_to_vip' => $this->getLoyalToVIPRate($dateRange['start'], $dateRange['end'], $branchId)
                ]
            ],
            'segments' => [
                'vip' => $this->getVIPCustomers($dateRange['start'], $dateRange['end'], $branchId),
                'loyal' => $this->getLoyalCustomers($dateRange['start'], $dateRange['end'], $branchId),
                'regular' => $this->getRegularCustomers($dateRange['start'], $dateRange['end'], $branchId),
                'at_risk' => $this->getAtRiskCustomers($dateRange['start'], $dateRange['end'], $branchId),
                'inactive' => $this->getChurnedCustomers($dateRange['start'], $dateRange['end'], $branchId)
            ],
            'churn_risk' => [
                'high_risk' => $this->getHighRiskCustomers($dateRange['start'], $dateRange['end'], $branchId),
                'medium_risk' => $this->getMediumRiskCustomers($dateRange['start'], $dateRange['end'], $branchId),
                'low_risk' => $this->getLowRiskCustomers($dateRange['start'], $dateRange['end'], $branchId)
            ],
            'ai_suggestions' => $this->getAISuggestions($dateRange['start'], $dateRange['end'], $branchId)
        ];
    }

    /**
     * Get customer segments based on purchase behavior
     */
    protected function getCustomerSegments($startDate, $endDate)
    {
        $customers = Order::whereBetween('created_at', [$startDate, $endDate])
            ->select([
                'user_id',
                DB::raw('COUNT(*) as total_orders'),
                DB::raw('COALESCE(SUM(total), 0) as total_spent'),
                DB::raw('MAX(created_at) as last_order_date'),
                DB::raw('MIN(created_at) as first_order_date')
            ])
            ->groupBy('user_id')
            ->get();

        $segments = [
            'vip' => [],
            'loyal' => [],
            'regular' => [],
            'at_risk' => [],
            'inactive' => []
        ];

        foreach ($customers as $customer) {
            $recency = Carbon::parse($customer->last_order_date)->diffInDays(now());
            $frequency = $customer->total_orders;
            $monetary = $customer->total_spent;

            // Segment customers based on RFM (Recency, Frequency, Monetary) analysis
            if ($monetary >= 1000 && $frequency >= 5 && $recency <= 30) {
                $segments['vip'][] = $this->formatCustomerData($customer);
            } elseif ($monetary >= 500 && $frequency >= 3 && $recency <= 60) {
                $segments['loyal'][] = $this->formatCustomerData($customer);
            } elseif ($monetary >= 100 && $frequency >= 2 && $recency <= 90) {
                $segments['regular'][] = $this->formatCustomerData($customer);
            } elseif ($recency > 90 && $recency <= 180) {
                $segments['at_risk'][] = $this->formatCustomerData($customer);
            } else {
                $segments['inactive'][] = $this->formatCustomerData($customer);
            }
        }

        return $segments;
    }

    /**
     * Calculate customer lifetime value
     */
    protected function getCustomerLifetimeValues($startDate, $endDate)
    {
        $customers = Order::whereBetween('created_at', [$startDate, $endDate])
            ->select([
                'user_id',
                DB::raw('COUNT(*) as total_orders'),
                DB::raw('COALESCE(SUM(total), 0) as total_spent'),
                DB::raw('MAX(created_at) as last_order_date'),
                DB::raw('MIN(created_at) as first_order_date')
            ])
            ->groupBy('user_id')
            ->get();

        $lifetimeValues = [];

        foreach ($customers as $customer) {
            $customerAge = Carbon::parse($customer->first_order_date)->diffInDays(now());
            $averageOrderValue = $customer->total_orders > 0 ? $customer->total_spent / $customer->total_orders : 0;
            $purchaseFrequency = $customerAge > 0 ? $customer->total_orders / ($customerAge / 30) : 0; // Orders per month
            $customerLifetime = 12; // Assuming average customer lifetime of 12 months

            $lifetimeValue = $averageOrderValue * $purchaseFrequency * $customerLifetime;

            $lifetimeValues[] = [
                'user_id' => $customer->user_id,
                'total_spent' => number_format($customer->total_spent, 2),
                'average_order_value' => number_format($averageOrderValue, 2),
                'purchase_frequency' => number_format($purchaseFrequency, 2),
                'lifetime_value' => number_format($lifetimeValue, 2),
                'customer_age_days' => $customerAge
            ];
        }

        return $lifetimeValues;
    }

    /**
     * Analyze customer churn risk
     */
    protected function getChurnRiskAnalysis($startDate, $endDate)
    {
        $customers = Order::whereBetween('created_at', [$startDate, $endDate])
            ->select([
                'user_id',
                DB::raw('COUNT(*) as total_orders'),
                DB::raw('COALESCE(SUM(total), 0) as total_spent'),
                DB::raw('MAX(created_at) as last_order_date'),
                DB::raw('MIN(created_at) as first_order_date')
            ])
            ->groupBy('user_id')
            ->get();

        $churnRisks = [];

        foreach ($customers as $customer) {
            $recency = Carbon::parse($customer->last_order_date)->diffInDays(now());
            $frequency = $customer->total_orders;
            $monetary = $customer->total_spent;
            $customerAge = Carbon::parse($customer->first_order_date)->diffInDays(now());

            // Calculate churn risk score (0-100)
            $recencyScore = max(0, 100 - ($recency * 2)); // Higher recency = lower score
            $frequencyScore = min(100, $frequency * 20); // Higher frequency = higher score
            $monetaryScore = min(100, ($monetary / 1000) * 100); // Higher monetary = higher score
            $loyaltyScore = min(100, ($customerAge / 365) * 100); // Higher age = higher score

            $churnRiskScore = ($recencyScore * 0.4) + ($frequencyScore * 0.3) + 
                            ($monetaryScore * 0.2) + ($loyaltyScore * 0.1);

            $churnRisks[] = [
                'user_id' => $customer->user_id,
                'churn_risk_score' => number_format($churnRiskScore, 2),
                'risk_level' => $this->getRiskLevel($churnRiskScore),
                'factors' => [
                    'recency_score' => number_format($recencyScore, 2),
                    'frequency_score' => number_format($frequencyScore, 2),
                    'monetary_score' => number_format($monetaryScore, 2),
                    'loyalty_score' => number_format($loyaltyScore, 2)
                ]
            ];
        }

        return $churnRisks;
    }

    /**
     * Get customer behavior metrics
     */
    protected function getCustomerBehaviorMetrics($startDate, $endDate)
    {
        $metrics = Order::whereBetween('created_at', [$startDate, $endDate])
            ->select([
                DB::raw('COUNT(DISTINCT user_id) as total_customers'),
                DB::raw('COUNT(*) / NULLIF(COUNT(DISTINCT user_id), 0) as average_orders_per_customer'),
                DB::raw('COALESCE(AVG(total), 0) as average_order_value'),
                DB::raw('COUNT(DISTINCT CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) THEN user_id END) as active_customers_30d'),
                DB::raw('COUNT(DISTINCT CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 90 DAY) THEN user_id END) as active_customers_90d')
            ])
            ->first();

        return [
            'total_customers' => $metrics->total_customers ?? 0,
            'average_orders_per_customer' => number_format($metrics->average_orders_per_customer ?? 0, 2),
            'average_order_value' => number_format($metrics->average_order_value ?? 0, 2),
            'active_customers_30d' => $metrics->active_customers_30d ?? 0,
            'active_customers_90d' => $metrics->active_customers_90d ?? 0,
            'retention_rate_30d' => $this->calculateRetentionRate($startDate, $endDate, 30),
            'retention_rate_90d' => $this->calculateRetentionRate($startDate, $endDate, 90)
        ];
    }

    /**
     * Calculate customer retention rate
     */
    protected function calculateRetentionRate($startDate, $endDate, $days)
    {
        $totalCustomers = Order::whereBetween('created_at', [$startDate, $endDate])
            ->distinct('user_id')
            ->count('user_id');

        if ($totalCustomers === 0) {
            return 0;
        }

        $retainedCustomers = Order::whereBetween('created_at', [$startDate, $endDate])
            ->where('created_at', '>=', now()->subDays($days))
            ->distinct('user_id')
            ->count('user_id');

        return number_format(($retainedCustomers / $totalCustomers) * 100, 2);
    }

    /**
     * Get risk level based on churn risk score
     */
    protected function getRiskLevel($score)
    {
        if ($score >= 80) return 'Low Risk';
        if ($score >= 60) return 'Medium Risk';
        if ($score >= 40) return 'High Risk';
        return 'Very High Risk';
    }

    /**
     * Format customer data for segments
     */
    protected function formatCustomerData($customer)
    {
        return [
            'user_id' => $customer->user_id,
            'total_orders' => $customer->total_orders,
            'total_spent' => number_format($customer->total_spent, 2),
            'last_order_date' => $customer->last_order_date,
            'first_order_date' => $customer->first_order_date,
            'days_since_last_order' => Carbon::parse($customer->last_order_date)->diffInDays(now())
        ];
    }

    /**
     * Get date range for analysis
     */
    public function getDateRange(?string $startDate, ?string $endDate)
    {
        $now = now();
        $end = $endDate ? Carbon::parse($endDate) : $now;
        $start = $startDate ? Carbon::parse($startDate) : $now->copy()->subDays(30);

        // Ensure we're not using future dates
        if ($end->isFuture()) {
            $end = $now;
        }
        if ($start->isFuture()) {
            $start = $end->copy()->subDays(30);
        }
        if ($start->gt($end)) {
            $start = $end->copy()->subDays(30);
        }

        return [
            'start' => $start->format('Y-m-d'),
            'end' => $end->format('Y-m-d')
        ];
    }

    public function getTotalCustomers($branchId)
    {
        return DB::table('orders')
            ->where('branch_id', $branchId)
            ->whereNull('deleted_at')
            ->select('user_id')
            ->distinct()
            ->count();
    }

    public function getActiveCustomers($startDate, $endDate, $branchId)
    {
        return DB::table('orders')
            ->where('branch_id', $branchId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->whereNull('deleted_at')
            ->select('user_id')
            ->distinct()
            ->count();
    }

    public function getAverageOrderValue($startDate, $endDate, $branchId)
    {
        return DB::table('orders')
            ->where('branch_id', $branchId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->whereNull('deleted_at')
            ->avg('total_amount') ?? 0;
    }

    public function getRetentionRate($startDate, $endDate, $branchId)
    {
        $totalCustomers = $this->getTotalCustomers($branchId);
        if ($totalCustomers === 0) {
            return 0;
        }

        $retainedCustomers = DB::table('orders')
            ->where('branch_id', $branchId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->whereNull('deleted_at')
            ->select('user_id')
            ->distinct()
            ->count();

        return ($retainedCustomers / $totalCustomers) * 100;
    }

    public function getRepeatPurchaseRate($startDate, $endDate, $branchId)
    {
        $totalCustomers = DB::table('orders')
            ->where('branch_id', $branchId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->whereNull('deleted_at')
            ->select('user_id')
            ->distinct()
            ->count();

        if ($totalCustomers === 0) {
            return 0;
        }

        $repeatCustomers = DB::table('orders')
            ->where('branch_id', $branchId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->whereNull('deleted_at')
            ->select('user_id')
            ->groupBy('user_id')
            ->havingRaw('COUNT(*) > 1')
            ->count();

        return ($repeatCustomers / $totalCustomers) * 100;
    }

    public function getPurchaseFrequency($startDate, $endDate, $branchId)
    {
        $orderCounts = DB::table('orders')
            ->where('branch_id', $branchId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->whereNull('deleted_at')
            ->select('user_id', DB::raw('COUNT(*) as count'))
            ->groupBy('user_id')
            ->get()
            ->pluck('count');

        if ($orderCounts->isEmpty()) {
            return 0;
        }

        return $orderCounts->avg() ?? 0;
    }

    public function getCustomerLifespan($startDate, $endDate, $branchId)
    {
        $lifespans = DB::table('orders')
            ->where('branch_id', $branchId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->whereNull('deleted_at')
            ->select('user_id', 
                DB::raw('DATEDIFF(MAX(created_at), MIN(created_at)) as lifespan'))
            ->groupBy('user_id')
            ->get()
            ->pluck('lifespan');

        if ($lifespans->isEmpty()) {
            return 0;
        }

        return $lifespans->avg() ?? 0;
    }

    public function getCustomerLifetimeValue($startDate, $endDate, $branchId)
    {
        $avgOrderValue = DB::table('orders')
            ->where('branch_id', $branchId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->whereNull('deleted_at')
            ->select('user_id', DB::raw('AVG(total_amount) as avg_value'))
            ->groupBy('user_id')
            ->get()
            ->avg('avg_value') ?? 0;

        $purchaseFrequency = $this->getPurchaseFrequency($startDate, $endDate, $branchId);
        $customerLifespan = $this->getCustomerLifespan($startDate, $endDate, $branchId);

        return $avgOrderValue * $purchaseFrequency * $customerLifespan;
    }

    public function getAveragePurchaseFrequency($startDate, $endDate, $branchId)
    {
        $orderCounts = DB::table('orders')
            ->where('branch_id', $branchId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->whereNull('deleted_at')
            ->select('user_id', DB::raw('COUNT(*) as count'))
            ->groupBy('user_id')
            ->get()
            ->pluck('count');

        if ($orderCounts->isEmpty()) {
            return 0;
        }

        return $orderCounts->avg() ?? 0;
    }

    public function getTopCategories($startDate, $endDate, $branchId)
    {
        return DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->where('orders.branch_id', $branchId)
            ->whereBetween('orders.created_at', [$startDate, $endDate])
            ->whereNull('orders.deleted_at')
            ->select('products.category', DB::raw('COUNT(*) as count'))
            ->groupBy('products.category')
            ->orderBy('count', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($item) {
                return [
                    'category' => $item->category,
                    'count' => $item->count
                ];
            });
    }

    public function getPeakHours($startDate, $endDate, $branchId)
    {
        return DB::table('orders')
            ->where('branch_id', $branchId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->whereNull('deleted_at')
            ->select(DB::raw('HOUR(created_at) as hour'), DB::raw('COUNT(*) as count'))
            ->groupBy('hour')
            ->orderBy('count', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($item) {
                return [
                    'hour' => $item->hour,
                    'count' => $item->count
                ];
            });
    }

    public function getAverageBasketSize($startDate, $endDate, $branchId)
    {
        $basketSizes = DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->where('orders.branch_id', $branchId)
            ->whereBetween('orders.created_at', [$startDate, $endDate])
            ->whereNull('orders.deleted_at')
            ->select('orders.id', DB::raw('SUM(quantity) as items_count'))
            ->groupBy('orders.id')
            ->get()
            ->pluck('items_count');

        if ($basketSizes->isEmpty()) {
            return 0;
        }

        return $basketSizes->avg() ?? 0;
    }

    public function getCustomerSatisfaction($startDate, $endDate, $branchId)
    {
        return DB::table('customer_feedback')
            ->join('orders', 'customer_feedback.order_id', '=', 'orders.id')
            ->where('orders.branch_id', $branchId)
            ->whereBetween('customer_feedback.created_at', [$startDate, $endDate])
            ->whereNull('orders.deleted_at')
            ->select(DB::raw('AVG(rating) as avg_rating'))
            ->first()
            ->avg_rating ?? 0;
    }

    public function getHighRiskCustomers($startDate, $endDate, $branchId)
    {
        return DB::table('orders')
            ->where('branch_id', $branchId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->whereNull('deleted_at')
            ->select('user_id')
            ->groupBy('user_id')
            ->havingRaw('MAX(created_at) < DATE_SUB(NOW(), INTERVAL 90 DAY)')
            ->count();
    }

    public function getMediumRiskCustomers($startDate, $endDate, $branchId)
    {
        return DB::table('orders')
            ->where('branch_id', $branchId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->whereNull('deleted_at')
            ->select('user_id')
            ->groupBy('user_id')
            ->havingRaw('MAX(created_at) < DATE_SUB(NOW(), INTERVAL 60 DAY)')
            ->havingRaw('MAX(created_at) >= DATE_SUB(NOW(), INTERVAL 90 DAY)')
            ->count();
    }

    public function getLowRiskCustomers($startDate, $endDate, $branchId)
    {
        return DB::table('orders')
            ->where('branch_id', $branchId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->whereNull('deleted_at')
            ->select('user_id')
            ->groupBy('user_id')
            ->havingRaw('MAX(created_at) >= DATE_SUB(NOW(), INTERVAL 60 DAY)')
            ->count();
    }

    public function getSafeCustomers($startDate, $endDate, $branchId)
    {
        return DB::table('orders')
            ->where('branch_id', $branchId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->whereNull('deleted_at')
            ->select('user_id')
            ->groupBy('user_id')
            ->havingRaw('MAX(created_at) >= DATE_SUB(NOW(), INTERVAL 30 DAY)')
            ->count();
    }

    public function getNewCustomers($startDate, $endDate, $branchId)
    {
        return Order::where('branch_id', $branchId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->select('customer_id')
            ->groupBy('customer_id')
            ->havingRaw('COUNT(*) = 1')
            ->count();
    }

    public function getRegularCustomers($startDate, $endDate, $branchId)
    {
        return Order::where('branch_id', $branchId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->select('customer_id')
            ->groupBy('customer_id')
            ->havingRaw('COUNT(*) >= ?', [2])
            ->count();
    }

    public function getLoyalCustomers($startDate, $endDate, $branchId)
    {
        return Order::where('branch_id', $branchId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->select('customer_id')
            ->groupBy('customer_id')
            ->havingRaw('COUNT(*) >= ?', [5])
            ->count();
    }

    public function getVIPCustomers($startDate, $endDate, $branchId)
    {
        return Order::where('branch_id', $branchId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->select('customer_id', DB::raw('SUM(total) as total_spent'))
            ->groupBy('customer_id')
            ->havingRaw('SUM(total) >= ?', [1000])
            ->count();
    }

    public function getChurnedCustomers($startDate, $endDate, $branchId)
    {
        $inactiveThreshold = Carbon::now()->subMonths(3);
        
        return Customer::where('branch_id', $branchId)
            ->whereDoesntHave('orders', function($query) use ($inactiveThreshold) {
                $query->where('created_at', '>=', $inactiveThreshold);
            })
            ->count();
    }

    public function getNewToRegularRate($startDate, $endDate, $branchId)
    {
        $newCustomers = $this->getNewCustomers($startDate, $endDate, $branchId);
        if ($newCustomers === 0) return 0;

        $convertedCustomers = Order::where('branch_id', $branchId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('customer_id')
            ->having(DB::raw('COUNT(*)'), '>=', 2)
            ->count();

        return round(($convertedCustomers / $newCustomers) * 100, 2);
    }

    public function getRegularToLoyalRate($startDate, $endDate, $branchId)
    {
        $regularCustomers = $this->getRegularCustomers($startDate, $endDate, $branchId);
        if ($regularCustomers === 0) return 0;

        $loyalCustomers = $this->getLoyalCustomers($startDate, $endDate, $branchId);
        return round(($loyalCustomers / $regularCustomers) * 100, 2);
    }

    public function getLoyalToVIPRate($startDate, $endDate, $branchId)
    {
        $loyalCustomers = $this->getLoyalCustomers($startDate, $endDate, $branchId);
        if ($loyalCustomers === 0) return 0;

        $vipCustomers = $this->getVIPCustomers($startDate, $endDate, $branchId);
        return round(($vipCustomers / $loyalCustomers) * 100, 2);
    }

    public function getJourneyStages($startDate, $endDate, $branchId)
    {
        return [
            'new' => $this->getNewCustomers($startDate, $endDate, $branchId),
            'regular' => $this->getRegularCustomers($startDate, $endDate, $branchId),
            'loyal' => $this->getLoyalCustomers($startDate, $endDate, $branchId),
            'vip' => $this->getVIPCustomers($startDate, $endDate, $branchId),
            'churned' => $this->getChurnedCustomers($startDate, $endDate, $branchId)
        ];
    }

    public function getTouchpoints($startDate, $endDate, $branchId)
    {
        // Implementation for customer touchpoints
        return []; // Placeholder
    }

    public function getBottlenecks($startDate, $endDate, $branchId)
    {
        // Implementation for identifying bottlenecks
        return []; // Placeholder
    }

    public function getOpportunities($startDate, $endDate, $branchId)
    {
        // Implementation for identifying opportunities
        return []; // Placeholder
    }

    public function getAISuggestions($startDate, $endDate, $branchId)
    {
        // Implementation for AI-powered suggestions
        return []; // Placeholder
    }

    public function getSegmentSuggestions($startDate, $endDate, $branchId)
    {
        // Implementation for segment suggestions
        return []; // Placeholder
    }

    public function generateRetentionCampaign($customerId)
    {
        // Implementation for retention campaign generation
        return [
            'customer' => [
                'id' => $customerId,
                'last_order' => 'N/A',
                'total_spent' => 0
            ],
            'campaign' => [
                'suggestion' => 'No campaign suggestions available',
                'coupon_code' => 'N/A',
                'discount' => '0%',
                'message_draft' => 'No message draft available',
                'optimal_time' => 'N/A'
            ]
        ];
    }

    public function getAtRiskCustomers($startDate, $endDate, $branchId)
    {
        $ninetyDaysAgo = now()->subDays(90)->format('Y-m-d');
        
        return Order::where('branch_id', $branchId)
            ->where('created_at', '<', $ninetyDaysAgo)
            ->select('customer_id')
            ->groupBy('customer_id')
            ->havingRaw('MAX(created_at) < ?', [$ninetyDaysAgo])
            ->count();
    }

    public function getChurnRate($startDate, $endDate, $branchId)
    {
        // Get total customers
        $totalCustomers = DB::table('orders')
            ->where('branch_id', $branchId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->whereNull('deleted_at')
            ->distinct('user_id')
            ->count('user_id');

        if ($totalCustomers === 0) {
            return 0;
        }

        // Get churned customers (no orders in last 30 days)
        $churnedCustomers = DB::table('orders')
            ->where('branch_id', $branchId)
            ->where('created_at', '<', now()->subDays(30))
            ->whereNull('deleted_at')
            ->whereNotExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('orders as o2')
                    ->whereRaw('o2.user_id = orders.user_id')
                    ->where('o2.created_at', '>=', now()->subDays(30));
            })
            ->distinct('user_id')
            ->count('user_id');

        return ($churnedCustomers / $totalCustomers) * 100;
    }

    public function getLoyaltyScore($startDate, $endDate, $branchId)
    {
        $scores = DB::table('orders')
            ->where('branch_id', $branchId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->whereNull('deleted_at')
            ->select('user_id', 
                DB::raw('COUNT(*) as order_count'),
                DB::raw('AVG(total_amount) as avg_order_value'),
                DB::raw('DATEDIFF(MAX(created_at), MIN(created_at)) as customer_tenure'))
            ->groupBy('user_id')
            ->get()
            ->map(function ($item) {
                // Calculate loyalty score based on multiple factors
                $orderScore = min($item->order_count * 10, 40); // Up to 40 points for order frequency
                $valueScore = min($item->avg_order_value / 100, 30); // Up to 30 points for order value
                $tenureScore = min($item->customer_tenure / 30, 30); // Up to 30 points for tenure
                
                return $orderScore + $valueScore + $tenureScore;
            });

        if ($scores->isEmpty()) {
            return 0;
        }

        return $scores->avg() ?? 0;
    }

    public function getEngagementScore($startDate, $endDate, $branchId)
    {
        $scores = DB::table('orders')
            ->where('branch_id', $branchId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->whereNull('deleted_at')
            ->select('user_id',
                DB::raw('COUNT(DISTINCT DATE(created_at)) as visit_days'),
                DB::raw('COUNT(*) as total_orders'),
                DB::raw('AVG(total_amount) as avg_order_value'))
            ->groupBy('user_id')
            ->get()
            ->map(function ($item) {
                // Calculate engagement score based on multiple factors
                $visitScore = min($item->visit_days * 10, 40); // Up to 40 points for visit frequency
                $orderScore = min($item->total_orders * 5, 30); // Up to 30 points for order frequency
                $valueScore = min($item->avg_order_value / 100, 30); // Up to 30 points for order value
                
                return $visitScore + $orderScore + $valueScore;
            });

        if ($scores->isEmpty()) {
            return 0;
        }

        return $scores->avg() ?? 0;
    }

    public function getValueScore($startDate, $endDate, $branchId)
    {
        $scores = DB::table('orders')
            ->where('branch_id', $branchId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->whereNull('deleted_at')
            ->select('user_id',
                DB::raw('SUM(total_amount) as total_spent'),
                DB::raw('AVG(total_amount) as avg_order_value'),
                DB::raw('COUNT(*) as order_count'))
            ->groupBy('user_id')
            ->get()
            ->map(function ($item) {
                // Calculate value score based on multiple factors
                $totalSpentScore = min($item->total_spent / 1000, 40); // Up to 40 points for total spent
                $avgValueScore = min($item->avg_order_value / 100, 30); // Up to 30 points for average order value
                $frequencyScore = min($item->order_count * 5, 30); // Up to 30 points for order frequency
                
                return $totalSpentScore + $avgValueScore + $frequencyScore;
            });

        if ($scores->isEmpty()) {
            return 0;
        }

        return $scores->avg() ?? 0;
    }

    public function getRiskScore($startDate, $endDate, $branchId)
    {
        $scores = DB::table('orders')
            ->where('branch_id', $branchId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->whereNull('deleted_at')
            ->select('user_id',
                DB::raw('DATEDIFF(MAX(created_at), MIN(created_at)) as customer_tenure'),
                DB::raw('COUNT(*) as order_count'),
                DB::raw('AVG(total_amount) as avg_order_value'))
            ->groupBy('user_id')
            ->get()
            ->map(function ($item) {
                // Calculate risk score based on multiple factors
                $tenureRisk = max(0, 100 - ($item->customer_tenure / 30) * 20); // Higher risk for newer customers
                $frequencyRisk = max(0, 100 - ($item->order_count * 10)); // Higher risk for fewer orders
                $valueRisk = max(0, 100 - ($item->avg_order_value / 100)); // Higher risk for lower order values
                
                return ($tenureRisk + $frequencyRisk + $valueRisk) / 3;
            });

        if ($scores->isEmpty()) {
            return 0;
        }

        return $scores->avg() ?? 0;
    }

    public function getSegmentDistribution($startDate, $endDate, $branchId)
    {
        $customers = DB::table('orders')
            ->where('branch_id', $branchId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->whereNull('deleted_at')
            ->select('user_id',
                DB::raw('COUNT(*) as order_count'),
                DB::raw('AVG(total_amount) as avg_order_value'),
                DB::raw('SUM(total_amount) as total_spent'))
            ->groupBy('user_id')
            ->get();

        $segments = [
            'vip' => 0,
            'loyal' => 0,
            'regular' => 0,
            'at_risk' => 0,
            'inactive' => 0
        ];

        foreach ($customers as $customer) {
            if ($customer->total_spent >= 1000 && $customer->order_count >= 5) {
                $segments['vip']++;
            } elseif ($customer->total_spent >= 500 && $customer->order_count >= 3) {
                $segments['loyal']++;
            } elseif ($customer->total_spent >= 100 && $customer->order_count >= 2) {
                $segments['regular']++;
            } elseif ($customer->total_spent < 100 || $customer->order_count < 2) {
                $segments['at_risk']++;
            } else {
                $segments['inactive']++;
            }
        }

        return $segments;
    }

    public function getTrendAnalysis($startDate, $endDate, $branchId)
    {
        // Get daily trends for key metrics
        $dailyTrends = DB::table('orders')
            ->where('branch_id', $branchId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->whereNull('deleted_at')
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(DISTINCT user_id) as new_customers'),
                DB::raw('COUNT(*) as total_orders'),
                DB::raw('AVG(total_amount) as avg_order_value'),
                DB::raw('SUM(total_amount) as total_revenue')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Calculate week-over-week growth
        $weeklyGrowth = [];
        $currentWeek = null;
        $previousWeek = null;

        foreach ($dailyTrends as $day) {
            $week = date('W', strtotime($day->date));
            
            if ($currentWeek !== $week) {
                if ($currentWeek !== null) {
                    $previousWeek = $currentWeek;
                }
                $currentWeek = $week;
            }

            if ($previousWeek !== null) {
                $weeklyGrowth[$week] = [
                    'customer_growth' => $this->calculateGrowth(
                        $this->getWeeklyMetric($dailyTrends, $currentWeek, 'new_customers'),
                        $this->getWeeklyMetric($dailyTrends, $previousWeek, 'new_customers')
                    ),
                    'order_growth' => $this->calculateGrowth(
                        $this->getWeeklyMetric($dailyTrends, $currentWeek, 'total_orders'),
                        $this->getWeeklyMetric($dailyTrends, $previousWeek, 'total_orders')
                    ),
                    'revenue_growth' => $this->calculateGrowth(
                        $this->getWeeklyMetric($dailyTrends, $currentWeek, 'total_revenue'),
                        $this->getWeeklyMetric($dailyTrends, $previousWeek, 'total_revenue')
                    )
                ];
            }
        }

        // Get peak hours trend
        $peakHoursTrend = DB::table('orders')
            ->where('branch_id', $branchId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->whereNull('deleted_at')
            ->select(
                DB::raw('HOUR(created_at) as hour'),
                DB::raw('COUNT(*) as order_count'),
                DB::raw('AVG(total_amount) as avg_order_value')
            )
            ->groupBy('hour')
            ->orderBy('hour')
            ->get();

        // Get category trends
        $categoryTrends = DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->where('orders.branch_id', $branchId)
            ->whereBetween('orders.created_at', [$startDate, $endDate])
            ->whereNull('orders.deleted_at')
            ->select(
                'products.category',
                DB::raw('COUNT(*) as order_count'),
                DB::raw('SUM(order_items.quantity) as total_quantity'),
                DB::raw('AVG(order_items.price) as avg_price')
            )
            ->groupBy('products.category')
            ->orderBy('order_count', 'desc')
            ->get();

        return [
            'daily_trends' => $dailyTrends,
            'weekly_growth' => $weeklyGrowth,
            'peak_hours_trend' => $peakHoursTrend,
            'category_trends' => $categoryTrends,
            'summary' => [
                'total_days' => $dailyTrends->count(),
                'avg_daily_orders' => $dailyTrends->avg('total_orders'),
                'avg_daily_revenue' => $dailyTrends->avg('total_revenue'),
                'best_performing_day' => $dailyTrends->max('total_revenue'),
                'worst_performing_day' => $dailyTrends->min('total_revenue'),
                'most_active_hour' => $peakHoursTrend->max('order_count'),
                'top_category' => $categoryTrends->first()->category ?? null
            ]
        ];
    }

    private function calculateGrowth($current, $previous)
    {
        if ($previous == 0) {
            return $current > 0 ? 100 : 0;
        }
        return (($current - $previous) / $previous) * 100;
    }

    private function getWeeklyMetric($dailyTrends, $week, $metric)
    {
        return $dailyTrends
            ->filter(function ($day) use ($week) {
                return date('W', strtotime($day->date)) == $week;
            })
            ->sum($metric);
    }
} 