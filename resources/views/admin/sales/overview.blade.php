@extends('layouts.admin')

@section('title', 'Sales Analytics')

@section('content')
<div class="w-full px-4 py-6 mx-auto max-w-7xl">
    <!-- Page Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-6 gap-4">
        <h1 class="text-2xl font-bold text-gray-800">Sales Analytics</h1>
        <div class="flex flex-col sm:flex-row gap-2 items-center">
            <div class="flex gap-2">
                <input type="date" class="border rounded px-2 py-1 text-sm" id="startDate" value="{{ $startDate }}">
                <span class="text-gray-500 flex items-center">to</span>
                <input type="date" class="border rounded px-2 py-1 text-sm" id="endDate" value="{{ $endDate }}">
            </div>
            <select class="border rounded px-2 py-1 text-sm" id="period">
                <option value="daily" {{ $period === 'daily' ? 'selected' : '' }}>Daily</option>
                <option value="weekly" {{ $period === 'weekly' ? 'selected' : '' }}>Weekly</option>
                <option value="monthly" {{ $period === 'monthly' ? 'selected' : '' }}>Monthly</option>
                <option value="yearly" {{ $period === 'yearly' ? 'selected' : '' }}>Yearly</option>
            </select>
            <button class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-1 rounded text-sm font-semibold transition" onclick="updateAnalytics()">
                <i class="fas fa-sync-alt mr-1"></i> Update
            </button>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-white shadow rounded-lg p-4 flex items-center gap-4">
            <div class="flex-1">
                <div class="text-xs text-gray-500 uppercase font-semibold mb-1">Total Sales</div>
                <div class="text-xl font-bold text-gray-800">Rs {{ number_format($data['summary']['total_sales'], 2) }}</div>
            </div>
            <div class="text-blue-500 text-2xl">
                <i class="fas fa-dollar-sign"></i>
            </div>
        </div>
        <div class="bg-white shadow rounded-lg p-4 flex items-center gap-4">
            <div class="flex-1">
                <div class="text-xs text-gray-500 uppercase font-semibold mb-1">Total Orders</div>
                <div class="text-xl font-bold text-gray-800">{{ $data['summary']['total_orders'] }}</div>
            </div>
            <div class="text-green-500 text-2xl">
                <i class="fas fa-shopping-cart"></i>
            </div>
        </div>
        <div class="bg-white shadow rounded-lg p-4 flex items-center gap-4">
            <div class="flex-1">
                <div class="text-xs text-gray-500 uppercase font-semibold mb-1">Average Order Value</div>
                <div class="text-xl font-bold text-gray-800">Rs {{ number_format($data['summary']['average_order_value'], 2) }}</div>
            </div>
            <div class="text-indigo-500 text-2xl">
                <i class="fas fa-chart-line"></i>
            </div>
        </div>
        <div class="bg-white shadow rounded-lg p-4 flex items-center gap-4">
            <div class="flex-1">
                <div class="text-xs text-gray-500 uppercase font-semibold mb-1">Unique Customers</div>
                <div class="text-xl font-bold text-gray-800">{{ $data['summary']['unique_customers'] }}</div>
            </div>
            <div class="text-yellow-500 text-2xl">
                <i class="fas fa-users"></i>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-6">
        <!-- Sales Trend Chart -->
        <div class="bg-white shadow rounded-lg p-4 col-span-2">
            <div class="mb-2 font-semibold text-gray-700">Sales Trend</div>
            <canvas id="salesTrendChart"></canvas>
        </div>
        <!-- Top Products Chart -->
        <div class="bg-white shadow rounded-lg p-4">
            <div class="mb-2 font-semibold text-gray-700">Top Products</div>
            <canvas id="topProductsChart"></canvas>
        </div>
    </div>

    <!-- Additional Metrics Row -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
        <!-- Payment Methods -->
        <div class="bg-white shadow rounded-lg p-4">
            <div class="mb-2 font-semibold text-gray-700">Payment Methods</div>
            <canvas id="paymentMethodsChart"></canvas>
        </div>
        <!-- Category Analysis -->
        <div class="bg-white shadow rounded-lg p-4">
            <div class="mb-2 font-semibold text-gray-700">Category Analysis</div>
            <canvas id="categoryAnalysisChart"></canvas>
        </div>
    </div>

    <!-- AI Analysis Section -->
    <div class="bg-white shadow rounded-lg p-4">
        <div class="mb-2 font-semibold text-gray-700">AI Analysis</div>
        <div id="aiAnalysis" class="p-3 bg-gray-50 rounded">
            {{ $data['ai_analysis'] ?? 'No AI analysis available' }}
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
function updateAnalytics() {
    const startDate = document.getElementById('startDate').value;
    const endDate = document.getElementById('endDate').value;
    const period = document.getElementById('period').value;
    const branchId = '{{ $branchId }}';
    window.location.href = `{{ route('admin.sales.overview') }}?start_date=${startDate}&end_date=${endDate}&period=${period}&branch=${branchId}`;
}

document.addEventListener('DOMContentLoaded', function() {
    // Sales Trend Chart
    const salesTrendCtx = document.getElementById('salesTrendChart').getContext('2d');
    new Chart(salesTrendCtx, {
        type: 'line',
        data: {
            labels: {!! json_encode(array_column($data['trends'], 'period')) !!},
            datasets: [{
                label: 'Sales',
                data: {!! json_encode(array_column($data['trends'], 'total_sales')) !!},
                borderColor: 'rgb(59, 130, 246)',
                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                tension: 0.1
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });

    // Top Products Chart
    const topProductsCtx = document.getElementById('topProductsChart').getContext('2d');
    new Chart(topProductsCtx, {
        type: 'doughnut',
        data: {
            labels: {!! json_encode(array_column($data['top_products'], 'item_name')) !!},
            datasets: [{
                data: {!! json_encode(array_column($data['top_products'], 'total_revenue')) !!},
                backgroundColor: [
                    'rgb(59, 130, 246)',
                    'rgb(16, 185, 129)',
                    'rgb(245, 158, 11)',
                    'rgb(239, 68, 68)',
                    'rgb(168, 85, 247)'
                ]
            }]
        },
        options: {
            responsive: true
        }
    });

    // Payment Methods Chart
    const paymentMethodsCtx = document.getElementById('paymentMethodsChart').getContext('2d');
    new Chart(paymentMethodsCtx, {
        type: 'pie',
        data: {
            labels: {!! json_encode(array_column($data['payment_methods'], 'payment_method')) !!},
            datasets: [{
                data: {!! json_encode(array_column($data['payment_methods'], 'total_amount')) !!},
                backgroundColor: [
                    'rgb(59, 130, 246)',
                    'rgb(16, 185, 129)',
                    'rgb(245, 158, 11)',
                    'rgb(239, 68, 68)'
                ]
            }]
        },
        options: {
            responsive: true
        }
    });

    // Category Analysis Chart
    const categoryAnalysisCtx = document.getElementById('categoryAnalysisChart').getContext('2d');
    new Chart(categoryAnalysisCtx, {
        type: 'bar',
        data: {
            labels: {!! json_encode(array_column($data['category_analysis'], 'category')) !!},
            datasets: [{
                label: 'Revenue',
                data: {!! json_encode(array_column($data['category_analysis'], 'total_revenue')) !!},
                backgroundColor: 'rgb(59, 130, 246)'
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
});
</script>
@endpush 