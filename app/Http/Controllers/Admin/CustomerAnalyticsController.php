<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\CustomerAnalyticsService;
use Illuminate\Http\Request;
use Carbon\Carbon;

class CustomerAnalyticsController extends Controller
{
    protected $customerAnalyticsService;

    public function __construct(CustomerAnalyticsService $customerAnalyticsService)
    {
        $this->customerAnalyticsService = $customerAnalyticsService;
    }

    /**
     * Display the customer analytics dashboard
     */
    public function index(Request $request)
    {
        $dateRange = $this->customerAnalyticsService->getDateRange(
            $request->input('start_date'),
            $request->input('end_date')
        );
        
        $branchId = $request->input('branch', 1);

        $data = [
            'behavior_metrics' => $this->getBehaviorMetrics($dateRange['start'], $dateRange['end'], $branchId),
            'advanced_metrics' => $this->getAdvancedMetrics($dateRange['start'], $dateRange['end'], $branchId),
            'journey_map' => $this->getJourneyMap($dateRange['start'], $dateRange['end'], $branchId),
            'segments' => $this->getSegments($dateRange['start'], $dateRange['end'], $branchId),
            'churn_risk' => $this->getChurnRiskDistribution($dateRange['start'], $dateRange['end'], $branchId),
            'ai_suggestions' => $this->getAISuggestions($dateRange['start'], $dateRange['end'], $branchId)
        ];

        return view('admin.customer-analytics.index', compact('data'));
    }

    /**
     * Display the customer segments page
     */
    public function segments()
    {
        $startDate = request('start_date', now()->subMonths(3)->format('Y-m-d'));
        $endDate = request('end_date', now()->format('Y-m-d'));

        $segments = $this->customerAnalyticsService->getCustomerSegments($startDate, $endDate);

        return view('admin.customer-analytics.segments', compact('segments'));
    }

    /**
     * Display the churn risk analysis page
     */
    public function churn()
    {
        $startDate = request('start_date', now()->subMonths(3)->format('Y-m-d'));
        $endDate = request('end_date', now()->format('Y-m-d'));

        $churnRisk = $this->customerAnalyticsService->getChurnRiskAnalysis($startDate, $endDate);

        return view('admin.customer-analytics.churn', compact('churnRisk'));
    }

    protected function getBehaviorMetrics($startDate, $endDate, $branchId)
    {
        return [
            'total_customers' => $this->customerAnalyticsService->getTotalCustomers($branchId),
            'active_customers_30d' => $this->customerAnalyticsService->getActiveCustomers($startDate, $endDate, $branchId),
            'average_order_value' => $this->customerAnalyticsService->getAverageOrderValue($startDate, $endDate, $branchId),
            'retention_rate_30d' => $this->customerAnalyticsService->getRetentionRate($startDate, $endDate, $branchId),
            'repeat_purchase_rate' => $this->customerAnalyticsService->getRepeatPurchaseRate($startDate, $endDate, $branchId),
            'average_purchase_frequency' => $this->customerAnalyticsService->getAveragePurchaseFrequency($startDate, $endDate, $branchId),
            'top_categories' => $this->customerAnalyticsService->getTopCategories($startDate, $endDate, $branchId),
            'peak_hours' => $this->customerAnalyticsService->getPeakHours($startDate, $endDate, $branchId),
            'average_basket_size' => $this->customerAnalyticsService->getAverageBasketSize($startDate, $endDate, $branchId),
            'customer_satisfaction' => $this->customerAnalyticsService->getCustomerSatisfaction($startDate, $endDate, $branchId)
        ];
    }

    protected function getAdvancedMetrics($startDate, $endDate, $branchId)
    {
        return [
            'clv' => $this->customerAnalyticsService->getCustomerLifetimeValue($startDate, $endDate, $branchId),
            'purchase_frequency' => $this->customerAnalyticsService->getPurchaseFrequency($startDate, $endDate, $branchId),
            'customer_lifespan' => $this->customerAnalyticsService->getCustomerLifespan($startDate, $endDate, $branchId),
            'churn_rate' => $this->customerAnalyticsService->getChurnRate($startDate, $endDate, $branchId),
            'loyalty_score' => $this->customerAnalyticsService->getLoyaltyScore($startDate, $endDate, $branchId),
            'engagement_score' => $this->customerAnalyticsService->getEngagementScore($startDate, $endDate, $branchId),
            'value_score' => $this->customerAnalyticsService->getValueScore($startDate, $endDate, $branchId),
            'risk_score' => $this->customerAnalyticsService->getRiskScore($startDate, $endDate, $branchId),
            'segment_distribution' => $this->customerAnalyticsService->getSegmentDistribution($startDate, $endDate, $branchId),
            'trend_analysis' => $this->customerAnalyticsService->getTrendAnalysis($startDate, $endDate, $branchId)
        ];
    }

    protected function getJourneyMap($startDate, $endDate, $branchId)
    {
        return [
            'new' => $this->customerAnalyticsService->getNewCustomers($startDate, $endDate, $branchId),
            'regular' => $this->customerAnalyticsService->getRegularCustomers($startDate, $endDate, $branchId),
            'loyal' => $this->customerAnalyticsService->getLoyalCustomers($startDate, $endDate, $branchId),
            'vip' => $this->customerAnalyticsService->getVIPCustomers($startDate, $endDate, $branchId),
            'churned' => $this->customerAnalyticsService->getChurnedCustomers($startDate, $endDate, $branchId),
            'conversion_rates' => [
                'new_to_regular' => $this->customerAnalyticsService->getNewToRegularRate($startDate, $endDate, $branchId),
                'regular_to_loyal' => $this->customerAnalyticsService->getRegularToLoyalRate($startDate, $endDate, $branchId),
                'loyal_to_vip' => $this->customerAnalyticsService->getLoyalToVIPRate($startDate, $endDate, $branchId)
            ],
            'journey_stages' => $this->customerAnalyticsService->getJourneyStages($startDate, $endDate, $branchId),
            'touchpoints' => $this->customerAnalyticsService->getTouchpoints($startDate, $endDate, $branchId),
            'bottlenecks' => $this->customerAnalyticsService->getBottlenecks($startDate, $endDate, $branchId),
            'opportunities' => $this->customerAnalyticsService->getOpportunities($startDate, $endDate, $branchId)
        ];
    }

    protected function getAISuggestions($startDate, $endDate, $branchId)
    {
        return $this->customerAnalyticsService->getAISuggestions($startDate, $endDate, $branchId);
    }

    public function getSegmentSuggestions(Request $request)
    {
        $startDate = $request->input('start_date', Carbon::now()->subDays(30)->format('Y-m-d'));
        $endDate = $request->input('end_date', Carbon::now()->format('Y-m-d'));
        $branchId = $request->input('branch_id', 1);

        $suggestions = $this->customerAnalyticsService->getSegmentSuggestions($startDate, $endDate, $branchId);

        return response()->json([
            'suggestions' => $suggestions
        ]);
    }

    public function generateRetentionCampaign($customerId)
    {
        $campaign = $this->customerAnalyticsService->generateRetentionCampaign($customerId);

        return response()->json([
            'customer' => $campaign['customer'],
            'campaign' => $campaign['campaign']
        ]);
    }

    public function getSegments($startDate, $endDate, $branchId)
    {
        return [
            'segments' => [
                [
                    'name' => 'New Customers',
                    'count' => $this->customerAnalyticsService->getNewCustomers($startDate, $endDate, $branchId),
                    'description' => 'Customers who made their first purchase in this period'
                ],
                [
                    'name' => 'Regular Customers',
                    'count' => $this->customerAnalyticsService->getRegularCustomers($startDate, $endDate, $branchId),
                    'description' => 'Customers who made 2-4 purchases in this period'
                ],
                [
                    'name' => 'Loyal Customers',
                    'count' => $this->customerAnalyticsService->getLoyalCustomers($startDate, $endDate, $branchId),
                    'description' => 'Customers who made 5 or more purchases in this period'
                ],
                [
                    'name' => 'VIP Customers',
                    'count' => $this->customerAnalyticsService->getVIPCustomers($startDate, $endDate, $branchId),
                    'description' => 'Customers who spent $1000 or more in this period'
                ],
                [
                    'name' => 'At Risk Customers',
                    'count' => $this->customerAnalyticsService->getAtRiskCustomers($startDate, $endDate, $branchId),
                    'description' => 'Customers who haven\'t made a purchase in the last 90 days'
                ]
            ],
            'total_customers' => $this->customerAnalyticsService->getTotalCustomers($startDate, $endDate, $branchId),
            'active_customers' => $this->customerAnalyticsService->getActiveCustomers($startDate, $endDate, $branchId),
            'retention_rate' => $this->customerAnalyticsService->getRetentionRate($startDate, $endDate, $branchId)
        ];
    }

    public function getChurnRiskDistribution($startDate, $endDate, $branchId)
    {
        return [
            'risk_levels' => [
                [
                    'level' => 'High Risk',
                    'count' => $this->customerAnalyticsService->getHighRiskCustomers($startDate, $endDate, $branchId),
                    'description' => 'No purchase in last 90 days and declining order frequency'
                ],
                [
                    'level' => 'Medium Risk',
                    'count' => $this->customerAnalyticsService->getMediumRiskCustomers($startDate, $endDate, $branchId),
                    'description' => 'No purchase in last 60 days or declining order value'
                ],
                [
                    'level' => 'Low Risk',
                    'count' => $this->customerAnalyticsService->getLowRiskCustomers($startDate, $endDate, $branchId),
                    'description' => 'No purchase in last 30 days but stable order history'
                ],
                [
                    'level' => 'Safe',
                    'count' => $this->customerAnalyticsService->getSafeCustomers($startDate, $endDate, $branchId),
                    'description' => 'Active customers with stable or increasing engagement'
                ]
            ],
            'total_customers' => $this->customerAnalyticsService->getTotalCustomers($startDate, $endDate, $branchId),
            'churn_rate' => $this->customerAnalyticsService->getChurnRate($startDate, $endDate, $branchId),
            'retention_rate' => $this->customerAnalyticsService->getRetentionRate($startDate, $endDate, $branchId)
        ];
    }
} 