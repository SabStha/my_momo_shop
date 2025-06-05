@extends('desktop.admin.layouts.admin')

@section('title', 'Manage Inventory')

@section('content')
<!-- Add CSRF token meta tag -->
<meta name="csrf-token" content="{{ csrf_token() }}">

<div class="container">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">Manage Inventory</h3>
                    <div>
                        <a href="{{ route('admin.inventory.create') }}" class="btn btn-success me-2">
                            <i class="fas fa-plus"></i> Add New Item
                        </a>
                        <button type="button" class="btn btn-primary" onclick="submitSelectedItems()">
                            <i class="fas fa-shopping-cart"></i> Collective Order
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <div class="mt-4">
                        <h4>All Inventory Items</h4>
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="selectAll">
                                            </div>
                                        </th>
                                        <th>SKU</th>
                                        <th>Name</th>
                                        <th>Category</th>
                                        <th>Quantity</th>
                                        <th>Unit</th>
                                        <th>Unit Price</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($items as $item)
                                        <tr>
                                            <td>
                                                <div class="form-check">
                                                    <input class="form-check-input item-checkbox" type="checkbox" 
                                                           value="{{ $item->id }}" 
                                                           data-name="{{ $item->name }}"
                                                           data-price="{{ $item->unit_price }}"
                                                           data-locked="{{ $item->is_locked ? 'true' : 'false' }}"
                                                           {{ !$item->is_locked ? 'disabled' : '' }}>
                                                </div>
                                            </td>
                                            <td>{{ $item->sku }}</td>
                                            <td>{{ $item->name }}</td>
                                            <td>{{ $item->category->name }}</td>
                                            <td>{{ $item->quantity }}</td>
                                            <td>{{ $item->unit }}</td>
                                            <td>${{ number_format($item->unit_price, 2) }}</td>
                                            <td>
                                                <span class="badge bg-{{ $item->status === 'active' ? 'success' : 'warning' }}">
                                                    {{ ucfirst($item->status) }}
                                                </span>
                                            </td>
                                            <td>
                                                <div class="btn-group">
                                                    <a href="{{ route('admin.inventory.edit', $item) }}" 
                                                       class="btn btn-info btn-sm">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <button type="button" 
                                                            class="btn {{ $item->is_locked ? 'btn-warning' : 'btn-info' }} btn-sm lock-btn"
                                                            data-item-id="{{ $item->id }}"
                                                            data-locked="{{ $item->is_locked ? 'true' : 'false' }}">
                                                        <i class="fas fa-{{ $item->is_locked ? 'unlock' : 'lock' }}"></i>
                                                    </button>
                                                    <form action="{{ route('admin.inventory.destroy', $item) }}" 
                                                          method="POST" 
                                                          class="d-inline"
                                                          onsubmit="return confirm('Are you sure you want to delete this item?');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-danger btn-sm">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="9" class="text-center">No inventory items found.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        {{ $items->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Order Confirmation Modal -->
<div class="modal fade" id="orderConfirmationModal" tabindex="-1" aria-labelledby="orderConfirmationModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="orderConfirmationModalLabel">Confirm Order</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to proceed with the order for the following items?</p>
                <div id="selectedItemsList" class="mt-3">
                    <!-- Selected items will be listed here -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="proceedToOrder()">Proceed to Order</button>
            </div>
        </div>
    </div>
</div>

<!-- Confirmation Modals -->
<div class="modal fade" id="lockConfirmModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Lock/Unlock Item</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to <span id="lockActionText">lock</span> this item?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-warning" id="confirmLockBtn">Yes, <span id="lockButtonText">Lock</span> Item</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="deleteConfirmModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Delete Item</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to delete this item? This action cannot be undone.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Yes, Delete Item</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Select all checkbox functionality
    const selectAllCheckbox = document.getElementById('selectAll');
    const itemCheckboxes = document.querySelectorAll('.item-checkbox');

    // Update select all checkbox state
    function updateSelectAllCheckbox() {
        const enabledCheckboxes = Array.from(itemCheckboxes).filter(cb => !cb.disabled);
        const allChecked = enabledCheckboxes.length > 0 && enabledCheckboxes.every(cb => cb.checked);
        selectAllCheckbox.checked = allChecked;
    }

    selectAllCheckbox.addEventListener('change', function() {
        itemCheckboxes.forEach(checkbox => {
            if (!checkbox.disabled) {
                checkbox.checked = this.checked;
            }
        });
    });

    // Update select all checkbox when individual checkboxes change
    itemCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', updateSelectAllCheckbox);
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
            const modal = new bootstrap.Modal(document.getElementById('lockConfirmModal'));
            modal.show();
        });
    });

    // Delete button functionality
    document.querySelectorAll('form[action*="destroy"]').forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            currentForm = this;
            const modal = new bootstrap.Modal(document.getElementById('deleteConfirmModal'));
            modal.show();
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
                    currentLockBtn.classList.toggle('btn-warning');
                    currentLockBtn.classList.toggle('btn-info');
                    currentLockBtn.querySelector('i').classList.toggle('fa-lock');
                    currentLockBtn.querySelector('i').classList.toggle('fa-unlock');

                    // Update checkbox
                    const checkbox = document.querySelector(`.item-checkbox[value="${itemId}"]`);
                    checkbox.dataset.locked = (!isLocked).toString();
                    checkbox.disabled = isLocked;
                    checkbox.checked = false;

                    // Update select all checkbox state
                    updateSelectAllCheckbox();

                    // Show success message
                    const alert = document.createElement('div');
                    alert.className = 'alert alert-success alert-dismissible fade show';
                    alert.innerHTML = `
                        ${data.message}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    `;
                    document.querySelector('.card-body').insertBefore(alert, document.querySelector('.mt-4'));

                    // Remove alert after 3 seconds
                    setTimeout(() => {
                        alert.remove();
                    }, 3000);

                    // Close modal
                    bootstrap.Modal.getInstance(document.getElementById('lockConfirmModal')).hide();
                }
            })
            .catch(error => {
                console.error('Error:', error);
                // Show error message
                const alert = document.createElement('div');
                alert.className = 'alert alert-danger alert-dismissible fade show';
                alert.innerHTML = `
                    An error occurred while updating the item.
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                `;
                document.querySelector('.card-body').insertBefore(alert, document.querySelector('.mt-4'));

                // Remove alert after 3 seconds
                setTimeout(() => {
                    alert.remove();
                }, 3000);
            });
        }
    });

    // Confirm delete
    document.getElementById('confirmDeleteBtn').addEventListener('click', function() {
        if (currentForm) {
            currentForm.submit();
        }
    });
});

function submitSelectedItems() {
    const selectedItems = Array.from(document.querySelectorAll('.item-checkbox:checked'));
    
    if (selectedItems.length === 0) {
        alert('Please select at least one locked item to order.');
        return;
    }

    // Verify all selected items are locked
    const unlockedItems = selectedItems.filter(item => item.dataset.locked !== 'true');
    if (unlockedItems.length > 0) {
        alert('Only locked items can be ordered. Please lock the items first.');
        return;
    }

    // Build the list of selected items
    const itemsList = document.getElementById('selectedItemsList');
    itemsList.innerHTML = '<ul class="list-group">';
    selectedItems.forEach(item => {
        itemsList.innerHTML += `
            <li class="list-group-item d-flex justify-content-between align-items-center">
                ${item.dataset.name}
                <span class="badge bg-primary rounded-pill">$${parseFloat(item.dataset.price).toFixed(2)}</span>
            </li>
        `;
    });
    itemsList.innerHTML += '</ul>';

    // Show the confirmation modal
    const modal = new bootstrap.Modal(document.getElementById('orderConfirmationModal'));
    modal.show();
}

function proceedToOrder() {
    const selectedItems = Array.from(document.querySelectorAll('.item-checkbox:checked'));
    const itemIds = selectedItems.map(item => item.value);
    
    // Get CSRF token from meta tag
    const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    
    // Submit the order
    fetch("{{ route('admin.supply.orders.store') }}", {
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
            // Show success message
            const alert = document.createElement('div');
            alert.className = 'alert alert-success alert-dismissible fade show';
            alert.innerHTML = `
                Order created successfully!
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            `;
            document.querySelector('.card-body').insertBefore(alert, document.querySelector('.mt-4'));

            // Remove alert after 3 seconds
            setTimeout(() => {
                alert.remove();
            }, 3000);

            // Redirect to supply orders page
            window.location.href = "{{ route('admin.supply.orders.index') }}";
        } else {
            throw new Error(data.message || 'Failed to create order');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        // Show error message
        const alert = document.createElement('div');
        alert.className = 'alert alert-danger alert-dismissible fade show';
        alert.innerHTML = `
            ${error.message || 'An error occurred while creating the order.'}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        `;
        document.querySelector('.card-body').insertBefore(alert, document.querySelector('.mt-4'));

        // Remove alert after 3 seconds
        setTimeout(() => {
            alert.remove();
        }, 3000);
    });
}
</script>
@endsection 