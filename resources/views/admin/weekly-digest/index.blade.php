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
                        <div class="text-2xl font-bold mt-1">Rs {{ number_format($digest['kpis']['total_sales'], 2) }}</div>
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
                        <div class="text-2xl font-bold mt-1">Rs {{ number_format($digest['kpis']['average_order_value'], 2) }}</div>
                    </div>
                    <div class="p-4 bg-gray-50 rounded-lg">
                        <span class="text-sm text-gray-600">Total Orders</span>
                        <div class="text-2xl font-bold mt-1">{{ $digest['kpis']['total_orders'] }}</div>
                    </div>
                </div>
            </div>

            <!-- Behavioral Triggers -->
            <div class="bg-white shadow rounded-lg p-6">
                <h2 class="text-xl font-semibold mb-4">Behavioral Triggers</h2>
                <div class="space-y-6">
                    <!-- Churn Risk Customers -->
                    <div>
                        <h3 class="text-lg font-medium text-red-600 mb-3">Churn Risk Customers</h3>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Contact</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Last Order</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Spent</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Trigger</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @forelse($digest['behavioral_triggers']['churn_risk'] as $customer)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">{{ $customer->name }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900">{{ $customer->email }}</div>
                                            <div class="text-sm text-gray-500">{{ $customer->phone }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900">{{ $customer->days_since_last_order }} days ago</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900">Rs {{ number_format($customer->total_spent, 2) }}</div>
                                            <div class="text-sm text-gray-500">{{ $customer->total_orders }} orders</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                                {{ $customer->trigger_reason }}
                                            </span>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="5" class="px-6 py-4 text-center text-sm text-gray-500">
                                            No churn risk customers found
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- VIP Customers -->
                    <div>
                        <h3 class="text-lg font-medium text-green-600 mb-3">VIP Customers</h3>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Contact</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Last Order</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Spent</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @forelse($digest['behavioral_triggers']['vip'] as $customer)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">{{ $customer->name }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900">{{ $customer->email }}</div>
                                            <div class="text-sm text-gray-500">{{ $customer->phone }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900">{{ $customer->days_since_last_order }} days ago</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900">Rs {{ number_format($customer->total_spent, 2) }}</div>
                                            <div class="text-sm text-gray-500">{{ $customer->total_orders }} orders</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                {{ $customer->trigger_reason }}
                                            </span>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="5" class="px-6 py-4 text-center text-sm text-gray-500">
                                            No VIP customers found
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
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
                            <div class="font-bold">Rs {{ number_format($product->total_revenue, 2) }}</div>
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
                            <div class="font-bold">Rs {{ number_format($hour->total_revenue, 2) }}</div>
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