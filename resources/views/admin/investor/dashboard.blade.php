@extends('layouts.admin')

@section('title', 'Investor Dashboard')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="bg-gradient-to-r from-blue-600 to-purple-600 rounded-lg shadow-lg p-6 text-white">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold">Investor Dashboard</h1>
                <p class="text-blue-100 mt-2">Comprehensive business performance overview for investors</p>
            </div>
            <div class="text-right">
                <div class="text-sm text-blue-100">Last Updated</div>
                <div class="font-semibold">{{ now()->format('M d, Y H:i') }}</div>
            </div>
        </div>
    </div>

    <!-- Financial Performance Overview -->
    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
        <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-green-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Total Revenue</p>
                    <p class="text-2xl font-bold text-gray-900">Rs {{ number_format($financialMetrics['total_revenue'], 2) }}</p>
                    <p class="text-sm {{ $financialMetrics['revenue_growth'] >= 0 ? 'text-green-600' : 'text-red-600' }}">
                        {{ $financialMetrics['revenue_growth'] >= 0 ? '+' : '' }}{{ number_format($financialMetrics['revenue_growth'], 1) }}% vs last year
                    </p>
                </div>
                <div class="p-3 bg-green-100 rounded-full">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-blue-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Net Profit</p>
                    <p class="text-2xl font-bold text-gray-900">Rs {{ number_format($financialMetrics['net_profit'], 2) }}</p>
                    <p class="text-sm text-gray-500">{{ number_format($financialMetrics['net_margin'], 1) }}% margin</p>
                </div>
                <div class="p-3 bg-blue-100 rounded-full">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-purple-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Active Customers</p>
                    <p class="text-2xl font-bold text-gray-900">{{ number_format($growthMetrics['active_customers']) }}</p>
                    <p class="text-sm {{ $growthMetrics['customer_growth'] >= 0 ? 'text-green-600' : 'text-red-600' }}">
                        {{ $growthMetrics['customer_growth'] >= 0 ? '+' : '' }}{{ number_format($growthMetrics['customer_growth'], 1) }}% growth
                    </p>
                </div>
                <div class="p-3 bg-purple-100 rounded-full">
                    <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-orange-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Estimated Valuation</p>
                    <p class="text-2xl font-bold text-gray-900">Rs {{ number_format($investmentMetrics['estimated_valuation'], 2) }}</p>
                    <p class="text-sm text-gray-500">{{ $investmentMetrics['revenue_multiple'] }}x revenue multiple</p>
                </div>
                <div class="p-3 bg-orange-100 rounded-full">
                    <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Detailed Metrics Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Financial Performance -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Financial Performance</h3>
            <div class="space-y-4">
                <div class="flex justify-between items-center">
                    <span class="text-gray-600">Gross Profit</span>
                    <span class="font-semibold">Rs {{ number_format($financialMetrics['gross_profit'], 2) }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-gray-600">Gross Margin</span>
                    <span class="font-semibold text-green-600">{{ number_format($financialMetrics['gross_margin'], 1) }}%</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-gray-600">Average Order Value</span>
                    <span class="font-semibold">Rs {{ number_format($financialMetrics['average_order_value'], 2) }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-gray-600">Total Orders</span>
                    <span class="font-semibold">{{ number_format($financialMetrics['total_orders']) }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-gray-600">Operating Expenses</span>
                    <span class="font-semibold">Rs {{ number_format($financialMetrics['operating_expenses'], 2) }}</span>
                </div>
            </div>
        </div>

        <!-- Business Growth -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Business Growth</h3>
            <div class="space-y-4">
                <div class="flex justify-between items-center">
                    <span class="text-gray-600">Customer Lifetime Value</span>
                    <span class="font-semibold">Rs {{ number_format($growthMetrics['customer_lifetime_value'], 2) }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-gray-600">Repeat Purchase Rate</span>
                    <span class="font-semibold text-blue-600">{{ number_format($growthMetrics['repeat_purchase_rate'], 1) }}%</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-gray-600">Total Branches</span>
                    <span class="font-semibold">{{ $growthMetrics['total_branches'] }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-gray-600">New Branches (12m)</span>
                    <span class="font-semibold text-green-600">{{ $growthMetrics['new_branches'] }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-gray-600">Market Penetration</span>
                    <span class="font-semibold text-purple-600">{{ number_format($investmentMetrics['market_penetration'], 2) }}%</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Operational Efficiency & Risk Assessment -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Operational Efficiency -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Operational Efficiency</h3>
            <div class="space-y-4">
                <div class="flex justify-between items-center">
                    <span class="text-gray-600">Inventory Turnover</span>
                    <span class="font-semibold">{{ number_format($operationalMetrics['inventory_turnover'], 2) }}x</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-gray-600">Revenue per Employee</span>
                    <span class="font-semibold">Rs {{ number_format($operationalMetrics['revenue_per_employee'], 2) }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-gray-600">Campaign ROI</span>
                    <span class="font-semibold {{ $operationalMetrics['campaign_roi'] >= 0 ? 'text-green-600' : 'text-red-600' }}">
                        {{ number_format($operationalMetrics['campaign_roi'], 1) }}%
                    </span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-gray-600">Digital Payment Adoption</span>
                    <span class="font-semibold text-blue-600">{{ number_format($operationalMetrics['digital_adoption'], 1) }}%</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-gray-600">Total Employees</span>
                    <span class="font-semibold">{{ $operationalMetrics['total_employees'] }}</span>
                </div>
            </div>
        </div>

        <!-- Risk Assessment -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Risk Assessment</h3>
            <div class="space-y-4">
                <div class="flex justify-between items-center">
                    <span class="text-gray-600">Customer Churn Risk</span>
                    <span class="font-semibold {{ $riskMetrics['churn_risk'] > 20 ? 'text-red-600' : 'text-green-600' }}">
                        {{ number_format($riskMetrics['churn_risk'], 1) }}%
                    </span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-gray-600">Inventory Risk</span>
                    <span class="font-semibold {{ $riskMetrics['inventory_risk'] > 15 ? 'text-red-600' : 'text-green-600' }}">
                        {{ number_format($riskMetrics['inventory_risk'], 1) }}%
                    </span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-gray-600">Revenue Concentration</span>
                    <span class="font-semibold {{ $riskMetrics['revenue_concentration'] > 50 ? 'text-red-600' : 'text-green-600' }}">
                        {{ number_format($riskMetrics['revenue_concentration'], 1) }}%
                    </span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-gray-600">High Risk Customers</span>
                    <span class="font-semibold text-red-600">{{ $riskMetrics['high_risk_customers'] }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-gray-600">Low Stock Items</span>
                    <span class="font-semibold text-orange-600">{{ $riskMetrics['low_stock_items'] }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Investment Highlights & Future Projections -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Investment Highlights -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Investment Highlights</h3>
            <div class="space-y-4">
                <div class="flex justify-between items-center">
                    <span class="text-gray-600">Annual Revenue</span>
                    <span class="font-semibold">Rs {{ number_format($investmentMetrics['annual_revenue'], 2) }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-gray-600">Growth Rate</span>
                    <span class="font-semibold {{ $investmentMetrics['growth_rate'] >= 0 ? 'text-green-600' : 'text-red-600' }}">
                        {{ number_format($investmentMetrics['growth_rate'], 1) }}%
                    </span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-gray-600">Revenue Multiple</span>
                    <span class="font-semibold">{{ $investmentMetrics['revenue_multiple'] }}x</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-gray-600">Market Penetration</span>
                    <span class="font-semibold text-blue-600">{{ number_format($investmentMetrics['market_penetration'], 2) }}%</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-gray-600">Digital Order %</span>
                    <span class="font-semibold text-purple-600">{{ number_format($projectionMetrics['digital_order_percentage'], 1) }}%</span>
                </div>
            </div>
        </div>

        <!-- Future Projections -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Future Projections</h3>
            <div class="space-y-4">
                <div class="flex justify-between items-center">
                    <span class="text-gray-600">Projected Revenue (Next 12m)</span>
                    <span class="font-semibold text-green-600">Rs {{ number_format($projectionMetrics['projected_revenue'], 2) }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-gray-600">Potential Markets</span>
                    <span class="font-semibold">{{ $projectionMetrics['potential_markets'] }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-gray-600">Estimated Market Size</span>
                    <span class="font-semibold">Rs {{ number_format($projectionMetrics['estimated_market_size'], 2) }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-gray-600">Expansion Potential</span>
                    <span class="font-semibold text-blue-600">High</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-gray-600">Technology Readiness</span>
                    <span class="font-semibold text-green-600">Advanced</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Branch Performance Table -->
    <div class="bg-white rounded-lg shadow-sm">
        <div class="p-6 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Branch Performance</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Branch</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Revenue</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Orders</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Avg Order Value</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($branchPerformance as $branch)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $branch['name'] }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Rs {{ number_format($branch['revenue'], 2) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ number_format($branch['orders']) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Rs {{ number_format($branch['average_order_value'], 2) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $branch['is_active'] ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                {{ $branch['is_active'] ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Monthly Revenue Trend Chart -->
    <div class="bg-white rounded-lg shadow-sm p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Monthly Revenue Trend</h3>
        <div class="h-64">
            <canvas id="revenueChart"></canvas>
        </div>
    </div>

    <!-- Key Insights -->
    <div class="bg-gradient-to-r from-blue-50 to-purple-50 rounded-lg p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Key Investment Insights</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="bg-white rounded-lg p-4 shadow-sm">
                <h4 class="font-semibold text-gray-900 mb-2">Strengths</h4>
                <ul class="text-sm text-gray-600 space-y-1">
                    <li>• Strong revenue growth of {{ number_format($financialMetrics['revenue_growth'], 1) }}%</li>
                    <li>• Healthy gross margin of {{ number_format($financialMetrics['gross_margin'], 1) }}%</li>
                    <li>• Growing customer base with {{ number_format($growthMetrics['customer_growth'], 1) }}% growth</li>
                    <li>• High digital payment adoption at {{ number_format($operationalMetrics['digital_adoption'], 1) }}%</li>
                </ul>
            </div>
            <div class="bg-white rounded-lg p-4 shadow-sm">
                <h4 class="font-semibold text-gray-900 mb-2">Opportunities</h4>
                <ul class="text-sm text-gray-600 space-y-1">
                    <li>• {{ $projectionMetrics['potential_markets'] }} potential new markets</li>
                    <li>• Technology-driven growth potential</li>
                    <li>• Market penetration only at {{ number_format($investmentMetrics['market_penetration'], 2) }}%</li>
                    <li>• Strong repeat purchase rate of {{ number_format($growthMetrics['repeat_purchase_rate'], 1) }}%</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js for Revenue Chart -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('revenueChart').getContext('2d');
    
    const data = @json($monthlyTrends);
    const labels = data.map(item => item.period);
    const revenues = data.map(item => item.revenue);
    
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Monthly Revenue',
                data: revenues,
                borderColor: 'rgb(59, 130, 246)',
                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return 'Rs ' + value.toLocaleString();
                        }
                    }
                }
            }
        }
    });
});
</script>
@endsection 