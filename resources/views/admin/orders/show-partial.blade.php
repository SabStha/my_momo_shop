<div>
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold text-gray-900">Order #{{ $order->id }}</h2>
        <span class="inline-flex px-3 py-1 text-sm font-semibold rounded-full 
            {{ $order->status === 'completed' ? 'bg-green-100 text-green-800' : 
               ($order->status === 'cancelled' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800') }}">
            {{ ucfirst($order->status) }}
        </span>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Order Details -->
        <div class="lg:col-span-2">
            <div class="bg-gray-50 border rounded-lg p-6 h-full">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Order Information</h3>
                
                <div class="grid grid-cols-2 md:grid-cols-3 gap-4 mb-6">
                    <div>
                        <label class="block text-xs font-medium text-gray-500 uppercase">Order Type</label>
                        <p class="mt-1 text-sm text-gray-900 font-medium">{{ ucfirst(str_replace('_', ' ', $order->order_type)) }}</p>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 uppercase">Order Number</label>
                        <p class="mt-1 text-sm text-gray-900 font-medium">{{ $order->order_number ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 uppercase">Created At</label>
                        <p class="mt-1 text-sm text-gray-900 font-medium">{{ $order->created_at->format('M d, Y H:i') }}</p>
                    </div>
                </div>

                <!-- Order Items -->
                <h4 class="text-md font-semibold text-gray-900 mb-3 border-b pb-2">Items</h4>
                <div class="overflow-x-auto mb-4">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead>
                            <tr>
                                <th class="py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Item</th>
                                <th class="py-2 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Qty</th>
                                <th class="py-2 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Price</th>
                                <th class="py-2 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse ($order->items as $item)
                            <tr>
                                <td class="py-3 text-sm text-gray-900 font-medium">
                                    {{ $item->product->name ?? $item->item_name }}
                                </td>
                                <td class="py-3 text-sm text-gray-900 text-right">{{ $item->quantity }}</td>
                                <td class="py-3 text-sm text-gray-900 text-right">Rs. {{ number_format($item->price, 2) }}</td>
                                <td class="py-3 text-sm text-gray-900 text-right">Rs. {{ number_format($item->subtotal, 2) }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="py-3 text-center text-gray-500">No items found</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Order Totals -->
                <div class="border-t pt-4">
                    <div class="flex justify-between items-center mb-2">
                        <span class="text-sm text-gray-600">Subtotal:</span>
                        <span class="text-sm font-medium text-gray-900">Rs. {{ number_format($order->total_amount, 2) }}</span>
                    </div>
                    @if($order->tax_amount)
                    <div class="flex justify-between items-center mb-2">
                        <span class="text-sm text-gray-600">Tax:</span>
                        <span class="text-sm font-medium text-gray-900">Rs. {{ number_format($order->tax_amount, 2) }}</span>
                    </div>
                    @endif
                    <div class="flex justify-between items-center mt-3 pt-3 border-t text-lg font-bold">
                        <span class="text-gray-900">Total:</span>
                        <span class="text-indigo-600">Rs. {{ number_format($order->grand_total ?? $order->total_amount, 2) }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Customer & Payment Info -->
        <div class="space-y-6">
            <!-- Customer Information -->
            <div class="bg-gray-50 border rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Customer Details</h3>
                <div class="space-y-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-500 uppercase">Name</label>
                        <p class="mt-1 text-sm text-gray-900 font-medium">{{ $order->user->name ?? $order->guest_name ?? 'Guest' }}</p>
                    </div>
                    @if($order->user && $order->user->email)
                    <div>
                        <label class="block text-xs font-medium text-gray-500 uppercase">Email</label>
                        <p class="mt-1 text-sm text-gray-900 break-all">{{ $order->user->email }}</p>
                    </div>
                    @elseif($order->guest_email)
                    <div>
                        <label class="block text-xs font-medium text-gray-500 uppercase">Guest Email</label>
                        <p class="mt-1 text-sm text-gray-900 break-all">{{ $order->guest_email }}</p>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Delivery Address -->
            @if($order->delivery_address)
            <div class="bg-gray-50 border rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-3 flex items-center">
                    <i class="fas fa-map-marker-alt text-red-500 mr-2"></i> Delivery Address
                </h3>
                <div class="text-sm text-gray-700 space-y-1">
                    @if(is_array($order->delivery_address))
                        @if(!empty($order->delivery_address['building_name']))
                            <p class="font-medium text-gray-900">{{ $order->delivery_address['building_name'] }}</p>
                        @endif
                        @if(!empty($order->delivery_address['area_locality']))
                            <p>{{ $order->delivery_address['area_locality'] }}</p>
                        @endif
                        @if(!empty($order->delivery_address['city']))
                            <p>{{ $order->delivery_address['city'] }}{{ !empty($order->delivery_address['ward_number']) ? ', Ward ' . $order->delivery_address['ward_number'] : '' }}</p>
                        @endif
                        @if(!empty($order->delivery_address['detailed_directions']))
                            <div class="mt-3 pt-3 border-t">
                                <p class="text-xs font-semibold text-gray-500 uppercase mb-1">Instructions:</p>
                                <p class="italic text-gray-600">{{ $order->delivery_address['detailed_directions'] }}</p>
                            </div>
                        @endif
                    @else
                        <p>{{ $order->delivery_address }}</p>
                    @endif
                </div>
            </div>
            @endif

            <!-- Payment Information -->
            <div class="bg-gray-50 border rounded-lg p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">Payment</h3>
                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full 
                        {{ $order->payment_status === 'paid' ? 'bg-green-100 text-green-800' : 
                           ($order->payment_status === 'failed' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800') }}">
                        {{ ucfirst($order->payment_status) }}
                    </span>
                </div>
                
                <div class="space-y-3 border-t pt-3">
                    @if($order->payment_method)
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-600">Method</span>
                        <span class="text-sm font-medium text-gray-900">{{ ucfirst($order->payment_method) }}</span>
                    </div>
                    @endif
                    @if($order->amount_received)
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-600">Amount Received</span>
                        <span class="text-sm font-medium text-green-600">Rs. {{ number_format($order->amount_received, 2) }}</span>
                    </div>
                    @endif
                    @if($order->change)
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-600">Change</span>
                        <span class="text-sm font-medium text-red-500">Rs. {{ number_format($order->change, 2) }}</span>
                    </div>
                    @endif
                    @if($order->reference_number)
                    <div>
                        <span class="text-xs text-gray-500 uppercase block mb-1">Reference No.</span>
                        <span class="text-sm font-mono text-gray-900 bg-white border px-2 py-1 rounded block truncate" title="{{ $order->reference_number }}">{{ $order->reference_number }}</span>
                    </div>
                    @endif
                </div>
                
                @if($order->payment_status !== 'paid')
                <div class="mt-4 pt-4 border-t">
                    <form action="{{ route('admin.orders.process-payment') }}" method="POST">
                        @csrf
                        <input type="hidden" name="order_id" value="{{ $order->id }}">
                        <!-- Basic fallback, real payment might require more fields -->
                        <input type="hidden" name="payment_method" value="cash">
                        <input type="hidden" name="amount" value="{{ $order->grand_total ?? $order->total_amount }}">
                        <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md text-sm font-medium transition-colors">
                            <i class="fas fa-check-circle mr-1"></i> Mark as Paid (Cash)
                        </button>
                    </form>
                </div>
                @endif
            </div>
            
            <div class="flex gap-2">
                <a href="{{ route('admin.orders.kitchen-print', $order->id) }}" target="_blank" class="flex-1 bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 text-center px-4 py-2 rounded-md text-sm font-medium transition-colors">
                    <i class="fas fa-print mr-1"></i> Print Receipt
                </a>
                <a href="{{ route('admin.orders.show', $order) }}" class="flex-1 bg-indigo-50 border border-indigo-100 hover:bg-indigo-100 text-indigo-700 text-center px-4 py-2 rounded-md text-sm font-medium transition-colors">
                    Full Page <i class="fas fa-external-link-alt ml-1 text-xs"></i>
                </a>
            </div>
        </div>
    </div>
</div>
