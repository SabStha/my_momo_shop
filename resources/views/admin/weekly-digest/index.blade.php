@extends('layouts.admin')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold">Weekly Digest</h1>
            <p class="text-sm text-gray-600 mt-1">{{ $currentBranch->name }}</p>
        </div>
        <div class="text-sm text-gray-600">
            Period: {{ \Carbon\Carbon::parse($dateRange['start'])->format('M d, Y') }} - {{ \Carbon\Carbon::parse($dateRange['end'])->format('M d, Y') }}
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- KPIs Section -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Main KPIs -->
            <div class="bg-white shadow rounded-lg p-6">
                <h2 class="text-xl font-semibold mb-4">Key Performance Indicators</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="p-4 bg-gray-50 rounded-lg">
                        <span class="text-sm text-gray-600">Total Sales</span>
                        <div class="text-2xl font-bold mt-1">${{ number_format($digest['kpis']['total_sales'], 2) }}</div>
                    </div>
                    <div class="p-4 bg-gray-50 rounded-lg">
                        <span class="text-sm text-gray-600">New Customers</span>
                        <div class="text-2xl font-bold mt-1">{{ $digest['kpis']['new_customers'] }}</div>
                    </div>
                    <div class="p-4 bg-gray-50 rounded-lg">
                        <span class="text-sm text-gray-600">Churn Rate</span>
                        <div class="text-2xl font-bold mt-1">{{ number_format($digest['kpis']['churn_rate'], 2) }}%</div>
                    </div>
                    <div class="p-4 bg-gray-50 rounded-lg">
                        <span class="text-sm text-gray-600">Average Order Value</span>
                        <div class="text-2xl font-bold mt-1">${{ number_format($digest['kpis']['average_order_value'], 2) }}</div>
                    </div>
                    <div class="p-4 bg-gray-50 rounded-lg">
                        <span class="text-sm text-gray-600">Total Orders</span>
                        <div class="text-2xl font-bold mt-1">{{ $digest['kpis']['total_orders'] }}</div>
                    </div>
                </div>
            </div>

            <!-- Top Products -->
            <div class="bg-white shadow rounded-lg p-6">
                <h2 class="text-xl font-semibold mb-4">Top Products</h2>
                <div class="space-y-4">
                    @foreach($digest['kpis']['top_products'] as $product)
                    <div class="flex justify-between items-center p-4 bg-gray-50 rounded-lg">
                        <div>
                            <div class="font-medium">{{ $product->item_name }}</div>
                            <div class="text-sm text-gray-600">{{ $product->total_quantity }} units sold</div>
                        </div>
                        <div class="text-right">
                            <div class="font-bold">${{ number_format($product->total_revenue, 2) }}</div>
                            <div class="text-sm text-gray-600">Revenue</div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            <!-- Peak Hours -->
            <div class="bg-white shadow rounded-lg p-6">
                <h2 class="text-xl font-semibold mb-4">Peak Hours</h2>
                <div class="space-y-4">
                    @foreach($digest['kpis']['peak_hours'] as $hour)
                    <div class="flex justify-between items-center p-4 bg-gray-50 rounded-lg">
                        <div>
                            <div class="font-medium">{{ $hour->hour }}:00</div>
                            <div class="text-sm text-gray-600">{{ $hour->order_count }} orders</div>
                        </div>
                        <div class="text-right">
                            <div class="font-bold">${{ number_format($hour->total_revenue, 2) }}</div>
                            <div class="text-sm text-gray-600">Revenue</div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- AI Summary Section -->
        <div class="bg-white shadow rounded-lg p-6">
            <h2 class="text-xl font-semibold mb-4">AI-Generated Summary</h2>
            <div class="prose max-w-none">
                <p class="text-gray-700 leading-relaxed">{{ $digest['summary'] }}</p>
            </div>
        </div>
    </div>
</div>
@endsection 