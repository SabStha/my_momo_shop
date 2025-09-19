<!-- Order History -->
<div class="bg-gradient-to-br from-blue-50 to-indigo-100 rounded-xl shadow-lg border border-blue-200 mb-4 lg:mb-6 overflow-hidden">
    <!-- Header Section -->
    <div class="relative">
        <div class="absolute inset-0 bg-gradient-to-r from-blue-600/10 to-indigo-600/10"></div>
        <div class="relative px-6 py-6">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between space-y-4 md:space-y-0">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900 mb-2">Order History</h2>
                    <p class="text-gray-600">Track your past orders and their status</p>
                </div>
                
                <!-- Search and Filter Controls -->
                <div class="flex flex-col sm:flex-row space-y-2 sm:space-y-0 sm:space-x-3">
                    <div class="relative">
                        <input type="text" 
                               id="orderSearchInput"
                               placeholder="Search orders..." 
                               class="w-full sm:w-64 px-4 py-2 pl-10 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors bg-white/80 backdrop-blur-sm">
                        <svg class="absolute left-3 top-2.5 h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>
                    <select id="orderStatusFilter" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white/80 backdrop-blur-sm">
                        <option value="all">All Orders</option>
                        <option value="completed">Completed</option>
                        <option value="processing">Processing</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <!-- Content Section -->
    <div class="bg-white/60 backdrop-blur-sm border-t border-white/30 px-6 py-6">
        @php
            $orders = auth()->user()->orders()->latest()->paginate(10);
        @endphp
        
        @if($orders->count() > 0)
            <div class="space-y-4" id="ordersContainer">
                @foreach($orders as $order)
                    <div class="order-card bg-white/90 backdrop-blur-sm border border-gray-200 rounded-xl p-6 hover:shadow-lg hover:border-blue-300 transition-all duration-300 transform hover:-translate-y-1">
                        <!-- Order Header -->
                        <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-4">
                            <div class="flex items-center space-x-4">
                                <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-full flex items-center justify-center shadow-lg">
                                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="text-lg font-semibold text-gray-900">Order #{{ $order->order_number ?? $order->id }}</h3>
                                    <p class="text-sm text-gray-500 flex items-center">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                        </svg>
                                        {{ $order->created_at->format('M d, Y \a\t g:i A') }}
                                    </p>
                                </div>
                            </div>
                            <div class="flex flex-col items-end space-y-2 mt-4 md:mt-0">
                                <span class="px-3 py-1 text-sm font-semibold rounded-full 
                                    @if($order->status === 'completed') bg-green-100 text-green-800 border border-green-200
                                    @elseif($order->status === 'cancelled') bg-red-100 text-red-800 border border-red-200
                                    @elseif($order->status === 'processing') bg-blue-100 text-blue-800 border border-blue-200
                                    @else bg-gray-100 text-gray-800 border border-gray-200 @endif">
                                    {{ ucfirst($order->status) }}
                                </span>
                                <p class="text-xl font-bold text-gray-900">Rs {{ number_format($order->grand_total ?? $order->total, 2) }}</p>
                            </div>
                        </div>
                        
                        <!-- Order Items -->
                        @if($order->items->count() > 0)
                            <div class="border-t border-gray-200 pt-4 mb-4">
                                <h4 class="text-sm font-semibold text-gray-700 mb-3 flex items-center">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                                    </svg>
                                    Order Items
                                </h4>
                                <div class="space-y-2">
                                    @foreach($order->items->take(3) as $item)
                                        <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                                            <div class="flex items-center space-x-3">
                                                <div class="w-8 h-8 bg-gradient-to-br from-orange-400 to-red-500 rounded-full flex items-center justify-center">
                                                    <span class="text-white text-xs font-bold">{{ $item->quantity }}</span>
                                                </div>
                                                <span class="text-gray-700 font-medium">{{ $item->product->name ?? 'Product' }}</span>
                                            </div>
                                            <span class="text-gray-900 font-semibold">Rs {{ number_format($item->price * $item->quantity, 2) }}</span>
                                        </div>
                                    @endforeach
                                    @if($order->items->count() > 3)
                                        <div class="text-center py-2">
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                +{{ $order->items->count() - 3 }} more items
                                            </span>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endif
                        
                        <!-- Order Footer -->
                        <div class="border-t border-gray-200 pt-4">
                            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between space-y-3 sm:space-y-0">
                                <div class="flex items-center space-x-2">
                                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    </svg>
                                    <span class="text-sm text-gray-600">
                                        @if($order->order_type)
                                            {{ ucfirst(str_replace('_', ' ', $order->order_type)) }}
                                        @else
                                            Online Order
                                        @endif
                                    </span>
                                </div>
                                <a href="{{ route('orders.show', $order) }}" 
                                   class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-blue-600 to-indigo-600 text-white text-sm font-medium rounded-lg hover:from-blue-700 hover:to-indigo-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-all duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                    </svg>
                                    View Details
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            
            @if($orders->hasPages())
                <div class="mt-8 flex justify-center">
                    <div class="bg-white/80 backdrop-blur-sm rounded-lg p-4 shadow-lg">
                        {{ $orders->links() }}
                    </div>
                </div>
            @endif
        @else
            <!-- Enhanced Empty State -->
            <div class="text-center py-12">
                <div class="w-24 h-24 bg-gradient-to-br from-blue-100 to-indigo-100 rounded-full flex items-center justify-center mx-auto mb-6 shadow-lg">
                    <svg class="w-12 h-12 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                    </svg>
                </div>
                <h3 class="text-xl font-semibold text-gray-900 mb-2">No orders yet</h3>
                <p class="text-gray-600 mb-8 max-w-md mx-auto">Start your culinary journey with us! Browse our delicious menu and place your first order.</p>
                <div class="space-y-4">
                    <a href="{{ route('menu') }}" class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 text-white font-medium rounded-lg hover:from-blue-700 hover:to-indigo-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-all duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        Browse Menu
                    </a>
                </div>
            </div>
        @endif
    </div>
</div>

<!-- JavaScript for Search and Filter -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('orderSearchInput');
    const statusFilter = document.getElementById('orderStatusFilter');
    const ordersContainer = document.getElementById('ordersContainer');
    
    if (searchInput && statusFilter && ordersContainer) {
        const orderCards = Array.from(ordersContainer.querySelectorAll('.order-card'));
        
        function filterOrders() {
            const searchTerm = searchInput.value.toLowerCase();
            const statusFilterValue = statusFilter.value;
            
            orderCards.forEach(card => {
                const orderText = card.textContent.toLowerCase();
                const statusElement = card.querySelector('span[class*="bg-"]');
                const orderStatus = statusElement ? statusElement.textContent.toLowerCase() : '';
                
                const matchesSearch = orderText.includes(searchTerm);
                const matchesStatus = statusFilterValue === 'all' || orderStatus.includes(statusFilterValue);
                
                if (matchesSearch && matchesStatus) {
                    card.style.display = 'block';
                    card.style.animation = 'fadeIn 0.3s ease-in-out';
                } else {
                    card.style.display = 'none';
                }
            });
        }
        
        searchInput.addEventListener('input', filterOrders);
        statusFilter.addEventListener('change', filterOrders);
    }
});

// Add CSS animation
const style = document.createElement('style');
style.textContent = `
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
`;
document.head.appendChild(style);
</script> 