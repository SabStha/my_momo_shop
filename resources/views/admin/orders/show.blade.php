@extends('layouts.admin')

@section('title', 'Order Details')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Order #{{ $order->id }}</h1>
        <a href="{{ route('admin.orders.index', ['branch' => $order->branch_id]) }}" 
           class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-md text-sm font-medium">
            <i class="fas fa-arrow-left mr-2"></i> Back to Orders
        </a>
    </div>

    @if(session('success'))
        <div class="bg-green-100 text-green-800 p-4 rounded mb-6">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="bg-red-100 text-red-800 p-4 rounded mb-6">
            {{ session('error') }}
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Order Details -->
        <div class="lg:col-span-2">
            <div class="bg-white shadow rounded-lg p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Order Information</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Order ID</label>
                        <p class="mt-1 text-sm text-gray-900">#{{ $order->id }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Order Number</label>
                        <p class="mt-1 text-sm text-gray-900">{{ $order->order_number ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Order Type</label>
                        <p class="mt-1 text-sm text-gray-900">{{ ucfirst(str_replace('_', ' ', $order->order_type)) }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Created At</label>
                        <p class="mt-1 text-sm text-gray-900">{{ $order->created_at->format('M d, Y H:i') }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Status</label>
                        <span class="mt-1 inline-flex px-2 py-1 text-xs font-semibold rounded-full 
                            {{ $order->status === 'completed' ? 'bg-green-100 text-green-800' : 
                               ($order->status === 'cancelled' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800') }}">
                            {{ ucfirst($order->status) }}
                        </span>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Payment Status</label>
                        <span class="mt-1 inline-flex px-2 py-1 text-xs font-semibold rounded-full 
                            {{ $order->payment_status === 'paid' ? 'bg-green-100 text-green-800' : 
                               ($order->payment_status === 'failed' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800') }}">
                            {{ ucfirst($order->payment_status) }}
                        </span>
                    </div>
                </div>

                <!-- Order Items -->
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Order Items</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Item</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Price</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($order->items as $item)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $item->product->name ?? $item->item_name }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $item->quantity }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Rs. {{ number_format($item->price, 2) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Rs. {{ number_format($item->subtotal, 2) }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="px-6 py-4 text-center text-gray-500">No items found</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Order Totals -->
                <div class="mt-6 border-t pt-4">
                    <div class="flex justify-between items-center">
                        <span class="text-sm font-medium text-gray-700">Subtotal:</span>
                        <span class="text-sm text-gray-900">Rs. {{ number_format($order->total_amount, 2) }}</span>
                    </div>
                    @if($order->tax_amount)
                    <div class="flex justify-between items-center mt-2">
                        <span class="text-sm font-medium text-gray-700">Tax:</span>
                        <span class="text-sm text-gray-900">Rs. {{ number_format($order->tax_amount, 2) }}</span>
                    </div>
                    @endif
                    <div class="flex justify-between items-center mt-2 text-lg font-semibold">
                        <span class="text-gray-900">Total:</span>
                        <span class="text-gray-900">Rs. {{ number_format($order->grand_total ?? $order->total_amount, 2) }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Customer & Payment Info -->
        <div class="space-y-6">
            <!-- Customer Information -->
            <div class="bg-white shadow rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Customer Information</h3>
                <div class="space-y-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Name</label>
                        <p class="mt-1 text-sm text-gray-900">{{ $order->user->name ?? 'Guest' }}</p>
                    </div>
                    @if($order->user)
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Email</label>
                        <p class="mt-1 text-sm text-gray-900">{{ $order->user->email }}</p>
                    </div>
                    @endif
                    @if($order->guest_name)
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Guest Name</label>
                        <p class="mt-1 text-sm text-gray-900">{{ $order->guest_name }}</p>
                    </div>
                    @endif
                    @if($order->guest_email)
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Guest Email</label>
                        <p class="mt-1 text-sm text-gray-900">{{ $order->guest_email }}</p>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Delivery Address -->
            @if($order->delivery_address)
            <div class="bg-white shadow rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">📍 Delivery Address</h3>
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                    @if(is_array($order->delivery_address))
                        @php
                            $addr = $order->delivery_address;
                            $addressParts = [];
                            
                            if (!empty($addr['building_name'])) {
                                $addressParts[] = '<strong class="text-gray-900">' . e($addr['building_name']) . '</strong>';
                            }
                            if (!empty($addr['area_locality'])) {
                                $addressParts[] = '<span class="text-gray-700">' . e($addr['area_locality']) . '</span>';
                            }
                            if (!empty($addr['ward_number']) && !empty($addr['city'])) {
                                $addressParts[] = '<span class="text-gray-700">Ward ' . e($addr['ward_number']) . ', ' . e($addr['city']) . '</span>';
                            } elseif (!empty($addr['city'])) {
                                $addressParts[] = '<span class="text-gray-700">' . e($addr['city']) . '</span>';
                            } elseif (!empty($addr['ward_number'])) {
                                $addressParts[] = '<span class="text-gray-700">Ward ' . e($addr['ward_number']) . '</span>';
                            }
                        @endphp
                        
                        <div class="text-sm space-y-1">
                            <p class="leading-relaxed">
                                {!! implode('<br>', $addressParts) !!}
                            </p>
                        </div>
                        
                        @if(!empty($addr['detailed_directions']))
                            <div class="mt-3 pt-3 border-t border-blue-200">
                                <p class="text-xs font-semibold text-blue-900 mb-1">🧭 Delivery Instructions:</p>
                                <p class="text-sm text-gray-700 italic">{{ $addr['detailed_directions'] }}</p>
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
                            <div class="mt-4">
                                <a href="https://www.google.com/maps/search/?api=1&query={{ $mapQuery }}" 
                                   target="_blank" 
                                   class="inline-flex items-center px-3 py-2 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-md transition-colors">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"></path>
                                    </svg>
                                    Open in Google Maps
                                </a>
                            </div>
                        @endif
                    @else
                        <p class="text-sm text-gray-700">{{ $order->delivery_address }}</p>
                    @endif
                </div>
            </div>
            @endif

            <!-- Payment Information -->
            <div class="bg-white shadow rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Payment Information</h3>
                <div class="space-y-3">
                    @if($order->payment_method)
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Payment Method</label>
                        <p class="mt-1 text-sm text-gray-900">{{ ucfirst($order->payment_method) }}</p>
                    </div>
                    @endif
                    @if($order->amount_received)
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Amount Received</label>
                        <p class="mt-1 text-sm text-gray-900">Rs. {{ number_format($order->amount_received, 2) }}</p>
                    </div>
                    @endif
                    @if($order->change)
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Change</label>
                        <p class="mt-1 text-sm text-gray-900">Rs. {{ number_format($order->change, 2) }}</p>
                    </div>
                    @endif
                    @if($order->reference_number)
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Reference Number</label>
                        <p class="mt-1 text-sm text-gray-900">{{ $order->reference_number }}</p>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Actions -->
            <div class="bg-white shadow rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Actions</h3>
                <div class="space-y-3">
                    @if($order->payment_status !== 'paid')
                    <form action="{{ route('admin.orders.process-payment') }}" method="POST" class="inline">
                        @csrf
                        <input type="hidden" name="order_id" value="{{ $order->id }}">
                        <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                            Mark as Paid
                        </button>
                    </form>
                    @endif
                    
                    <form action="{{ route('admin.orders.destroy', $order) }}" method="POST" class="inline" 
                          onsubmit="return confirm('Are you sure you want to delete this order?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="w-full bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                            Delete Order
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 