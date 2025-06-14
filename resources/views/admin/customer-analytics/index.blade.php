@extends('layouts.admin')

@section('title', 'Customer Analytics')

@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.css">
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
            <div class="bg-blue-100 rounded-lg p-4 flex items-center gap-4">
                <div class="flex-shrink-0 bg-blue-500 text-white rounded-full p-3">
                    <i class="fas fa-users"></i>
                </div>
                <div>
                    <div class="text-2xl font-bold" id="total-customers">0</div>
                    <div class="text-sm text-gray-600">Total Customers</div>
                </div>
            </div>
            <div class="bg-green-100 rounded-lg p-4 flex items-center gap-4">
                <div class="flex-shrink-0 bg-green-500 text-white rounded-full p-3">
                    <i class="fas fa-user-check"></i>
                </div>
                <div>
                    <div class="text-2xl font-bold" id="active-customers">0</div>
                    <div class="text-sm text-gray-600">Active Customers (30d)</div>
                </div>
            </div>
            <div class="bg-yellow-100 rounded-lg p-4 flex items-center gap-4">
                <div class="flex-shrink-0 bg-yellow-500 text-white rounded-full p-3">
                    <i class="fas fa-shopping-cart"></i>
                </div>
                <div>
                    <div class="text-2xl font-bold" id="avg-order-value">$0</div>
                    <div class="text-sm text-gray-600">Average Order Value</div>
                </div>
            </div>
            <div class="bg-indigo-100 rounded-lg p-4 flex items-center gap-4">
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
            <div class="bg-blue-50 rounded-lg p-4 flex flex-col items-center">
                <div class="text-lg font-semibold">Customer Lifetime Value</div>
                <div class="text-2xl font-bold mt-2" id="customer-lifetime-value">$0</div>
            </div>
            <div class="bg-green-50 rounded-lg p-4 flex flex-col items-center">
                <div class="text-lg font-semibold">Purchase Frequency</div>
                <div class="text-2xl font-bold mt-2" id="purchase-frequency">0</div>
                <div class="text-xs text-gray-500">Orders per Customer</div>
            </div>
            <div class="bg-yellow-50 rounded-lg p-4 flex flex-col items-center">
                <div class="text-lg font-semibold">Avg. Customer Lifespan</div>
                <div class="text-2xl font-bold mt-2" id="customer-lifespan">0</div>
                <div class="text-xs text-gray-500">Months</div>
            </div>
            <div class="bg-indigo-50 rounded-lg p-4 flex flex-col items-center">
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

        <!-- Charts Row -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-6">
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold mb-4">Customer Segments</h3>
                <canvas id="segments-chart" class="w-full h-72"></canvas>
            </div>
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold mb-4">Churn Risk Distribution</h3>
                <canvas id="churn-chart" class="w-full h-72"></canvas>
            </div>
        </div>

        <!-- Customer Segments Table -->
        <div class="bg-white rounded-lg shadow p-6 mt-6">
            <h3 class="text-lg font-semibold mb-4">Customer Segments Details</h3>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm text-left text-gray-700">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="px-4 py-2">Segment</th>
                            <th class="px-4 py-2">Customers</th>
                            <th class="px-4 py-2">Total Revenue</th>
                            <th class="px-4 py-2">Avg. Order Value</th>
                            <th class="px-4 py-2">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="segments-table">
                        <!-- Will be populated by JavaScript -->
                    </tbody>
                </table>
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

        fetch(`/api/customer-analytics?start_date=${startDate}&end_date=${endDate}&branch=${branchId}`, {
            credentials: 'same-origin',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => {
            const contentType = response.headers.get('content-type');
            if (contentType && contentType.includes('application/json')) {
                return response.json();
            } else {
                throw new Error('Not authenticated or received HTML instead of JSON.');
            }
        })
        .then(data => {
            // Update summary cards
            document.getElementById('total-customers').textContent = data.behavior_metrics.total_customers;
            document.getElementById('active-customers').textContent = data.behavior_metrics.active_customers_30d;
            document.getElementById('avg-order-value').textContent = `$${data.behavior_metrics.average_order_value}`;
            document.getElementById('retention-rate').textContent = `${data.behavior_metrics.retention_rate_30d}%`;

            // Update advanced metrics
            document.getElementById('customer-lifetime-value').textContent = `$${data.advanced_metrics.clv}`;
            document.getElementById('purchase-frequency').textContent = data.advanced_metrics.purchase_frequency;
            document.getElementById('customer-lifespan').textContent = data.advanced_metrics.customer_lifespan;
            document.getElementById('segment-suggestions').textContent = data.ai_suggestions.length;

            // Update journey map
            document.getElementById('new-customers').textContent = data.journey_map.new;
            document.getElementById('new-to-regular').textContent = `${data.journey_map.conversion_rates.new_to_regular}%`;
            document.getElementById('regular-customers').textContent = data.journey_map.regular;
            document.getElementById('regular-to-loyal').textContent = `${data.journey_map.conversion_rates.regular_to_loyal}%`;
            document.getElementById('loyal-customers').textContent = data.journey_map.loyal;
            document.getElementById('loyal-to-vip').textContent = `${data.journey_map.conversion_rates.loyal_to_vip}%`;
            document.getElementById('vip-customers').textContent = data.journey_map.vip;
            document.getElementById('churned-customers').textContent = data.journey_map.churned;

            // Update charts
            updateSegmentsChart(data.segments);
            updateChurnChart(data.churn_risk);

            // Update tables
            updateSegmentsTable(data.segments);
            updateHighRiskTable(data.churn_risk.high_risk);
        })
        .catch(error => console.error('Error fetching analytics:', error));
    }

    function updateSegmentsTable(segments) {
        const tableBody = document.getElementById('segments-table');
        tableBody.innerHTML = '';

        const segmentTypes = [
            { key: 'vip', label: 'VIP' },
            { key: 'loyal', label: 'Loyal' },
            { key: 'regular', label: 'Regular' },
            { key: 'at_risk', label: 'At Risk' },
            { key: 'inactive', label: 'Inactive' }
        ];

        segmentTypes.forEach(segment => {
            const segmentData = segments[segment.key];
            const customerCount = typeof segmentData === 'number' ? segmentData : 
                                Array.isArray(segmentData) ? segmentData.length : 0;
            
            // For now, we'll use placeholder values for revenue and avg order value
            // These should be calculated on the backend and included in the API response
            const totalRevenue = 0;
            const avgOrderValue = 0;

            const row = document.createElement('tr');
            row.innerHTML = `
                <td class="px-4 py-2">${segment.label}</td>
                <td class="px-4 py-2">${customerCount}</td>
                <td class="px-4 py-2">$${totalRevenue.toFixed(2)}</td>
                <td class="px-4 py-2">$${avgOrderValue.toFixed(2)}</td>
                <td class="px-4 py-2">
                    <button class="text-blue-600 hover:text-blue-800" onclick="showSegmentDetails('${segment.key}')">
                        <i class="fas fa-eye"></i> View
                    </button>
                </td>
            `;
            tableBody.appendChild(row);
        });
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

    function showSegmentDetails(segmentKey) {
        // TODO: Implement segment details view
        console.log('Show details for segment:', segmentKey);
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

    // Call updateAnalytics when the page loads
    document.addEventListener('DOMContentLoaded', updateAnalytics);
</script>
@endpush 