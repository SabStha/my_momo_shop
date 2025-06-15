<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\CustomerAnalyticsService;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\Customer;
use App\Models\Order;
use App\Models\CustomerSegment;

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
            'total_customers' => $this->customerAnalyticsService->getTotalCustomers($startDate, $endDate, $branchId),
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

    /**
     * Get trend analysis for a segment
     */
    public function getTrendAnalysis(Request $request)
    {
        $segment = $request->input('segment');
        $startDate = $request->input('start_date', now()->subMonths(3)->format('Y-m-d'));
        $endDate = $request->input('end_date', now()->format('Y-m-d'));
        $branchId = $request->input('branch', 1);

        try {
            $analysis = $this->customerAnalyticsService->getTrendAnalysis($startDate, $endDate, $branchId);
            
            return response()->json([
                'status' => 'success',
                'analysis' => $analysis
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error analyzing trends: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate campaign for a segment
     */
    public function generateCampaign(Request $request)
    {
        $type = $request->input('type');
        $segment = $request->input('segment');
        $startDate = $request->input('start_date', now()->subMonths(3)->format('Y-m-d'));
        $endDate = $request->input('end_date', now()->format('Y-m-d'));
        $branchId = $request->input('branch', 1);

        try {
            $campaign = $this->customerAnalyticsService->generateCampaign($type, $segment, $startDate, $endDate, $branchId);
            
            return response()->json([
                'status' => 'success',
                'suggestions' => $campaign
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error generating campaign: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Export segment data
     */
    public function exportSegment(string $segment)
    {
        $startDate = request('start_date', now()->subMonths(3)->format('Y-m-d'));
        $endDate = request('end_date', now()->format('Y-m-d'));
        $branchId = request('branch', 1);

        try {
            $data = $this->customerAnalyticsService->getSegmentData($segment, $startDate, $endDate, $branchId);
            $filename = "{$segment}_segment_" . now()->format('Y-m-d') . ".csv";
            
            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => "attachment; filename=\"$filename\"",
            ];

            $callback = function() use ($data) {
                $file = fopen('php://output', 'w');
                
                // Add headers
                fputcsv($file, ['Customer ID', 'Name', 'Email', 'Total Spent', 'Orders', 'Last Order', 'CLV', 'Risk Level', 'Loyalty Level']);
                
                // Add data
                foreach ($data as $row) {
                    fputcsv($file, [
                        $row['user_id'],
                        $row['name'],
                        $row['email'],
                        $row['total_spent'],
                        $row['total_orders'],
                        $row['last_order_date'],
                        $row['clv'],
                        $row['risk_level'],
                        $row['loyalty_level']
                    ]);
                }
                
                fclose($file);
            };

            return response()->stream($callback, 200, $headers);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error exporting segment: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Analyze customer journey for a specific segment
     */
    public function journeyAnalysis(Request $request)
    {
        $segment = $request->query('segment', 'all');
        $startDate = $request->input('start_date', now()->subMonths(3)->format('Y-m-d'));
        $endDate = $request->input('end_date', now()->format('Y-m-d'));
        $branchId = $request->input('branch_id', 1);
        
        try {
            // Get journey funnel data
            $funnelData = $this->customerAnalyticsService->getJourneyFunnelData($segment, $startDate, $endDate, $branchId);
            
            // Analyze drop-off points
            $dropoffData = $this->customerAnalyticsService->analyzeDropoffPoints($funnelData);
            
            // Generate insights
            $insights = $this->customerAnalyticsService->generateJourneyInsights($funnelData, $dropoffData);
            
            return response()->json([
                'status' => 'success',
                'funnel' => $funnelData,
                'dropoff' => $dropoffData,
                'insights' => $insights
            ]);
        } catch (\Exception $e) {
            \Log::error('Journey Analysis Error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to analyze customer journey: ' . $e->getMessage()
            ], 500);
        }
    }

    public function explainTrend(Request $request)
    {
        try {
            $metric = $request->input('metric');
            $startDate = $request->input('start_date');
            $endDate = $request->input('end_date');
            $branchId = $request->input('branch_id', session('selected_branch_id'));

            if (!$metric || !$startDate || !$endDate) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Missing required parameters'
                ], 400);
            }

            // Get the trend data
            $trendData = $this->getTrendData($metric, $startDate, $endDate, $branchId);
            
            if (!$trendData) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'No data available for the selected period'
                ], 404);
            }

            // Calculate trend metrics
            $metrics = $this->calculateTrendMetrics($trendData);
            
            // Generate explanation
            $explanation = $this->generateTrendExplanation($metrics, $metric);

            return response()->json([
                'status' => 'success',
                'insights' => $explanation['insights'],
                'factors' => $explanation['factors'],
                'recommendations' => $explanation['recommendations']
            ]);
        } catch (\Exception $e) {
            \Log::error('Error in explainTrend: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to analyze trend'
            ], 500);
        }
    }

    private function getTrendData($metric, $startDate, $endDate, $branchId)
    {
        $query = Order::where('branch_id', $branchId)
            ->whereBetween('created_at', [$startDate, $endDate]);

        if ($metric === 'revenue') {
            return $query->selectRaw('DATE(created_at) as date, SUM(total_amount) as value')
                ->groupBy('date')
                ->orderBy('date')
                ->get()
                ->toArray();
        } else if ($metric === 'orders') {
            return $query->selectRaw('DATE(created_at) as date, COUNT(*) as value')
                ->groupBy('date')
                ->orderBy('date')
                ->get()
                ->toArray();
        }

        return null;
    }

    private function calculateTrendMetrics($data)
    {
        if (empty($data)) {
            return null;
        }

        $values = array_column($data, 'value');
        $dates = array_column($data, 'date');

        $firstValue = $values[0];
        $lastValue = end($values);
        $totalChange = $lastValue - $firstValue;
        $percentChange = $firstValue != 0 ? ($totalChange / $firstValue) * 100 : 0;

        // Calculate moving average
        $windowSize = 7; // 7-day moving average
        $movingAverages = [];
        for ($i = 0; $i < count($values); $i++) {
            $start = max(0, $i - $windowSize + 1);
            $window = array_slice($values, $start, $i - $start + 1);
            $movingAverages[] = array_sum($window) / count($window);
        }

        return [
            'values' => $values,
            'dates' => $dates,
            'first_value' => $firstValue,
            'last_value' => $lastValue,
            'total_change' => $totalChange,
            'percent_change' => $percentChange,
            'moving_averages' => $movingAverages
        ];
    }

    private function generateTrendExplanation($metrics, $metric)
    {
        if (!$metrics) {
            return [
                'insights' => 'No data available for analysis.',
                'factors' => [],
                'recommendations' => []
            ];
        }

        $insights = [];
        $factors = [];
        $recommendations = [];

        // Analyze trend direction
        if ($metrics['percent_change'] > 10) {
            $insights[] = "Strong positive growth of " . number_format($metrics['percent_change'], 1) . "%";
            $factors[] = "Consistent upward trend in daily values";
            $recommendations[] = "Consider scaling successful strategies";
        } elseif ($metrics['percent_change'] > 0) {
            $insights[] = "Moderate growth of " . number_format($metrics['percent_change'], 1) . "%";
            $factors[] = "Stable but slow growth pattern";
            $recommendations[] = "Look for opportunities to accelerate growth";
        } elseif ($metrics['percent_change'] > -10) {
            $insights[] = "Slight decline of " . number_format(abs($metrics['percent_change']), 1) . "%";
            $factors[] = "Minor downward trend";
            $recommendations[] = "Implement retention strategies";
        } else {
            $insights[] = "Significant decline of " . number_format(abs($metrics['percent_change']), 1) . "%";
            $factors[] = "Major downward trend";
            $recommendations[] = "Urgent action needed to reverse the trend";
        }

        // Analyze volatility
        $volatility = $this->calculateVolatility($metrics['values']);
        if ($volatility > 0.5) {
            $insights[] = "High volatility in daily values";
            $factors[] = "Inconsistent performance";
            $recommendations[] = "Investigate causes of fluctuations";
        }

        // Analyze recent trend
        $recentTrend = $this->analyzeRecentTrend($metrics['moving_averages']);
        if ($recentTrend > 0) {
            $insights[] = "Recent positive momentum";
            $factors[] = "Improving performance in last 7 days";
            $recommendations[] = "Maintain current strategies";
        } elseif ($recentTrend < 0) {
            $insights[] = "Recent negative momentum";
            $factors[] = "Declining performance in last 7 days";
            $recommendations[] = "Review recent changes";
        }

        return [
            'insights' => implode('. ', $insights),
            'factors' => $factors,
            'recommendations' => $recommendations
        ];
    }

    private function calculateVolatility($values)
    {
        $mean = array_sum($values) / count($values);
        $variance = array_sum(array_map(function($value) use ($mean) {
            return pow($value - $mean, 2);
        }, $values)) / count($values);
        return sqrt($variance) / $mean;
    }

    private function analyzeRecentTrend($movingAverages)
    {
        if (count($movingAverages) < 2) {
            return 0;
        }
        $recent = array_slice($movingAverages, -7);
        return ($recent[count($recent) - 1] - $recent[0]) / $recent[0];
    }

    public function getSegmentEvolution(Request $request)
    {
        $months = $request->get('months', 6);
        $endDate = now();
        $startDate = now()->subMonths($months);

        $segments = CustomerSegment::where('branch_id', session('selected_branch_id'))
            ->whereBetween('created_at', [$startDate, $endDate])
            ->get()
            ->groupBy(function($segment) {
                return $segment->created_at->format('Y-m');
            });

        $labels = [];
        $vip = [];
        $loyal = [];
        $regular = [];
        $atRisk = [];

        for ($i = 0; $i < $months; $i++) {
            $date = $endDate->copy()->subMonths($i)->format('Y-m');
            $labels[] = $endDate->copy()->subMonths($i)->format('M Y');
            
            $monthSegments = $segments[$date] ?? collect();
            
            $vip[] = $monthSegments->where('segment_type', 'vip')->count();
            $loyal[] = $monthSegments->where('segment_type', 'loyal')->count();
            $regular[] = $monthSegments->where('segment_type', 'regular')->count();
            $atRisk[] = $monthSegments->where('segment_type', 'at_risk')->count();
        }

        return response()->json([
            'status' => 'success',
            'labels' => array_reverse($labels),
            'vip' => array_reverse($vip),
            'loyal' => array_reverse($loyal),
            'regular' => array_reverse($regular),
            'at_risk' => array_reverse($atRisk)
        ]);
    }
} 