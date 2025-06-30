<div class="bg-white rounded-lg shadow">
    <div class="px-6 py-4 border-b border-gray-200">
        <h2 class="text-lg font-semibold text-gray-900">Order History</h2>
    </div>
    <div class="p-6">
        <!-- Order Statistics -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <div class="bg-blue-50 p-4 rounded-lg text-center">
                <div class="text-2xl font-bold text-blue-600">{{ $totalOrders }}</div>
                <div class="text-sm text-blue-800">Total Orders</div>
            </div>
            <div class="bg-green-50 p-4 rounded-lg text-center">
                <div class="text-2xl font-bold text-green-600">Rs.{{ number_format($totalSpent, 2) }}</div>
                <div class="text-sm text-green-800">Total Spent</div>
            </div>
            <div class="bg-purple-50 p-4 rounded-lg text-center">
                <div class="text-2xl font-bold text-purple-600">{{ $totalOrders > 0 ? number_format($totalSpent / $totalOrders, 2) : 0 }}</div>
                <div class="text-sm text-purple-800">Avg. Order</div>
            </div>
        </div>

        <!-- Recent Orders -->
        <div class="mb-6">
            <h3 class="text-sm font-medium text-gray-700 mb-3">Recent Orders</h3>
            @if($recentOrders->count() > 0)
                <div class="space-y-3">
                    @foreach($recentOrders as $order)
                        <div class="border border-gray-200 rounded-lg p-4 hover:bg-gray-50 transition-colors">
                            <div class="flex justify-between items-start">
                                <div class="flex-1">
                                    <div class="flex items-center space-x-2 mb-2">
                                        <span class="text-sm font-medium text-gray-900">Order #{{ $order->id }}</span>
                                        <span class="px-2 py-1 text-xs rounded-full 
                                            @if($order->status === 'completed') bg-green-100 text-green-800
                                            @elseif($order->status === 'pending') bg-yellow-100 text-yellow-800
                                            @elseif($order->status === 'cancelled') bg-red-100 text-red-800
                                            @else bg-gray-100 text-gray-800 @endif">
                                            {{ ucfirst($order->status) }}
                                        </span>
                                    </div>
                                    <div class="text-sm text-gray-600">
                                        {{ $order->created_at->format('M d, Y \a\t g:i A') }}
                                    </div>
                                    <div class="text-sm text-gray-600">
                                        {{ $order->items->count() }} items • Rs.{{ number_format($order->total, 2) }}
                                    </div>
                                </div>
                                <div class="text-right">
                                    <a href="{{ route('orders.show', $order) }}" 
                                       class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                        View Details
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
                
                @if($totalOrders > 5)
                    <div class="mt-4 text-center">
                        <a href="{{ route('orders') }}" 
                           class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                            View All Orders →
                        </a>
                    </div>
                @endif
            @else
                <div class="text-center py-8">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">No orders yet</h3>
                    <p class="mt-1 text-sm text-gray-500">Start shopping to see your order history here.</p>
                    <div class="mt-6">
                        <a href="{{ route('menu') }}" 
                           class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                            Browse Menu
                        </a>
                    </div>
                </div>
            @endif
        </div>

        <!-- Quick Actions -->
        <div class="bg-gray-50 p-4 rounded-lg">
            <h3 class="text-sm font-semibold text-gray-900 mb-3">Quick Actions</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                <a href="{{ route('menu') }}" 
                   class="flex items-center p-3 bg-white rounded-lg border border-gray-200 hover:border-blue-300 hover:bg-blue-50 transition-colors">
                    <svg class="w-5 h-5 text-blue-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                    </svg>
                    <span class="text-sm font-medium text-gray-900">Browse Menu</span>
                </a>
                <a href="{{ route('cart') }}" 
                   class="flex items-center p-3 bg-white rounded-lg border border-gray-200 hover:border-blue-300 hover:bg-blue-50 transition-colors">
                    <svg class="w-5 h-5 text-blue-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 13m0 0l-2.5 5M7 13l2.5 5m6-5v6a2 2 0 01-2 2H9a2 2 0 01-2-2v-6m8 0V9a2 2 0 00-2-2H9a2 2 0 00-2 2v4.01"></path>
                    </svg>
                    <span class="text-sm font-medium text-gray-900">View Cart</span>
                </a>
            </div>
        </div>
    </div>
</div> 