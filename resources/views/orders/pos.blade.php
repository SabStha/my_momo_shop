@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="bg-white rounded-lg shadow-lg p-6">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold text-gray-800">POS System</h2>
            <div class="flex space-x-4">
                <button class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600 transition" id="dineInBtn">
                    Dine In
                </button>
                <button class="px-4 py-2 bg-green-500 text-white rounded hover:bg-green-600 transition" id="takeawayBtn">
                    Takeaway
                </button>
            </div>
        </div>

        <!-- Dine In Section -->
        <div id="dineInSection" class="mb-8">
            <h3 class="text-xl font-semibold mb-4 text-gray-700">Dine In Orders</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($dineInOrders as $order)
                <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                    <div class="flex justify-between items-start mb-4">
                        <div>
                            <h4 class="font-semibold">Table #{{ $order->table_number }}</h4>
                            <p class="text-sm text-gray-600">{{ $order->created_at->format('H:i') }}</p>
                        </div>
                        <span class="px-3 py-1 rounded-full text-sm font-medium
                            @if($order->status === 'active') bg-green-100 text-green-800
                            @else bg-gray-100 text-gray-800 @endif">
                            {{ ucfirst($order->status) }}
                        </span>
                    </div>
                    
                    <div class="space-y-2">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Items:</span>
                            <span class="font-medium">{{ $order->items_count }}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Total:</span>
                            <span class="font-medium">Rs {{ number_format($order->total_amount, 2) }}</span>
                        </div>
                    </div>

                    <div class="mt-4 flex justify-end space-x-2">
                        <button class="px-3 py-1 text-sm bg-blue-500 text-white rounded hover:bg-blue-600 transition">
                            View Order
                        </button>
                        <button class="px-3 py-1 text-sm bg-green-500 text-white rounded hover:bg-green-600 transition">
                            Process Payment
                        </button>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        <!-- Takeaway Section -->
        <div id="takeawaySection" class="mb-8">
            <h3 class="text-xl font-semibold mb-4 text-gray-700">Takeaway Orders</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($takeawayOrders as $order)
                <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                    <div class="flex justify-between items-start mb-4">
                        <div>
                            <h4 class="font-semibold">Order #{{ $order->id }}</h4>
                            <p class="text-sm text-gray-600">{{ $order->created_at->format('H:i') }}</p>
                        </div>
                        <span class="px-3 py-1 rounded-full text-sm font-medium
                            @if($order->status === 'preparing') bg-yellow-100 text-yellow-800
                            @elseif($order->status === 'ready') bg-green-100 text-green-800
                            @else bg-gray-100 text-gray-800 @endif">
                            {{ ucfirst($order->status) }}
                        </span>
                    </div>
                    
                    <div class="space-y-2">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Customer:</span>
                            <span class="font-medium">{{ $order->customer_name }}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Total:</span>
                            <span class="font-medium">Rs {{ number_format($order->total_amount, 2) }}</span>
                        </div>
                    </div>

                    <div class="mt-4 flex justify-end space-x-2">
                        <button class="px-3 py-1 text-sm bg-blue-500 text-white rounded hover:bg-blue-600 transition">
                            View Order
                        </button>
                        <button class="px-3 py-1 text-sm bg-green-500 text-white rounded hover:bg-green-600 transition">
                            Mark Ready
                        </button>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const dineInBtn = document.getElementById('dineInBtn');
        const takeawayBtn = document.getElementById('takeawayBtn');
        const dineInSection = document.getElementById('dineInSection');
        const takeawaySection = document.getElementById('takeawaySection');

        dineInBtn.addEventListener('click', function() {
            dineInSection.style.display = 'block';
            takeawaySection.style.display = 'none';
            dineInBtn.classList.add('bg-blue-600');
            takeawayBtn.classList.remove('bg-green-600');
        });

        takeawayBtn.addEventListener('click', function() {
            dineInSection.style.display = 'none';
            takeawaySection.style.display = 'block';
            takeawayBtn.classList.add('bg-green-600');
            dineInBtn.classList.remove('bg-blue-600');
        });
    });
</script>
@endpush
@endsection 