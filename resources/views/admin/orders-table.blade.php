<table class="min-w-full bg-white divide-y divide-gray-200">
    <thead class="bg-gray-50">
        <tr>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Order ID</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Payment Status</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
        </tr>
    </thead>
    <tbody class="bg-white divide-y divide-gray-200">
        @forelse($orders as $order)
        <tr>
            <td class="px-6 py-4 whitespace-nowrap">#{{ $order->id }}</td>
            <td class="px-6 py-4 whitespace-nowrap">{{ $order->user->name ?? 'Guest' }}</td>
            <td class="px-6 py-4 whitespace-nowrap">{{ $order->created_at->format('M d, Y H:i') }}</td>
            <td class="px-6 py-4 whitespace-nowrap">{{ number_format($order->total_amount, 2) }}</td>
            <td class="px-6 py-4 whitespace-nowrap">
                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $order->status === 'completed' ? 'bg-green-100 text-green-800' : ($order->status === 'cancelled' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800') }}">
                    {{ ucfirst($order->status) }}
                </span>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $order->payment_status === 'paid' ? 'bg-green-100 text-green-800' : ($order->payment_status === 'failed' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800') }}">
                    {{ ucfirst($order->payment_status) }}
                </span>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                <a href="{{ route('admin.orders.show', $order) }}" class="text-indigo-600 hover:text-indigo-900 mr-3">View</a>
            </td>
        </tr>
        @empty
        <tr>
            <td colspan="7" class="px-6 py-4 text-center text-gray-500">No orders found.</td>
        </tr>
        @endforelse
    </tbody>
</table> 