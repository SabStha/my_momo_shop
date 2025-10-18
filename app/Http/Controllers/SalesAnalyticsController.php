<?php

namespace App\Http\Controllers;

use App\Services\SalesAnalyticsService;
use Illuminate\Http\Request;

class SalesAnalyticsController extends Controller
{
    protected $salesAnalyticsService;

    public function __construct(SalesAnalyticsService $salesAnalyticsService)
    {
        $this->salesAnalyticsService = $salesAnalyticsService;
    }

    /**
     * Get sales overview
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSalesOverview(Request $request)
    {
        $period = $request->input('period', 'monthly');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $overview = $this->salesAnalyticsService->getSalesOverview($period, $startDate, $endDate);

        return response()->json([
            'status' => 'success',
            'data' => $overview
        ]);
    }

    /**
     * Display the sales analytics dashboard
     */
    public function index(Request $request)
    {
        $period = $request->input('period', 'monthly');
        $startDate = $request->input('start_date', now()->subMonths(3)->format('Y-m-d'));
        $endDate = $request->input('end_date', now()->format('Y-m-d'));
        $branchId = $request->input('branch', session('selected_branch_id'));

        \Log::info('📊 Sales Analytics Page Accessed', [
            'period' => $period,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'branchId' => $branchId
        ]);

        $data = $this->salesAnalyticsService->getSalesOverview($period, $startDate, $endDate, $branchId);

        \Log::info('📊 Sales Analytics Data Retrieved', [
            'total_sales' => $data['summary']['total_sales'] ?? 0,
            'total_orders' => $data['summary']['total_orders'] ?? 0,
            'unique_customers' => $data['summary']['unique_customers'] ?? 0
        ]);

        return view('admin.sales.overview', compact('data', 'period', 'startDate', 'endDate', 'branchId'));
    }
} 