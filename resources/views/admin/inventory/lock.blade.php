@extends('layouts.admin')

@section('title', 'Lock Inventory')

@section('content')
<div class="max-w-7xl mx-auto py-10 px-4">
    <div class="mb-6 bg-white shadow rounded-lg p-4">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-4">
                <div class="bg-red-100 p-3 rounded-full">
                    <i class="fas fa-lock text-red-600 text-xl"></i>
                </div>
                <div>
                    <h2 class="text-xl font-semibold text-gray-800">Lock Inventory</h2>
                    <p class="text-sm text-gray-600">Lock inventory items during checks to prevent modifications</p>
                </div>
            </div>
            <div class="flex items-center space-x-4">
                <button type="button" id="lockAll" class="inline-flex items-center px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2">
                    <i class="fas fa-lock mr-2"></i> Lock All Items
                </button>
                <button type="button" id="unlockAll" class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2">
                    <i class="fas fa-unlock mr-2"></i> Unlock All Items
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
                    <label for="lockStatus" class="block text-sm font-medium text-gray-700">Lock Status</label>
                    <select id="lockStatus" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                        <option value="">All Status</option>
                        <option value="locked">Locked</option>
                        <option value="unlocked">Unlocked</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <!-- Lock Form -->
    <form id="lockForm" action="{{ route('admin.inventory.lock.store') }}" method="POST">
        @csrf
        <div class="bg-white rounded-lg shadow overflow-hidden">
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
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Lock Status</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Locked By</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Locked At</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($items as $item)
                        <tr class="hover:bg-gray-50" data-category="{{ $item->category_id }}">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <input type="checkbox" name="selected_items[]" value="{{ $item->id }}" 
                                       class="item-checkbox rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $item->sku }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $item->name }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $item->category->name ?? 'Uncategorized' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $item->quantity }} {{ $item->unit }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 text-xs font-semibold rounded-full
                                    {{ $item->is_locked ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800' }}">
                                    {{ $item->is_locked ? 'Locked' : 'Unlocked' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $item->locked_by ? $item->lockedBy->name : '-' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $item->locked_at ? $item->locked_at->format('M d, Y H:i') : '-' }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-6 flex justify-end space-x-4">
            <button type="submit" name="action" value="lock" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                <i class="fas fa-lock mr-2"></i> Lock Selected
            </button>
            <button type="submit" name="action" value="unlock" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                <i class="fas fa-unlock mr-2"></i> Unlock Selected
            </button>
        </div>
    </form>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('lockForm');
    const selectAll = document.getElementById('selectAll');
    const itemCheckboxes = document.querySelectorAll('.item-checkbox');
    const lockAllButton = document.getElementById('lockAll');
    const unlockAllButton = document.getElementById('unlockAll');
    const searchInput = document.getElementById('search');
    const categorySelect = document.getElementById('category');
    const lockStatusSelect = document.getElementById('lockStatus');

    // Select all items
    selectAll.addEventListener('change', function() {
        itemCheckboxes.forEach(checkbox => {
            checkbox.checked = this.checked;
        });
    });

    // Lock all items
    lockAllButton.addEventListener('click', function() {
        itemCheckboxes.forEach(checkbox => {
            checkbox.checked = true;
        });
        selectAll.checked = true;
        form.querySelector('button[value="lock"]').click();
    });

    // Unlock all items
    unlockAllButton.addEventListener('click', function() {
        itemCheckboxes.forEach(checkbox => {
            checkbox.checked = true;
        });
        selectAll.checked = true;
        form.querySelector('button[value="unlock"]').click();
    });

    // Filter items
    function filterItems() {
        const searchTerm = searchInput.value.toLowerCase();
        const categoryId = categorySelect.value;
        const lockStatus = lockStatusSelect.value;

        document.querySelectorAll('tbody tr').forEach(row => {
            const name = row.querySelector('td:nth-child(3)').textContent.toLowerCase();
            const sku = row.querySelector('td:nth-child(2)').textContent.toLowerCase();
            const itemCategory = row.dataset.category;
            const isLocked = row.querySelector('td:nth-child(6)').textContent.trim() === 'Locked';
            
            const matchesSearch = name.includes(searchTerm) || sku.includes(searchTerm);
            const matchesCategory = !categoryId || itemCategory === categoryId;
            const matchesLockStatus = !lockStatus || 
                (lockStatus === 'locked' && isLocked) ||
                (lockStatus === 'unlocked' && !isLocked);

            row.style.display = matchesSearch && matchesCategory && matchesLockStatus ? '' : 'none';
        });
    }

    searchInput.addEventListener('input', filterItems);
    categorySelect.addEventListener('change', filterItems);
    lockStatusSelect.addEventListener('change', filterItems);
});
</script>
@endpush

@endsection 