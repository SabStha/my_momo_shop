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
            <div class="mt-6 p-4 bg-blue-50 rounded-lg border border-blue-200">
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                    </div>
                    <div class="ml-3 flex-1">
                        <p class="text-sm font-semibold text-blue-900 mb-2">📍 Delivery Address</p>
                        <div class="text-sm text-gray-700 space-y-1">
                            @if(is_array($order->delivery_address))
                                @php
                                    $addr = $order->delivery_address;
                                    $addressParts = [];
                                    
                                    // Build address in a natural, readable format
                                    if (!empty($addr['building_name'])) {
                                        $addressParts[] = '<strong>' . e($addr['building_name']) . '</strong>';
                                    }
                                    if (!empty($addr['area_locality'])) {
                                        $addressParts[] = e($addr['area_locality']);
                                    }
                                    if (!empty($addr['ward_number']) && !empty($addr['city'])) {
                                        $addressParts[] = 'Ward ' . e($addr['ward_number']) . ', ' . e($addr['city']);
                                    } elseif (!empty($addr['city'])) {
                                        $addressParts[] = e($addr['city']);
                                    } elseif (!empty($addr['ward_number'])) {
                                        $addressParts[] = 'Ward ' . e($addr['ward_number']);
                                    }
                                @endphp
                                
                                <p class="leading-relaxed">
                                    {!! implode('<br>', $addressParts) !!}
                                </p>
                                
                                @if(!empty($addr['detailed_directions']))
                                    <div class="mt-2 pt-2 border-t border-blue-200">
                                        <p class="text-xs font-medium text-blue-800 mb-1">🧭 Directions:</p>
                                        <p class="text-sm text-gray-600 italic">{{ $addr['detailed_directions'] }}</p>
                                    </div>
                                @endif
                                
                                @if(!empty($addr['city']) || !empty($addr['area_locality']))
                                    @php
                                        $mapQuery = urlencode(
                                            ($addr['building_name'] ?? '') . ' ' .
                                            ($addr['area_locality'] ?? '') . ' ' .
                                            ($addr['city'] ?? '')
                                        );
                                    @endphp
                                    <div class="mt-3">
                                        <a href="https://www.google.com/maps/search/?api=1&query={{ $mapQuery }}" 
                                           target="_blank" 
                                           class="inline-flex items-center text-xs font-medium text-blue-600 hover:text-blue-800">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"></path>
                                            </svg>
                                            View on Google Maps
                                        </a>
                                    </div>
                                @endif
                            @else
                                <p>{{ $order->delivery_address }}</p>
                            @endif
                        </div>
                    </div>
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