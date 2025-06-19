<div class="bg-white rounded-lg shadow-md border border-gray-200 order-card" data-order-id="{{ $order->id }}" data-priority="{{ $order->order_type === 'dine_in' ? 'high' : ($order->order_type === 'takeaway' ? 'medium' : 'low') }}" data-status="{{ $order->status }}">
    <div class="px-6 py-4 border-b border-gray-200">
        <div class="flex justify-between items-center">
            <div class="flex items-center space-x-3">
                <input type="checkbox" class="order-checkbox h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded" data-order-id="{{ $order->id }}">
                <h3 class="text-lg font-semibold text-gray-900">Order #{{ $order->id }}</h3>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $order->order_type === 'dine_in' ? 'bg-blue-100 text-blue-800' : 'bg-indigo-100 text-indigo-800' }}">
                    {{ ucfirst(str_replace('_', ' ', $order->order_type)) }}
                </span>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                    {{ $order->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : 
                       ($order->status === 'processing' ? 'bg-orange-100 text-orange-800' : 'bg-green-100 text-green-800') }}">
                    {{ ucfirst($order->status) }}
                </span>
            </div>
            <div class="text-right">
                <p class="text-lg font-bold text-gray-900">₹{{ number_format($order->total, 2) }}</p>
                <p class="text-xs text-gray-500">{{ $order->created_at->diffForHumans() }}</p>
            </div>
        </div>
    </div>
    <div class="p-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <h4 class="text-sm font-medium text-gray-900 mb-2">Customer Details</h4>
                <div class="text-sm text-gray-600">
                    <p><span class="font-medium">Name:</span> {{ $order->user ? $order->user->name : 'Guest' }}</p>
                    @if($order->user && $order->user->phone)
                        <p><span class="font-medium">Phone:</span> {{ $order->user->phone }}</p>
                    @endif
                    @if($order->table)
                        <p><span class="font-medium">Table:</span> {{ $order->table->name }}</p>
                    @endif
                </div>
            </div>
            <div>
                <h4 class="text-sm font-medium text-gray-900 mb-2">Order Details</h4>
                <div class="text-sm text-gray-600">
                    <p><span class="font-medium">Items:</span> {{ $order->items->count() }} items</p>
                    <p><span class="font-medium">Status:</span> {{ ucfirst($order->status) }}</p>
                    <p><span class="font-medium">Type:</span> {{ ucfirst(str_replace('_', ' ', $order->order_type)) }}</p>
                </div>
            </div>
        </div>

        <div class="mt-6">
            <h4 class="text-sm font-medium text-gray-900 mb-3">Order Items</h4>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Item</th>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Price</th>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($order->items as $item)
                            <tr>
                                <td class="px-3 py-2 whitespace-nowrap text-sm text-gray-900">{{ $item->product->name }}</td>
                                <td class="px-3 py-2 whitespace-nowrap text-sm text-gray-500">{{ $item->quantity }}</td>
                                <td class="px-3 py-2 whitespace-nowrap text-sm text-gray-500">₹{{ number_format($item->price, 2) }}</td>
                                <td class="px-3 py-2 whitespace-nowrap text-sm text-gray-900">₹{{ number_format($item->quantity * $item->price, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-6 flex space-x-3">
            <button class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium" onclick="processPayment({{ $order->id }})" data-payment-method="cash">
                <i class="fas fa-money-bill-wave mr-1"></i> Process Payment
            </button>
            <button class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-medium" onclick="quickProcess({{ $order->id }}, 'card')">
                <i class="fas fa-credit-card mr-1"></i> Quick Card
            </button>
            <button class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg text-sm font-medium" onclick="quickProcess({{ $order->id }}, 'mobile')">
                <i class="fas fa-mobile-alt mr-1"></i> Quick Mobile
            </button>
            <a href="{{ route('admin.payments.show', $order->id) }}" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg text-sm font-medium">
                <i class="fas fa-eye mr-1"></i> View Details
            </a>
        </div>
    </div>
</div>

@push('scripts')
<script>
function processPayment(orderId) {
    // Show payment modal
    const modal = new bootstrap.Modal(document.getElementById('paymentModal'));
    document.getElementById('orderIdInput').value = orderId;
    modal.show();
}
</script>
@endpush 