@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Orders Management</h1>
    </div>

    <!-- Tabs -->
    <div class="mb-6 border-b border-gray-200">
        <ul class="flex flex-wrap -mb-px" id="orderTabs" role="tablist">
            <li class="mr-2" role="presentation">
                <button class="inline-block p-4 border-b-2 rounded-t-lg" id="inline-tab" data-tab="inline" type="button" role="tab">
                    Inline Orders
                </button>
            </li>
            <li class="mr-2" role="presentation">
                <button class="inline-block p-4 border-b-2 rounded-t-lg" id="pos-tab" data-tab="pos" type="button" role="tab">
                    POS Orders
                </button>
            </li>
            <li class="mr-2" role="presentation">
                <button class="inline-block p-4 border-b-2 rounded-t-lg" id="history-tab" data-tab="history" type="button" role="tab">
                    Order History
                </button>
            </li>
        </ul>
    </div>

    <!-- Tab Content -->
    <div id="orderTabContent">
        <!-- Inline Orders Section -->
        <div class="hidden p-4 rounded-lg bg-gray-50" id="inline-content" role="tabpanel">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($inlineOrders as $order)
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex justify-between items-start mb-4">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">Order #{{ $order->order_number }}</h3>
                            <p class="text-sm text-gray-500">{{ $order->created_at->format('M d, Y H:i') }}</p>
                        </div>
                        <span class="px-2 py-1 text-xs font-semibold rounded-full 
                            @if($order->status === 'pending') bg-yellow-100 text-yellow-800
                            @elseif($order->status === 'processing') bg-blue-100 text-blue-800
                            @elseif($order->status === 'completed') bg-green-100 text-green-800
                            @else bg-red-100 text-red-800 @endif">
                            {{ ucfirst($order->status) }}
                        </span>
                    </div>
                    <div class="space-y-2">
                        <p class="text-sm text-gray-600">Customer: {{ $order->user->name ?? 'Guest' }}</p>
                        <p class="text-sm text-gray-600">Total: Rs {{ number_format($order->grand_total, 2) }}</p>
                        <p class="text-sm text-gray-600">Items: {{ $order->items->count() }}</p>
                    </div>
                    <div class="mt-4 flex justify-end space-x-2">
                        <a href="{{ route('orders.show', $order) }}" class="px-3 py-1 text-sm bg-blue-500 text-white rounded hover:bg-blue-600 transition">
                            View Details
                        </a>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        <!-- POS Orders Section -->
        <div class="hidden p-4 rounded-lg bg-gray-50" id="pos-content" role="tabpanel">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($posOrders as $order)
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex justify-between items-start mb-4">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">POS Order #{{ $order->order_number }}</h3>
                            <p class="text-sm text-gray-500">{{ $order->created_at->format('M d, Y H:i') }}</p>
                        </div>
                        <span class="px-2 py-1 text-xs font-semibold rounded-full 
                            @if($order->status === 'pending') bg-yellow-100 text-yellow-800
                            @elseif($order->status === 'processing') bg-blue-100 text-blue-800
                            @elseif($order->status === 'completed') bg-green-100 text-green-800
                            @else bg-red-100 text-red-800 @endif">
                            {{ ucfirst($order->status) }}
                        </span>
                    </div>
                    <div class="space-y-2">
                        <p class="text-sm text-gray-600">Table: {{ $order->table->name ?? 'N/A' }}</p>
                        <p class="text-sm text-gray-600">Total: Rs {{ number_format($order->grand_total, 2) }}</p>
                        <p class="text-sm text-gray-600">Items: {{ $order->items->count() }}</p>
                    </div>
                    <div class="mt-4 flex justify-end space-x-2">
                        <a href="{{ route('orders.show', $order) }}" class="px-3 py-1 text-sm bg-blue-500 text-white rounded hover:bg-blue-600 transition">
                            View Details
                        </a>
                        @if($order->status === 'pending')
                        <button class="px-3 py-1 text-sm bg-green-500 text-white rounded hover:bg-green-600 transition">
                            Process Payment
                        </button>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        <!-- Order History Section -->
        <div class="hidden p-4 rounded-lg bg-gray-50" id="history-content" role="tabpanel">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Order #</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($orderHistory as $order)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $order->order_number }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $order->created_at->format('M d, Y H:i') }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $order->user->name ?? 'Guest' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ ucfirst($order->type) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Rs {{ number_format($order->grand_total, 2) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 text-xs font-semibold rounded-full 
                                    @if($order->status === 'completed') bg-green-100 text-green-800
                                    @elseif($order->status === 'cancelled') bg-red-100 text-red-800
                                    @else bg-gray-100 text-gray-800 @endif">
                                    {{ ucfirst($order->status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <a href="{{ route('orders.show', $order) }}" class="text-blue-600 hover:text-blue-900">View</a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="mt-4">
                {{ $orderHistory->links() }}
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const tabs = document.querySelectorAll('[role="tab"]');
    const contents = document.querySelectorAll('[role="tabpanel"]');

    function setActiveTab(tabId) {
        // Update tab buttons
        tabs.forEach(tab => {
            if (tab.dataset.tab === tabId) {
                tab.classList.add('border-blue-500', 'text-blue-600');
                tab.classList.remove('border-transparent', 'text-gray-500', 'hover:text-gray-700', 'hover:border-gray-300');
            } else {
                tab.classList.remove('border-blue-500', 'text-blue-600');
                tab.classList.add('border-transparent', 'text-gray-500', 'hover:text-gray-700', 'hover:border-gray-300');
            }
        });

        // Update content sections
        contents.forEach(content => {
            if (content.id === `${tabId}-content`) {
                content.classList.remove('hidden');
            } else {
                content.classList.add('hidden');
            }
        });
    }

    // Add click event listeners to tabs
    tabs.forEach(tab => {
        tab.addEventListener('click', () => {
            setActiveTab(tab.dataset.tab);
        });
    });

    // Set initial active tab
    setActiveTab('inline');
});
</script>
@endpush
@endsection 