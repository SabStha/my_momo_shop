@extends('desktop.admin.layouts.admin')

@section('title', 'Inventory Items')

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Inventory Items</h5>
                <div>
                    <button type="button" class="btn btn-success" id="createOrderBtn" disabled>
                        <i class="fas fa-shopping-cart"></i> Create Order for Selected Items
                    </button>
                </div>
            </div>
        </div>
        <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif

            @if($inventoryItems->isEmpty())
                <div class="text-center py-4">
                    <p class="text-muted mb-0">No inventory items found.</p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover">
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
                            @foreach($inventoryItems as $item)
                                <tr>
                                    <td>
                                        <div class="form-check">
                                            <input class="form-check-input item-checkbox" type="checkbox" 
                                                   value="{{ $item->id }}" 
                                                   data-name="{{ $item->name }}"
                                                   data-sku="{{ $item->sku }}"
                                                   data-quantity="{{ $item->quantity }}"
                                                   data-unit="{{ $item->unit }}"
                                                   data-price="{{ $item->unit_price }}">
                                        </div>
                                    </td>
                                    <td>{{ $item->sku }}</td>
                                    <td>{{ $item->name }}</td>
                                    <td>{{ $item->category->name ?? 'Uncategorized' }}</td>
                                    <td>
                                        <span class="{{ $item->needsRestock() ? 'text-danger' : '' }}">
                                            {{ $item->quantity }} {{ $item->unit }}
                                        </span>
                                    </td>
                                    <td>{{ $item->unit }}</td>
                                    <td>${{ number_format($item->unit_price, 2) }}</td>
                                    <td>
                                        <span class="badge bg-{{ $item->status === 'active' ? 'success' : ($item->status === 'inactive' ? 'warning' : 'danger') }}">
                                            {{ ucfirst($item->status) }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group">
                                            <a href="{{ route('admin.inventory.edit', ['item' => $item->id]) }}" 
                                               class="btn btn-sm btn-primary">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <button type="button" 
                                                    class="btn btn-sm {{ $item->is_locked ? 'btn-danger' : 'btn-secondary' }} lock-item"
                                                    data-item-id="{{ $item->id }}"
                                                    data-locked="{{ $item->is_locked ? 'true' : 'false' }}">
                                                <i class="fas {{ $item->is_locked ? 'fa-lock' : 'fa-lock-open' }}"></i>
                                            </button>
                                            <form action="{{ route('admin.inventory.destroy', ['item' => $item->id]) }}" 
                                                  method="POST" 
                                                  class="d-inline"
                                                  onsubmit="return confirm('Are you sure you want to delete this item?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger">
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
            @endif
        </div>
    </div>
</div>

<!-- Order Modal -->
<div class="modal fade" id="orderModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Create Order</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.supply.orders.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="supplier_id" class="form-label">Supplier</label>
                        <select class="form-select" id="supplier_id" name="supplier_id" required>
                            <option value="">Select Supplier</option>
                            @foreach($suppliers as $supplier)
                                <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Selected Items</label>
                        <div id="selectedItemsList" class="list-group">
                            <!-- Selected items will be listed here -->
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="notes" class="form-label">Notes</label>
                        <textarea class="form-control" id="notes" name="notes" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create Order</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const selectAll = document.getElementById('selectAll');
    const itemCheckboxes = document.querySelectorAll('.item-checkbox');
    const createOrderBtn = document.getElementById('createOrderBtn');
    const selectedItemsList = document.getElementById('selectedItemsList');
    const orderModal = new bootstrap.Modal(document.getElementById('orderModal'));
    const lockButtons = document.querySelectorAll('.lock-item');

    // Handle select all checkbox
    selectAll.addEventListener('change', function() {
        itemCheckboxes.forEach(checkbox => {
            if (!checkbox.closest('tr').querySelector('.lock-item').dataset.locked === 'true') {
                checkbox.checked = this.checked;
            }
        });
        updateCreateOrderButton();
    });

    // Handle individual checkboxes
    itemCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            updateCreateOrderButton();
        });
    });

    // Handle lock buttons
    lockButtons.forEach(button => {
        button.addEventListener('click', function() {
            const itemId = this.dataset.itemId;
            const isLocked = this.dataset.locked === 'true';
            const checkbox = this.closest('tr').querySelector('.item-checkbox');

            // Toggle lock state
            this.dataset.locked = !isLocked;
            this.classList.toggle('btn-danger', !isLocked);
            this.classList.toggle('btn-secondary', isLocked);
            this.querySelector('i').classList.toggle('fa-lock', !isLocked);
            this.querySelector('i').classList.toggle('fa-lock-open', isLocked);

            // If locking, uncheck the checkbox
            if (!isLocked) {
                checkbox.checked = false;
            }

            // Update button state
            updateCreateOrderButton();

            // Send AJAX request to update lock state
            fetch(`/admin/inventory/${itemId}/toggle-lock`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ is_locked: !isLocked })
            });
        });
    });

    // Handle create order button
    createOrderBtn.addEventListener('click', function() {
        const selectedItems = Array.from(itemCheckboxes)
            .filter(checkbox => checkbox.checked)
            .map(checkbox => ({
                id: checkbox.value,
                name: checkbox.dataset.name,
                sku: checkbox.dataset.sku,
                quantity: checkbox.dataset.quantity,
                unit: checkbox.dataset.unit,
                price: checkbox.dataset.price
            }));

        // Update selected items list in modal
        selectedItemsList.innerHTML = selectedItems.map(item => `
            <div class="list-group-item">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <strong>${item.name}</strong> (${item.sku})
                        <br>
                        <small class="text-muted">
                            Current Quantity: ${item.quantity} ${item.unit}
                        </small>
                    </div>
                    <div class="input-group" style="width: 200px;">
                        <input type="number" name="items[${item.id}][quantity]" 
                               class="form-control" min="1" value="1" required>
                        <input type="hidden" name="items[${item.id}][inventory_item_id]" value="${item.id}">
                        <input type="hidden" name="items[${item.id}][unit_price]" value="${item.price}">
                        <span class="input-group-text">${item.unit}</span>
                    </div>
                </div>
            </div>
        `).join('');

        orderModal.show();
    });

    function updateCreateOrderButton() {
        const hasSelectedItems = Array.from(itemCheckboxes).some(checkbox => checkbox.checked);
        createOrderBtn.disabled = !hasSelectedItems;
    }
});
</script>
@endpush 