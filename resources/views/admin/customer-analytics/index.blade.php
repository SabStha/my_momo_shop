@extends('layouts.admin')

@section('title', 'Customer Analytics')

@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.css">
<style>
    .modal {
        display: none;
        position: fixed;
        z-index: 1000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.5);
    }

    .modal-dialog {
        margin: 1.75rem auto;
        max-width: 800px;
    }

    .modal-content {
        position: relative;
        background-color: #fff;
        border-radius: 0.3rem;
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
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

    .trend-analysis h5 {
        margin-top: 1.5rem;
        margin-bottom: 1rem;
        font-weight: 600;
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
    <div class="flex flex-col gap-6">
        <div class="bg-white rounded-lg shadow p-6 flex flex-col md:flex-row md:items-center md:justify-between">
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
        fetch(`/api/customer-analytics/trend-analysis?segment=${segment}`)
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
                    `;

                    // Update Monthly Metrics
                    const monthlyMetricsBody = document.getElementById('monthlyMetricsBody');
                    monthlyMetricsBody.innerHTML = data.analysis.monthly_metrics.map(metric => `
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${metric.month}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${metric.new_customers}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${metric.total_orders}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">$${metric.avg_order_value.toFixed(2)}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">$${metric.total_revenue.toFixed(2)}</td>
                        </tr>
                    `).join('');

                    // Update Customer Growth
                    const customerGrowthBody = document.getElementById('customerGrowthBody');
                    customerGrowthBody.innerHTML = data.analysis.customer_growth.map(growth => `
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${growth.month}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${growth.total_customers}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${growth.new_customers}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${growth.active_customers}</td>
                        </tr>
                    `).join('');

                    // Update Revenue Trends
                    const revenueTrendsBody = document.getElementById('revenueTrendsBody');
                    revenueTrendsBody.innerHTML = data.analysis.revenue_trends.map(trend => `
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${trend.month}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">$${trend.total_revenue.toFixed(2)}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">$${trend.avg_order_value.toFixed(2)}</td>
                        </tr>
                    `).join('');
                } else {
                    analysisContent.innerHTML = `
                        <div class="bg-red-50 p-4 rounded-lg">
                            <h4 class="font-medium text-red-800">Error</h4>
                            <p class="mt-2 text-sm text-red-700">${data.message}</p>
                        </div>
                    `;
                }
            })
            .catch(error => {
                analysisContent.innerHTML = `
                    <div class="bg-red-50 p-4 rounded-lg">
                        <h4 class="font-medium text-red-800">Error</h4>
                        <p class="mt-2 text-sm text-red-700">Error analyzing trends: ${error.message}</p>
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
            `;
            tableBody.appendChild(row);
            return;
        }

        // If the array is empty, show a message
        if (highRiskCustomers.length === 0) {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td colspan="6" class="px-4 py-2 text-center text-gray-500">
                    No high risk customers found
                </td>
            `;
            tableBody.appendChild(row);
            return;
        }

        highRiskCustomers.forEach(customer => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td class="px-4 py-2">${customer.user_id || 'N/A'}</td>
                <td class="px-4 py-2">${customer.last_order_date ? new Date(customer.last_order_date).toLocaleDateString() : 'N/A'}</td>
                <td class="px-4 py-2">$${(customer.total_spent || 0).toFixed(2)}</td>
                <td class="px-4 py-2">${customer.risk_score || 'N/A'}</td>
                <td class="px-4 py-2">${customer.sentiment || 'Neutral'}</td>
                <td class="px-4 py-2">
                    <button class="text-blue-600 hover:text-blue-800" onclick="showRetentionCampaign(${customer.user_id || 0})">
                        <i class="fas fa-bullhorn"></i> Campaign
                    </button>
                </td>
            `;
            tableBody.appendChild(row);
        });
    }

    function showRetentionCampaign(customerId) {
        // TODO: Implement retention campaign modal
        console.log('Show retention campaign for customer:', customerId);
    }

    function generateSegmentSuggestions() {
        const startDate = document.getElementById('start_date').value;
        const endDate = document.getElementById('end_date').value;
        const branchId = window.branchId || 1;

        // Show loading state
        const suggestionsList = document.getElementById('segment-suggestions-list');
        suggestionsList.innerHTML = '<div class="text-center py-4"><i class="fas fa-spinner fa-spin"></i> Generating suggestions...</div>';

        fetch(`/api/customer-analytics/segment-suggestions?start_date=${startDate}&end_date=${endDate}&branch=${branchId}`, {
            credentials: 'same-origin',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'error') {
                throw new Error(data.message);
            }

            if (!data.data || data.data.length === 0) {
                suggestionsList.innerHTML = '<div class="text-center py-4 text-gray-500">No new segment suggestions found</div>';
                return;
            }

            // Display suggestions
            suggestionsList.innerHTML = data.data.map(suggestion => `
                <div class="bg-white border rounded-lg p-4 mb-4">
                    <div class="flex items-center justify-between mb-2">
                        <h4 class="text-lg font-semibold">${suggestion.name}</h4>
                        <span class="px-2 py-1 rounded text-sm ${suggestion.priority === 'high' ? 'bg-red-100 text-red-800' : suggestion.priority === 'medium' ? 'bg-yellow-100 text-yellow-800' : 'bg-green-100 text-green-800'}">
                            ${suggestion.priority} priority
                        </span>
                    </div>
                    <p class="text-gray-600 mb-2">${suggestion.description}</p>
                    <div class="flex items-center gap-4 text-sm text-gray-500">
                        <span><i class="fas fa-users"></i> ${suggestion.customer_count} customers</span>
                        <span><i class="fas fa-chart-line"></i> ${suggestion.potential_revenue} potential revenue</span>
                    </div>
                    <div class="mt-4 flex justify-end gap-2">
                        <button class="text-blue-600 hover:text-blue-800" onclick="viewSegmentDetails('${suggestion.id}')">
                            <i class="fas fa-eye"></i> View Details
                        </button>
                        <button class="text-green-600 hover:text-green-800" onclick="createSegment('${suggestion.id}')">
                            <i class="fas fa-plus"></i> Create Segment
                        </button>
                    </div>
                </div>
            `).join('');
        })
        .catch(error => {
            console.error('Error generating suggestions:', error);
            suggestionsList.innerHTML = `
                <div class="text-center py-4">
                    <div class="text-red-500 mb-2">Error generating suggestions</div>
                    <div class="text-sm text-gray-500">${error.message}</div>
                </div>
            `;
        });
    }

    function viewSegmentDetails(suggestionId) {
        // TODO: Implement segment details view
        console.log('View details for suggestion:', suggestionId);
    }

    function createSegment(suggestionId) {
        // TODO: Implement segment creation
        console.log('Create segment from suggestion:', suggestionId);
    }

    // Modal close handlers
    document.addEventListener('DOMContentLoaded', function() {
        const closeCampaignModal = document.getElementById('closeCampaignModal');
        const closeTrendModal = document.getElementById('closeTrendModal');
        const generateCampaign = document.getElementById('generateCampaign');

        if (closeCampaignModal) {
            closeCampaignModal.addEventListener('click', () => {
        document.getElementById('campaignModal').classList.add('hidden');
    });
        }

        if (closeTrendModal) {
            closeTrendModal.addEventListener('click', () => {
        document.getElementById('trendAnalysisModal').classList.add('hidden');
    });
        }

        if (generateCampaign) {
            generateCampaign.addEventListener('click', () => {
        const type = document.getElementById('campaignType').value;
        const segment = document.getElementById('campaignSegment').value;
                
                // Show loading state
                document.getElementById('campaignSuggestions').innerHTML = `
                    <div class="flex justify-center items-center py-4">
                        <svg class="animate-spin h-5 w-5 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span class="ml-2 text-sm text-gray-600">Generating campaign...</span>
                    </div>
                `;
        
        fetch('/api/customer-analytics/generate-campaign', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ type, segment })
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
    });
        }

    // Call updateAnalytics when the page loads
        updateAnalytics();
    });
</script>
@endpush 