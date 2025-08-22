@extends('layouts.app')

@section('title', 'Order Details')

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Order Details</h1>
                    <p class="mt-2 text-gray-600">Order #{{ $order->order_number }}</p>
                </div>
                <div class="flex space-x-3">
                    <a href="{{ route('orders') }}" class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                        Back to Orders
                    </a>
                    <a href="{{ route('orders.receipt', $order) }}" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                        View Receipt
                    </a>
                </div>
            </div>
        </div>

        <!-- Order Status -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900">Order Status</h2>
                    <p class="text-sm text-gray-600 mt-1">Created on {{ $order->created_at->format('M d, Y \a\t g:i A') }}</p>
                </div>
                <div class="flex items-center space-x-4">
                    <span class="px-3 py-1 rounded-full text-sm font-medium 
                        @if($order->status === 'completed') bg-green-100 text-green-800
                        @elseif($order->status === 'pending') bg-yellow-100 text-yellow-800
                        @elseif($order->status === 'cancelled') bg-red-100 text-red-800
                        @else bg-gray-100 text-gray-800
                        @endif">
                        {{ ucfirst($order->status) }}
                    </span>
                    <span class="px-3 py-1 rounded-full text-sm font-medium 
                        @if($order->payment_status === 'paid') bg-green-100 text-green-800
                        @else bg-red-100 text-red-800
                        @endif">
                        {{ ucfirst($order->payment_status) }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Customer Information -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Customer Information</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <p class="text-sm font-medium text-gray-500">Name</p>
                    <p class="text-gray-900">{{ $order->customer_name }}</p>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-500">Email</p>
                    <p class="text-gray-900">{{ $order->customer_email }}</p>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-500">Phone</p>
                    <p class="text-gray-900">{{ $order->customer_phone }}</p>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-500">Order Type</p>
                    <p class="text-gray-900">{{ ucfirst($order->order_type) }}</p>
                </div>
            </div>

            @if($order->delivery_address)
            <div class="mt-4">
                <p class="text-sm font-medium text-gray-500">Delivery Address</p>
                <div class="mt-1 text-gray-900">
                    @if(is_array($order->delivery_address))
                        @if(isset($order->delivery_address['building_name']))
                            <p>{{ $order->delivery_address['building_name'] }}</p>
                        @endif
                        @if(isset($order->delivery_address['area_locality']))
                            <p>{{ $order->delivery_address['area_locality'] }}</p>
                        @endif
                        @if(isset($order->delivery_address['ward_number']))
                            <p>Ward {{ $order->delivery_address['ward_number'] }}</p>
                        @endif
                        @if(isset($order->delivery_address['city']))
                            <p>{{ $order->delivery_address['city'] }}</p>
                        @endif
                        @if(isset($order->delivery_address['detailed_directions']))
                            <p class="text-sm text-gray-600">{{ $order->delivery_address['detailed_directions'] }}</p>
                        @endif
                    @else
                        <p>{{ $order->delivery_address }}</p>
                    @endif
                </div>
            </div>
            @endif
        </div>

        <!-- Order Items -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Order Items</h2>
            <div class="space-y-4">
                @foreach($order->items as $item)
                <div class="flex items-center justify-between py-3 border-b border-gray-100 last:border-b-0">
                    <div class="flex items-center space-x-4">
                        <div class="flex-shrink-0">
                            @if($item->product && $item->product->image)
                                <img src="{{ asset('storage/' . $item->product->image) }}" 
                                     alt="{{ $item->item_name }}" 
                                     class="w-12 h-12 rounded-lg object-cover">
                            @else
                                <div class="w-12 h-12 bg-gray-200 rounded-lg flex items-center justify-center">
                                    <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                </div>
                            @endif
                        </div>
                        <div>
                            <p class="font-medium text-gray-900">{{ $item->item_name }}</p>
                            <p class="text-sm text-gray-500">Qty: {{ $item->quantity }}</p>
                        </div>
                    </div>
                    <div class="text-right">
                        <p class="font-medium text-gray-900">Rs. {{ number_format($item->subtotal, 2) }}</p>
                        <p class="text-sm text-gray-500">Rs. {{ number_format($item->price, 2) }} each</p>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        <!-- Order Summary -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Order Summary</h2>
            <div class="space-y-3">
                <div class="flex justify-between">
                    <span class="text-gray-600">Subtotal</span>
                    <span class="font-medium">Rs. {{ number_format($order->total_amount, 2) }}</span>
                </div>
                <div class="flex justify-between">
                                            <span class="text-gray-600">Tax ({{ getTaxRate() }}%)</span>
                    <span class="font-medium">Rs. {{ number_format($order->tax_amount, 2) }}</span>
                </div>
                @if($order->applied_offer)
                <div class="flex justify-between text-green-600">
                    <span>Discount</span>
                    <span>- Rs. {{ number_format($order->discount_amount ?? 0, 2) }}</span>
                </div>
                @endif
                <div class="border-t border-gray-200 pt-3">
                    <div class="flex justify-between">
                        <span class="text-lg font-semibold text-gray-900">Total</span>
                        <span class="text-lg font-semibold text-gray-900">Rs. {{ number_format($order->grand_total, 2) }}</span>
                    </div>
                </div>
            </div>

            @if($order->payment_status === 'paid')
            <div class="mt-6 p-4 bg-green-50 rounded-lg">
                <div class="flex items-center">
                    <svg class="w-5 h-5 text-green-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 001.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                    </svg>
                    <span class="text-green-800 font-medium">Payment Completed</span>
                </div>
                @if($order->payment_method)
                <p class="text-sm text-green-700 mt-1">Paid via {{ ucfirst($order->payment_method) }}</p>
                @endif
            </div>
            @endif
        </div>
    </div>
</div>
@endsection 