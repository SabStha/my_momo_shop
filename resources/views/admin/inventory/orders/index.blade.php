@extends('layouts.admin')

@section('title', isset($branch) ? "{$branch->name} - Inventory Items" : 'Inventory Items')

@section('content')
<div class="max-w-7xl mx-auto py-10 px-4">
    @if(isset($branch) && !$branch->is_main)
        <div class="bg-blue-100 border border-blue-400 text-blue-700 px-4 py-3 rounded relative mb-4" role="alert">
            <div class="flex">
                <div class="flex-shrink-0">
                    <i class="fas fa-info-circle"></i>
                </div>
                <div class="ml-3">
                    <p class="text-sm">
                        <strong>Centralized Order Management:</strong> Orders created from this branch are automatically routed to the <strong>Main Branch</strong> for supplier processing. All supplier orders are managed centrally for better coordination and efficiency.
                    </p>
                </div>
            </div>
        </div>
    @endif
    <div class="mb-6 bg-white shadow rounded-lg p-4">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-4">
                <div class="bg-blue-100 p-3 rounded-full">
                    <i class="fas fa-boxes text-blue-600 text-xl"></i>
                </div>
                <div>
                    <h2 class="text-xl font-semibold text-gray-800">Inventory Items</h2>
                    <p class="text-sm text-gray-600">View and manage all inventory products</p>
                </div>
            </div>
            <div class="flex items-center space-x-4">
                <a href="{{ route('admin.inventory.create', isset($branch) ? ['branch' => $branch->id] : []) }}" 
                   class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2">
                    <i class="fas fa-plus mr-2"></i> Add New Item
                </a>
                <button onclick="generateForecast()" 
                        class="inline-flex items-center px-4 py-2 bg-purple-600 text-white rounded-md hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-2">
                    <i class="fas fa-magic mr-2"></i> 
                    <span id="forecastButtonText">
                        {{ isset($branch) && $branch->is_main ? 'Generate Main Branch Forecast' : 'Generate Branch Forecast' }}
                    </span>
                </button>
                <a href="{{ route('admin.inventory.orders.list', isset($branch) ? ['branch' => $branch->id] : []) }}" 
                   class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                    <i class="fas fa-list mr-2"></i> View Orders
                </a>
                @if(auth()->user()->hasRole('admin') && isset($branch) && $branch->is_main)
                    <a href="{{ route('admin.inventory.orders.show', 14) }}" 
                       class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                        <i class="fas fa-truck-loading mr-2"></i> Supplier Order Details
                    </a>
                @endif
                <a href="{{ route('admin.inventory.index', isset($branch) ? ['branch' => $branch->id] : []) }}" 
                   class="inline-flex items-center px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2">
                    <i class="fas fa-arrow-left mr-2"></i> Back to Inventory
                </a>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow mb-6">
        <div class="p-4">
            <form action="{{ route('admin.inventory.orders.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-5 gap-4">
                @if(isset($branch))
                    <input type="hidden" name="branch" value="{{ $branch->id }}">
                @endif
                <div>
                    <label for="search" class="block text-sm font-medium text-gray-700">Search</label>
                    <input type="text" name="search" id="search" value="{{ request('search') }}"
                           placeholder="Search by name or SKU"
                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                </div>
                <div>
                    <label for="category" class="block text-sm font-medium text-gray-700">Category</label>
                    <select name="category" id="category" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                        <option value="">All Categories</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ request('category') == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="supplier" class="block text-sm font-medium text-gray-700">Supplier</label>
                    <select name="supplier" id="supplier" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                        <option value="">All Suppliers</option>
                        @foreach($suppliers as $supplier)
                            <option value="{{ $supplier->id }}" {{ request('supplier') == $supplier->id ? 'selected' : '' }}>
                                {{ $supplier->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                    <select name="status" id="status" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                        <option value="">All Statuses</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
                <div class="flex items-end">
                    <button type="submit" class="w-full inline-flex justify-center items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        <i class="fas fa-filter mr-2"></i> Apply Filters
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Inventory Items Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">SKU</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Supplier</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Unit Price</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($items as $item)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $item->sku }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $item->name }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $item->category->name ?? 'Uncategorized' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $item->supplier->name ?? 'No Supplier' }}</td>
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
                                <a href="{{ route('admin.inventory.show', $item) }}" 
                                   class="text-blue-600 hover:text-blue-900" 
                                   title="View">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('admin.inventory.edit', $item) }}" 
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
                    @empty
                    <tr>
                        <td colspan="8" class="px-6 py-4 text-center text-sm text-gray-500">
                            No inventory items found
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="px-6 py-4">
            {{ $items->links() }}
        </div>
    </div>
</div>

<!-- Forecast Modal -->
<div id="forecastModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900" id="forecastModalTitle">AI Intelligent Supply Forecast</h3>
                <button onclick="closeForecastModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            
            <div id="forecastLoading" class="text-center py-8">
                <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-purple-600 mx-auto"></div>
                <p class="mt-4 text-gray-600">Analyzing inventory data and generating intelligent AI forecast...</p>
                <p class="mt-2 text-sm text-gray-500">Determining fresh vs. storable items for optimal ordering strategy</p>
            </div>
            
            <div id="forecastResults" class="hidden">
                <div class="mb-4">
                    <h4 class="text-md font-semibold text-gray-800 mb-2">Forecast Summary</h4>
                    <div id="forecastSummary" class="bg-blue-50 p-3 rounded-lg"></div>
                </div>
                
                <div class="mb-4">
                    <h4 class="text-md font-semibold text-gray-800 mb-2">Recommended Orders</h4>
                    <div id="forecastOrders" class="max-h-96 overflow-y-auto"></div>
                </div>
                
                <div class="flex justify-end space-x-3">
                    <button onclick="closeForecastModal()" 
                            class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400">
                        Close
                    </button>
                    <button onclick="createOrdersFromForecast()" 
                            class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700">
                        <i class="fas fa-shopping-cart mr-2"></i>Create Orders
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div id="deleteConfirmModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <div class="flex items-center justify-center mb-4">
                <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100">
                    <i class="fas fa-exclamation-triangle text-red-600 text-xl"></i>
                </div>
            </div>
            
            <div class="text-center">
                <h3 class="text-lg font-medium text-gray-900 mb-2">Delete Item</h3>
                <p class="text-sm text-gray-500 mb-6">
                    Are you sure you want to remove "<span id="deleteItemName" class="font-semibold text-gray-700"></span>" from the forecast?
                </p>
                
                <div class="flex justify-center space-x-3">
                    <button onclick="closeDeleteConfirmModal()" 
                            class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400 transition-colors">
                        Cancel
                    </button>
                    <button onclick="confirmDeleteItem()" 
                            class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 transition-colors">
                        <i class="fas fa-trash mr-2"></i>Delete
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Success Modal -->
<div id="successModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
    <div class="bg-white rounded-lg shadow-xl max-w-md w-full mx-4 transform transition-all duration-300 scale-95 opacity-0" id="successModalContent">
        <div class="p-6 text-center">
            <!-- Success Icon -->
            <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-green-100 mb-4">
                <svg class="h-8 w-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
            </div>
            
            <!-- Success Title -->
            <h3 class="text-lg font-semibold text-gray-900 mb-2" id="successTitle">Order Created Successfully!</h3>
            
            <!-- Success Message -->
            <p class="text-sm text-gray-600 mb-6" id="successMessage">
                Your inventory order has been created and is ready for processing.
            </p>
            
            <!-- Order Details (if available) -->
            <div id="orderDetails" class="bg-gray-50 rounded-lg p-4 mb-6 hidden">
                <div class="text-left">
                    <div class="flex justify-between items-center mb-2">
                        <span class="text-sm font-medium text-gray-700">Order Number:</span>
                        <span class="text-sm text-gray-900" id="orderNumber"></span>
                    </div>
                    <div class="flex justify-between items-center mb-2">
                        <span class="text-sm font-medium text-gray-700">Total Items:</span>
                        <span class="text-sm text-gray-900" id="totalItems"></span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm font-medium text-gray-700">Total Amount:</span>
                        <span class="text-sm font-semibold text-green-600" id="totalAmount"></span>
                    </div>
                </div>
            </div>
            
            <!-- Action Buttons -->
            <div class="flex space-x-3">
                <button type="button" onclick="closeSuccessModal()" class="flex-1 px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 border border-gray-300 rounded-md hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-colors duration-200">
                    Continue
                </button>
                <button type="button" id="viewOrderBtn" onclick="viewOrderDetails()" class="flex-1 px-4 py-2 text-sm font-medium text-white bg-green-600 border border-transparent rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors duration-200">
                    View Order
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function generateForecast() {
    // Show modal and loading
    document.getElementById('forecastModal').classList.remove('hidden');
    document.getElementById('forecastLoading').classList.remove('hidden');
    document.getElementById('forecastResults').classList.add('hidden');
    
    // Get current branch
    const branchId = {{ isset($branch) ? $branch->id : 'null' }};
    const isMainBranch = branchId == 1 || branchId === null;
    
    // Update modal title and loading message based on branch type
    const modalTitle = isMainBranch ? 'Main Branch Supply Forecast' : 'Branch Supply Forecast';
    const loadingMessage = isMainBranch ? 
        'Analyzing inventory across all branches and generating bulk supply forecast...' :
        'Analyzing branch inventory and generating daily supply forecast...';
    const loadingSubtitle = isMainBranch ?
        'Calculating total demand for external supplier orders' :
        'Determining daily needs for Main Branch orders';
    
    document.getElementById('forecastModalTitle').textContent = modalTitle;
    document.querySelector('#forecastLoading p').textContent = loadingMessage;
    document.querySelector('#forecastLoading p + p').textContent = loadingSubtitle;
    
    // Make AJAX request to generate forecast
    fetch('{{ route("admin.inventory.forecast") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            branch_id: branchId
        })
    })
    .then(response => response.json())
    .then(data => {
        // Hide loading, show results
        document.getElementById('forecastLoading').classList.add('hidden');
        document.getElementById('forecastResults').classList.remove('hidden');
        
        if (data.success) {
            displayForecastResults(data.forecast);
        } else {
            document.getElementById('forecastSummary').innerHTML = 
                '<div class="text-red-600">Error: ' + data.message + '</div>';
        }
    })
    .catch(error => {
        document.getElementById('forecastLoading').classList.add('hidden');
        document.getElementById('forecastResults').classList.remove('hidden');
        document.getElementById('forecastSummary').innerHTML = 
            '<div class="text-red-600">Error: ' + error.message + '</div>';
    });
}

function displayForecastResults(forecast) {
    // Debug: Log the forecast data
    console.log('Forecast data received:', forecast);
    console.log('Items:', forecast.items);
    
    const isMainBranch = forecast.forecast_type === 'main_branch';
    const forecastTitle = isMainBranch ? 'Main Branch Supply Forecast' : 'Branch Supply Forecast';
    const forecastSubtitle = isMainBranch ? 
        'Bulk orders from external suppliers for all branches' : 
        'Daily orders from Main Branch (Central Warehouse)';
    
    // Display summary with different styling based on forecast type
    document.getElementById('forecastSummary').innerHTML = `
        <div class="mb-4">
            <h4 class="text-lg font-semibold ${isMainBranch ? 'text-purple-800' : 'text-blue-800'}">${forecastTitle}</h4>
            <p class="text-sm text-gray-600">${forecastSubtitle}</p>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="text-center">
                <div class="text-2xl font-bold ${isMainBranch ? 'text-purple-600' : 'text-blue-600'}">${forecast.total_items}</div>
                <div class="text-sm text-gray-600">Items to Order</div>
            </div>
            <div class="text-center">
                <div class="text-2xl font-bold text-green-600">Rs. ${forecast.total_value}</div>
                <div class="text-sm text-gray-600">Total Value</div>
            </div>
            <div class="text-center">
                <div class="text-2xl font-bold text-purple-600">${forecast.confidence}%</div>
                <div class="text-sm text-gray-600">AI Confidence</div>
            </div>
        </div>
        <div class="mt-4 p-3 ${isMainBranch ? 'bg-purple-50 border-l-4 border-purple-400' : 'bg-blue-50 border-l-4 border-blue-400'}">
            <div class="flex items-center">
                <i class="fas ${isMainBranch ? 'fa-warehouse text-purple-600' : 'fa-store text-blue-600'} mr-2"></i>
                <span class="text-sm ${isMainBranch ? 'text-purple-800' : 'text-blue-800'}">
                    ${isMainBranch ? 
                        '🔄 Central Warehouse: Ordering from external suppliers for all branches' : 
                        '📦 Branch Operations: Ordering from Main Branch for daily needs'
                    }
                </span>
            </div>
            ${isMainBranch ? `
                <div class="mt-3 p-2 bg-white rounded border">
                    <div class="text-xs font-semibold text-purple-700 mb-1">📊 Branch Order Analysis (Last 7 Days)</div>
                    <div class="text-xs text-gray-600">
                        <div class="grid grid-cols-2 gap-2">
                            <div>• Total Branch Orders: <span class="font-semibold">${forecast.branch_order_analysis?.total_branch_orders || 0}</span></div>
                            <div>• Items in Demand: <span class="font-semibold">${Object.keys(forecast.branch_order_analysis?.order_analysis || {}).length}</span></div>
                        </div>
                    </div>
                </div>
            ` : ''}
        </div>
    `;
    
    // Display editable orders table
    const ordersHtml = `
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Item Name</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Current Stock</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Order Qty</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Unit Price</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type & Coverage</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Supply Source</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Notes</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Priority</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200" id="forecastItemsTbody">
                ${forecast.items.map((item, idx) => {
                    // Debug: Log each item's data
                    console.log(`Item ${idx}:`, item);
                    console.log(`Item ${idx} item_type:`, item.item_type, 'Type:', typeof item.item_type);
                    console.log(`Item ${idx} coverage_days:`, item.coverage_days, 'Type:', typeof item.coverage_days);
                    console.log(`Item ${idx} ordering_strategy:`, item.ordering_strategy, 'Type:', typeof item.ordering_strategy);
                    
                    const supplySource = isMainBranch ? 'External Supplier' : 'Main Branch';
                    const supplySourceColor = isMainBranch ? 'bg-orange-100 text-orange-800' : 'bg-green-100 text-green-800';
                    
                    return `
                    <tr data-row="${idx}">
                        <td class="px-4 py-2 text-sm text-gray-900">${item.name}</td>
                        <td class="px-4 py-2 text-sm text-gray-900">${item.current_stock}</td>
                        <td class="px-4 py-2 text-sm text-gray-900">
                            <div class="flex items-center space-x-2">
                                <input type="number" min="0" class="border rounded px-2 py-1 w-20" value="${item.recommended_order}" onchange="updateForecastItem(${idx}, 'recommended_order', this.value)" id="qty-${idx}">
                                <div class="text-xs text-gray-500">
                                    <span class="block">AI: ${item.recommended_order}</span>
                                    <span class="block text-gray-400">original</span>
                                </div>
                                <div id="qty-change-${idx}" class="text-xs hidden">
                                    <span class="px-1 py-0.5 bg-yellow-100 text-yellow-800 rounded">Changed</span>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-2 text-sm text-gray-900">
                            <div class="flex items-center space-x-2">
                                <input type="number" min="0" step="0.01" class="border rounded px-2 py-1 w-24" value="${item.unit_price}" onchange="updateForecastItem(${idx}, 'unit_price', this.value)" id="price-${idx}">
                                <div class="text-xs text-gray-500">
                                    <span class="block">AI: Rs. ${item.unit_price}</span>
                                    <span class="block text-gray-400">original</span>
                                </div>
                                <div id="price-change-${idx}" class="text-xs hidden">
                                    <span class="px-1 py-0.5 bg-yellow-100 text-yellow-800 rounded">Changed</span>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-2 text-sm text-gray-900">
                            <div class="flex flex-col">
                                <span class="px-2 py-1 text-xs font-semibold rounded-full ${
                                    (item.item_type && item.item_type === 'Fresh') ? 'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800'
                                }">${item.item_type || 'Storable'}</span>
                                <span class="text-xs text-gray-600 mt-1">${item.coverage_days || 2} day${(item.coverage_days || 2) > 1 ? 's' : ''} coverage</span>
                                <span class="text-xs text-gray-500">${item.ordering_strategy || 'Can be stored for multiple days'}</span>
                            </div>
                        </td>
                        <td class="px-4 py-2 text-sm text-gray-900">
                            <span class="px-2 py-1 text-xs font-semibold rounded-full ${supplySourceColor}">
                                ${supplySource}
                            </span>
                        </td>
                        <td class="px-4 py-2 text-sm text-gray-900">
                            <input type="text" class="border rounded px-2 py-1 w-32" value="${item.notes || (item.reasoning || '')}" onchange="updateForecastItem(${idx}, 'notes', this.value)">
                            ${isMainBranch && item.branch_demand ? `
                                <div class="mt-1 text-xs text-blue-600 bg-blue-50 p-1 rounded">
                                    <i class="fas fa-chart-line mr-1"></i>${item.branch_demand}
                                </div>
                            ` : ''}
                        </td>
                        <td class="px-4 py-2">
                            <span class="px-2 py-1 text-xs font-semibold rounded-full ${
                                item.priority === 'High' ? 'bg-red-100 text-red-800' :
                                item.priority === 'Medium' ? 'bg-yellow-100 text-yellow-800' :
                                'bg-green-100 text-green-800'
                            }">${item.priority}</span>
                        </td>
                        <td class="px-4 py-2 text-center">
                            <button type="button" onclick="deleteForecastItem(${idx})" 
                                    class="text-red-600 hover:text-red-900 hover:bg-red-50 p-2 rounded-full transition-colors duration-200" 
                                    title="Delete this item">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                `
                }).join('')}
            </tbody>
        </table>
    `;
    document.getElementById('forecastOrders').innerHTML = ordersHtml;
    window._forecastItems = JSON.parse(JSON.stringify(forecast.items)); // Deep copy for editing
    
    // Store original values for comparison
    window._originalValues = forecast.items.map(item => ({
        recommended_order: item.recommended_order,
        unit_price: item.unit_price
    }));
}

function updateForecastItem(idx, field, value) {
    if (!window._forecastItems || !window._originalValues) return;
    
    const originalValue = window._originalValues[idx][field];
    
    if (field === 'recommended_order' || field === 'unit_price') {
        value = parseFloat(value);
        if (isNaN(value)) value = 0;
    }
    
    window._forecastItems[idx][field] = value;
    
    // Show change indicator if value is different from original
    if (field === 'recommended_order') {
        const changeIndicator = document.getElementById(`qty-change-${idx}`);
        if (value !== originalValue) {
            changeIndicator.classList.remove('hidden');
        } else {
            changeIndicator.classList.add('hidden');
        }
    } else if (field === 'unit_price') {
        const changeIndicator = document.getElementById(`price-change-${idx}`);
        if (value !== originalValue) {
            changeIndicator.classList.remove('hidden');
        } else {
            changeIndicator.classList.add('hidden');
        }
    }
}

function deleteForecastItem(idx) {
    if (!window._forecastItems) return;
    
    // Store the index to delete
    window._itemToDelete = idx;
    
    // Show confirmation modal
    const item = window._forecastItems[idx];
    document.getElementById('deleteItemName').textContent = item.name;
    document.getElementById('deleteConfirmModal').classList.remove('hidden');
}

function closeDeleteConfirmModal() {
    document.getElementById('deleteConfirmModal').classList.add('hidden');
    window._itemToDelete = null;
}

// Add keyboard support for delete confirmation modal
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        closeDeleteConfirmModal();
    }
});

// Add click outside to close functionality
document.getElementById('deleteConfirmModal').addEventListener('click', function(event) {
    if (event.target === this) {
        closeDeleteConfirmModal();
    }
});

function confirmDeleteItem() {
    if (window._itemToDelete === null || !window._forecastItems) return;
    
    // Remove the item
    window._forecastItems.splice(window._itemToDelete, 1);
    
    // Close confirmation modal
    closeDeleteConfirmModal();
    
    // Re-render table
    displayForecastResults({
        total_items: window._forecastItems.length,
        total_value: window._forecastItems.reduce((sum, item) => sum + (parseFloat(item.unit_price) * parseFloat(item.recommended_order)), 0).toFixed(2),
        confidence: 80, // Keep previous or recalc if needed
        items: window._forecastItems
    });
}

function showSuccessModal(data) {
    // Set modal content based on response data
    const title = document.getElementById('successTitle');
    const message = document.getElementById('successMessage');
    const orderDetails = document.getElementById('orderDetails');
    const orderNumber = document.getElementById('orderNumber');
    const totalItems = document.getElementById('totalItems');
    const totalAmount = document.getElementById('totalAmount');
    const viewOrderBtn = document.getElementById('viewOrderBtn');
    
    // Update title and message
    title.textContent = data.message || 'Order Created Successfully!';
    message.textContent = data.order ? 
        'Your inventory order has been created and is ready for processing.' :
        'Your inventory order has been created successfully.';
    
    // Show order details if available
    if (data.order) {
        orderNumber.textContent = data.order.order_number || 'N/A';
        totalItems.textContent = data.order.items ? data.order.items.length : 'N/A';
        totalAmount.textContent = data.order.total_amount ? 
            `$${parseFloat(data.order.total_amount).toFixed(2)}` : 'N/A';
        orderDetails.classList.remove('hidden');
        
        // Show view order button if order_id is available
        if (data.order_id) {
            viewOrderBtn.classList.remove('hidden');
            viewOrderBtn.onclick = () => {
                window.location.href = '/admin/inventory/orders/' + data.order_id;
            };
        } else {
            viewOrderBtn.classList.add('hidden');
        }
    } else {
        orderDetails.classList.add('hidden');
        viewOrderBtn.classList.add('hidden');
    }
    
    // Store order data for view button
    window._lastCreatedOrder = data;
    
    // Show modal with animation
    const modal = document.getElementById('successModal');
    const modalContent = document.getElementById('successModalContent');
    
    modal.classList.remove('hidden');
    
    // Trigger animation
    setTimeout(() => {
        modalContent.classList.remove('scale-95', 'opacity-0');
        modalContent.classList.add('scale-100', 'opacity-100');
    }, 10);
}

function closeSuccessModal() {
    const modal = document.getElementById('successModal');
    const modalContent = document.getElementById('successModalContent');
    
    // Trigger close animation
    modalContent.classList.remove('scale-100', 'opacity-100');
    modalContent.classList.add('scale-95', 'opacity-0');
    
    // Hide modal after animation
    setTimeout(() => {
        modal.classList.add('hidden');
    }, 300);
}

function viewOrderDetails() {
    if (window._lastCreatedOrder && window._lastCreatedOrder.order_id) {
        window.location.href = '/admin/inventory/orders/' + window._lastCreatedOrder.order_id;
    } else {
        // Fallback to orders list
        const branchId = {{ isset($branch) ? $branch->id : 'null' }};
        window.location.href = '{{ route("admin.inventory.orders.index") }}' + (branchId ? `?branch=${branchId}` : '');
    }
}

// Add keyboard support for success modal
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        closeSuccessModal();
    }
});

// Add click outside to close functionality for success modal
document.getElementById('successModal').addEventListener('click', function(event) {
    if (event.target === this) {
        closeSuccessModal();
    }
});

function createOrdersFromForecast() {
    console.log('Starting createOrdersFromForecast...');
    
    // Collect edited items
    const items = window._forecastItems || [];
    console.log('Forecast items:', items);
    
    if (!items.length) {
        alert('No items to create orders for.');
        return;
    }

    // Get current branch
    const branchId = {{ isset($branch) ? $branch->id : 'null' }};
    console.log('Branch ID:', branchId);
    
    // Get the first available supplier as default (for forecast orders)
    const suppliers = @json($suppliers ?? []);
    console.log('Suppliers:', suppliers);
    
    if (!suppliers.length) {
        alert('No suppliers available. Please add suppliers first.');
        return;
    }
    
    // Prepare order data
    const orderData = {
        supplier_id: suppliers[0].id, // Use first supplier as default
        branch_id: branchId,
        expected_delivery_date: new Date(Date.now() + 7 * 24 * 60 * 60 * 1000).toISOString().split('T')[0], // 7 days from now
        notes: 'Order created from AI forecast',
        items: items.map(item => {
            console.log('Processing item:', item);
            const processedItem = {
                inventory_item_id: item.id || item.inventory_item_id, // Try both possible field names
                quantity: parseInt(item.recommended_order) || 0,
                unit_price: parseFloat(item.unit_price) || 0
            };
            console.log('Processed item:', processedItem);
            return processedItem;
        }).filter(item => {
            const isValid = item.inventory_item_id && item.quantity > 0;
            console.log('Item validation:', item, 'isValid:', isValid);
            return isValid;
        })
    };

    console.log('Order data:', orderData);

    // Filter out items with zero quantity or missing ID
    if (orderData.items.length === 0) {
        alert('No items with valid quantities and inventory IDs to order.');
        return;
    }

    // Show loading state
    const createButton = document.querySelector('button[onclick="createOrdersFromForecast()"]');
    const originalText = createButton.innerHTML;
    createButton.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Creating Orders...';
    createButton.disabled = true;

    // Get CSRF token
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    console.log('CSRF Token:', csrfToken);

    // Make AJAX request to create orders
    console.log('Making request to:', '{{ route("admin.inventory.orders.store") }}');
    console.log('Request data:', JSON.stringify(orderData, null, 2));
    
    fetch('{{ route("admin.inventory.orders.store") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        },
        body: JSON.stringify(orderData)
    })
    .then(response => {
        console.log('Response status:', response.status);
        console.log('Response headers:', response.headers);
        
        // Check if response is ok
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        // Check if response is JSON
        const contentType = response.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
            throw new Error('Response is not JSON. Server might be returning an HTML error page.');
        }
        
        return response.json();
    })
    .then(data => {
        console.log('Response data:', data);
        if (data.success) {
            // Close forecast modal first
            closeForecastModal();
            
            // Show beautiful success modal
            showSuccessModal(data);
        } else {
            // Show error message
            alert('Error creating orders: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('Fetch Error:', error);
        console.error('Error name:', error.name);
        console.error('Error message:', error.message);
        
        // More specific error messages
        if (error.name === 'TypeError' && error.message.includes('JSON')) {
            alert('Server returned an invalid response. This might be due to a server error or authentication issue.');
        } else if (error.message.includes('HTTP error')) {
            alert('Server error: ' + error.message + '. Please check if you are logged in and have proper permissions.');
        } else {
            alert('Network error: ' + error.message + '. Please check your internet connection and try again.');
        }
    })
    .finally(() => {
        // Restore button state
        createButton.innerHTML = originalText;
        createButton.disabled = false;
    });
}

function closeForecastModal() {
    document.getElementById('forecastModal').classList.add('hidden');
}
</script>
@endpush

@endsection