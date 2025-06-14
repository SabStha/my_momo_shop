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
        $cacheKey = "purchase_frequency:{$branchId}:{$startDate}:{$endDate}";
        
        return cache()->remember($cacheKey, now()->addHours(24), function () use ($startDate, $endDate, $branchId) {
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
        });
    }

    public function getCustomerLifespan($startDate, $endDate, $branchId)
    {
        $cacheKey = "customer_lifespan:{$branchId}:{$startDate}:{$endDate}";
        
        return cache()->remember($cacheKey, now()->addHours(24), function () use ($startDate, $endDate, $branchId) {
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
        });
    }

    public function getCustomerLifetimeValue($startDate, $endDate, $branchId)
    {
        $cacheKey = "clv:{$branchId}:{$startDate}:{$endDate}";
        
        return cache()->remember($cacheKey, now()->addHours(24), function () use ($startDate, $endDate, $branchId) {
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
        });
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
        // Get customer behavior data
        $customers = Order::whereBetween('created_at', [$startDate, $endDate])
            ->when($branchId, function ($query) use ($branchId) {
                return $query->where('branch_id', $branchId);
            })
            ->select([
                'user_id',
                DB::raw('COUNT(*) as total_orders'),
                DB::raw('COALESCE(SUM(total), 0) as total_spent'),
                DB::raw('MAX(created_at) as last_order_date'),
                DB::raw('MIN(created_at) as first_order_date')
            ])
            ->groupBy('user_id')
            ->get();

        // Get top categories for each customer
        $customerCategories = OrderItem::join('orders', 'orders.id', '=', 'order_items.order_id')
            ->join('products', 'products.id', '=', 'order_items.product_id')
            ->whereBetween('orders.created_at', [$startDate, $endDate])
            ->when($branchId, function ($query) use ($branchId) {
                return $query->where('orders.branch_id', $branchId);
            })
            ->select([
                'orders.user_id',
                'products.category',
                DB::raw('COUNT(*) as category_count')
            ])
            ->groupBy('orders.user_id', 'products.category')
            ->get()
            ->groupBy('user_id');

        $suggestions = [];

        // Analyze customer behavior patterns
        foreach ($customers as $customer) {
            $recency = Carbon::parse($customer->last_order_date)->diffInDays(now());
            $frequency = $customer->total_orders;
            $monetary = $customer->total_spent;
            $customerAge = Carbon::parse($customer->first_order_date)->diffInDays(now());

            // Get customer's top categories
            $topCategories = $customerCategories->get($customer->user_id, collect())
                ->sortByDesc('category_count')
                ->take(3)
                ->pluck('category')
                ->toArray();

            // Generate suggestions based on behavior patterns
            if ($monetary >= 50000 && $frequency >= 3 && $recency <= 30) {
                $suggestions[] = [
                    'id' => uniqid(),
                    'name' => 'High-Value Category Enthusiasts',
                    'description' => 'Customers who spend significantly in specific categories: ' . implode(', ', $topCategories),
                    'priority' => 'high',
                    'customer_count' => 1,
                    'potential_revenue' => 'Rs ' . number_format($monetary * 1.2, 2) // 20% potential growth
                ];
            }

            if ($recency > 60 && $recency <= 120 && $monetary >= 20000) {
                $suggestions[] = [
                    'id' => uniqid(),
                    'name' => 'At-Risk Regular Customers',
                    'description' => 'Previously regular customers showing signs of reduced engagement',
                    'priority' => 'high',
                    'customer_count' => 1,
                    'potential_revenue' => 'Rs ' . number_format($monetary * 0.8, 2) // Potential recovery
                ];
            }

            if ($frequency >= 2 && $customerAge <= 60) {
                $suggestions[] = [
                    'id' => uniqid(),
                    'name' => 'Rapid Adopters',
                    'description' => 'New customers showing high engagement in first 60 days',
                    'priority' => 'medium',
                    'customer_count' => 1,
                    'potential_revenue' => 'Rs ' . number_format($monetary * 1.5, 2) // High growth potential
                ];
            }

            if ($frequency >= 4 && $recency <= 15) {
                $suggestions[] = [
                    'id' => uniqid(),
                    'name' => 'Frequent Shoppers',
                    'description' => 'Customers who shop frequently and recently',
                    'priority' => 'medium',
                    'customer_count' => 1,
                    'potential_revenue' => 'Rs ' . number_format($monetary * 1.3, 2) // 30% growth potential
                ];
            }
        }

        // Group similar suggestions
        $groupedSuggestions = collect($suggestions)->groupBy('name')->map(function ($group) {
            $first = $group->first();
            return [
                'id' => $first['id'],
                'name' => $first['name'],
                'description' => $first['description'],
                'priority' => $first['priority'],
                'customer_count' => $group->count(),
                'potential_revenue' => 'Rs ' . number_format($group->sum(function ($item) {
                    return floatval(str_replace(['Rs ', ','], '', $item['potential_revenue']));
                }), 2)
            ];
        })->values()->toArray();

        return $groupedSuggestions;
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
        $cacheKey = "segment_distribution:{$branchId}:{$startDate}:{$endDate}";
        
        return cache()->remember($cacheKey, now()->addHours(24), function () use ($startDate, $endDate, $branchId) {
            $segments = ['vip', 'loyal', 'regular', 'at_risk', 'inactive'];
            $distribution = [];

            foreach ($segments as $segment) {
                $distribution[$segment] = DB::table('orders')
                    ->where('branch_id', $branchId)
                    ->whereBetween('created_at', [$startDate, $endDate])
                    ->whereNull('deleted_at')
                    ->select('user_id')
                    ->groupBy('user_id')
                    ->havingRaw($this->getSegmentCondition($segment))
                    ->count();
            }

            return $distribution;
        });
    }

    /**
     * Get segment condition for SQL query
     */
    protected function getSegmentCondition($segment)
    {
        return match($segment) {
            'vip' => 'COUNT(*) >= 5 AND SUM(total) >= 1000',
            'loyal' => 'COUNT(*) >= 3 AND SUM(total) >= 500',
            'regular' => 'COUNT(*) >= 2 AND SUM(total) >= 200',
            'at_risk' => 'MAX(created_at) < DATE_SUB(NOW(), INTERVAL 60 DAY)',
            'inactive' => 'MAX(created_at) < DATE_SUB(NOW(), INTERVAL 90 DAY)',
            default => '1=1'
        };
    }

    /**
     * Get detailed trend analysis for customer metrics
     */
    public function getTrendAnalysis($startDate, $endDate, $branchId)
    {
        $cacheKey = "trend_analysis:{$branchId}:{$startDate}:{$endDate}";
        
        return cache()->remember($cacheKey, now()->addHours(24), function () use ($startDate, $endDate, $branchId) {
            return [
                'monthly_metrics' => $this->getMonthlyMetrics($startDate, $endDate, $branchId),
                'customer_growth' => $this->getCustomerGrowthTrend($startDate, $endDate, $branchId),
                'revenue_trends' => $this->getRevenueTrends($startDate, $endDate, $branchId),
                'segment_evolution' => $this->getSegmentEvolution($startDate, $endDate, $branchId),
                'churn_trends' => $this->getChurnTrends($startDate, $endDate, $branchId)
            ];
        });
    }

    /**
     * Get monthly metrics for trend analysis
     */
    protected function getMonthlyMetrics($startDate, $endDate, $branchId)
    {
        return DB::table('orders')
            ->where('branch_id', $branchId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->whereNull('deleted_at')
            ->select(
                DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month'),
                DB::raw('COUNT(DISTINCT user_id) as new_customers'),
                DB::raw('COUNT(*) as total_orders'),
                DB::raw('AVG(total) as average_order_value'),
                DB::raw('SUM(total) as total_revenue')
            )
            ->groupBy('month')
            ->orderBy('month')
            ->get();
    }

    /**
     * Get customer growth trend
     */
    protected function getCustomerGrowthTrend($startDate, $endDate, $branchId)
    {
        // First get the first and last order dates for each user
        $userFirstLastOrders = Order::whereBetween('created_at', [$startDate, $endDate])
            ->when($branchId, function ($query) use ($branchId) {
                return $query->where('branch_id', $branchId);
            })
            ->select([
                'user_id',
                DB::raw('MIN(created_at) as first_order'),
                DB::raw('MAX(created_at) as last_order')
            ])
            ->groupBy('user_id')
            ->get();

        // Then get monthly customer counts
        $monthlyData = Order::whereBetween('created_at', [$startDate, $endDate])
            ->when($branchId, function ($query) use ($branchId) {
                return $query->where('branch_id', $branchId);
            })
            ->select([
                DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month'),
                DB::raw('COUNT(DISTINCT user_id) as total_customers')
            ])
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        // Process the data to identify new and active customers
        $result = [];
        foreach ($monthlyData as $data) {
            $month = $data->month;
            $newCustomers = $userFirstLastOrders->filter(function ($user) use ($month) {
                return date('Y-m', strtotime($user->first_order)) === $month;
            })->count();

            $activeCustomers = $userFirstLastOrders->filter(function ($user) use ($month) {
                return date('Y-m', strtotime($user->last_order)) === $month;
            })->count();

            $result[] = [
                'month' => $month,
                'total_customers' => $data->total_customers,
                'new_customers' => $newCustomers,
                'active_customers' => $activeCustomers
            ];
        }

        return $result;
    }

    /**
     * Get revenue trends
     */
    protected function getRevenueTrends($startDate, $endDate, $branchId)
    {
        return DB::table('orders')
            ->where('branch_id', $branchId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->whereNull('deleted_at')
            ->select(
                DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month'),
                DB::raw('SUM(total) as total_revenue'),
                DB::raw('AVG(total) as average_order_value'),
                DB::raw('COUNT(*) as order_count'),
                DB::raw('SUM(total) / COUNT(DISTINCT user_id) as revenue_per_customer')
            )
            ->groupBy('month')
            ->orderBy('month')
            ->get();
    }

    /**
     * Get segment evolution over time
     */
    protected function getSegmentEvolution($startDate, $endDate, $branchId)
    {
        $segments = ['vip', 'loyal', 'regular', 'at_risk', 'inactive'];
        $evolution = [];

        foreach ($segments as $segment) {
            $evolution[$segment] = DB::table('orders')
                ->where('branch_id', $branchId)
                ->whereBetween('created_at', [$startDate, $endDate])
                ->whereNull('deleted_at')
                ->select(
                    DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month'),
                    DB::raw('COUNT(DISTINCT user_id) as customer_count')
                )
                ->groupBy('month')
                ->orderBy('month')
                ->get();
        }

        return $evolution;
    }

    /**
     * Get churn trends
     */
    protected function getChurnTrends($startDate, $endDate, $branchId)
    {
        return DB::table('orders')
            ->where('branch_id', $branchId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->whereNull('deleted_at')
            ->select(
                DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month'),
                DB::raw('COUNT(DISTINCT CASE WHEN created_at < DATE_SUB(NOW(), INTERVAL 90 DAY) THEN user_id END) as churned_customers'),
                DB::raw('COUNT(DISTINCT user_id) as total_customers'),
                DB::raw('COUNT(DISTINCT CASE WHEN created_at < DATE_SUB(NOW(), INTERVAL 90 DAY) THEN user_id END) / COUNT(DISTINCT user_id) * 100 as churn_rate')
            )
            ->groupBy('month')
            ->orderBy('month')
            ->get();
    }

    /**
     * Get retention strategies based on customer behavior
     */
    public function getRetentionStrategies($startDate, $endDate, $branchId)
    {
        return [
            'at_risk_customers' => $this->getAtRiskCustomerStrategies($startDate, $endDate, $branchId),
            'loyalty_programs' => $this->getLoyaltyProgramStrategies($startDate, $endDate, $branchId),
            'win_back_campaigns' => $this->getWinBackStrategies($startDate, $endDate, $branchId),
            'engagement_boosters' => $this->getEngagementBoosters($startDate, $endDate, $branchId),
            'personalized_offers' => $this->getPersonalizedOfferStrategies($startDate, $endDate, $branchId)
        ];
    }

    /**
     * Get strategies for at-risk customers
     */
    protected function getAtRiskCustomerStrategies($startDate, $endDate, $branchId)
    {
        $atRiskCustomers = DB::table('orders')
            ->where('branch_id', $branchId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->whereNull('deleted_at')
            ->select('user_id')
            ->groupBy('user_id')
            ->havingRaw('MAX(created_at) < DATE_SUB(NOW(), INTERVAL 60 DAY)')
            ->havingRaw('MAX(created_at) >= DATE_SUB(NOW(), INTERVAL 90 DAY)')
            ->get();

        return $atRiskCustomers->map(function ($customer) {
            $lastOrder = DB::table('orders')
                ->where('user_id', $customer->user_id)
                ->latest()
                ->first();

            $avgOrderValue = DB::table('orders')
                ->where('user_id', $customer->user_id)
                ->avg('total');

            return [
                'user_id' => $customer->user_id,
                'days_since_last_order' => now()->diffInDays($lastOrder->created_at),
                'last_order_value' => $lastOrder->total,
                'average_order_value' => $avgOrderValue,
                'recommended_actions' => [
                    'personalized_discount' => $this->calculatePersonalizedDiscount($avgOrderValue),
                    'loyalty_points_boost' => $this->calculateLoyaltyPointsBoost($lastOrder->total),
                    'reengagement_campaign' => $this->getReengagementCampaignType($lastOrder->total)
                ]
            ];
        });
    }

    /**
     * Get loyalty program strategies
     */
    protected function getLoyaltyProgramStrategies($startDate, $endDate, $branchId)
    {
        $loyalCustomers = DB::table('orders')
            ->where('branch_id', $branchId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->whereNull('deleted_at')
            ->select('user_id')
            ->groupBy('user_id')
            ->havingRaw('COUNT(*) >= 3')
            ->get();

        return $loyalCustomers->map(function ($customer) {
            $orderHistory = DB::table('orders')
                ->where('user_id', $customer->user_id)
                ->orderBy('created_at', 'desc')
                ->take(5)
                ->get();

            $totalSpent = $orderHistory->sum('total');
            $orderFrequency = $this->calculateOrderFrequency($orderHistory);

            return [
                'user_id' => $customer->user_id,
                'total_spent' => $totalSpent,
                'order_frequency' => $orderFrequency,
                'recommended_tiers' => $this->getRecommendedLoyaltyTier($totalSpent, $orderFrequency),
                'benefits' => $this->getLoyaltyBenefits($totalSpent, $orderFrequency)
            ];
        });
    }

    /**
     * Get win-back strategies for churned customers
     */
    protected function getWinBackStrategies($startDate, $endDate, $branchId)
    {
        $churnedCustomers = DB::table('orders')
            ->where('branch_id', $branchId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->whereNull('deleted_at')
            ->select('user_id')
            ->groupBy('user_id')
            ->havingRaw('MAX(created_at) < DATE_SUB(NOW(), INTERVAL 90 DAY)')
            ->get();

        return $churnedCustomers->map(function ($customer) {
            $lastOrder = DB::table('orders')
                ->where('user_id', $customer->user_id)
                ->latest()
                ->first();

            $orderHistory = DB::table('orders')
                ->where('user_id', $customer->user_id)
                ->get();

            return [
                'user_id' => $customer->user_id,
                'days_since_last_order' => now()->diffInDays($lastOrder->created_at),
                'total_orders' => $orderHistory->count(),
                'average_order_value' => $orderHistory->avg('total'),
                'win_back_strategy' => $this->determineWinBackStrategy($orderHistory, $lastOrder)
            ];
        });
    }

    /**
     * Get engagement booster strategies
     */
    protected function getEngagementBoosters($startDate, $endDate, $branchId)
    {
        $customers = DB::table('orders')
            ->where('branch_id', $branchId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->whereNull('deleted_at')
            ->select('user_id')
            ->groupBy('user_id')
            ->get();

        return $customers->map(function ($customer) {
            $orderHistory = DB::table('orders')
                ->where('user_id', $customer->user_id)
                ->orderBy('created_at', 'desc')
                ->get();

            $engagementScore = $this->calculateEngagementScore($orderHistory);
            $preferredCategories = $this->getPreferredCategories($customer->user_id);

            return [
                'user_id' => $customer->user_id,
                'engagement_score' => $engagementScore,
                'preferred_categories' => $preferredCategories,
                'recommended_actions' => $this->getEngagementActions($engagementScore, $preferredCategories)
            ];
        });
    }

    /**
     * Get personalized offer strategies
     */
    protected function getPersonalizedOfferStrategies($startDate, $endDate, $branchId)
    {
        $customers = DB::table('orders')
            ->where('branch_id', $branchId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->whereNull('deleted_at')
            ->select('user_id')
            ->groupBy('user_id')
            ->get();

        return $customers->map(function ($customer) {
            $orderHistory = DB::table('orders')
                ->where('user_id', $customer->user_id)
                ->orderBy('created_at', 'desc')
                ->take(10)
                ->get();

            $purchasePattern = $this->analyzePurchasePattern($orderHistory);
            $seasonalPreferences = $this->getSeasonalPreferences($orderHistory);

            return [
                'user_id' => $customer->user_id,
                'purchase_pattern' => $purchasePattern,
                'seasonal_preferences' => $seasonalPreferences,
                'recommended_offers' => $this->generatePersonalizedOffers($purchasePattern, $seasonalPreferences)
            ];
        });
    }

    /**
     * Calculate personalized discount based on average order value
     */
    protected function calculatePersonalizedDiscount($avgOrderValue)
    {
        if ($avgOrderValue >= 1000) {
            return ['type' => 'percentage', 'value' => 15];
        } elseif ($avgOrderValue >= 500) {
            return ['type' => 'percentage', 'value' => 10];
        } else {
            return ['type' => 'percentage', 'value' => 5];
        }
    }

    /**
     * Calculate loyalty points boost
     */
    protected function calculateLoyaltyPointsBoost($lastOrderValue)
    {
        return [
            'multiplier' => $lastOrderValue >= 500 ? 2 : 1.5,
            'duration_days' => 30
        ];
    }

    /**
     * Get reengagement campaign type
     */
    protected function getReengagementCampaignType($lastOrderValue)
    {
        if ($lastOrderValue >= 1000) {
            return 'premium_reengagement';
        } elseif ($lastOrderValue >= 500) {
            return 'standard_reengagement';
        } else {
            return 'basic_reengagement';
        }
    }

    /**
     * Calculate order frequency
     */
    protected function calculateOrderFrequency($orderHistory)
    {
        if ($orderHistory->count() < 2) {
            return 0;
        }

        $firstOrder = $orderHistory->last();
        $lastOrder = $orderHistory->first();
        $daysBetween = now()->diffInDays($firstOrder->created_at);
        
        return $daysBetween > 0 ? $orderHistory->count() / ($daysBetween / 30) : 0;
    }

    /**
     * Get recommended loyalty tier
     */
    protected function getRecommendedLoyaltyTier($totalSpent, $orderFrequency)
    {
        if ($totalSpent >= 5000 && $orderFrequency >= 2) {
            return 'platinum';
        } elseif ($totalSpent >= 2000 && $orderFrequency >= 1) {
            return 'gold';
        } elseif ($totalSpent >= 1000) {
            return 'silver';
        } else {
            return 'bronze';
        }
    }

    /**
     * Get loyalty benefits
     */
    protected function getLoyaltyBenefits($totalSpent, $orderFrequency)
    {
        $tier = $this->getRecommendedLoyaltyTier($totalSpent, $orderFrequency);
        
        return match($tier) {
            'platinum' => [
                'discount' => 20,
                'free_shipping' => true,
                'priority_support' => true,
                'exclusive_offers' => true
            ],
            'gold' => [
                'discount' => 15,
                'free_shipping' => true,
                'priority_support' => false,
                'exclusive_offers' => true
            ],
            'silver' => [
                'discount' => 10,
                'free_shipping' => false,
                'priority_support' => false,
                'exclusive_offers' => true
            ],
            default => [
                'discount' => 5,
                'free_shipping' => false,
                'priority_support' => false,
                'exclusive_offers' => false
            ]
        };
    }

    /**
     * Determine win-back strategy
     */
    protected function determineWinBackStrategy($orderHistory, $lastOrder)
    {
        $totalOrders = $orderHistory->count();
        $avgOrderValue = $orderHistory->avg('total');
        $daysSinceLastOrder = now()->diffInDays($lastOrder->created_at);

        if ($totalOrders >= 5 && $avgOrderValue >= 500) {
            return [
                'type' => 'premium_winback',
                'offer' => [
                    'discount' => 25,
                    'free_shipping' => true,
                    'loyalty_points_boost' => 3
                ]
            ];
        } elseif ($totalOrders >= 3 && $avgOrderValue >= 200) {
            return [
                'type' => 'standard_winback',
                'offer' => [
                    'discount' => 15,
                    'free_shipping' => true,
                    'loyalty_points_boost' => 2
                ]
            ];
        } else {
            return [
                'type' => 'basic_winback',
                'offer' => [
                    'discount' => 10,
                    'free_shipping' => false,
                    'loyalty_points_boost' => 1.5
                ]
            ];
        }
    }

    /**
     * Calculate engagement score
     */
    protected function calculateEngagementScore($orderHistory)
    {
        if ($orderHistory->isEmpty()) {
            return 0;
        }

        $recencyScore = $this->calculateRecencyScore($orderHistory->first()->created_at);
        $frequencyScore = min($orderHistory->count() / 2, 1);
        $monetaryScore = min($orderHistory->avg('total') / 1000, 1);

        return ($recencyScore * 0.5) + ($frequencyScore * 0.3) + ($monetaryScore * 0.2);
    }

    /**
     * Get preferred categories
     */
    protected function getPreferredCategories($userId)
    {
        return DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->where('orders.user_id', $userId)
            ->select('products.category', DB::raw('COUNT(*) as count'))
            ->groupBy('products.category')
            ->orderBy('count', 'desc')
            ->take(3)
            ->pluck('category')
            ->toArray();
    }

    /**
     * Get engagement actions
     */
    protected function getEngagementActions($engagementScore, $preferredCategories)
    {
        $actions = [];

        if ($engagementScore < 0.3) {
            $actions[] = [
                'type' => 'reengagement_campaign',
                'priority' => 'high',
                'categories' => $preferredCategories
            ];
        }

        if ($engagementScore < 0.5) {
            $actions[] = [
                'type' => 'loyalty_reminder',
                'priority' => 'medium',
                'categories' => $preferredCategories
            ];
        }

        if ($engagementScore >= 0.7) {
            $actions[] = [
                'type' => 'upsell_opportunity',
                'priority' => 'low',
                'categories' => $preferredCategories
            ];
        }

        return $actions;
    }

    /**
     * Analyze purchase pattern
     */
    protected function analyzePurchasePattern($orderHistory)
    {
        $patterns = [
            'frequency' => $this->calculateOrderFrequency($orderHistory),
            'average_value' => $orderHistory->avg('total'),
            'preferred_days' => $this->getPreferredDays($orderHistory),
            'preferred_times' => $this->getPreferredTimes($orderHistory)
        ];

        return $patterns;
    }

    /**
     * Get seasonal preferences
     */
    protected function getSeasonalPreferences($orderHistory)
    {
        $seasonalData = $orderHistory->groupBy(function ($order) {
            return date('m', strtotime($order->created_at));
        })->map(function ($orders) {
            return [
                'count' => $orders->count(),
                'total_value' => $orders->sum('total')
            ];
        });

        return $seasonalData;
    }

    /**
     * Generate personalized offers
     */
    protected function generatePersonalizedOffers($purchasePattern, $seasonalPreferences)
    {
        $offers = [];

        // Time-based offers
        if (isset($purchasePattern['preferred_times'])) {
            $offers[] = [
                'type' => 'time_based',
                'discount' => 10,
                'valid_hours' => $purchasePattern['preferred_times']
            ];
        }

        // Value-based offers
        if ($purchasePattern['average_value'] >= 500) {
            $offers[] = [
                'type' => 'value_based',
                'discount' => 15,
                'minimum_purchase' => 500
            ];
        }

        // Seasonal offers
        $currentMonth = date('m');
        if (isset($seasonalPreferences[$currentMonth])) {
            $offers[] = [
                'type' => 'seasonal',
                'discount' => 20,
                'valid_month' => $currentMonth
            ];
        }

        return $offers;
    }

    /**
     * Get preferred days
     */
    protected function getPreferredDays($orderHistory)
    {
        return $orderHistory->groupBy(function ($order) {
            return date('l', strtotime($order->created_at));
        })->map(function ($orders) {
            return $orders->count();
        })->sortDesc()->keys()->take(3)->toArray();
    }

    /**
     * Get preferred times
     */
    protected function getPreferredTimes($orderHistory)
    {
        return $orderHistory->groupBy(function ($order) {
            return date('H', strtotime($order->created_at));
        })->map(function ($orders) {
            return $orders->count();
        })->sortDesc()->keys()->take(3)->toArray();
    }

    /**
     * Clear analytics cache for a branch
     */
    public function clearAnalyticsCache($branchId)
    {
        $patterns = [
            "clv:{$branchId}:*",
            "purchase_frequency:{$branchId}:*",
            "customer_lifespan:{$branchId}:*",
            "segment_distribution:{$branchId}:*",
            "trend_analysis:{$branchId}:*"
        ];

        foreach ($patterns as $pattern) {
            cache()->forget($pattern);
        }
    }
} 