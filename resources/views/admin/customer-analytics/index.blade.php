@extends('layouts.admin')

@section('title', 'Customer Analytics')

@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.css">
<style>
    .modal {
        position: fixed !important;
        z-index: 9999 !important;
        left: 0 !important;
        top: 0 !important;
        width: 100% !important;
        height: 100% !important;
        background-color: rgba(0,0,0,0.5) !important;
        display: block !important;
        visibility: visible !important;
        opacity: 1 !important;
    }
    
    .modal-content {
        background-color: white !important;
        margin: 5% auto !important;
        padding: 20px !important;
        border-radius: 8px !important;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1) !important;
        position: relative !important;
        z-index: 10000 !important;
    }
    
    .trend-analysis h5 {
        font-weight: bold;
        margin-top: 15px;
        margin-bottom: 8px;
        color: #374151;
    }
    
    .trend-analysis ul {
        margin-bottom: 15px;
    }
    
    .trend-analysis li {
        margin-bottom: 5px;
    }

    .modal-dialog {
        margin: 1.75rem auto;
        max-width: 800px;
    }

    .modal-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 1rem;
        border-bottom: 1px solid #dee2e6;
    }

    .modal-body {
        position: relative;
        padding: 1rem;
        max-height: calc(100vh - 210px);
        overflow-y: auto;
    }

    .modal-footer {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        padding: 1rem;
        border-top: 1px solid #dee2e6;
    }

    .btn-close {
        padding: 1rem;
        margin: -1rem -1rem -1rem auto;
        background: transparent;
        border: 0;
        font-size: 1.5rem;
        cursor: pointer;
    }

    .trend-analysis h5:first-child {
        margin-top: 0;
    }

    .campaign-suggestions .card {
        border: 1px solid #dee2e6;
        border-radius: 0.3rem;
    }

    .campaign-suggestions .card-header {
        background-color: #f8f9fa;
        padding: 0.75rem 1rem;
    }

    .campaign-suggestions .card-body {
        padding: 1rem;
    }

    .campaign-item {
        padding: 1rem;
        border: 1px solid #dee2e6;
        border-radius: 0.3rem;
        margin-bottom: 1rem;
    }

    .campaign-item:last-child {
        margin-bottom: 0;
    }

    .campaign-item h6 {
        margin-bottom: 0.5rem;
        font-weight: 600;
    }

    .campaign-item ul {
        margin-top: 0.5rem;
        margin-bottom: 0;
    }

    .campaign-item li {
        margin-bottom: 0.25rem;
    }

    .campaign-item li:last-child {
        margin-bottom: 0;
    }

</style>
@endpush

@section('content')
<div class="w-full px-4 py-6 mx-auto max-w-7xl">
    <!-- AI Assistant Sidebar -->
    

    <div class="flex flex-col gap-6">
        <div class="dashboard-header bg-white rounded-lg shadow p-6 flex flex-col md:flex-row md:items-center md:justify-between">
            <h3 class="text-xl font-semibold mb-4 md:mb-0">Customer Analytics Dashboard</h3>
            <div class="flex gap-2 items-center">
                <input type="date" id="start_date" class="border rounded px-3 py-2 text-sm" value="{{ request('start_date', now()->subMonths(3)->format('Y-m-d')) }}">
                <input type="date" id="end_date" class="border rounded px-3 py-2 text-sm" value="{{ request('end_date', now()->format('Y-m-d')) }}">
                <button class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded text-sm font-medium" onclick="updateAnalytics()">Update</button>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-blue-100 rounded-lg p-4 flex items-center gap-4 relative">
                <div class="loading-indicator hidden absolute inset-0 bg-white bg-opacity-75 flex items-center justify-center">
                    <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-500"></div>
                </div>
                <div class="flex-shrink-0 bg-blue-500 text-white rounded-full p-3">
                    <i class="fas fa-users"></i>
                </div>
                <div>
                    <div class="text-2xl font-bold" id="total-customers">0</div>
                    <div class="text-sm text-gray-600">Total Customers</div>
                </div>
            </div>
            <div class="bg-green-100 rounded-lg p-4 flex items-center gap-4 relative">
                <div class="loading-indicator hidden absolute inset-0 bg-white bg-opacity-75 flex items-center justify-center">
                    <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-green-500"></div>
                </div>
                <div class="flex-shrink-0 bg-green-500 text-white rounded-full p-3">
                    <i class="fas fa-user-check"></i>
                </div>
                <div>
                    <div class="text-2xl font-bold" id="active-customers">0</div>
                    <div class="text-sm text-gray-600">Active Customers (30d)</div>
                </div>
            </div>
            <div class="bg-yellow-100 rounded-lg p-4 flex items-center gap-4 relative">
                <div class="loading-indicator hidden absolute inset-0 bg-white bg-opacity-75 flex items-center justify-center">
                    <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-yellow-500"></div>
                </div>
                <div class="flex-shrink-0 bg-yellow-500 text-white rounded-full p-3">
                    <i class="fas fa-shopping-cart"></i>
                </div>
                <div>
                    <div class="text-2xl font-bold" id="avg-order-value">$0</div>
                    <div class="text-sm text-gray-600">Average Order Value</div>
                </div>
            </div>
            <div class="bg-indigo-100 rounded-lg p-4 flex items-center gap-4 relative">
                <div class="loading-indicator hidden absolute inset-0 bg-white bg-opacity-75 flex items-center justify-center">
                    <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-500"></div>
                </div>
                <div class="flex-shrink-0 bg-indigo-500 text-white rounded-full p-3">
                    <i class="fas fa-redo"></i>
                </div>
                <div>
                    <div class="text-2xl font-bold" id="retention-rate">0%</div>
                    <div class="text-sm text-gray-600">Retention Rate</div>
                </div>
            </div>
        </div>

        <!-- Advanced Metrics -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-blue-50 rounded-lg p-4 flex flex-col items-center relative">
                <div class="loading-indicator hidden absolute inset-0 bg-white bg-opacity-75 flex items-center justify-center">
                    <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-500"></div>
                </div>
                <div class="text-lg font-semibold">Customer Lifetime Value</div>
                <div class="text-2xl font-bold mt-2" id="customer-lifetime-value">$0</div>
            </div>
            <div class="bg-green-50 rounded-lg p-4 flex flex-col items-center relative">
                <div class="loading-indicator hidden absolute inset-0 bg-white bg-opacity-75 flex items-center justify-center">
                    <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-green-500"></div>
                </div>
                <div class="text-lg font-semibold">Purchase Frequency</div>
                <div class="text-2xl font-bold mt-2" id="purchase-frequency">0</div>
                <div class="text-xs text-gray-500">Orders per Customer</div>
            </div>
            <div class="bg-yellow-50 rounded-lg p-4 flex flex-col items-center relative">
                <div class="loading-indicator hidden absolute inset-0 bg-white bg-opacity-75 flex items-center justify-center">
                    <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-yellow-500"></div>
                </div>
                <div class="text-lg font-semibold">Avg. Customer Lifespan</div>
                <div class="text-2xl font-bold mt-2" id="customer-lifespan">0</div>
                <div class="text-xs text-gray-500">Months</div>
            </div>
            <div class="bg-indigo-50 rounded-lg p-4 flex flex-col items-center relative">
                <div class="loading-indicator hidden absolute inset-0 bg-white bg-opacity-75 flex items-center justify-center">
                    <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-500"></div>
                </div>
                <div class="text-lg font-semibold">AI Segment Suggestions</div>
                <div class="text-2xl font-bold mt-2" id="segment-suggestions">0</div>
                <div class="text-xs text-gray-500">New Segments Found</div>
            </div>
        </div>

        <!-- Revenue and Orders Trend Buttons -->
        <div class="flex space-x-4 mb-6">
            <button onclick="explainTrend('revenue')" class="flex-1 bg-white rounded-lg shadow p-4 hover:shadow-md transition-shadow">
                <div class="flex justify-between items-center mb-2">
                    <h3 class="text-lg font-semibold">Revenue Trend</h3>
                    <span class="text-sm text-blue-600 hover:text-blue-800">
                        <i class="fas fa-question-circle"></i> Why?
                    </span>
                </div>
                <div class="relative h-32">
                    <canvas id="revenueChart"></canvas>
                </div>
            </button>

            <button onclick="explainTrend('orders')" class="flex-1 bg-white rounded-lg shadow p-4 hover:shadow-md transition-shadow">
                <div class="flex justify-between items-center mb-2">
                    <h3 class="text-lg font-semibold">Orders Trend</h3>
                    <span class="text-sm text-blue-600 hover:text-blue-800">
                        <i class="fas fa-question-circle"></i> Why?
                    </span>
                </div>
                <div class="relative h-32">
                    <canvas id="ordersChart"></canvas>
                </div>
            </button>
        </div>

        <!-- Segment Evolution Chart -->
        <div class="bg-white rounded-lg shadow p-6 mt-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold">Segment Evolution</h3>
                <div class="flex space-x-2">
                    <select id="segmentTimeRange" class="border rounded px-3 py-2 text-sm" onchange="updateSegmentEvolution()">
                        <option value="3">Last 3 Months</option>
                        <option value="6">Last 6 Months</option>
                        <option value="12">Last 12 Months</option>
                    </select>
                </div>
            </div>
            <div class="relative h-96">
                <canvas id="segmentEvolutionChart"></canvas>
            </div>
        </div>

        <!-- Customer Journey Map -->
        <div class="bg-white rounded-lg shadow p-6 mt-6">
            <h3 class="text-lg font-semibold mb-4">Customer Journey Map</h3>
            <div class="flex flex-wrap gap-6 items-center justify-between">
                <div class="flex flex-col items-center">
                    <div class="text-base font-semibold">New</div>
                    <div class="text-2xl font-bold" id="new-customers">0</div>
                    <div class="text-xs text-gray-500" id="new-to-regular">0%</div>
                </div>
                <div class="text-2xl text-gray-400">→</div>
                <div class="flex flex-col items-center">
                    <div class="text-base font-semibold">Regular</div>
                    <div class="text-2xl font-bold" id="regular-customers">0</div>
                    <div class="text-xs text-gray-500" id="regular-to-loyal">0%</div>
                </div>
                <div class="text-2xl text-gray-400">→</div>
                <div class="flex flex-col items-center">
                    <div class="text-base font-semibold">Loyal</div>
                    <div class="text-2xl font-bold" id="loyal-customers">0</div>
                    <div class="text-xs text-gray-500" id="loyal-to-vip">0%</div>
                </div>
                <div class="text-2xl text-gray-400">→</div>
                <div class="flex flex-col items-center">
                    <div class="text-base font-semibold">VIP</div>
                    <div class="text-2xl font-bold" id="vip-customers">0</div>
                </div>
                <div class="text-2xl text-gray-400">→</div>
                <div class="flex flex-col items-center">
                    <div class="text-base font-semibold">Churned</div>
                    <div class="text-2xl font-bold" id="churned-customers">0</div>
                </div>
            </div>
        </div>

        <!-- AI Segment Suggestions -->
        <div class="bg-white rounded-lg shadow p-6 mt-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold">AI-Powered Segment Suggestions</h3>
                <button class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded text-sm font-medium" onclick="generateSegmentSuggestions()">
                    <i class="fas fa-magic"></i> Generate Suggestions
                </button>
            </div>
            <div id="segment-suggestions-list"></div>
        </div>

        <!-- Segment CLV Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mt-6">
            <div class="bg-purple-50 rounded-lg p-4">
                <div class="text-lg font-semibold text-purple-700">VIP Segment CLV</div>
                <div class="text-2xl font-bold mt-2" id="vip-clv">$0</div>
                <div class="text-xs text-gray-500">Lifetime Value</div>
            </div>
            <div class="bg-blue-50 rounded-lg p-4">
                <div class="text-lg font-semibold text-blue-700">Loyal Segment CLV</div>
                <div class="text-2xl font-bold mt-2" id="loyal-clv">$0</div>
                <div class="text-xs text-gray-500">Lifetime Value</div>
            </div>
            <div class="bg-green-50 rounded-lg p-4">
                <div class="text-lg font-semibold text-green-700">Regular Segment CLV</div>
                <div class="text-2xl font-bold mt-2" id="regular-clv">$0</div>
                <div class="text-xs text-gray-500">Lifetime Value</div>
            </div>
            <div class="bg-yellow-50 rounded-lg p-4">
                <div class="text-lg font-semibold text-yellow-700">New Segment CLV</div>
                <div class="text-2xl font-bold mt-2" id="new-clv">$0</div>
                <div class="text-xs text-gray-500">Lifetime Value</div>
            </div>
        </div>

        <!-- Customer Segments Table with Risk Badges -->
        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold">Customer Segments</h3>
            </div>
            <!-- Add chart canvas -->
            <div class="mb-6">
                <canvas id="segments-chart"></canvas>
            </div>
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Segment</th>
                            <th>Customers</th>
                            <th>Avg. Order Value</th>
                            <th>CLV</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="segments-table">
                        <!-- Table rows will be dynamically inserted here -->
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Churn Risk Analysis -->
        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold">Churn Risk Analysis</h3>
            </div>
            <!-- Add churn chart canvas -->
            <div class="mb-6">
                <canvas id="churn-chart"></canvas>
            </div>
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Customer ID</th>
                            <th>Last Order</th>
                            <th>Total Spent</th>
                            <th>Risk Score</th>
                            <th>Sentiment</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="high-risk-table">
                        <!-- Will be populated by JavaScript -->
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Campaign Modal -->
        <div id="campaignModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full">
            <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-medium text-gray-900" id="campaignSegmentTitle">Campaign for Segment</h3>
                    <button type="button" class="text-gray-400 hover:text-gray-500" id="closeCampaignModal">
                        <span class="sr-only">Close</span>
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <div class="mt-2">
                    <div class="mb-4">
                        <label for="campaignType" class="block text-sm font-medium text-gray-700">Campaign Type</label>
                        <select id="campaignType" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="retention">Retention</option>
                            <option value="acquisition">Acquisition</option>
                            <option value="loyalty">Loyalty</option>
                        </select>
                    </div>
                    <input type="hidden" id="campaignSegment">
                    <div id="campaignSuggestions" class="mt-4"></div>
                </div>
                <div class="mt-4 flex justify-end">
                    <button type="button" class="inline-flex justify-center px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500" id="generateCampaign">
                        Generate Campaign
                    </button>
                </div>
            </div>
        </div>

        <!-- Trend Analysis Modal -->
        <div id="trendAnalysisModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full">
            <div class="relative top-20 mx-auto p-5 border w-3/4 shadow-lg rounded-md bg-white">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-medium text-gray-900" id="trendSegmentTitle">Trend Analysis for Segment</h3>
                    <button type="button" class="text-gray-400 hover:text-gray-500" id="closeTrendModal">
                        <span class="sr-only">Close</span>
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <div class="mt-2">
                    <div id="trendAnalysisContent" class="space-y-6">
                        <!-- Monthly Metrics -->
                        <div class="bg-white shadow overflow-hidden sm:rounded-lg">
                            <div class="px-4 py-5 sm:px-6">
                                <h3 class="text-lg leading-6 font-medium text-gray-900">Monthly Metrics</h3>
                            </div>
                            <div class="border-t border-gray-200">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Month</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">New Customers</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Orders</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Avg Order Value</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Revenue</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200" id="monthlyMetricsBody"></tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Customer Growth -->
                        <div class="bg-white shadow overflow-hidden sm:rounded-lg">
                            <div class="px-4 py-5 sm:px-6">
                                <h3 class="text-lg leading-6 font-medium text-gray-900">Customer Growth</h3>
                            </div>
                            <div class="border-t border-gray-200">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Month</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Customers</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">New Customers</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Active Customers</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200" id="customerGrowthBody"></tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Revenue Trends -->
                        <div class="bg-white shadow overflow-hidden sm:rounded-lg">
                            <div class="px-4 py-5 sm:px-6">
                                <h3 class="text-lg leading-6 font-medium text-gray-900">Revenue Trends</h3>
                            </div>
                            <div class="border-t border-gray-200">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Month</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Revenue</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Avg Order Value</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200" id="revenueTrendsBody"></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- High Risk Customers -->
        <div class="bg-white rounded-lg shadow p-6 mt-6">
            <h3 class="text-lg font-semibold mb-4">High Risk Customers</h3>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm text-left text-gray-700">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="px-4 py-2">Customer ID</th>
                            <th class="px-4 py-2">Last Order</th>
                            <th class="px-4 py-2">Total Spent</th>
                            <th class="px-4 py-2">Risk Score</th>
                            <th class="px-4 py-2">Sentiment</th>
                            <th class="px-4 py-2">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="high-risk-table">
                        <!-- Will be populated by JavaScript -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Retention Campaign Modal (Tailwind) -->
<div id="retention-campaign-modal" class="fixed inset-0 z-50 hidden bg-black bg-opacity-40 flex items-center justify-center">
    <div class="bg-white rounded-lg shadow-lg p-6 w-full max-w-lg">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold">Retention Campaign</h3>
            <button class="text-gray-500 hover:text-gray-700" onclick="closeRetentionCampaignModal()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div id="retention-campaign-content"></div>
    </div>
</div>

<!-- Journey Analyzer Modal -->
<div id="journeyAnalyzerModal" class="modal">
    <div class="modal-content max-w-4xl">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-xl font-bold">Customer Journey Analysis</h2>
            <button onclick="closeJourneyAnalyzerModal()" class="text-gray-500 hover:text-gray-700">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="loading-indicator hidden absolute inset-0 bg-white bg-opacity-75 flex items-center justify-center">
            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-yellow-500"></div>
        </div>
        <div class="mb-4">
            <div class="flex space-x-4">
                <button onclick="analyzeJourney('all')" class="px-4 py-2 bg-yellow-500 text-white rounded hover:bg-yellow-600">
                    All Customers
                </button>
                <button onclick="analyzeJourney('vip')" class="px-4 py-2 bg-yellow-500 text-white rounded hover:bg-yellow-600">
                    VIP Customers
                </button>
                <button onclick="analyzeJourney('at-risk')" class="px-4 py-2 bg-yellow-500 text-white rounded hover:bg-yellow-600">
                    At-Risk Customers
                </button>
            </div>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="bg-white p-4 rounded-lg shadow">
                <h3 class="text-lg font-semibold mb-2">Journey Funnel</h3>
                <div class="relative h-64">
                    <canvas id="journeyFunnelChart"></canvas>
                </div>
            </div>
            <div class="bg-white p-4 rounded-lg shadow">
                <h3 class="text-lg font-semibold mb-2">Drop-off Analysis</h3>
                <div id="dropoffAnalysis" class="space-y-4">
                    <div class="animate-pulse">
                        <div class="h-4 bg-gray-200 rounded w-3/4 mb-2"></div>
                        <div class="h-4 bg-gray-200 rounded w-1/2"></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="mt-4 bg-white p-4 rounded-lg shadow">
            <h3 class="text-lg font-semibold mb-2">AI Insights & Recommendations</h3>
            <div id="journeyInsights" class="space-y-4">
                <div class="animate-pulse">
                    <div class="h-4 bg-gray-200 rounded w-3/4 mb-2"></div>
                    <div class="h-4 bg-gray-200 rounded w-1/2"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Trend Explanation Modal - UNUSED (removed)
<div id="trendExplanationModal" class="modal">
    <div class="modal-content max-w-2xl">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-xl font-bold">Trend Analysis</h2>
            <button onclick="closeTrendExplanationModal()" class="text-gray-500 hover:text-gray-700">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="space-y-4">
            <div class="bg-white p-4 rounded-lg shadow">
                <h3 class="text-lg font-semibold mb-2">Trend Overview</h3>
                <div id="trendOverview" class="text-gray-600">
                    <div class="animate-pulse">
                        <div class="h-4 bg-gray-200 rounded w-3/4 mb-2"></div>
                        <div class="h-4 bg-gray-200 rounded w-1/2"></div>
                    </div>
                </div>
            </div>
            <div class="bg-white p-4 rounded-lg shadow">
                <h3 class="text-lg font-semibold mb-2">Key Factors</h3>
                <div id="trendFactors" class="space-y-3">
                    <div class="animate-pulse">
                        <div class="h-4 bg-gray-200 rounded w-3/4 mb-2"></div>
                        <div class="h-4 bg-gray-200 rounded w-1/2"></div>
                    </div>
                </div>
            </div>
            <div class="bg-white p-4 rounded-lg shadow">
                <h3 class="text-lg font-semibold mb-2">Recommendations</h3>
                <div id="trendRecommendations" class="space-y-3">
                    <div class="animate-pulse">
                        <div class="h-4 bg-gray-200 rounded w-3/4 mb-2"></div>
                        <div class="h-4 bg-gray-200 rounded w-1/2"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
-->

@endsection

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
<script>
    // Set branchId as a global variable
    window.branchId = {{ request('branch', 1) }};

    // Initialize chart variables
    let segmentsChart = null;
    let churnChart = null;

    function updateSegmentsChart(segments) {
        const ctx = document.getElementById('segments-chart').getContext('2d');
        
        // Destroy existing chart if it exists
        if (segmentsChart) {
            segmentsChart.destroy();
        }

        segmentsChart = new Chart(ctx, {
            type: 'pie',
            data: {
                labels: ['VIP', 'Loyal', 'Regular', 'At Risk', 'Inactive'],
                datasets: [{
                    data: [
                        segments.vip.length,
                        segments.loyal.length,
                        segments.regular.length,
                        segments.at_risk.length,
                        segments.inactive.length
                    ],
                    backgroundColor: [
                        '#4F46E5', // Indigo
                        '#10B981', // Green
                        '#F59E0B', // Yellow
                        '#EF4444', // Red
                        '#6B7280'  // Gray
                    ]
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    }

    function updateChurnChart(churnRisk) {
        const ctx = document.getElementById('churn-chart').getContext('2d');
        
        // Destroy existing chart if it exists
        if (churnChart) {
            churnChart.destroy();
        }

        churnChart = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['High Risk', 'Medium Risk', 'Low Risk'],
                datasets: [{
                    data: [
                        churnRisk.high_risk.length,
                        churnRisk.medium_risk.length,
                        churnRisk.low_risk.length
                    ],
                    backgroundColor: [
                        '#EF4444', // Red
                        '#F59E0B', // Yellow
                        '#10B981'  // Green
                    ]
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    }

    function updateAnalytics() {
        const startDate = document.getElementById('start_date').value;
        const endDate = document.getElementById('end_date').value;
        const branchId = window.branchId || 1;

        // Show loading state
        document.querySelectorAll('.loading-indicator').forEach(el => el.classList.remove('hidden'));
        document.querySelectorAll('.error-message').forEach(el => el.classList.add('hidden'));

        fetch(`/api/customer-analytics?start_date=${startDate}&end_date=${endDate}&branch=${branchId}`, {
            credentials: 'same-origin',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            const contentType = response.headers.get('content-type');
            if (contentType && contentType.includes('application/json')) {
                return response.json();
            } else {
                throw new Error('Server returned non-JSON response');
            }
        })
        .then(data => {
            if (!data || !data.behavior_metrics) {
                throw new Error('Invalid data format received from server');
            }

            try {
            // Update summary cards
                document.getElementById('total-customers').textContent = data.behavior_metrics.total_customers || '0';
                document.getElementById('active-customers').textContent = data.behavior_metrics.active_customers_30d || '0';
                document.getElementById('avg-order-value').textContent = `$${data.behavior_metrics.average_order_value || '0.00'}`;
                document.getElementById('retention-rate').textContent = `${data.behavior_metrics.retention_rate_30d || '0'}%`;

            // Update advanced metrics
                document.getElementById('customer-lifetime-value').textContent = `$${data.advanced_metrics?.clv || '0.00'}`;
                document.getElementById('purchase-frequency').textContent = data.advanced_metrics?.purchase_frequency || '0';
                document.getElementById('customer-lifespan').textContent = data.advanced_metrics?.customer_lifespan || '0';
                document.getElementById('segment-suggestions').textContent = data.ai_suggestions?.length || '0';

            // Update journey map
                if (data.journey_map) {
                    document.getElementById('new-customers').textContent = data.journey_map.new || '0';
                    document.getElementById('new-to-regular').textContent = `${data.journey_map.conversion_rates?.new_to_regular || '0'}%`;
                    document.getElementById('regular-customers').textContent = data.journey_map.regular || '0';
                    document.getElementById('regular-to-loyal').textContent = `${data.journey_map.conversion_rates?.regular_to_loyal || '0'}%`;
                    document.getElementById('loyal-customers').textContent = data.journey_map.loyal || '0';
                    document.getElementById('loyal-to-vip').textContent = `${data.journey_map.conversion_rates?.loyal_to_vip || '0'}%`;
                    document.getElementById('vip-customers').textContent = data.journey_map.vip || '0';
                    document.getElementById('churned-customers').textContent = data.journey_map.churned || '0';
                }

                // Update charts and tables if data exists
                if (data.segments) {
            updateSegmentsChart(data.segments);
            updateSegmentsTable(data.segments);
                }
                if (data.churn_risk) {
                    updateChurnChart(data.churn_risk);
            updateHighRiskTable(data.churn_risk.high_risk);
                }
            } catch (error) {
                console.error('Error updating UI:', error);
                showError('Error updating dashboard: ' + error.message);
            }
        })
        .catch(error => {
            console.error('Error fetching analytics:', error);
            showError('Failed to load analytics data: ' + error.message);
        })
        .finally(() => {
            // Hide loading indicators
            document.querySelectorAll('.loading-indicator').forEach(el => el.classList.add('hidden'));
        });
    }

    function showError(message) {
        const errorDiv = document.createElement('div');
        errorDiv.className = 'error-message bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4';
        errorDiv.innerHTML = `
            <strong class="font-bold">Error!</strong>
            <span class="block sm:inline">${message}</span>
        `;
        
        // Insert error message at the top of the dashboard
        const dashboard = document.querySelector('.w-full.px-4.py-6.mx-auto.max-w-7xl');
        if (dashboard) {
            dashboard.insertBefore(errorDiv, dashboard.firstChild);
        }
    }

    function updateSegmentsTable(segments) {
        const tableBody = document.getElementById('segments-table');
        tableBody.innerHTML = '';

        // Convert segments object to array if needed
        const segmentArray = [
            { name: 'VIP', count: segments.vip?.length || 0, clv: segments.vip?.clv || 0, risk_level: 'Low', loyalty_level: 'VIP' },
            { name: 'Loyal', count: segments.loyal?.length || 0, clv: segments.loyal?.clv || 0, risk_level: 'Low', loyalty_level: 'Loyal' },
            { name: 'Regular', count: segments.regular?.length || 0, clv: segments.regular?.clv || 0, risk_level: 'Medium', loyalty_level: 'Regular' },
            { name: 'New', count: segments.new?.length || 0, clv: segments.new?.clv || 0, risk_level: 'Low', loyalty_level: 'New' },
            { name: 'At-Risk', count: segments.at_risk?.length || 0, clv: segments.at_risk?.clv || 0, risk_level: 'High', loyalty_level: 'Regular' }
        ];

        segmentArray.forEach(segment => {
            const row = document.createElement('tr');
            const riskBadge = getRiskBadge(segment.risk_level);
            const loyaltyBadge = getLoyaltyBadge(segment.loyalty_level);

            row.innerHTML = `
                <td class="px-4 py-2">
                    <div class="flex items-center">
                        <span class="font-medium">${segment.name}</span>
                        ${loyaltyBadge}
                    </div>
                </td>
                <td class="px-4 py-2">${segment.count}</td>
                <td class="px-4 py-2">$${segment.clv}</td>
                <td class="px-4 py-2">${riskBadge}</td>
                <td class="px-4 py-2">
                    <div class="flex space-x-2">
                        <button onclick="showCampaignModal('${segment.name.toLowerCase()}')" class="text-blue-600 hover:text-blue-800">
                        <i class="fas fa-bullhorn"></i> Campaign
                    </button>
                        <button onclick="showTrendAnalysis('${segment.name.toLowerCase()}')" class="text-green-600 hover:text-green-800">
                            <i class="fas fa-chart-line"></i> View Trends
                    </button>
                    </div>
                </td>
            `;
            tableBody.appendChild(row);
        });
    }

    function getRiskBadge(riskLevel) {
        const colors = {
            'Low': 'bg-green-100 text-green-800',
            'Medium': 'bg-yellow-100 text-yellow-800',
            'High': 'bg-red-100 text-red-800'
        };
        return `<span class="ml-2 px-2 py-1 rounded-full text-xs ${colors[riskLevel] || 'bg-gray-100 text-gray-800'}">${riskLevel} Risk</span>`;
    }

    function getLoyaltyBadge(loyaltyLevel) {
        const colors = {
            'VIP': 'bg-purple-100 text-purple-800',
            'Loyal': 'bg-blue-100 text-blue-800',
            'Regular': 'bg-green-100 text-green-800',
            'New': 'bg-gray-100 text-gray-800'
        };
        return `<span class="ml-2 px-2 py-1 rounded-full text-xs ${colors[loyaltyLevel] || 'bg-gray-100 text-gray-800'}">${loyaltyLevel}</span>`;
    }

    function showCampaignModal(segment) {
        const modal = document.getElementById('campaignModal');
        const segmentTitle = document.getElementById('campaignSegmentTitle');
        segmentTitle.textContent = segment.charAt(0).toUpperCase() + segment.slice(1) + ' Segment';
        
        // Set segment value in form
        document.getElementById('campaignSegment').value = segment;
        
        // Show loading state
        document.getElementById('campaignSuggestions').innerHTML = `
            <div class="flex justify-center items-center py-4">
                <svg class="animate-spin h-5 w-5 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span class="ml-2 text-sm text-gray-600">Loading campaign options...</span>
            </div>
        `;
        
        // Show modal
        modal.classList.remove('hidden');
        
        // Fetch campaign suggestions
        const startDate = document.getElementById('start_date').value;
        const endDate = document.getElementById('end_date').value;
        const branchId = window.branchId || 1;
        
        fetch(`/api/customer-analytics/generate-campaign`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({
                type: document.getElementById('campaignType').value,
                segment: segment,
                start_date: startDate,
                end_date: endDate,
                branch_id: branchId
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                document.getElementById('campaignSuggestions').innerHTML = `
                    <div class="bg-green-50 p-4 rounded-lg">
                        <h4 class="font-medium text-green-800">Campaign Suggestions</h4>
                        <div class="mt-2 space-y-4">
                            ${data.suggestions.map(suggestion => `
                                <div class="bg-white p-4 rounded-lg shadow-sm border border-green-100">
                                    <h5 class="font-medium text-gray-900 mb-2">${suggestion.title}</h5>
                                    <p class="text-gray-600 text-sm">${suggestion.description}</p>
                                    <div class="mt-2 flex items-center text-xs text-gray-500 space-x-4">
                                        <span class="flex items-center">
                                            <svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                            </svg>
                                            ${suggestion.target_customers} customers
                                        </span>
                                        <span class="flex items-center">
                                            <svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                                            </svg>
                                            ${suggestion.estimated_impact}
                                        </span>
                                    </div>
                                </div>
                            `).join('')}
                        </div>
                    </div>
                `;
            } else {
                document.getElementById('campaignSuggestions').innerHTML = `
                    <div class="bg-red-50 p-4 rounded-lg">
                        <h4 class="font-medium text-red-800">Error</h4>
                        <p class="mt-2 text-sm text-red-700">${data.message || 'Failed to generate campaign suggestions'}</p>
                    </div>
                `;
            }
        })
        .catch(error => {
            document.getElementById('campaignSuggestions').innerHTML = `
                <div class="bg-red-50 p-4 rounded-lg">
                    <h4 class="font-medium text-red-800">Error</h4>
                    <p class="mt-2 text-sm text-red-700">Failed to generate campaign suggestions: ${error.message}</p>
                </div>
            `;
        });
    }

    function showTrendAnalysis(segment) {
        const modal = document.getElementById('trendAnalysisModal');
        const segmentTitle = document.getElementById('trendSegmentTitle');
        segmentTitle.textContent = segment.charAt(0).toUpperCase() + segment.slice(1) + ' Segment';
        
        // Create or get the analysis content container
        let analysisContent = document.getElementById('trendAnalysisContent');
        if (!analysisContent) {
            analysisContent = document.createElement('div');
            analysisContent.id = 'trendAnalysisContent';
            analysisContent.className = 'space-y-6';
            modal.querySelector('.mt-2').appendChild(analysisContent);
        }
        
        // Show loading state
        analysisContent.innerHTML = `
            <div class="flex justify-center items-center py-8">
                <svg class="animate-spin h-8 w-8 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span class="ml-2 text-sm text-gray-600">Analyzing trends...</span>
            </div>
        `;
        
        // Show modal
        modal.classList.remove('hidden');
        
        // Fetch trend analysis
        const explainTrendUrl = @json(route('admin.analytics.explain-trend'));
        fetch(explainTrendUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({
                metric: segment,
                start_date: document.getElementById('start_date').value,
                end_date: document.getElementById('end_date').value
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                // Create the tables structure
                analysisContent.innerHTML = `
                    <!-- Monthly Metrics -->
                    <div class="bg-white shadow overflow-hidden sm:rounded-lg">
                        <div class="px-4 py-5 sm:px-6">
                            <h3 class="text-lg leading-6 font-medium text-gray-900">Monthly Metrics</h3>
                        </div>
                    </div>
                `;
            } else {
                throw new Error(data.message || 'Failed to analyze trends');
            }
        })
        .catch(error => {
            analysisContent.innerHTML = `
                <div class="text-red-600 p-4">
                    <p>Error analyzing trends: ${error.message}</p>
                </div>
            `;
        });
    }

    function exportSegment(segment) {
        window.location.href = `/api/customer-analytics/export/${segment}`;
    }

    function updateHighRiskTable(highRiskCustomers) {
        const tableBody = document.getElementById('high-risk-table');
        tableBody.innerHTML = '';

        // If highRiskCustomers is a number, it means there are no high risk customers
        if (typeof highRiskCustomers === 'number') {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td colspan="6" class="px-4 py-2 text-center text-gray-500">
                    No high risk customers found
                </td>
            `;
            tableBody.appendChild(row);
            return;
        }

        // If it's not an array, log a warning and return
        if (!Array.isArray(highRiskCustomers)) {
            console.warn('High risk customers data is not an array:', highRiskCustomers);
            const row = document.createElement('tr');
            row.innerHTML = `
                <td colspan="6" class="px-4 py-2 text-center text-gray-500">
                    Unable to load high risk customers data
                </td>
            `
        }
    }

    // Trend Explanation Functions - UNUSED (removed)
    /*
    function showTrendExplanation(type, data) {
        const modal = document.getElementById('trendExplanationModal');
        modal.style.display = 'block';
        
        // Show loading state
        document.getElementById('trendOverview').innerHTML = `
            <div class="animate-pulse">
                <div class="h-4 bg-gray-200 rounded w-3/4 mb-2"></div>
                <div class="h-4 bg-gray-200 rounded w-1/2"></div>
            </div>
        `;
        
        // Fetch trend explanation
        const explainTrendUrl = @json(route('admin.analytics.explain-trend'));
        fetch(explainTrendUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({
                metric: type,
                start_date: document.getElementById('start_date').value,
                end_date: document.getElementById('end_date').value
            })
        })
        .then(response => response.json())
        .then(result => {
            if (result.status === 'success') {
                updateTrendExplanation(result.explanation);
            } else {
                throw new Error(result.message);
            }
        })
        .catch(error => {
            document.getElementById('trendOverview').innerHTML = `
                <div class="text-red-600">
                    <p>Error analyzing trend: ${error.message}</p>
                </div>
            `;
        });
    }

    function closeTrendExplanationModal() {
        document.getElementById('trendExplanationModal').style.display = 'none';
    }

    function updateTrendExplanation(explanation) {
        // Update overview
        document.getElementById('trendOverview').innerHTML = `
            <p class="text-gray-600">${explanation.overview}</p>
        `;

        // Update factors
        const factorsContainer = document.getElementById('trendFactors');
        factorsContainer.innerHTML = explanation.factors.map(factor => `
            <div class="flex items-start space-x-2">
                <i class="fas fa-circle text-blue-500 mt-1 text-xs"></i>
                <div>
                    <p class="font-medium text-gray-800">${factor.title}</p>
                    <p class="text-sm text-gray-600">${factor.description}</p>
                </div>
            </div>
        `).join('');

        // Update recommendations
        const recommendationsContainer = document.getElementById('trendRecommendations');
        recommendationsContainer.innerHTML = explanation.recommendations.map(rec => `
            <div class="flex items-start space-x-2">
                <i class="fas fa-lightbulb text-yellow-500 mt-1"></i>
                <div>
                    <p class="font-medium text-gray-800">${rec.title}</p>
                    <p class="text-sm text-gray-600">${rec.description}</p>
                </div>
            </div>
        `).join('');
    }

    // Add "Why?" buttons to trend charts
    function addTrendExplanationButtons() {
        // Add to revenue trend chart
        const revenueChart = document.getElementById('revenueTrendChart');
        if (revenueChart) {
            const chartContainer = revenueChart.parentElement;
            const whyButton = document.createElement('button');
            whyButton.className = 'absolute top-2 right-2 px-2 py-1 text-sm bg-blue-500 text-white rounded hover:bg-blue-600';
            whyButton.innerHTML = '<i class="fas fa-question-circle"></i> Why?';
            whyButton.onclick = () => showTrendExplanation('revenue', window.revenueData);
            chartContainer.appendChild(whyButton);
        }

        // Add to order trend chart
        const orderChart = document.getElementById('orderTrendChart');
        if (orderChart) {
            const chartContainer = orderChart.parentElement;
            const whyButton = document.createElement('button');
            whyButton.className = 'absolute top-2 right-2 px-2 py-1 text-sm bg-blue-500 text-white rounded hover:bg-blue-600';
            whyButton.innerHTML = '<i class="fas fa-question-circle"></i> Why?';
            whyButton.onclick = () => showTrendExplanation('orders', window.orderData);
            chartContainer.appendChild(whyButton);
        }
    }

    // Call this after charts are initialized
    document.addEventListener('DOMContentLoaded', function() {
        // ... existing code ...
        addTrendExplanationButtons();
    });
    */

    // Journey Analyzer Functions
    let journeyFunnelChart = null;

    function showJourneyAnalyzerModal() {
        document.getElementById('journeyAnalyzerModal').style.display = 'block';
        analyzeJourney('all'); // Default to analyzing all customers
    }

    function closeJourneyAnalyzerModal() {
        document.getElementById('journeyAnalyzerModal').style.display = 'none';
        if (journeyFunnelChart) {
            journeyFunnelChart.destroy();
            journeyFunnelChart = null;
        }
    }

    async function analyzeJourney(segment) {
        const loadingIndicator = document.querySelector('#journeyAnalyzerModal .loading-indicator');
        if (loadingIndicator) loadingIndicator.classList.remove('hidden');

        try {
            const response = await fetch(`/admin/analytics/journey-analysis?segment=${segment}`);
            const data = await response.json();

            // Update funnel chart
            updateJourneyFunnelChart(data.funnel);

            // Update drop-off analysis
            updateDropoffAnalysis(data.dropoff);

            // Update AI insights
            updateJourneyInsights(data.insights);

        } catch (error) {
            console.error('Error analyzing journey:', error);
            alert('Failed to analyze customer journey. Please try again.');
        } finally {
            if (loadingIndicator) loadingIndicator.classList.add('hidden');
        }
    }

    function updateJourneyFunnelChart(funnelData) {
        const ctx = document.getElementById('journeyFunnelChart').getContext('2d');
        
        if (journeyFunnelChart) {
            journeyFunnelChart.destroy();
        }

        journeyFunnelChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: funnelData.stages,
                datasets: [{
                    label: 'Customers',
                    data: funnelData.values,
                    backgroundColor: 'rgba(234, 179, 8, 0.6)',
                    borderColor: 'rgb(234, 179, 8)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                },
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const value = context.raw;
                                const total = funnelData.values[0];
                                const percentage = ((value / total) * 100).toFixed(1);
                                return `${value} customers (${percentage}%)`;
                            }
                        }
                    }
                }
            }
        });
    }

    function updateDropoffAnalysis(dropoffData) {
        const container = document.getElementById('dropoffAnalysis');
        container.innerHTML = '';

        dropoffData.forEach(stage => {
            const stageElement = document.createElement('div');
            stageElement.className = 'p-3 bg-gray-50 rounded-lg';
            stageElement.innerHTML = `
                <div class="flex justify-between items-center mb-2">
                    <span class="font-medium">${stage.stage}</span>
                    <span class="text-red-600">-${stage.dropoff}%</span>
                </div>
                <p class="text-sm text-gray-600">${stage.reason}</p>
            `;
            container.appendChild(stageElement);
        });
    }

    function updateJourneyInsights(insights) {
        const container = document.getElementById('journeyInsights');
        container.innerHTML = '';

        insights.forEach(insight => {
            const insightElement = document.createElement('div');
            insightElement.className = 'p-3 bg-yellow-50 rounded-lg';
            insightElement.innerHTML = `
                <div class="flex items-start space-x-2">
                    <i class="fas fa-lightbulb text-yellow-500 mt-1"></i>
                    <div>
                        <p class="font-medium text-yellow-800">${insight.title}</p>
                        <p class="text-sm text-yellow-700">${insight.description}</p>
                        ${insight.recommendation ? `
                            <div class="mt-2 p-2 bg-white rounded border border-yellow-200">
                                <p class="text-sm font-medium text-yellow-800">Recommendation:</p>
                                <p class="text-sm text-yellow-700">${insight.recommendation}</p>
                            </div>
                        ` : ''}
                    </div>
                </div>
            `;
            container.appendChild(insightElement);
        });
    }

    // Add journey analyzer button to the dashboard
    document.addEventListener('DOMContentLoaded', function() {
        const sidebar = document.getElementById('aiAssistantSidebar');
        if (sidebar) {
            sidebar.classList.add('collapsed');
        }
    });

    // Segment Evolution Chart
    let segmentEvolutionChart = null;

    function initSegmentEvolutionChart() {
        const ctx = document.getElementById('segmentEvolutionChart').getContext('2d');
        
        if (segmentEvolutionChart) {
            segmentEvolutionChart.destroy();
        }

        segmentEvolutionChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: [],
                datasets: [
                    {
                        label: 'VIP Customers',
                        data: [],
                        borderColor: 'rgb(234, 179, 8)',
                        backgroundColor: 'rgba(234, 179, 8, 0.1)',
                        fill: true
                    },
                    {
                        label: 'Loyal Customers',
                        data: [],
                        borderColor: 'rgb(16, 185, 129)',
                        backgroundColor: 'rgba(16, 185, 129, 0.1)',
                        fill: true
                    },
                    {
                        label: 'Regular Customers',
                        data: [],
                        borderColor: 'rgb(59, 130, 246)',
                        backgroundColor: 'rgba(59, 130, 246, 0.1)',
                        fill: true
                    },
                    {
                        label: 'At-Risk Customers',
                        data: [],
                        borderColor: 'rgb(239, 68, 68)',
                        backgroundColor: 'rgba(239, 68, 68, 0.1)',
                        fill: true
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    intersect: false,
                    mode: 'index'
                },
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return `${context.dataset.label}: ${context.raw} customers`;
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Number of Customers'
                        }
                    },
                    x: {
                        title: {
                            display: true,
                            text: 'Month'
                        }
                    }
                }
            }
        });

        updateSegmentEvolution();
    }

    async function updateSegmentEvolution() {
        const months = document.getElementById('segmentTimeRange').value;
        const loadingIndicator = document.querySelector('#segmentEvolutionChart').closest('.bg-white').querySelector('.loading-indicator');
        if (loadingIndicator) loadingIndicator.classList.remove('hidden');

        try {
            const response = await fetch(`/admin/analytics/segment-evolution?months=${months}`);
            const data = await response.json();

            if (data.status === 'success') {
                segmentEvolutionChart.data.labels = data.labels;
                segmentEvolutionChart.data.datasets[0].data = data.vip;
                segmentEvolutionChart.data.datasets[1].data = data.loyal;
                segmentEvolutionChart.data.datasets[2].data = data.regular;
                segmentEvolutionChart.data.datasets[3].data = data.at_risk;
                segmentEvolutionChart.update();
            } else {
                console.error('Error fetching segment evolution data:', data.message);
            }
        } catch (error) {
            console.error('Error updating segment evolution:', error);
        } finally {
            if (loadingIndicator) loadingIndicator.classList.add('hidden');
        }
    }

    // Initialize charts when the page loads
    document.addEventListener('DOMContentLoaded', function() {
        // ... existing initialization code ...
        initSegmentEvolutionChart();
    });

    const explainTrendUrl = @json(route('admin.analytics.explain-trend'));
    async function explainTrend(metric) {
        console.log('explainTrend function called with metric:', metric);
        
        const startDate = document.getElementById('start_date').value;
        const endDate = document.getElementById('end_date').value;
        const branchId = window.branchId || 1;
        
        console.log('Parameters:', { startDate, endDate, branchId, explainTrendUrl });
        
        try {
            const response = await fetch(explainTrendUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    metric: metric,
                    start_date: startDate,
                    end_date: endDate,
                    branch_id: branchId
                })
            });

            console.log('Response status:', response.status);

            if (!response.ok) {
                const errorData = await response.json();
                throw new Error(errorData.message || `HTTP error! status: ${response.status}`);
            }

            const data = await response.json();
            console.log('Response data:', data);
            
            if (data.status === 'success') {
                // Show the explanation in a modal
                const modal = document.createElement('div');
                modal.style.cssText = `
                    position: fixed !important;
                    z-index: 9999 !important;
                    left: 0 !important;
                    top: 0 !important;
                    width: 100% !important;
                    height: 100% !important;
                    background-color: rgba(0,0,0,0.5) !important;
                    display: block !important;
                    visibility: visible !important;
                    opacity: 1 !important;
                `;
                
                const modalContent = `
                    <div style="
                        background-color: white !important;
                        margin: 10% auto !important;
                        padding: 16px !important;
                        border-radius: 8px !important;
                        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1) !important;
                        position: relative !important;
                        z-index: 10000 !important;
                        max-width: 400px !important;
                        width: 80% !important;
                    ">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                            <h2 style="font-size: 1.1rem; font-weight: bold;">Trend Analysis: ${metric.charAt(0).toUpperCase() + metric.slice(1)}</h2>
                            <button onclick="this.closest('div[style*=\\'position: fixed\\']').remove()" style="color: #6b7280; cursor: pointer; background: none; border: none; font-size: 1.2rem;">
                                ×
                            </button>
                        </div>
                        <div>
                            <h5 style="font-weight: bold; margin-top: 12px; margin-bottom: 6px; color: #374151; font-size: 0.9rem;">Key Insights</h5>
                            <p style="font-size: 0.9rem; margin-bottom: 8px;">${data.insights}</p>
                            
                            <h5 style="font-weight: bold; margin-top: 12px; margin-bottom: 6px; color: #374151; font-size: 0.9rem;">Contributing Factors</h5>
                            <ul style="list-style-type: disc; padding-left: 16px; margin-bottom: 12px; font-size: 0.9rem;">
                                ${data.factors.map(factor => `<li style="margin-bottom: 4px;">${factor}</li>`).join('')}
                            </ul>
                            
                            <h5 style="font-weight: bold; margin-top: 12px; margin-bottom: 6px; color: #374151; font-size: 0.9rem;">Recommendations</h5>
                            <ul style="list-style-type: disc; padding-left: 16px; margin-bottom: 12px; font-size: 0.9rem;">
                                ${data.recommendations.map(rec => `<li style="margin-bottom: 4px;">${rec}</li>`).join('')}
                            </ul>
                        </div>
                    </div>
                `;
                
                console.log('Modal content to be injected:', modalContent);
                modal.innerHTML = modalContent;
                console.log('Modal innerHTML after setting:', modal.innerHTML);
                
                // Add click handler to close modal when clicking outside
                modal.addEventListener('click', function(e) {
                    if (e.target === modal) {
                        modal.remove();
                    }
                });
                
                document.body.appendChild(modal);
                console.log('Modal created and appended to DOM');
                console.log('Modal element:', modal);
                console.log('Modal innerHTML after appending:', modal.innerHTML);
                console.log('Modal is in DOM:', document.body.contains(modal));
                console.log('Modal computed display:', window.getComputedStyle(modal).display);
                console.log('Modal computed visibility:', window.getComputedStyle(modal).visibility);
                console.log('Modal computed opacity:', window.getComputedStyle(modal).opacity);
                
                // Force the modal to be visible
                setTimeout(() => {
                    modal.style.display = 'block';
                    modal.style.visibility = 'visible';
                    modal.style.opacity = '1';
                    console.log('Modal forced to be visible');
                    console.log('Modal final computed display:', window.getComputedStyle(modal).display);
                }, 100);
            } else {
                throw new Error(data.message || 'Failed to analyze trend');
            }
        } catch (error) {
            console.error('Error explaining trend:', error);
            alert('Failed to analyze trend: ' + error.message);
        }
    }

    // AI Assistant Functions
    function toggleSidebar() {
        const sidebar = document.getElementById('aiAssistantSidebar');
        sidebar.classList.toggle('collapsed');
    }

    function askQuestion(question) {
        document.getElementById('userInput').value = question;
        handleSubmit(new Event('submit'));
    }

    async function handleSubmit(event) {
        event.preventDefault();
        const input = document.getElementById('userInput');
        const question = input.value.trim();
        
        if (!question) return;

        // Add user message to chat
        addMessage(question, 'user');
        input.value = '';

        try {
            const response = await fetch('/api/customer-analytics/ai-assistant', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    question: question,
                    context: {
                        start_date: document.getElementById('start_date').value,
                        end_date: document.getElementById('end_date').value,
                        branch_id: window.branchId || 1
                    }
                })
            });

            const data = await response.json();
            
            if (data.status === 'success') {
                addMessage(data.response, 'assistant', data.suggestions);
            } else {
                throw new Error(data.message || 'Failed to get response');
            }
        } catch (error) {
            addMessage('Sorry, I encountered an error. Please try again.', 'assistant');
            console.error('Error:', error);
        }
    }

    function addMessage(content, type, suggestions = null) {
        const chatContent = document.getElementById('chatContent');
        const messageDiv = document.createElement('div');
        messageDiv.className = `chat-message ${type}`;
        
        let messageHTML = `
            <div class="message-content text-gray-800">${content}</div>
            <div class="message-meta">${new Date().toLocaleTimeString()}</div>
        `;

        if (suggestions && type === 'assistant') {
            messageHTML += `
                <div class="mt-2 space-y-2">
                    ${suggestions.map(suggestion => `
                        <div class="suggestion-chip" onclick="askQuestion('${suggestion}')">
                            ${suggestion}
                        </div>
                    `).join('')}
                </div>
            `;
        }

        messageDiv.innerHTML = messageHTML;
        chatContent.appendChild(messageDiv);
        chatContent.scrollTop = chatContent.scrollHeight;
    }

    // AI Segment Suggestions Function
    async function generateSegmentSuggestions() {
        const startDate = document.getElementById('start_date').value;
        const endDate = document.getElementById('end_date').value;
        const branchId = window.branchId || 1;
        
        try {
            const response = await fetch(`/admin/analytics/segment-suggestions?start_date=${startDate}&end_date=${endDate}&branch_id=${branchId}`, {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            });

            const data = await response.json();
            
            if (data.status === 'success') {
                const suggestionsList = document.getElementById('segment-suggestions-list');
                suggestionsList.innerHTML = data.suggestions.map(suggestion => `
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-3">
                        <h4 class="font-semibold text-blue-800 mb-2">${suggestion.name || 'Segment Suggestion'}</h4>
                        <p class="text-blue-700 text-sm mb-2">${suggestion.description || 'No description available'}</p>
                        <div class="text-xs text-blue-600 mb-2">
                            <strong>Priority:</strong> ${suggestion.priority || 'Medium'}
                        </div>
                        <div class="text-xs text-blue-600 mb-2">
                            <strong>Customer Count:</strong> ${suggestion.customer_count || 0}
                        </div>
                        <div class="text-xs text-blue-600">
                            <strong>Potential Revenue:</strong> ${suggestion.potential_revenue || 'Not calculated'}
                        </div>
                    </div>
                `).join('');
            } else {
                throw new Error(data.message || 'Failed to generate suggestions');
            }
        } catch (error) {
            console.error('Error generating segment suggestions:', error);
            alert('Failed to generate suggestions: ' + error.message);
        }
    }

    // AI Assistant Functions
</script>
@endpush