@extends('layouts.admin')

@section('title', 'Supply Order Details')

@section('content')
<div class="space-y-6">
    <div class="flex justify-between items-center">
        <h2 class="text-2xl font-semibold">Supply Order Details</h2>
        <div class="space-x-4">
            <a href="{{ route('admin.supply.orders.edit', $order) }}" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                <i class="fas fa-edit mr-2"></i> Edit Order
            </a>
            <a href="{{ route('admin.supply.orders.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2">
                <i class="fas fa-arrow-left mr-2"></i> Back to Orders
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="bg-white shadow rounded-lg overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h5 class="text-lg font-medium text-gray-900">Order Information</h5>
            </div>
            <div class="p-6">
                <dl class="divide-y divide-gray-200">
                    <div class="py-3 flex justify-between">
                        <dt class="text-sm font-medium text-gray-500 w-48">Order Number</dt>
                        <dd class="text-sm text-gray-900">{{ $order->order_number }}</dd>
                    </div>
                    <div class="py-3 flex justify-between">
                        <dt class="text-sm font-medium text-gray-500 w-48">Supplier</dt>
                        <dd class="text-sm text-gray-900">{{ $order->supplier->name }}</dd>
                    </div>
                    <div class="py-3 flex justify-between">
                        <dt class="text-sm font-medium text-gray-500 w-48">Status</dt>
                        <dd class="text-sm">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                {{ $order->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : 
                                   ($order->status === 'processing' ? 'bg-blue-100 text-blue-800' : 
                                   ($order->status === 'completed' ? 'bg-green-100 text-green-800' : 
                                   'bg-red-100 text-red-800')) }}">
                                {{ ucfirst($order->status) }}
                            </span>
                        </dd>
                    </div>
                    <div class="py-3 flex justify-between">
                        <dt class="text-sm font-medium text-gray-500 w-48">Expected Delivery</dt>
                        <dd class="text-sm text-gray-900">{{ $order->expected_delivery_date->format('M d, Y') }}</dd>
                    </div>
                    <div class="py-3 flex justify-between">
                        <dt class="text-sm font-medium text-gray-500 w-48">Created At</dt>
                        <dd class="text-sm text-gray-900">{{ $order->created_at->format('M d, Y H:i') }}</dd>
                    </div>
                    <div class="py-3 flex justify-between">
                        <dt class="text-sm font-medium text-gray-500 w-48">Last Updated</dt>
                        <dd class="text-sm text-gray-900">{{ $order->updated_at->format('M d, Y H:i') }}</dd>
                    </div>
                </dl>
            </div>
        </div>

        <div class="bg-white shadow rounded-lg overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h5 class="text-lg font-medium text-gray-900">Order Items</h5>
            </div>
            <div class="p-6">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Item</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Unit Price</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($order->items as $item)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $item->item->name }}
                                        <br>
                                        <span class="text-sm text-gray-500">{{ $item->quantity }} {{ $item->item->unit }}</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $item->quantity }} {{ $item->item->unit }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        Rs {{ number_format($item->unit_price, 2) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        Rs {{ number_format($item->quantity * $item->unit_price, 2) }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="bg-gray-50">
                            <tr>
                                <td colspan="3" class="px-6 py-4 text-right text-sm font-medium text-gray-900">Total</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    Rs {{ number_format($order->items->sum(function($item) { return $item->quantity * $item->unit_price; }), 2) }}
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>

    @if($order->notes)
        <div class="bg-white shadow rounded-lg overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h5 class="text-lg font-medium text-gray-900">Notes</h5>
            </div>
            <div class="p-6">
                <p class="text-sm text-gray-900">{{ $order->notes }}</p>
            </div>
        </div>
    @endif
</div>
@endsection 
