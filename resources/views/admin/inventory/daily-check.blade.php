@extends('layouts.admin')

@section('title', 'Daily Inventory Check')

@section('content')
<div class="max-w-7xl mx-auto py-10 px-4">
    <div class="mb-6 bg-white shadow rounded-lg p-4">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-4">
                <div class="bg-yellow-100 p-3 rounded-full">
                    <i class="fas fa-clipboard-check text-yellow-600 text-xl"></i>
                </div>
                <div>
                    <h2 class="text-xl font-semibold text-gray-800">Daily Inventory Check</h2>
                    <p class="text-sm text-gray-600">Record daily stock counts and discrepancies</p>
                </div>
            </div>
            <div class="flex items-center space-x-4">
                <button type="button" id="submitCheck" class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2">
                    <i class="fas fa-save mr-2"></i> Submit Check
                </button>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow mb-6">
        <div class="p-4">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
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
                    <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                    <select id="status" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                        <option value="">All Status</option>
                        <option value="low_stock">Low Stock</option>
                        <option value="out_of_stock">Out of Stock</option>
                        <option value="normal">Normal</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <!-- Daily Check Form -->
    <form id="dailyCheckForm" action="{{ route('admin.inventory.daily-check.submit') }}" method="POST">
        @csrf
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">SKU</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">System Quantity</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actual Quantity</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Difference</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Notes</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($items as $item)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $item->sku }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $item->name }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $item->category->name ?? 'Uncategorized' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $item->quantity }} {{ $item->unit }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <input type="number" name="items[{{ $item->id }}][actual_quantity]" 
                                       min="0" step="1" value="{{ $item->quantity }}"
                                       class="block w-24 border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <span class="difference" data-system="{{ $item->quantity }}">0</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <input type="text" name="items[{{ $item->id }}][notes]" 
                                       placeholder="Add notes..."
                                       class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </form>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('dailyCheckForm');
    const submitButton = document.getElementById('submitCheck');
    const searchInput = document.getElementById('search');
    const categorySelect = document.getElementById('category');
    const statusSelect = document.getElementById('status');

    // Calculate difference when actual quantity changes
    document.querySelectorAll('input[name$="[actual_quantity]"]').forEach(input => {
        input.addEventListener('change', function() {
            const row = this.closest('tr');
            const systemQuantity = parseInt(row.querySelector('.difference').dataset.system);
            const actualQuantity = parseInt(this.value) || 0;
            const difference = actualQuantity - systemQuantity;
            
            const differenceElement = row.querySelector('.difference');
            differenceElement.textContent = difference;
            differenceElement.className = 'difference ' + 
                (difference < 0 ? 'text-red-600' : 
                 difference > 0 ? 'text-green-600' : 'text-gray-600');
        });
    });

    // Filter items
    function filterItems() {
        const searchTerm = searchInput.value.toLowerCase();
        const categoryId = categorySelect.value;
        const status = statusSelect.value;

        document.querySelectorAll('tbody tr').forEach(row => {
            const name = row.querySelector('td:nth-child(2)').textContent.toLowerCase();
            const sku = row.querySelector('td:nth-child(1)').textContent.toLowerCase();
            const itemCategory = row.querySelector('td:nth-child(3)').textContent;
            const systemQuantity = parseInt(row.querySelector('.difference').dataset.system);
            
            const matchesSearch = name.includes(searchTerm) || sku.includes(searchTerm);
            const matchesCategory = !categoryId || itemCategory.includes(categorySelect.options[categorySelect.selectedIndex].text);
            const matchesStatus = !status || 
                (status === 'low_stock' && systemQuantity <= 10) ||
                (status === 'out_of_stock' && systemQuantity === 0) ||
                (status === 'normal' && systemQuantity > 10);

            row.style.display = matchesSearch && matchesCategory && matchesStatus ? '' : 'none';
        });
    }

    searchInput.addEventListener('input', filterItems);
    categorySelect.addEventListener('change', filterItems);
    statusSelect.addEventListener('change', filterItems);

    // Submit form
    submitButton.addEventListener('click', function() {
        form.submit();
    });
});
</script>
@endpush

@endsection 