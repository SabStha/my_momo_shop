<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\CustomerAnalyticsService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class CustomerAnalyticsController extends Controller
{
    protected $customerAnalyticsService;

    public function __construct(CustomerAnalyticsService $customerAnalyticsService)
    {
        $this->customerAnalyticsService = $customerAnalyticsService;
    }

    /**
     * Get comprehensive customer analytics
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $startDate = $request->input('start_date', now()->subDays(30)->format('Y-m-d'));
        $endDate = $request->input('end_date', now()->format('Y-m-d'));
        $branchId = $request->input('branch', 1);

        $analytics = [
            'behavior_metrics' => [
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
            ],
            'advanced_metrics' => [
                'clv' => $this->customerAnalyticsService->getCustomerLifetimeValue($startDate, $endDate, $branchId),
                'purchase_frequency' => $this->customerAnalyticsService->getPurchaseFrequency($startDate, $endDate, $branchId),
                'customer_lifespan' => $this->customerAnalyticsService->getCustomerLifespan($startDate, $endDate, $branchId)
            ],
            'journey_map' => [
                'new' => $this->customerAnalyticsService->getNewCustomers($startDate, $endDate, $branchId),
                'regular' => $this->customerAnalyticsService->getRegularCustomers($startDate, $endDate, $branchId),
                'loyal' => $this->customerAnalyticsService->getLoyalCustomers($startDate, $endDate, $branchId),
                'vip' => $this->customerAnalyticsService->getVIPCustomers($startDate, $endDate, $branchId),
                'churned' => $this->customerAnalyticsService->getChurnedCustomers($startDate, $endDate, $branchId),
                'conversion_rates' => [
                    'new_to_regular' => $this->customerAnalyticsService->getNewToRegularRate($startDate, $endDate, $branchId),
                    'regular_to_loyal' => $this->customerAnalyticsService->getRegularToLoyalRate($startDate, $endDate, $branchId),
                    'loyal_to_vip' => $this->customerAnalyticsService->getLoyalToVIPRate($startDate, $endDate, $branchId)
                ]
            ],
            'segments' => [
                'vip' => $this->customerAnalyticsService->getVIPCustomers($startDate, $endDate, $branchId),
                'loyal' => $this->customerAnalyticsService->getLoyalCustomers($startDate, $endDate, $branchId),
                'regular' => $this->customerAnalyticsService->getRegularCustomers($startDate, $endDate, $branchId),
                'at_risk' => $this->customerAnalyticsService->getAtRiskCustomers($startDate, $endDate, $branchId),
                'inactive' => $this->customerAnalyticsService->getChurnedCustomers($startDate, $endDate, $branchId)
            ],
            'churn_risk' => [
                'high_risk' => $this->customerAnalyticsService->getHighRiskCustomers($startDate, $endDate, $branchId),
                'medium_risk' => $this->customerAnalyticsService->getMediumRiskCustomers($startDate, $endDate, $branchId),
                'low_risk' => $this->customerAnalyticsService->getLowRiskCustomers($startDate, $endDate, $branchId)
            ],
            'ai_suggestions' => []
        ];

        return response()->json($analytics);
    }

    /**
     * Get customer segments
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getSegments(Request $request): JsonResponse
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $segments = $this->customerAnalyticsService->getCustomerSegments($startDate, $endDate);

        return response()->json([
            'status' => 'success',
            'data' => $segments
        ]);
    }

    /**
     * Get customer lifetime values
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getLifetimeValues(Request $request): JsonResponse
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $lifetimeValues = $this->customerAnalyticsService->getCustomerLifetimeValues($startDate, $endDate);

        return response()->json([
            'status' => 'success',
            'data' => $lifetimeValues
        ]);
    }

    /**
     * Get churn risk analysis
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getChurnRisk(Request $request): JsonResponse
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $churnRisk = $this->customerAnalyticsService->getChurnRiskAnalysis($startDate, $endDate);

        return response()->json([
            'status' => 'success',
            'data' => $churnRisk
        ]);
    }

    /**
     * Get customer behavior metrics
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getBehaviorMetrics(Request $request): JsonResponse
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $metrics = $this->customerAnalyticsService->getCustomerBehaviorMetrics($startDate, $endDate);

        return response()->json([
            'status' => 'success',
            'data' => $metrics
        ]);
    }

    /**
     * Get AI-powered segment suggestions
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getSegmentSuggestions(Request $request): JsonResponse
    {
        $branchId = $request->input('branch', 1);
        $startDate = $request->input('start_date', now()->subDays(30)->format('Y-m-d'));
        $endDate = $request->input('end_date', now()->format('Y-m-d'));

        try {
            $suggestions = $this->customerAnalyticsService->getSegmentSuggestions($startDate, $endDate, $branchId);
            
            return response()->json([
                'status' => 'success',
                'data' => $suggestions
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error generating segment suggestions: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate retention campaign for a customer
     *
     * @param int $customerId
     * @return JsonResponse
     */
    public function generateRetentionCampaign(int $customerId): JsonResponse
    {
        try {
            $campaign = $this->customerAnalyticsService->generateRetentionCampaign($customerId);
            
            return response()->json([
                'status' => 'success',
                'data' => $campaign
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error generating retention campaign: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get trend analysis for a segment
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getTrendAnalysis(Request $request): JsonResponse
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
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function generateCampaign(Request $request): JsonResponse
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
     *
     * @param string $segment
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
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
} 