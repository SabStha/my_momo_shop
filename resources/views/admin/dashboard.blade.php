@extends('layouts.admin')

@section('title', 'Admin Dashboard')

@section('content')
<div class="space-y-6">
    <!-- Branch Selection -->
    <div class="bg-white rounded-lg shadow-sm p-6">
        <form action="{{ route('admin.branches.select') }}" method="POST" class="flex items-center space-x-4">
            @csrf
            <div class="flex-1">
                <label for="branch_id" class="block text-sm font-medium text-gray-700">Select Branch</label>
                <select name="branch_id" id="branch_id" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                    <option value="">Select a branch</option>
                    @foreach($branches as $branch)
                        <option value="{{ $branch->id }}" {{ session('selected_branch_id') == $branch->id ? 'selected' : '' }}>
                            {{ $branch->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="pt-5">
                <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Select Branch
                </button>
            </div>
        </form>
    </div>

    @if(session('selected_branch_id'))
        <!-- Quick Stats -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Total Customers</h3>
                <div class="text-3xl font-bold text-indigo-600">{{ $totalCustomers }}</div>
                <p class="text-sm text-gray-500 mt-2">Active customers in this branch</p>
            </div>

            <div class="bg-white rounded-lg shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Total Orders</h3>
                <div class="text-3xl font-bold text-indigo-600">{{ $totalOrders }}</div>
                <p class="text-sm text-gray-500 mt-2">Orders processed this month</p>
            </div>

            <div class="bg-white rounded-lg shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Total Revenue</h3>
                <div class="text-3xl font-bold text-indigo-600">${{ number_format($totalRevenue, 2) }}</div>
                <p class="text-sm text-gray-500 mt-2">Revenue this month</p>
            </div>

            <div class="bg-white rounded-lg shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Active Campaigns</h3>
                <div class="text-3xl font-bold text-indigo-600">{{ $activeCampaigns }}</div>
                <p class="text-sm text-gray-500 mt-2">Running marketing campaigns</p>
            </div>
        </div>

        <!-- Rules Builder Section -->
        <div class="bg-white shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <div class="flex justify-between items-center">
                    <h3 class="text-lg font-medium text-gray-900">Automation Rules</h3>
                    <a href="{{ route('admin.rules.create') }}" class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-md text-indigo-700 bg-indigo-100 hover:bg-indigo-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        Create New Rule
                    </a>
                </div>
                <div class="p-6">
                    <div class="space-y-4">
                        @forelse($rules as $rule)
                            <div class="border rounded-lg p-4">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <h4 class="text-lg font-medium text-gray-900">{{ $rule->name }}</h4>
                                        <p class="text-sm text-gray-500">{{ $rule->description }}</p>
                                    </div>
                                    <div class="flex items-center space-x-2">
                                        <a href="{{ route('admin.rules.edit', $rule) }}" class="text-indigo-600 hover:text-indigo-900">Edit</a>
                                        <form action="{{ route('admin.rules.destroy', $rule) }}" method="POST" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-900" onclick="return confirm('Are you sure you want to delete this rule?')">Delete</button>
                                        </form>
                                    </div>
                                </div>
                                <div class="mt-4">
                                    <div class="flex items-center space-x-2">
                                        <span class="px-2 py-1 text-xs font-medium rounded-full {{ $rule->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                            {{ $rule->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                        <span class="text-sm text-gray-500">Priority: {{ $rule->priority }}</span>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-4">
                                <p class="text-gray-500">No automation rules found.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <!-- Campaigns Section -->
        <div class="bg-white shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <div class="flex justify-between items-center">
                    <h3 class="text-lg font-medium text-gray-900">Marketing Campaigns</h3>
                    <a href="{{ route('admin.campaigns.create') }}" class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-md text-indigo-700 bg-indigo-100 hover:bg-indigo-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        Create New Campaign
                    </a>
                </div>
                <div class="p-6">
                    <div class="space-y-4">
                        @forelse($campaigns as $campaign)
                            <div class="border rounded-lg p-4">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <h4 class="text-lg font-medium text-gray-900">{{ $campaign->name }}</h4>
                                        <p class="text-sm text-gray-500">{{ $campaign->description }}</p>
                                    </div>
                                    <div class="flex items-center space-x-2">
                                        <a href="{{ route('admin.campaigns.edit', $campaign) }}" class="text-indigo-600 hover:text-indigo-900">Edit</a>
                                        <form action="{{ route('admin.campaigns.destroy', $campaign) }}" method="POST" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-900" onclick="return confirm('Are you sure you want to delete this campaign?')">Delete</button>
                                        </form>
                                    </div>
                                </div>
                                <div class="mt-4">
                                    <div class="flex items-center space-x-2">
                                        <span class="px-2 py-1 text-xs font-medium rounded-full {{ $campaign->status === 'active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                            {{ ucfirst($campaign->status) }}
                                        </span>
                                        <span class="text-sm text-gray-500">
                                            {{ \Carbon\Carbon::parse($campaign->start_date)->format('M d, Y') }} - {{ \Carbon\Carbon::parse($campaign->end_date)->format('M d, Y') }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-4">
                                <p class="text-gray-500">No campaigns found.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <!-- Sales and Order Trends -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Sales Trend -->
            <div class="bg-white rounded-lg shadow-sm">
                <div class="p-6 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Sales Trend</h3>
                </div>
                <div class="p-6">
                    <div class="h-64">
                        <canvas id="salesTrendChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Order Trend -->
            <div class="bg-white rounded-lg shadow-sm">
                <div class="p-6 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Order Trend</h3>
                </div>
                <div class="p-6">
                    <div class="h-64">
                        <canvas id="orderTrendChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Campaign Performance Overview -->
        <div class="bg-white rounded-lg shadow-sm">
            <div class="p-6 border-b border-gray-200">
                <div class="flex justify-between items-center">
                    <h3 class="text-lg font-semibold text-gray-900">Campaign Performance</h3>
                    <a href="{{ route('admin.campaigns.performance') }}" class="text-sm text-indigo-600 hover:text-indigo-900">View All</a>
                </div>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                    <div>
                        <h4 class="text-sm font-medium text-gray-500">Total Redemptions</h4>
                        <p class="mt-2 text-3xl font-bold text-gray-900">{{ $campaignMetrics['total_redemptions'] }}</p>
                    </div>
                    <div>
                        <h4 class="text-sm font-medium text-gray-500">Average Open Rate</h4>
                        <p class="mt-2 text-3xl font-bold text-gray-900">{{ number_format($campaignMetrics['average_open_rate'], 1) }}%</p>
                    </div>
                    <div>
                        <h4 class="text-sm font-medium text-gray-500">Average Engagement</h4>
                        <p class="mt-2 text-3xl font-bold text-gray-900">{{ number_format($campaignMetrics['average_engagement_rate'], 1) }}%</p>
                    </div>
                    <div>
                        <h4 class="text-sm font-medium text-gray-500">Average ROI</h4>
                        <p class="mt-2 text-3xl font-bold {{ $campaignMetrics['average_roi'] >= 0 ? 'text-green-600' : 'text-red-600' }}">
                            {{ number_format($campaignMetrics['average_roi'], 1) }}%
                        </p>
                    </div>
                </div>
            </div>
        </div>
    @else
        <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="text-center">
                <h3 class="text-lg font-medium text-gray-900">Please Select a Branch</h3>
                <p class="mt-2 text-sm text-gray-500">Select a branch to view its dashboard and manage its operations.</p>
            </div>
        </div>
    @endif
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Sales Trend Chart
        const salesCtx = document.getElementById('salesTrendChart').getContext('2d');
        new Chart(salesCtx, {
            type: 'line',
            data: {
                labels: {!! json_encode($salesTrend->pluck('date')) !!},
                datasets: [{
                    label: 'Sales',
                    data: {!! json_encode($salesTrend->pluck('amount')) !!},
                    borderColor: 'rgb(79, 70, 229)',
                    tension: 0.1,
                    fill: false
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
                                return '$' + value;
                            }
                        }
                    }
                }
            }
        });

        // Order Trend Chart
        const orderCtx = document.getElementById('orderTrendChart').getContext('2d');
        new Chart(orderCtx, {
            type: 'line',
            data: {
                labels: {!! json_encode($salesTrend->pluck('date')) !!},
                datasets: [{
                    label: 'Orders',
                    data: {!! json_encode($salesTrend->pluck('count')) !!},
                    borderColor: 'rgb(16, 185, 129)',
                    tension: 0.1,
                    fill: false
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
                            stepSize: 1
                        }
                    }
                }
            }
        });
    });
</script>
@endpush
