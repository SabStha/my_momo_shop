@extends('layouts.admin')

@section('title', isset($branch) ? "{$branch->name} - Inventory" : 'Inventory Management')

@section('content')
<div class="max-w-7xl mx-auto py-10 px-4">
    @if(isset($branch))
    <div class="mb-6 bg-white shadow rounded-lg p-4">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-4">
                <div class="bg-purple-100 p-3 rounded-full">
                    <i class="fas fa-store text-purple-600 text-xl"></i>
                </div>
                <div>
                    <h2 class="text-xl font-semibold text-gray-800">{{ $branch->name }}</h2>
                    <div class="flex items-center space-x-2">
                        <span class="text-sm text-gray-600">Branch Code: {{ $branch->code }}</span>
                        @if($branch->is_main)
                            <span class="px-2 py-1 text-xs font-semibold bg-green-100 text-green-800 rounded-full">Main Branch</span>
                        @endif
                    </div>
                </div>
            </div>
            <div class="flex items-center space-x-4">
                <form action="{{ route('admin.inventory.index') }}" method="GET" class="inline">
                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2">
                        <i class="fas fa-sign-out-alt mr-2"></i>Exit Branch View
                    </button>
                </form>
            </div>
        </div>
    </div>
    @else
    <div class="mb-6 bg-white shadow rounded-lg p-4">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-4">
                <div class="bg-blue-100 p-3 rounded-full">
                    <i class="fas fa-warehouse text-blue-600 text-xl"></i>
                </div>
                <div>
                    <h2 class="text-xl font-semibold text-gray-800">Main Inventory</h2>
                    <p class="text-sm text-gray-600">Viewing all inventory items</p>
                </div>
            </div>
            <div class="flex items-center space-x-4">
                <div class="relative">
                    <select id="branchSelector" class="block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                        <option value="">Select Branch</option>
                        @foreach($branches as $branch)
                            <option value="{{ route('admin.branches.show', $branch->id) }}">
                                {{ $branch->name }} {{ $branch->is_main ? '(Main Branch)' : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
    </div>
    @endif

    <div class="container mx-auto px-4 py-8">
        <div class="flex flex-col md:flex-row justify-between items-center gap-4 mb-6">
            <h2 class="text-2xl font-bold text-gray-800">Inventory Management</h2>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('admin.inventory.create', isset($branch) ? ['branch' => $branch->id] : []) }}" 
                   class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                    <i class="fas fa-plus mr-2"></i> Add New Item
                </a>
                <a href="{{ route('admin.inventory.categories', isset($branch) ? ['branch' => $branch->id] : []) }}" 
                   class="inline-flex items-center px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2">
                    <i class="fas fa-tags mr-2"></i> Categories
                </a>
                <a href="{{ route('admin.suppliers.index', isset($branch) ? ['branch' => $branch->id] : []) }}" 
                   class="inline-flex items-center px-4 py-2 bg-cyan-600 text-white rounded-md hover:bg-cyan-700 focus:outline-none focus:ring-2 focus:ring-cyan-500 focus:ring-offset-2">
                    <i class="fas fa-truck mr-2"></i> Suppliers
                </a>
                <a href="{{ route('admin.inventory.bulk-order', isset($branch) ? ['branch' => $branch->id] : []) }}" 
                   class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                    <i class="fas fa-boxes mr-2"></i> Bulk Order
                </a>
                <a href="{{ route('admin.inventory.lock', isset($branch) ? ['branch' => $branch->id] : []) }}" 
                   class="inline-flex items-center px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2">
                    <i class="fas fa-lock mr-2"></i> Lock Inventory
                </a>
                <a href="{{ route('admin.inventory.daily-check', isset($branch) ? ['branch' => $branch->id] : []) }}" 
                   class="inline-flex items-center px-4 py-2 bg-yellow-600 text-white rounded-md hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:ring-offset-2">
                    <i class="fas fa-clipboard-check mr-2"></i> Daily Check
                </a>
                <a href="{{ route('admin.inventory.orders.history', isset($branch) ? ['branch' => $branch->id] : []) }}" 
                   class="inline-flex items-center px-4 py-2 bg-purple-600 text-white rounded-md hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-2">
                    <i class="fas fa-history mr-2"></i> Order History
                </a>
            </div>
        </div>

        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                <span class="block sm:inline">{{ session('success') }}</span>
            </div>
        @endif

        @if(session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                <span class="block sm:inline">{{ session('error') }}</span>
            </div>
        @endif

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <div class="bg-yellow-50 border border-yellow-200 rounded-lg shadow-sm p-6">
                <h5 class="text-lg font-semibold text-yellow-800">Low Stock Items</h5>
                <p class="text-3xl mt-2 font-bold text-yellow-600">{{ $lowStockCount }}</p>
            </div>
            <div class="bg-blue-50 border border-blue-200 rounded-lg shadow-sm p-6">
                <h5 class="text-lg font-semibold text-blue-800">Total Items</h5>
                <p class="text-3xl mt-2 font-bold text-blue-600">{{ $items->total() }}</p>
            </div>
            <div class="bg-green-50 border border-green-200 rounded-lg shadow-sm p-6">
                <h5 class="text-lg font-semibold text-green-800">Active Orders</h5>
                <p class="text-3xl mt-2 font-bold text-green-600">{{ $orders->where('status', 'pending')->count() }}</p>
            </div>
        </div>

        <!-- Inventory Items Table -->
        <div class="bg-white rounded-lg shadow overflow-hidden mb-8">
            <div class="p-4">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Inventory Items</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">SKU</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Unit</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Unit Price</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($items as $item)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $item->sku }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $item->name }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $item->category->name ?? 'Uncategorized' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm {{ $item->needsRestock() ? 'text-red-600 font-semibold' : 'text-gray-900' }}">
                                    {{ $item->quantity }} {{ $item->unit }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $item->unit }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Rs. {{ number_format($item->unit_price, 2) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full
                                        {{ $item->status === 'active' ? 'bg-green-100 text-green-800' :
                                           ($item->status === 'inactive' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                                        {{ ucfirst($item->status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                                    <div class="flex justify-center space-x-3">
                                        <a href="{{ route('admin.inventory.show', $item) }}" 
                                           class="text-blue-600 hover:text-blue-900" 
                                           title="View">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('admin.inventory.edit', $item->id) }}" 
                                           class="text-green-600 hover:text-green-900" 
                                           title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('admin.inventory.destroy', $item) }}" 
                                              method="POST" 
                                              class="inline"
                                              onsubmit="return confirm('Are you sure you want to delete this item?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" 
                                                    class="text-red-600 hover:text-red-900" 
                                                    title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-6">
                    {{ $items->links() }}
                </div>
            </div>
        </div>

        <!-- Recent Orders Table -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="p-4">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Recent Orders</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Order #</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Supplier</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($orders as $order)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $order->order_number }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $order->supplier->name }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $order->ordered_at->format('M d, Y') }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Rs. {{ number_format($order->total_amount, 2) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full
                                        {{ $order->status === 'completed' ? 'bg-green-100 text-green-800' :
                                           ($order->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                                        {{ ucfirst($order->status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                                    <div class="flex justify-center space-x-3">
                                        <a href="{{ route('admin.inventory.orders.show', $order) }}" 
                                           class="text-blue-600 hover:text-blue-900" 
                                           title="View">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        @if($order->status === 'pending')
                                        <a href="{{ route('admin.inventory.orders.edit', $order) }}" 
                                           class="text-green-600 hover:text-green-900" 
                                           title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-6">
                    {{ $orders->links() }}
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const branchSelector = document.getElementById('branchSelector');
        if (branchSelector) {
            branchSelector.addEventListener('change', function(e) {
                e.preventDefault();
                const selectedUrl = this.value;
                if (selectedUrl) {
                    window.location.href = selectedUrl;
                }
            });
        }
    });
</script>
@endpush

@endsection
