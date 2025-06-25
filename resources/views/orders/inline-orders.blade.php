@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="bg-white rounded-lg shadow-lg p-6">
        <h2 class="text-2xl font-bold mb-6 text-gray-800">Inline Orders</h2>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($inlineOrders as $order)
            <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                <div class="flex justify-between items-start mb-4">
                    <div>
                        <h3 class="font-semibold text-lg">Order #{{ $order->id }}</h3>
                        <p class="text-sm text-gray-600">{{ $order->created_at->format('M d, Y H:i') }}</p>
                    </div>
                    <span class="px-3 py-1 rounded-full text-sm font-medium
                        @if($order->status === 'pending') bg-yellow-100 text-yellow-800
                        @elseif($order->status === 'processing') bg-blue-100 text-blue-800
                        @elseif($order->status === 'completed') bg-green-100 text-green-800
                        @else bg-red-100 text-red-800 @endif">
                        {{ ucfirst($order->status) }}
                    </span>
                </div>
                
                <div class="space-y-2">
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600">Customer:</span>
                        <span class="font-medium">{{ $order->customer_name }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600">Total Amount:</span>
                        <span class="font-medium">Rs {{ number_format($order->total_amount, 2) }}</span>
                    </div>
                </div>

                <div class="mt-4 flex justify-end space-x-2">
                    <button class="px-3 py-1 text-sm bg-blue-500 text-white rounded hover:bg-blue-600 transition">
                        View Details
                    </button>
                    <button class="px-3 py-1 text-sm bg-green-500 text-white rounded hover:bg-green-600 transition">
                        Process
                    </button>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>
@endsection 