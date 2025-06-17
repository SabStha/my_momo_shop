@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="bg-white rounded-lg shadow-lg p-6">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold text-gray-800">Order History</h2>
            <div class="flex space-x-4">
                <div class="relative">
                    <input type="text" 
                           placeholder="Search orders..." 
                           class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                           id="searchInput">
                </div>
                <select class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                        id="filterType">
                    <option value="all">All Orders</option>
                    <option value="inline">Inline Orders</option>
                    <option value="dine-in">Dine In</option>
                    <option value="takeaway">Takeaway</option>
                </select>
                <input type="date" 
                       class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                       id="dateFilter">
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Order ID</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($orders as $order)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                            #{{ $order->id }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                @if($order->type === 'inline') bg-blue-100 text-blue-800
                                @elseif($order->type === 'dine-in') bg-green-100 text-green-800
                                @else bg-yellow-100 text-yellow-800 @endif">
                                {{ ucfirst($order->type) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $order->customer_name }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $order->created_at->format('M d, Y H:i') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                @if($order->status === 'completed') bg-green-100 text-green-800
                                @elseif($order->status === 'cancelled') bg-red-100 text-red-800
                                @else bg-yellow-100 text-yellow-800 @endif">
                                {{ ucfirst($order->status) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            ${{ number_format($order->total_amount, 2) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <div class="flex space-x-2">
                                <button class="text-blue-600 hover:text-blue-900">View</button>
                                <button class="text-green-600 hover:text-green-900">Print</button>
                                @if($order->status === 'completed')
                                <button class="text-gray-600 hover:text-gray-900">Refund</button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $orders->links() }}
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('searchInput');
        const filterType = document.getElementById('filterType');
        const dateFilter = document.getElementById('dateFilter');

        function applyFilters() {
            const searchTerm = searchInput.value.toLowerCase();
            const typeFilter = filterType.value;
            const dateValue = dateFilter.value;

            const rows = document.querySelectorAll('tbody tr');
            
            rows.forEach(row => {
                const orderId = row.cells[0].textContent.toLowerCase();
                const orderType = row.cells[1].textContent.toLowerCase();
                const orderDate = row.cells[3].textContent;
                
                const matchesSearch = orderId.includes(searchTerm);
                const matchesType = typeFilter === 'all' || orderType.includes(typeFilter);
                const matchesDate = !dateValue || orderDate.includes(dateValue);

                row.style.display = matchesSearch && matchesType && matchesDate ? '' : 'none';
            });
        }

        searchInput.addEventListener('input', applyFilters);
        filterType.addEventListener('change', applyFilters);
        dateFilter.addEventListener('change', applyFilters);
    });
</script>
@endpush
@endsection 