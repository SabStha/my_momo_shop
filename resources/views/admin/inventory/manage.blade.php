@extends('layouts.admin')

@section('title', 'Manage Inventory')

@section('content')
<meta name="csrf-token" content="{{ csrf_token() }}">

<div class="py-6 px-4 mx-auto max-w-7xl">
    <div class="flex justify-between items-center mb-4">
        <h2 class="text-2xl font-bold text-gray-800">
            <i class="fas fa-boxes text-blue-500 mr-2"></i>Manage Inventory
        </h2>
        <div class="flex gap-2">
            <a href="{{ route('admin.inventory.create') }}" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">
                <i class="fas fa-plus mr-2"></i> Add New Item
            </a>
            <button id="collectiveOrderBtn" disabled onclick="submitSelectedItems()" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                <i class="fas fa-shopping-cart mr-2"></i> Collective Order
            </button>
        </div>
    </div>

    @if(session('success'))
        <div class="bg-green-100 text-green-700 px-4 py-3 rounded mb-4">
            <i class="fas fa-check-circle mr-2"></i> {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="bg-red-100 text-red-700 px-4 py-3 rounded mb-4">
            <i class="fas fa-exclamation-circle mr-2"></i> {{ session('error') }}
        </div>
    @endif

    <div class="overflow-x-auto bg-white shadow rounded">
        <table class="min-w-full table-auto text-left">
            <thead class="bg-gray-100 text-gray-700">
                <tr>
                    <th class="p-3">
                        <input type="checkbox" id="selectAll" class="form-checkbox">
                    </th>
                    <th class="p-3">SKU</th>
                    <th class="p-3">Name</th>
                    <th class="p-3">Category</th>
                    <th class="p-3">Quantity</th>
                    <th class="p-3">Unit</th>
                    <th class="p-3">Unit Price</th>
                    <th class="p-3">Status</th>
                    <th class="p-3">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse($items as $item)
                    <tr class="{{ $item->is_locked ? 'bg-yellow-50' : '' }}">
                        <td class="p-3">
                            <input type="checkbox" class="item-checkbox form-checkbox" value="{{ $item->id }}" data-name="{{ $item->name }}" data-price="{{ $item->unit_price }}" data-locked="{{ $item->is_locked ? 'true' : 'false' }}" {{ !$item->is_locked ? 'disabled' : '' }}>
                        </td>
                        <td class="p-3">
                            <span class="bg-gray-200 text-gray-800 px-2 py-1 rounded text-sm">{{ $item->sku }}</span>
                        </td>
                        <td class="p-3 flex items-center">
                            {{ $item->name }}
                            @if($item->is_locked)
                                <i class="fas fa-lock text-yellow-500 ml-2" title="Item is locked"></i>
                            @endif
                        </td>
                        <td class="p-3">
                            <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded text-sm">{{ $item->category ? $item->category->name : 'Uncategorized' }}</span>
                        </td>
                        <td class="p-3">
                            <span class="px-2 py-1 rounded text-white {{ $item->quantity <= $item->reorder_point ? 'bg-red-500' : 'bg-green-500' }}">
                                {{ $item->quantity }}
                            </span>
                        </td>
                        <td class="p-3">{{ $item->unit }}</td>
                        <td class="p-3">${{ number_format($item->unit_price, 2) }}</td>
                        <td class="p-3">
                            <span class="px-2 py-1 rounded text-white {{ $item->status === 'active' ? 'bg-green-600' : 'bg-yellow-500' }}">
                                {{ ucfirst($item->status) }}
                            </span>
                        </td>
                        <td class="p-3">
                            <div class="flex gap-2">
                                <a href="{{ route('admin.inventory.edit', $item) }}" class="text-blue-600 hover:underline">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <button type="button" class="lock-btn text-yellow-600 hover:underline" data-item-id="{{ $item->id }}" data-locked="{{ $item->is_locked ? 'true' : 'false' }}">
                                    <i class="fas fa-{{ $item->is_locked ? 'unlock' : 'lock' }}"></i>
                                </button>
                                <form method="POST" action="{{ route('admin.inventory.destroy', $item) }}" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button" class="delete-btn text-red-600 hover:underline">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="text-center py-8 text-gray-500">
                            <i class="fas fa-box-open text-3xl mb-2"></i>
                            <p>No inventory items found.</p>
                            <a href="{{ route('admin.inventory.create') }}" class="mt-2 inline-block bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                                <i class="fas fa-plus mr-2"></i> Add Your First Item
                            </a>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="flex justify-between items-center mt-4 text-sm text-gray-500">
        <div>Showing {{ $items->firstItem() ?? 0 }} to {{ $items->lastItem() ?? 0 }} of {{ $items->total() }} items</div>
        {{ $items->links() }}
    </div>
</div>

<!-- Order Confirmation Modal -->
<div id="orderConfirmationModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <div class="flex items-center justify-center w-12 h-12 mx-auto bg-blue-100 rounded-full">
                <i class="fas fa-shopping-cart text-blue-600 text-xl"></i>
            </div>
            <h3 class="text-lg leading-6 font-medium text-gray-900 mt-4">Confirm Order</h3>
            <div class="mt-2 px-7 py-3">
                <p class="text-sm text-gray-500">Are you sure you want to proceed with the order for the following items?</p>
                <div id="selectedItemsList" class="mt-4 space-y-2">
                    <!-- Selected items will be listed here -->
                </div>
            </div>
            <div class="flex justify-end gap-2 mt-4">
                <button onclick="closeModal('orderConfirmationModal')" class="px-4 py-2 bg-gray-100 text-gray-700 rounded hover:bg-gray-200">
                    Cancel
                </button>
                <button onclick="proceedToOrder()" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                    Proceed to Order
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Lock/Unlock Confirmation Modal -->
<div id="lockConfirmModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <div class="flex items-center justify-center w-12 h-12 mx-auto bg-yellow-100 rounded-full">
                <i class="fas fa-lock text-yellow-600 text-xl"></i>
            </div>
            <h3 class="text-lg leading-6 font-medium text-gray-900 mt-4">Confirm Lock/Unlock Item</h3>
            <div class="mt-2 px-7 py-3">
                <p class="text-sm text-gray-500">Are you sure you want to <span id="lockActionText" class="font-bold">lock</span> this item?</p>
            </div>
            <div class="flex justify-end gap-2 mt-4">
                <button onclick="closeModal('lockConfirmModal')" class="px-4 py-2 bg-gray-100 text-gray-700 rounded hover:bg-gray-200">
                    Cancel
                </button>
                <button id="confirmLockBtn" class="px-4 py-2 bg-yellow-600 text-white rounded hover:bg-yellow-700">
                    Yes, <span id="lockButtonText">Lock</span> Item
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div id="deleteConfirmModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <div class="flex items-center justify-center w-12 h-12 mx-auto bg-red-100 rounded-full">
                <i class="fas fa-trash text-red-600 text-xl"></i>
            </div>
            <h3 class="text-lg leading-6 font-medium text-gray-900 mt-4">Confirm Delete Item</h3>
            <div class="mt-2 px-7 py-3">
                <p class="text-sm text-gray-500">Are you sure you want to delete this item? This action cannot be undone.</p>
            </div>
            <div class="flex justify-end gap-2 mt-4">
                <button onclick="closeModal('deleteConfirmModal')" class="px-4 py-2 bg-gray-100 text-gray-700 rounded hover:bg-gray-200">
                    Cancel
                </button>
                <button id="confirmDeleteBtn" class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700">
                    Yes, Delete Item
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Loading Overlay -->
<div id="loadingOverlay" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full z-50">
    <div class="flex items-center justify-center h-full">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-white"></div>
    </div>
</div>

<script>
// Utility functions
function showLoading() {
    document.getElementById('loadingOverlay').classList.remove('hidden');
}

function hideLoading() {
    document.getElementById('loadingOverlay').classList.add('hidden');
}

function showModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.remove('hidden');
    }
}

function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.add('hidden');
    }
}

function showAlert(type, message) {
    // Remove any existing alerts
    const existingAlerts = document.querySelectorAll('.alert-message');
    existingAlerts.forEach(alert => alert.remove());

    const alertDiv = document.createElement('div');
    alertDiv.className = `alert-message px-4 py-3 rounded mb-4 ${
        type === 'success' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'
    }`;
    alertDiv.innerHTML = `
        <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'} mr-2"></i>
        ${message}
    `;

    // Find the container and insert the alert at the top
    const container = document.querySelector('.max-w-7xl');
    if (container) {
        container.insertBefore(alertDiv, container.firstChild);
    } else {
        // Fallback: append to body if container not found
        document.body.insertBefore(alertDiv, document.body.firstChild);
    }

    setTimeout(() => alertDiv.remove(), 3000);
}

document.addEventListener('DOMContentLoaded', function () {
    const selectAllCheckbox = document.getElementById('selectAll');
    const itemCheckboxes = document.querySelectorAll('.item-checkbox');
    const collectiveOrderBtn = document.getElementById('collectiveOrderBtn');

    function updateButtonState() {
        const checked = Array.from(itemCheckboxes).filter(cb => cb.checked);
        collectiveOrderBtn.disabled = checked.length === 0;
    }

    selectAllCheckbox.addEventListener('change', function () {
        itemCheckboxes.forEach(cb => {
            if (!cb.disabled) cb.checked = this.checked;
        });
        updateButtonState();
    });

    itemCheckboxes.forEach(cb => {
        cb.addEventListener('change', updateButtonState);
    });

    let currentForm = null;
    let currentLockBtn = null;

    // Lock/Unlock button functionality
    document.querySelectorAll('.lock-btn').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            currentLockBtn = this;
            const isLocked = this.dataset.locked === 'true';
            const action = isLocked ? 'unlock' : 'lock';
            
            // Update modal text
            document.getElementById('lockActionText').textContent = action;
            document.getElementById('lockButtonText').textContent = action.charAt(0).toUpperCase() + action.slice(1);
            
            // Show confirmation modal
            showModal('lockConfirmModal');
        });
    });

    // Delete button functionality
    document.querySelectorAll('.delete-btn').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            currentForm = this.closest('form');
            showModal('deleteConfirmModal');
        });
    });

    // Confirm lock/unlock
    document.getElementById('confirmLockBtn').addEventListener('click', function() {
        if (currentLockBtn) {
            const itemId = currentLockBtn.dataset.itemId;
            const isLocked = currentLockBtn.dataset.locked === 'true';
            const action = isLocked ? 'unlock' : 'lock';
            
            // Get CSRF token from meta tag
            const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            
            showLoading();
            
            fetch(`/admin/inventory/${itemId}/${action}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': token,
                    'Accept': 'application/json'
                },
                credentials: 'same-origin'
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    // Update button appearance
                    currentLockBtn.dataset.locked = (!isLocked).toString();
                    currentLockBtn.querySelector('i').classList.toggle('fa-lock');
                    currentLockBtn.querySelector('i').classList.toggle('fa-unlock');
                    
                    // Update checkbox
                    const checkbox = document.querySelector(`.item-checkbox[value="${itemId}"]`);
                    checkbox.dataset.locked = (!isLocked).toString();
                    checkbox.disabled = isLocked;
                    checkbox.checked = false;

                    // Update row appearance
                    const row = currentLockBtn.closest('tr');
                    row.classList.toggle('bg-yellow-50');

                    // Update lock icon in name column
                    const lockIcon = row.querySelector('.fa-lock, .fa-unlock');
                    if (lockIcon) {
                        lockIcon.classList.toggle('fa-lock');
                        lockIcon.classList.toggle('fa-unlock');
                    } else {
                        const nameCell = row.querySelector('td:nth-child(3)');
                        const icon = document.createElement('i');
                        icon.className = `fas fa-${isLocked ? 'unlock' : 'lock'} text-yellow-500 ml-2`;
                        icon.setAttribute('title', isLocked ? 'Item is unlocked' : 'Item is locked');
                        nameCell.appendChild(icon);
                    }

                    // Update checkbox states
                    updateButtonState();

                    // Show success message
                    showAlert('success', data.message || 'Item status updated successfully');

                    // Close modal
                    const modal = document.getElementById('lockConfirmModal');
                    if (modal) {
                        modal.classList.add('hidden');
                    }
                } else {
                    throw new Error(data.message || 'Failed to update item status');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showAlert('error', error.message || 'An error occurred while updating the item.');
            })
            .finally(() => {
                hideLoading();
            });
        }
    });

    // Confirm delete
    document.getElementById('confirmDeleteBtn').addEventListener('click', function() {
        if (currentForm) {
            showLoading();
            currentForm.submit();
        }
    });

    // Add click event listeners to close buttons
    document.querySelectorAll('[onclick^="closeModal"]').forEach(button => {
        button.addEventListener('click', function() {
            const modalId = this.closest('.fixed').id;
            closeModal(modalId);
        });
    });

    // Add click event listener to modal backdrop
    document.querySelectorAll('.fixed').forEach(modal => {
        modal.addEventListener('click', function(e) {
            if (e.target === this) {
                closeModal(this.id);
            }
        });
    });
});

function submitSelectedItems() {
    const selectedItems = Array.from(document.querySelectorAll('.item-checkbox:checked'));
    
    if (selectedItems.length === 0) {
        showAlert('error', 'Please select at least one locked item to order.');
        return;
    }

    // Verify all selected items are locked
    const unlockedItems = selectedItems.filter(item => item.dataset.locked !== 'true');
    if (unlockedItems.length > 0) {
        showAlert('error', 'Only locked items can be ordered. Please lock the items first.');
        return;
    }

    // Build the list of selected items
    const itemsList = document.getElementById('selectedItemsList');
    itemsList.innerHTML = '';
    selectedItems.forEach(item => {
        itemsList.innerHTML += `
            <div class="flex justify-between items-center p-2 bg-gray-50 rounded">
                <div>
                    <h6 class="font-medium">${item.dataset.name}</h6>
                    <small class="text-gray-500">SKU: ${item.value}</small>
                </div>
                <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded">$${parseFloat(item.dataset.price).toFixed(2)}</span>
            </div>
        `;
    });

    // Show the confirmation modal
    showModal('orderConfirmationModal');
}

function proceedToOrder() {
    const selectedItems = Array.from(document.querySelectorAll('.item-checkbox:checked'));
    const itemIds = selectedItems.map(item => item.value);
    
    // Get CSRF token from meta tag
    const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    
    showLoading();
    
    fetch('/admin/supply/orders', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': token,
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            item_ids: itemIds
        }),
        credentials: 'same-origin'
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            showAlert('success', data.message || 'Orders created successfully');
            const modal = document.getElementById('orderConfirmationModal');
            if (modal) {
                modal.classList.add('hidden');
            }
            // Redirect to supply orders list page
            window.location.href = '/admin/supply/orders';
        } else {
            throw new Error(data.message || 'Failed to create order');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('error', error.message || 'An error occurred while creating the order.');
    })
    .finally(() => {
        hideLoading();
    });
}
</script>
@endsection
