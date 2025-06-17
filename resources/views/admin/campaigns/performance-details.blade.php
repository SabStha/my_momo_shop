@extends('layouts.admin')

@section('title', 'Campaign Performance Details')

@section('content')
<div class="space-y-6">
    <!-- Campaign Header -->
    <div class="bg-white rounded-lg shadow-sm p-6">
        <div class="flex justify-between items-start">
            <div>
                <h2 class="text-2xl font-bold text-gray-900">{{ $metrics['basic']['name'] }}</h2>
                <p class="text-gray-500">{{ $metrics['basic']['type'] }} Campaign</p>
            </div>
            <span class="px-3 py-1 rounded-full text-sm font-semibold
                {{ $metrics['basic']['status'] === 'active' ? 'bg-green-100 text-green-800' : 
                   ($metrics['basic']['status'] === 'completed' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800') }}">
                {{ $metrics['basic']['status'] }}
            </span>
        </div>
    </div>

    <!-- Performance Metrics -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div class="bg-white rounded-lg shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Redemptions</h3>
            <div class="text-3xl font-bold text-indigo-600">{{ $metrics['performance']['redemptions'] }}</div>
            <p class="text-sm text-gray-500 mt-2">Total successful redemptions</p>
        </div>

        <div class="bg-white rounded-lg shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Open Rate</h3>
            <div class="text-3xl font-bold text-indigo-600">{{ number_format($metrics['performance']['open_rate'], 1) }}%</div>
            <p class="text-sm text-gray-500 mt-2">Percentage of opened messages</p>
        </div>

        <div class="bg-white rounded-lg shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Engagement Rate</h3>
            <div class="text-3xl font-bold text-indigo-600">{{ number_format($metrics['performance']['engagement_rate'], 1) }}%</div>
            <p class="text-sm text-gray-500 mt-2">Percentage of engaged customers</p>
        </div>

        <div class="bg-white rounded-lg shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">ROI</h3>
            <div class="text-3xl font-bold {{ $metrics['performance']['roi'] >= 0 ? 'text-green-600' : 'text-red-600' }}">
                {{ number_format($metrics['performance']['roi'], 1) }}%
            </div>
            <p class="text-sm text-gray-500 mt-2">Return on Investment</p>
        </div>
    </div>

    <!-- Campaign Timeline -->
    <div class="bg-white rounded-lg shadow-sm">
        <div class="p-6 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Campaign Timeline</h3>
        </div>
        <div class="p-6">
            <div class="flow-root">
                <ul class="-mb-8">
                    @foreach($metrics['timeline'] as $event)
                    <li>
                        <div class="relative pb-8">
                            @if(!$loop->last)
                            <span class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200" aria-hidden="true"></span>
                            @endif
                            <div class="relative flex space-x-3">
                                <div>
                                    <span class="h-8 w-8 rounded-full bg-indigo-100 flex items-center justify-center ring-8 ring-white">
                                        <svg class="h-5 w-5 text-indigo-600" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd" />
                                        </svg>
                                    </span>
                                </div>
                                <div class="min-w-0 flex-1 pt-1.5 flex justify-between space-x-4">
                                    <div>
                                        <p class="text-sm text-gray-500">
                                            {{ $event['action'] }} 
                                            @if($event['revenue'])
                                            <span class="font-medium text-gray-900">(${{ number_format($event['revenue'], 2) }})</span>
                                            @endif
                                        </p>
                                    </div>
                                    <div class="text-right text-sm whitespace-nowrap text-gray-500">
                                        <time datetime="{{ $event['date'] }}">{{ $event['date']->format('M d, Y H:i') }}</time>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>

    <!-- Customer Segments -->
    <div class="bg-white rounded-lg shadow-sm">
        <div class="p-6 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Performance by Segment</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Segment</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customers</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Redemptions</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Revenue</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($metrics['customer_segments'] as $segment => $data)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $segment }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $data['count'] }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $data['redemptions'] }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${{ number_format($data['revenue'], 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection 