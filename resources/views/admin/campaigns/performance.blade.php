@extends('layouts.admin')

@section('title', 'Campaign Performance')

@section('content')
<div class="space-y-6">
    <!-- Overall Metrics -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white rounded-lg shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Campaign Overview</h3>
            <div class="space-y-4">
                <div class="flex justify-between">
                    <span class="text-gray-600">Total Campaigns</span>
                    <span class="font-semibold">{{ $overallMetrics['total_campaigns'] }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Customers Reached</span>
                    <span class="font-semibold">{{ $overallMetrics['total_customers_reached'] }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Total Redemptions</span>
                    <span class="font-semibold">{{ $overallMetrics['total_redemptions'] }}</span>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Engagement Metrics</h3>
            <div class="space-y-4">
                <div class="flex justify-between">
                    <span class="text-gray-600">Average Open Rate</span>
                    <span class="font-semibold">{{ number_format($overallMetrics['average_open_rate'], 1) }}%</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Average Engagement</span>
                    <span class="font-semibold">{{ number_format($overallMetrics['average_engagement_rate'], 1) }}%</span>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">ROI Metrics</h3>
            <div class="space-y-4">
                <div class="flex justify-between">
                    <span class="text-gray-600">Average ROI</span>
                    <span class="font-semibold {{ $overallMetrics['average_roi'] >= 0 ? 'text-green-600' : 'text-red-600' }}">
                        {{ number_format($overallMetrics['average_roi'], 1) }}%
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Campaign List -->
    <div class="bg-white rounded-lg shadow-sm">
        <div class="p-6 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Campaign Performance</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Campaign</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customers</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Redemptions</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Open Rate</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Engagement</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ROI</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($campaigns as $campaign)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">{{ $campaign['name'] }}</div>
                            <div class="text-sm text-gray-500">{{ $campaign['created_at']->format('M d, Y') }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $campaign['type'] }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                {{ $campaign['status'] === 'active' ? 'bg-green-100 text-green-800' : 
                                   ($campaign['status'] === 'completed' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800') }}">
                                {{ $campaign['status'] }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $campaign['total_customers'] }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $campaign['redemptions'] }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ number_format($campaign['open_rate'], 1) }}%</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ number_format($campaign['engagement_rate'], 1) }}%</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm {{ $campaign['roi'] >= 0 ? 'text-green-600' : 'text-red-600' }}">
                            {{ number_format($campaign['roi'], 1) }}%
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <a href="{{ route('admin.campaigns.performance.show', $campaign['id']) }}" 
                               class="text-indigo-600 hover:text-indigo-900">View Details</a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection 