@extends('layouts.admin')

@section('title', "Order #{$order->order_number}")

@section('content')
<div class="max-w-7xl mx-auto py-10 px-4">
    <div class="mb-6 bg-white shadow rounded-lg p-4">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-4">
                <div class="bg-indigo-100 p-3 rounded-full">
                    <i class="fas fa-shopping-cart text-indigo-600 text-xl"></i>
                </div>
                <div>
                    <h2 class="text-xl font-semibold text-gray-800">Order #{{ $order->order_number }}</h2>
                    <div class="flex items-center space-x-2">
                        <span class="text-sm text-gray-600">Created: {{ $order->order_date->format('M d, Y H:i') }}</span>
                        <span class="px-2 py-1 text-xs font-semibold rounded-full
                            {{ $order->status === 'completed' ? 'bg-green-100 text-green-800' :
                               ($order->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                            {{ ucfirst($order->status) }}
                        </span>
                    </div>
                </div>
            </div>
            <div class="flex items-center space-x-4">
                @if($order->status === 'pending')
                    <a href="{{ route('admin.inventory.orders.edit', $order) }}" 
                       class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2">
                        <i class="fas fa-edit mr-2"></i>Edit Order
                    </a>
                    <form action="{{ route('admin.inventory.orders.destroy', $order) }}" method="POST" class="inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" 
                                class="inline-flex items-center px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2"
                                onclick="return confirm('Are you sure you want to delete this order?')">
                            <i class="fas fa-trash mr-2"></i>Delete Order
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Order Details</h3>
            <dl class="grid grid-cols-1 gap-4">
                <div>
                    <dt class="text-sm font-medium text-gray-500">Supplier</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $order->supplier->name }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Expected Delivery Date</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $order->expected_delivery_date->format('M d, Y') }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Total Amount</dt>
                    <dd class="mt-1 text-sm text-gray-900">Rs. {{ number_format($order->total_amount, 2) }}</dd>
                </div>
                @if($order->notes)
                <div>
                    <dt class="text-sm font-medium text-gray-500">Notes</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $order->notes }}</dd>
                </div>
                @endif
            </dl>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Branch Information</h3>
            <dl class="grid grid-cols-1 gap-4">
                <div>
                    <dt class="text-sm font-medium text-gray-500">Branch Name</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $order->branch->name }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Branch Code</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $order->branch->code }}</dd>
                </div>
            </dl>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Order Items</h3>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Item</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Unit Price</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($order->items as $item)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $item->item->name }}
                                <div class="text-xs text-gray-500">{{ $item->item->sku }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $item->quantity }} {{ $item->item->unit }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                Rs. {{ number_format($item->unit_price, 2) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                Rs. {{ number_format($item->quantity * $item->unit_price, 2) }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-gray-50">
                        <tr>
                            <td colspan="3" class="px-6 py-4 text-right text-sm font-medium text-gray-900">Total:</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                Rs. {{ number_format($order->total_amount, 2) }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection 