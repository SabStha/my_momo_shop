@extends('layouts.admin')

@section('title', 'Order History')

@section('content')
<div class="max-w-7xl mx-auto py-10 px-4">
    <div class="mb-6 bg-white shadow rounded-lg p-4">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-4">
                <div class="bg-blue-100 p-3 rounded-full">
                    <i class="fas fa-history text-blue-600 text-xl"></i>
                </div>
                <div>
                    <h2 class="text-xl font-semibold text-gray-800">Order History</h2>
                    <p class="text-sm text-gray-600">Track order status changes and history</p>
                </div>
            </div>
            <div class="flex items-center space-x-4">
                <a href="{{ route('admin.inventory.orders.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2">
                    <i class="fas fa-arrow-left mr-2"></i> Back to Orders
                </a>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow mb-6">
        <div class="p-4">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label for="order_number" class="block text-sm font-medium text-gray-700">Order Number</label>
                    <input type="text" id="order_number" placeholder="Search by order number"
                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                </div>
                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                    <select id="status" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                        <option value="">All Statuses</option>
                        <option value="pending">Pending</option>
                        <option value="confirmed">Confirmed</option>
                        <option value="processing">Processing</option>
                        <option value="partially_delivered">Partially Delivered</option>
                        <option value="delivered">Delivered</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                </div>
                <div>
                    <label for="date_from" class="block text-sm font-medium text-gray-700">Date From</label>
                    <input type="date" id="date_from"
                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                </div>
                <div>
                    <label for="date_to" class="block text-sm font-medium text-gray-700">Date To</label>
                    <input type="date" id="date_to"
                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                </div>
            </div>
        </div>
    </div>

    <!-- Order History Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Order #</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Supplier</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created At</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Last Updated</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Amount</th>
                        <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($orders as $order)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">#{{ $order->id }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $order->supplier->name }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 py-1 text-xs font-semibold rounded-full
                                {{ $order->status === 'pending' ? 'bg-yellow-100 text-yellow-800' :
                                   ($order->status === 'confirmed' ? 'bg-blue-100 text-blue-800' :
                                   ($order->status === 'processing' ? 'bg-indigo-100 text-indigo-800' :
                                   ($order->status === 'partially_delivered' ? 'bg-purple-100 text-purple-800' :
                                   ($order->status === 'delivered' ? 'bg-green-100 text-green-800' :
                                   'bg-red-100 text-red-800')))) }}">
                                {{ ucfirst(str_replace('_', ' ', $order->status)) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $order->created_at->format('M d, Y H:i') }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $order->updated_at->format('M d, Y H:i') }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Rs. {{ number_format($order->total, 2) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                            <div class="flex justify-center space-x-3">
                                <a href="{{ route('admin.inventory.orders.show', $order) }}" 
                                   class="text-blue-600 hover:text-blue-900" 
                                   title="View Details">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('admin.inventory.orders.timeline', $order) }}" 
                                   class="text-indigo-600 hover:text-indigo-900" 
                                   title="View Timeline">
                                    <i class="fas fa-history"></i>
                                </a>
                                @if($order->status === 'pending')
                                    <form action="{{ route('admin.inventory.orders.confirm', $order) }}" 
                                          method="POST" 
                                          class="inline">
                                        @csrf
                                        <button type="submit" 
                                                class="text-green-600 hover:text-green-900" 
                                                title="Confirm Order">
                                            <i class="fas fa-check"></i>
                                        </button>
                                    </form>
                                    <form action="{{ route('admin.inventory.orders.cancel', $order) }}" 
                                          method="POST" 
                                          class="inline">
                                        @csrf
                                        <button type="submit" 
                                                class="text-red-600 hover:text-red-900" 
                                                title="Cancel Order">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-4 text-center text-sm text-gray-500">
                            No orders found
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="px-6 py-4">
            {{ $orders->links() }}
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const orderNumberInput = document.getElementById('order_number');
    const statusSelect = document.getElementById('status');
    const dateFromInput = document.getElementById('date_from');
    const dateToInput = document.getElementById('date_to');

    // Filter orders
    function filterOrders() {
        const orderNumber = orderNumberInput.value.toLowerCase();
        const status = statusSelect.value;
        const dateFrom = dateFromInput.value;
        const dateTo = dateToInput.value;

        document.querySelectorAll('tbody tr').forEach(row => {
            const orderId = row.querySelector('td:first-child').textContent.toLowerCase();
            const orderStatus = row.querySelector('td:nth-child(3)').textContent.trim().toLowerCase();
            const createdAt = row.querySelector('td:nth-child(4)').textContent;
            
            const matchesOrderNumber = !orderNumber || orderId.includes(orderNumber);
            const matchesStatus = !status || orderStatus.includes(status.toLowerCase());
            const matchesDateFrom = !dateFrom || new Date(createdAt) >= new Date(dateFrom);
            const matchesDateTo = !dateTo || new Date(createdAt) <= new Date(dateTo);

            row.style.display = matchesOrderNumber && matchesStatus && matchesDateFrom && matchesDateTo ? '' : 'none';
        });
    }

    orderNumberInput.addEventListener('input', filterOrders);
    statusSelect.addEventListener('change', filterOrders);
    dateFromInput.addEventListener('change', filterOrders);
    dateToInput.addEventListener('change', filterOrders);
});
</script>
@endpush

@endsection 