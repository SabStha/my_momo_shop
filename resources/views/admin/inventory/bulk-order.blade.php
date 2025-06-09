@extends('layouts.admin')

@section('title', 'Bulk Order')

@section('content')
<div class="max-w-7xl mx-auto py-10 px-4">
    <div class="mb-6 bg-white shadow rounded-lg p-4">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-4">
                <div class="bg-indigo-100 p-3 rounded-full">
                    <i class="fas fa-boxes text-indigo-600 text-xl"></i>
                </div>
                <div>
                    <h2 class="text-xl font-semibold text-gray-800">Bulk Order</h2>
                    <p class="text-sm text-gray-600">Select multiple items and create a single order</p>
                </div>
            </div>
            <div class="flex items-center space-x-4">
                <a href="{{ route('admin.inventory.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2">
                    <i class="fas fa-arrow-left mr-2"></i> Back to Inventory
                </a>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow mb-6">
        <div class="p-4">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label for="category" class="block text-sm font-medium text-gray-700">Category</label>
                    <select id="category" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                        <option value="">All Categories</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="search" class="block text-sm font-medium text-gray-700">Search Items</label>
                    <input type="text" id="search" placeholder="Search by name or SKU"
                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                </div>
                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700">Stock Status</label>
                    <select id="status" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                        <option value="">All Status</option>
                        <option value="low_stock">Low Stock</option>
                        <option value="out_of_stock">Out of Stock</option>
                        <option value="normal">Normal</option>
                    </select>
                </div>
                <div>
                    <label for="supplier" class="block text-sm font-medium text-gray-700">Supplier</label>
                    <select id="supplier" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                        <option value="">All Suppliers</option>
                        @foreach($suppliers as $supplier)
                            <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
    </div>

    <!-- Bulk Order Form -->
    <form id="bulkOrderForm" action="{{ route('admin.inventory.orders.store') }}" method="POST">
        @csrf
        @if(isset($branch))
            <input type="hidden" name="branch_id" value="{{ $branch->id }}">
        @endif

        <div class="bg-white rounded-lg shadow overflow-hidden mb-6">
            <div class="p-4 border-b border-gray-200">
                <div class="flex justify-between items-center">
                    <h3 class="text-lg font-medium text-gray-900">Selected Items</h3>
                    <div class="flex items-center space-x-4">
                        <span class="text-sm text-gray-600">Total Items: <span id="selectedCount">0</span></span>
                        <button type="button" id="clearSelection" class="text-red-600 hover:text-red-900 text-sm">
                            Clear Selection
                        </button>
                    </div>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <input type="checkbox" id="selectAll" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">SKU</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Current Stock</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Order Quantity</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Unit Price</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($items as $item)
                        <tr class="hover:bg-gray-50" data-category="{{ $item->category_id }}" data-supplier="{{ $item->supplier_id }}">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <input type="checkbox" name="selected_items[]" value="{{ $item->id }}" 
                                       class="item-checkbox rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $item->sku }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $item->name }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $item->category->name ?? 'Uncategorized' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $item->quantity }} {{ $item->unit }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <input type="number" name="items[{{ $item->id }}][quantity]" 
                                       min="1" step="1" value="0"
                                       class="order-quantity block w-24 border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                       disabled>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <input type="number" name="items[{{ $item->id }}][unit_price]" 
                                       min="0" step="0.01" value="{{ $item->unit_price }}"
                                       class="unit-price block w-24 border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                       disabled>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <span class="item-total">0.00</span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Order Details -->
        <div class="bg-white rounded-lg shadow overflow-hidden mb-6">
            <div class="p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Order Details</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="supplier_id" class="block text-sm font-medium text-gray-700">Supplier</label>
                        <select name="supplier_id" id="supplier_id" required
                                class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                            <option value="">Select Supplier</option>
                            @foreach($suppliers as $supplier)
                                <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                            @endforeach
                        </select>
                        @error('supplier_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="expected_delivery_date" class="block text-sm font-medium text-gray-700">Expected Delivery Date</label>
                        <input type="date" name="expected_delivery_date" id="expected_delivery_date" 
                               value="{{ old('expected_delivery_date') }}" required
                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        @error('expected_delivery_date')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="md:col-span-2">
                        <label for="notes" class="block text-sm font-medium text-gray-700">Notes</label>
                        <textarea name="notes" id="notes" rows="3" 
                                  class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">{{ old('notes') }}</textarea>
                        @error('notes')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        <!-- Order Summary -->
        <div class="bg-white rounded-lg shadow overflow-hidden mb-6">
            <div class="p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Order Summary</h3>
                <div class="flex justify-between items-center">
                    <div>
                        <p class="text-sm text-gray-600">Total Items: <span id="totalItems">0</span></p>
                        <p class="text-sm text-gray-600">Total Quantity: <span id="totalQuantity">0</span></p>
                    </div>
                    <div class="text-right">
                        <p class="text-lg font-semibold text-gray-900">Total Amount: Rs. <span id="totalAmount">0.00</span></p>
                    </div>
                </div>
            </div>
        </div>

        <div class="flex justify-end space-x-4">
            <a href="{{ route('admin.inventory.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                Cancel
            </a>
            <button type="submit" id="submitOrder" disabled
                    class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                Create Order
            </button>
        </div>
    </form>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('bulkOrderForm');
    const selectAll = document.getElementById('selectAll');
    const itemCheckboxes = document.querySelectorAll('.item-checkbox');
    const clearSelection = document.getElementById('clearSelection');
    const searchInput = document.getElementById('search');
    const categorySelect = document.getElementById('category');
    const statusSelect = document.getElementById('status');
    const supplierSelect = document.getElementById('supplier');
    const submitButton = document.getElementById('submitOrder');

    // Update selected count and enable/disable inputs
    function updateSelection() {
        const selectedItems = document.querySelectorAll('.item-checkbox:checked');
        const selectedCount = selectedItems.length;
        document.getElementById('selectedCount').textContent = selectedCount;
        document.getElementById('totalItems').textContent = selectedCount;
        
        // Enable/disable quantity and price inputs
        selectedItems.forEach(checkbox => {
            const row = checkbox.closest('tr');
            const quantityInput = row.querySelector('.order-quantity');
            const priceInput = row.querySelector('.unit-price');
            quantityInput.disabled = false;
            priceInput.disabled = false;
        });

        // Disable inputs for unselected items
        document.querySelectorAll('.item-checkbox:not(:checked)').forEach(checkbox => {
            const row = checkbox.closest('tr');
            const quantityInput = row.querySelector('.order-quantity');
            const priceInput = row.querySelector('.unit-price');
            quantityInput.disabled = true;
            quantityInput.value = 0;
            priceInput.disabled = true;
        });

        // Enable/disable submit button
        submitButton.disabled = selectedCount === 0;
        
        updateTotals();
    }

    // Update totals when quantities or prices change
    function updateTotals() {
        let totalQuantity = 0;
        let totalAmount = 0;

        document.querySelectorAll('.item-checkbox:checked').forEach(checkbox => {
            const row = checkbox.closest('tr');
            const quantity = parseInt(row.querySelector('.order-quantity').value) || 0;
            const price = parseFloat(row.querySelector('.unit-price').value) || 0;
            const total = quantity * price;
            
            row.querySelector('.item-total').textContent = total.toFixed(2);
            totalQuantity += quantity;
            totalAmount += total;
        });

        document.getElementById('totalQuantity').textContent = totalQuantity;
        document.getElementById('totalAmount').textContent = totalAmount.toFixed(2);
    }

    // Select all items
    selectAll.addEventListener('change', function() {
        itemCheckboxes.forEach(checkbox => {
            checkbox.checked = this.checked;
        });
        updateSelection();
    });

    // Clear selection
    clearSelection.addEventListener('click', function() {
        itemCheckboxes.forEach(checkbox => {
            checkbox.checked = false;
        });
        selectAll.checked = false;
        updateSelection();
    });

    // Update selection when individual checkboxes change
    itemCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', updateSelection);
    });

    // Update totals when quantities or prices change
    document.querySelectorAll('.order-quantity, .unit-price').forEach(input => {
        input.addEventListener('input', updateTotals);
    });

    // Filter items
    function filterItems() {
        const searchTerm = searchInput.value.toLowerCase();
        const categoryId = categorySelect.value;
        const status = statusSelect.value;
        const supplierId = supplierSelect.value;

        document.querySelectorAll('tbody tr').forEach(row => {
            const name = row.querySelector('td:nth-child(3)').textContent.toLowerCase();
            const sku = row.querySelector('td:nth-child(2)').textContent.toLowerCase();
            const itemCategory = row.dataset.category;
            const itemSupplier = row.dataset.supplier;
            const currentStock = parseInt(row.querySelector('td:nth-child(5)').textContent);
            
            const matchesSearch = name.includes(searchTerm) || sku.includes(searchTerm);
            const matchesCategory = !categoryId || itemCategory === categoryId;
            const matchesSupplier = !supplierId || itemSupplier === supplierId;
            const matchesStatus = !status || 
                (status === 'low_stock' && currentStock <= 10) ||
                (status === 'out_of_stock' && currentStock === 0) ||
                (status === 'normal' && currentStock > 10);

            row.style.display = matchesSearch && matchesCategory && matchesStatus && matchesSupplier ? '' : 'none';
        });
    }

    searchInput.addEventListener('input', filterItems);
    categorySelect.addEventListener('change', filterItems);
    statusSelect.addEventListener('change', filterItems);
    supplierSelect.addEventListener('change', filterItems);

    // Initialize
    updateSelection();
});
</script>
@endpush

@endsection 