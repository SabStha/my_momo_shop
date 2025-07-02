<!-- Order History -->
<div class="bg-white rounded-lg shadow mb-6">
    <div class="px-6 py-4 border-b border-gray-200">
        <h2 class="text-lg font-semibold text-gray-900">Order History</h2>
        <p class="text-sm text-gray-600 mt-1">View your past orders and their status</p>
    </div>
    <div class="p-6">
        @php
            $orders = auth()->user()->orders()->latest()->paginate(10);
        @endphp
        
        @if($orders->count() > 0)
            <div class="space-y-4">
                @foreach($orders as $order)
                    <div class="border border-gray-200 rounded-lg p-4 hover:bg-gray-50 transition-colors">
                        <div class="flex justify-between items-start mb-3">
                            <div>
                                <h3 class="font-medium text-gray-900">Order #{{ $order->order_number ?? $order->id }}</h3>
                                <p class="text-sm text-gray-500">{{ $order->created_at->format('M d, Y \a\t g:i A') }}</p>
                            </div>
                            <div class="text-right">
                                <span class="px-2 py-1 text-xs font-semibold rounded-full 
                                    @if($order->status === 'completed') bg-green-100 text-green-800
                                    @elseif($order->status === 'cancelled') bg-red-100 text-red-800
                                    @elseif($order->status === 'processing') bg-blue-100 text-blue-800
                                    @else bg-gray-100 text-gray-800 @endif">
                                    {{ ucfirst($order->status) }}
                                </span>
                                <p class="text-sm font-medium text-gray-900 mt-1">Rs {{ number_format($order->grand_total ?? $order->total, 2) }}</p>
                            </div>
                        </div>
                        
                        @if($order->items->count() > 0)
                            <div class="border-t pt-3">
                                <h4 class="text-sm font-medium text-gray-700 mb-2">Items:</h4>
                                <div class="space-y-1">
                                    @foreach($order->items->take(3) as $item)
                                        <div class="flex justify-between text-sm">
                                            <span class="text-gray-600">{{ $item->quantity }}x {{ $item->product->name ?? 'Product' }}</span>
                                            <span class="text-gray-900">Rs {{ number_format($item->price * $item->quantity, 2) }}</span>
                                        </div>
                                    @endforeach
                                    @if($order->items->count() > 3)
                                        <p class="text-xs text-gray-500">+{{ $order->items->count() - 3 }} more items</p>
                                    @endif
                                </div>
                            </div>
                        @endif
                        
                        <div class="border-t pt-3 mt-3">
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-500">
                                    @if($order->order_type)
                                        {{ ucfirst(str_replace('_', ' ', $order->order_type)) }}
                                    @else
                                        Online Order
                                    @endif
                                </span>
                                <a href="{{ route('orders.show', $order) }}" 
                                   class="text-sm text-blue-600 hover:text-blue-800 font-medium">
                                    View Details →
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            
            @if($orders->hasPages())
                <div class="mt-6">
                    {{ $orders->links() }}
                </div>
            @endif
        @else
            <div class="text-center py-8">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">No orders yet</h3>
                <p class="mt-1 text-sm text-gray-500">Start shopping to see your order history here.</p>
                <div class="mt-6">
                    <a href="{{ route('menu') }}" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Browse Menu
                    </a>
                </div>
            </div>
        @endif
    </div>
</div> 