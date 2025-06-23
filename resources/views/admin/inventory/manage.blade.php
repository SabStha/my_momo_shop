@extends('layouts.admin')

@section('title', isset($branch) ? "{$branch->name} - Manage Inventory" : 'Manage Inventory')

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
                    <h2 class="text-xl font-semibold text-gray-800">Manage Inventory</h2>
                    <p class="text-sm text-gray-600">Bulk inventory management tools</p>
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

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
        <!-- Bulk Update Card -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Bulk Update</h3>
                <p class="text-sm text-gray-600 mb-4">Update multiple items at once</p>
                <form action="{{ route('admin.inventory.bulk-update') }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label for="category" class="block text-sm font-medium text-gray-700">Category</label>
                        <select name="category_id" id="category" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="">All Categories</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="update_field" class="block text-sm font-medium text-gray-700">Update Field</label>
                        <select name="update_field" id="update_field" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="price">Unit Price</option>
                            <option value="quantity">Quantity</option>
                            <option value="status">Status</option>
                        </select>
                    </div>
                    <div>
                        <label for="update_value" class="block text-sm font-medium text-gray-700">New Value</label>
                        <input type="text" name="update_value" id="update_value" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                    <button type="submit" class="w-full inline-flex justify-center items-center px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                        <i class="fas fa-save mr-2"></i>Update Items
                    </button>
                </form>
            </div>
        </div>

        <!-- Export Card -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Export Data</h3>
                <p class="text-sm text-gray-600 mb-4">Export inventory data to CSV</p>
                <form action="{{ route('admin.inventory.export') }}" method="GET" class="space-y-4">
                    <div>
                        <label for="export_type" class="block text-sm font-medium text-gray-700">Export Type</label>
                        <select name="export_type" id="export_type" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="all">All Items</option>
                            <option value="low_stock">Low Stock Items</option>
                            <option value="category">By Category</option>
                        </select>
                    </div>
                    <div id="categorySelect" class="hidden">
                        <label for="export_category" class="block text-sm font-medium text-gray-700">Category</label>
                        <select name="category_id" id="export_category" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="w-full inline-flex justify-center items-center px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2">
                        <i class="fas fa-download mr-2"></i>Export Data
                    </button>
                </form>
            </div>
        </div>

        <!-- Import Card -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Import Data</h3>
                <p class="text-sm text-gray-600 mb-4">Import inventory data from CSV</p>
                <form action="{{ route('admin.inventory.import') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                    @csrf
                    <div>
                        <label for="import_file" class="block text-sm font-medium text-gray-700">CSV File</label>
                        <input type="file" name="import_file" id="import_file" accept=".csv" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                    </div>
                    <div>
                        <label class="flex items-center">
                            <input type="checkbox" name="update_existing" value="1" class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                            <span class="ml-2 text-sm text-gray-600">Update existing items</span>
                        </label>
                    </div>
                    <button type="submit" class="w-full inline-flex justify-center items-center px-4 py-2 bg-purple-600 text-white rounded-md hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-2">
                        <i class="fas fa-upload mr-2"></i>Import Data
                    </button>
                </form>
            </div>
        </div>

        <!-- Manage Orders Card -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Manage Orders</h3>
                <p class="text-sm text-gray-600 mb-4">Create and manage inventory orders</p>
                <div class="space-y-3">
                    <a href="{{ route('admin.inventory.orders.index', isset($branch) ? ['branch' => $branch->id] : []) }}" 
                       class="w-full inline-flex justify-center items-center px-4 py-2 bg-orange-600 text-white rounded-md hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2">
                        <i class="fas fa-list mr-2"></i>View All Orders
                    </a>
                    <a href="{{ route('admin.inventory.orders.create', isset($branch) ? ['branch' => $branch->id] : []) }}" 
                       class="w-full inline-flex justify-center items-center px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2">
                        <i class="fas fa-plus mr-2"></i>Create New Order
                    </a>
                    <a href="{{ route('admin.inventory.orders.history', isset($branch) ? ['branch' => $branch->id] : []) }}" 
                       class="w-full inline-flex justify-center items-center px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                        <i class="fas fa-history mr-2"></i>Order History
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Inventory Items Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
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
                                    <a href="{{ route('admin.inventory.edit', $item) }}" 
                                       class="text-blue-600 hover:text-blue-900" 
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
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const branchSelector = document.getElementById('branchSelector');
        if (branchSelector) {
            branchSelector.addEventListener('change', function(e) {
                if (e.target.value) {
                    window.location.href = e.target.value;
                }
            });
        }

        const exportType = document.getElementById('export_type');
        const categorySelect = document.getElementById('categorySelect');
        if (exportType && categorySelect) {
            exportType.addEventListener('change', function(e) {
                categorySelect.classList.toggle('hidden', e.target.value !== 'category');
            });
        }
    });
</script>
@endpush
@endsection
