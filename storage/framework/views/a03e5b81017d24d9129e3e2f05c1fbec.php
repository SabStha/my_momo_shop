<?php $__env->startSection('title', 'Supply Orders'); ?>

<?php $__env->startSection('content'); ?>
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h3 class="card-title">Supply Orders</h3>
                        <a href="<?php echo e(route('admin.supply.orders.create')); ?>" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Create New Order
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <?php if(session('success')): ?>
                        <div class="alert alert-success">
                            <?php echo e(session('success')); ?>

                        </div>
                    <?php endif; ?>

                    <?php if(session('error')): ?>
                        <div class="alert alert-danger">
                            <?php echo e(session('error')); ?>

                        </div>
                    <?php endif; ?>

                    <?php $__empty_1 = true; $__currentLoopData = $ordersBySupplier; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $supplierId => $orders): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <?php
                            $supplier = $orders->first()->supplier;
                            $pendingOrders = $orders->where('status', 'pending');
                            $receivedOrders = $orders->where('status', 'received');
                            $cancelledOrders = $orders->where('status', 'cancelled');
                        ?>

                        <div class="supplier-section mb-4">
                            <div class="card">
                                <div class="card-header bg-primary text-white">
                                    <h4 class="mb-0"><?php echo e($supplier->name); ?></h4>
                                </div>
                                <div class="card-body">
                                    <div class="btn-group mb-3" role="group">
                                        <button type="button" class="btn btn-warning order-status-btn active" data-status="pending">
                                            <i class="fas fa-clock"></i> Pending Orders
                                            <span class="badge bg-light text-dark ms-1"><?php echo e($pendingOrders->count()); ?></span>
                                        </button>
                                        <button type="button" class="btn btn-success order-status-btn" data-status="received">
                                            <i class="fas fa-check"></i> Received Orders
                                            <span class="badge bg-light text-dark ms-1"><?php echo e($receivedOrders->count()); ?></span>
                                        </button>
                                        <button type="button" class="btn btn-danger order-status-btn" data-status="cancelled">
                                            <i class="fas fa-times"></i> Cancelled Orders
                                            <span class="badge bg-light text-dark ms-1"><?php echo e($cancelledOrders->count()); ?></span>
                                        </button>
                                    </div>

                                    <!-- Pending Orders Table -->
                                    <div class="order-table" id="pending-orders-<?php echo e($supplierId); ?>" style="display: block;">
                                        <div class="table-responsive">
                                            <table class="table">
                                                <thead>
                                                    <tr>
                                                        <th>
                                                            <div class="form-check">
                                                                <input class="form-check-input select-all-items" type="checkbox">
                                                                <label class="form-check-label">Select All</label>
                                                            </div>
                                                        </th>
                                                        <th>Order #</th>
                                                        <th>Date</th>
                                                        <th>Total Amount</th>
                                                        <th>Items</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php $__currentLoopData = $pendingOrders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $order): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                        <tr>
                                                            <td>
                                                                <div class="form-check">
                                                                    <input class="form-check-input order-checkbox" 
                                                                           type="checkbox" 
                                                                           value="<?php echo e($order->id); ?>" 
                                                                           id="order_<?php echo e($order->id); ?>"
                                                                           data-order-number="<?php echo e($order->order_number); ?>">
                                                                </div>
                                                            </td>
                                                            <td><?php echo e($order->order_number); ?></td>
                                                            <td><?php echo e($order->ordered_at->format('M d, Y')); ?></td>
                                                            <td>$<?php echo e(number_format($order->total_amount, 2)); ?></td>
                                                            <td>
                                                                <ul class="list-unstyled mb-0">
                                                                    <?php $__currentLoopData = $order->items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                                        <li>
                                                                            <div class="form-check">
                                                                                <input class="form-check-input item-checkbox" 
                                                                                       type="checkbox" 
                                                                                       value="<?php echo e($item->id); ?>" 
                                                                                       id="item_<?php echo e($item->id); ?>"
                                                                                       data-order-id="<?php echo e($order->id); ?>"
                                                                                       data-quantity="<?php echo e($item->quantity); ?>"
                                                                                       data-unit="<?php echo e($item->inventoryItem->unit); ?>"
                                                                                       data-name="<?php echo e($item->inventoryItem->name); ?>"
                                                                                       data-inventory-item-id="<?php echo e($item->inventory_item_id); ?>">
                                                                                <label class="form-check-label" for="item_<?php echo e($item->id); ?>">
                                                                                    <?php echo e($item->quantity); ?> <?php echo e($item->inventoryItem->unit); ?> 
                                                                                    <?php echo e($item->inventoryItem->name); ?>

                                                                                </label>
                                                                            </div>
                                                                        </li>
                                                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                                </ul>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                </tbody>
                                            </table>
                                        </div>
                                        <?php if($pendingOrders->isNotEmpty()): ?>
                                            <div class="mt-3">
                                                <button type="button" class="btn btn-success" onclick="showReceiveModal()">
                                                    <i class="fas fa-check"></i> Receive Selected Items
                                                </button>
                                                <button type="button" class="btn btn-warning" onclick="showSendModal()">
                                                    <i class="fas fa-envelope"></i> Send Selected Orders
                                                </button>
                                            </div>
                                        <?php endif; ?>
                                    </div>

                                    <!-- Received Orders Table -->
                                    <div class="order-table" id="received-orders-<?php echo e($supplierId); ?>" style="display: none;">
                                        <div class="table-responsive">
                                            <table class="table">
                                                <thead>
                                                    <tr>
                                                        <th>Order #</th>
                                                        <th>Date</th>
                                                        <th>Received Date</th>
                                                        <th>Total Amount</th>
                                                        <th>Items</th>
                                                        <th>Actions</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php $__currentLoopData = $receivedOrders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $order): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                        <tr>
                                                            <td><?php echo e($order->order_number); ?></td>
                                                            <td><?php echo e($order->ordered_at->format('M d, Y')); ?></td>
                                                            <td><?php echo e($order->received_at->format('M d, Y')); ?></td>
                                                            <td>$<?php echo e(number_format($order->total_amount, 2)); ?></td>
                                                            <td>
                                                                <ul class="list-unstyled mb-0">
                                                                    <?php $__currentLoopData = $order->items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                                        <li>
                                                                            <?php echo e($item->quantity); ?> <?php echo e($item->inventoryItem->unit); ?> 
                                                                            <?php echo e($item->inventoryItem->name); ?>

                                                                        </li>
                                                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                                </ul>
                                                            </td>
                                                            <td>
                                                                <div class="btn-group">
                                                                    <a href="<?php echo e(route('admin.supply.orders.show', $order)); ?>" 
                                                                       class="btn btn-sm btn-info">
                                                                        <i class="fas fa-eye"></i> View
                                                                    </a>
                                                                    <button type="button" 
                                                                            class="btn btn-sm btn-warning"
                                                                            onclick="showPartialReceiveModal('<?php echo e($order->id); ?>', '<?php echo e($order->order_number); ?>')">
                                                                        <i class="fas fa-clipboard-check"></i> Partial Receive
                                                                    </button>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>

                                    <!-- Cancelled Orders Table -->
                                    <div class="order-table" id="cancelled-orders-<?php echo e($supplierId); ?>" style="display: none;">
                                        <div class="table-responsive">
                                            <table class="table">
                                                <thead>
                                                    <tr>
                                                        <th>Order #</th>
                                                        <th>Date</th>
                                                        <th>Total Amount</th>
                                                        <th>Items</th>
                                                        <th>Actions</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php $__currentLoopData = $cancelledOrders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $order): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                        <tr>
                                                            <td><?php echo e($order->order_number); ?></td>
                                                            <td><?php echo e($order->ordered_at->format('M d, Y')); ?></td>
                                                            <td>$<?php echo e(number_format($order->total_amount, 2)); ?></td>
                                                            <td>
                                                                <ul class="list-unstyled mb-0">
                                                                    <?php $__currentLoopData = $order->items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                                        <li>
                                                                            <?php echo e($item->quantity); ?> <?php echo e($item->inventoryItem->unit); ?> 
                                                                            <?php echo e($item->inventoryItem->name); ?>

                                                                        </li>
                                                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                                </ul>
                                                            </td>
                                                            <td>
                                                                <a href="<?php echo e(route('admin.supply.orders.show', $order)); ?>" 
                                                                   class="btn btn-sm btn-info">
                                                                    <i class="fas fa-eye"></i> View
                                                                </a>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <div class="alert alert-info">
                            No orders found.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Receive Modal -->
<div class="modal fade" id="receiveModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Receiving Items</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="receiveForm" method="POST" action="<?php echo e(route('admin.supply.orders.update', ['order' => 0])); ?>">
                <?php echo csrf_field(); ?>
                <?php echo method_field('PUT'); ?>
                <input type="hidden" name="status" value="received">
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> Please confirm the items you are receiving.
                    </div>
                    <div id="selectedItemsList" class="list-group mb-3">
                        <!-- Selected items will be populated here -->
                    </div>
                    <div class="form-group">
                        <label for="receiveNotes" class="form-label">Notes (optional)</label>
                        <textarea class="form-control" id="receiveNotes" name="notes" rows="3" 
                                  placeholder="Add any notes about the received items"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Confirm Receive</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Send Modal -->
<div class="modal fade" id="sendModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Sending Orders</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> Are you sure you want to send the selected orders to their suppliers?
                </div>
                <div id="selectedOrdersList" class="list-group">
                    <!-- Selected orders will be populated here -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-warning" id="confirmSendBtn">Confirm Send</button>
            </div>
        </div>
    </div>
</div>

<!-- Partial Receive Modal -->
<div class="modal fade" id="partialReceiveModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Partial Receive - Order #<span id="orderNumber"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="partialReceiveForm" method="POST">
                <?php echo csrf_field(); ?>
                <?php echo method_field('PUT'); ?>
                <input type="hidden" name="status" value="partially_received">
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> Enter the actual quantity received for each item.
                    </div>
                    <div id="itemsList" class="list-group">
                        <!-- Items will be populated here -->
                    </div>
                    <div class="mt-3">
                        <label for="notes" class="form-label">Notes about partial receipt</label>
                        <textarea class="form-control" id="notes" name="notes" rows="3" 
                                  placeholder="Add any notes about the partial receipt (e.g., reasons for shortfall)"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning">Confirm Partial Receive</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Cancel Confirmation Modal -->
<div class="modal fade" id="cancelConfirmModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Cancelling Order</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to cancel this order?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">No, Keep Order</button>
                <button type="button" class="btn btn-danger" id="confirmCancelBtn">Yes, Cancel Order</button>
            </div>
        </div>
    </div>
</div>

<!-- Success Modal -->
<div class="modal fade" id="successModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">
                    <i class="fas fa-check-circle"></i> Success
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p id="successMessage"></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-success" data-bs-dismiss="modal">OK</button>
            </div>
        </div>
    </div>
</div>

<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
// Add new script for order status buttons
document.querySelectorAll('.order-status-btn').forEach(button => {
    button.addEventListener('click', function() {
        const supplierSection = this.closest('.supplier-section');
        const status = this.dataset.status;
        const supplierId = supplierSection.querySelector('.order-table').id.split('-').pop();
        
        // Update button states
        supplierSection.querySelectorAll('.order-status-btn').forEach(btn => {
            btn.classList.remove('active');
        });
        this.classList.add('active');
        
        // Show/hide tables
        supplierSection.querySelectorAll('.order-table').forEach(table => {
            table.style.display = 'none';
        });
        supplierSection.querySelector(`#${status}-orders-${supplierId}`).style.display = 'block';
    });
});

// Handle select all checkbox
document.querySelectorAll('.select-all-items').forEach(selectAllCheckbox => {
    selectAllCheckbox.addEventListener('change', function() {
        // Get the supplier section this checkbox belongs to
        const supplierSection = this.closest('.supplier-section');
        
        // Select/deselect all order checkboxes in this section
        const orderCheckboxes = supplierSection.querySelectorAll('.order-checkbox');
        orderCheckboxes.forEach(checkbox => {
            checkbox.checked = this.checked;
        });
        
        // Select/deselect all item checkboxes in this section
        const itemCheckboxes = supplierSection.querySelectorAll('.item-checkbox');
        itemCheckboxes.forEach(checkbox => {
            checkbox.checked = this.checked;
        });
    });
});

// Handle order checkbox
document.querySelectorAll('.order-checkbox').forEach(checkbox => {
    checkbox.addEventListener('change', function() {
        const orderId = this.value;
        const supplierSection = this.closest('.supplier-section');
        const itemCheckboxes = supplierSection.querySelectorAll(`.item-checkbox[data-order-id="${orderId}"]`);
        
        // Update all item checkboxes for this order
        itemCheckboxes.forEach(itemCheckbox => {
            itemCheckbox.checked = this.checked;
        });
        
        // Update the select all checkbox state
        const selectAllCheckbox = supplierSection.querySelector('.select-all-items');
        const allOrderCheckboxes = supplierSection.querySelectorAll('.order-checkbox');
        const allChecked = Array.from(allOrderCheckboxes).every(orderCheckbox => orderCheckbox.checked);
        selectAllCheckbox.checked = allChecked;
    });
});

// Handle item checkbox
document.querySelectorAll('.item-checkbox').forEach(checkbox => {
    checkbox.addEventListener('change', function() {
        const orderId = this.dataset.orderId;
        const supplierSection = this.closest('.supplier-section');
        const orderCheckbox = supplierSection.querySelector(`#order_${orderId}`);
        const itemCheckboxes = supplierSection.querySelectorAll(`.item-checkbox[data-order-id="${orderId}"]`);
        
        // Update order checkbox state based on all items
        const allItemsChecked = Array.from(itemCheckboxes).every(item => item.checked);
        orderCheckbox.checked = allItemsChecked;
        
        // Update the select all checkbox state
        const selectAllCheckbox = supplierSection.querySelector('.select-all-items');
        const allOrderCheckboxes = supplierSection.querySelectorAll('.order-checkbox');
        const allOrdersChecked = Array.from(allOrderCheckboxes).every(orderCheckbox => orderCheckbox.checked);
        selectAllCheckbox.checked = allOrdersChecked;
    });
});

function showReceiveModal() {
    const selectedItems = Array.from(document.querySelectorAll('.item-checkbox:checked'));
    
    if (selectedItems.length === 0) {
        alert('Please select at least one item to receive.');
        return;
    }
    
    // Get the order ID from the first selected item
    const orderId = selectedItems[0].dataset.orderId;
    
    // Update the form action with the correct order ID
    const form = document.getElementById('receiveForm');
    form.action = `/admin/supply/orders/${orderId}`;
    
    const itemsList = document.getElementById('selectedItemsList');
    itemsList.innerHTML = selectedItems.map(item => `
        <div class="list-group-item">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h6 class="mb-1">${item.dataset.name}</h6>
                    <small class="text-muted">
                        Ordered: ${item.dataset.quantity} ${item.dataset.unit}
                    </small>
                </div>
                <div class="col-md-6">
                    <div class="input-group">
                        <input type="number" 
                               class="form-control" 
                               name="actual_received_quantities[${item.value}]" 
                               min="0" 
                               max="${item.dataset.quantity}" 
                               value="${item.dataset.quantity}"
                               required>
                        <span class="input-group-text">${item.dataset.unit}</span>
                    </div>
                </div>
            </div>
        </div>
    `).join('');
    
    const modal = new bootstrap.Modal(document.getElementById('receiveModal'));
    modal.show();
}

function showSendModal() {
    const selectedOrders = Array.from(document.querySelectorAll('.order-checkbox:checked'));
    
    if (selectedOrders.length === 0) {
        alert('Please select at least one order to send.');
        return;
    }
    
    const ordersList = document.getElementById('selectedOrdersList');
    ordersList.innerHTML = selectedOrders.map(order => `
        <div class="list-group-item">
            <h6 class="mb-1">Order #${order.dataset.orderNumber}</h6>
        </div>
    `).join('');
    
    const modal = new bootstrap.Modal(document.getElementById('sendModal'));
    modal.show();
}

// Handle receive form submission
document.getElementById('receiveForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    // Get the submit button and store its original text
    const submitButton = this.querySelector('button[type="submit"]');
    const originalText = submitButton.innerHTML;
    
    // Disable the button and show loading state
    submitButton.disabled = true;
    submitButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
    
    const selectedItems = Array.from(document.querySelectorAll('.item-checkbox:checked'));
    
    // Create form data with proper array structure
    const formData = new FormData();
    formData.append('status', 'received');
    formData.append('notes', document.getElementById('receiveNotes').value);
    
    // Add each selected item with proper array structure
    selectedItems.forEach((checkbox, index) => {
        const actualQuantity = document.querySelector(`input[name="actual_received_quantities[${checkbox.value}]"]`).value;
        formData.append(`items[${index}][id]`, checkbox.value);
        formData.append(`items[${index}][actual_received_quantity]`, actualQuantity);
        formData.append(`items[${index}][inventory_item_id]`, checkbox.dataset.inventoryItemId);
    });
    
    // Get the order ID from the form action
    const orderId = this.action.split('/').pop();
    
    fetch(`/admin/supply/orders/${orderId}`, {
        method: 'PUT',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json',
            'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: new URLSearchParams(formData)
    })
    .then(response => {
        if (!response.ok) {
            return response.json().then(err => Promise.reject(err));
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            // Close the receive modal
            bootstrap.Modal.getInstance(document.getElementById('receiveModal')).hide();
            
            // Show success message in popup
            document.getElementById('successMessage').textContent = data.message;
            const successModal = new bootstrap.Modal(document.getElementById('successModal'));
            successModal.show();
            
            // Reload page after success modal is closed
            document.getElementById('successModal').addEventListener('hidden.bs.modal', function () {
                window.location.reload();
            }, { once: true });
        } else {
            throw new Error(data.message || 'Failed to process receive');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        // Show error message
        const alert = document.createElement('div');
        alert.className = 'alert alert-danger alert-dismissible fade show';
        alert.innerHTML = `
            <i class="fas fa-exclamation-circle"></i> ${error.message || 'An error occurred while processing the receive.'}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        `;
        document.querySelector('.card-body').insertBefore(alert, document.querySelector('.mt-4'));
        
        // Reset button state
        submitButton.disabled = false;
        submitButton.innerHTML = originalText;
    });
});

// Handle send confirmation
document.getElementById('confirmSendBtn').addEventListener('click', function() {
    const selectedOrders = Array.from(document.querySelectorAll('.order-checkbox:checked'))
        .map(checkbox => checkbox.value);
    
    // Disable the button and show loading state
    const button = this;
    const originalText = button.innerHTML;
    button.disabled = true;
    button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Sending Orders...';
    
    // Send each order
    const sendPromises = selectedOrders.map(orderId => 
        fetch(`/admin/supply/orders/${orderId}/send`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            }
        })
    );
    
    Promise.all(sendPromises)
        .then(() => {
            // Close the send modal
            bootstrap.Modal.getInstance(document.getElementById('sendModal')).hide();
            
            // Show success message in popup
            document.getElementById('successMessage').textContent = 'Orders have been sent to suppliers successfully.';
            const successModal = new bootstrap.Modal(document.getElementById('successModal'));
            successModal.show();
            
            // Reset button state
            button.disabled = false;
            button.innerHTML = originalText;
            
            // Reload page after success modal is closed
            document.getElementById('successModal').addEventListener('hidden.bs.modal', function () {
                window.location.reload();
            }, { once: true });
        })
        .catch(error => {
            console.error('Error:', error);
            // Show error message
            const alert = document.createElement('div');
            alert.className = 'alert alert-danger alert-dismissible fade show';
            alert.innerHTML = `
                <i class="fas fa-exclamation-circle"></i> An error occurred while sending the orders.
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            `;
            document.querySelector('.card-body').insertBefore(alert, document.querySelector('.mt-4'));
            
            // Reset button state
            button.disabled = false;
            button.innerHTML = originalText;
        });
});

function confirmCancel(event) {
    event.preventDefault();
    currentForm = event.target;
    const modal = new bootstrap.Modal(document.getElementById('cancelConfirmModal'));
    modal.show();
}

function showPartialReceiveModal(orderId, orderNumber) {
    // Set the order number in the modal
    document.getElementById('orderNumber').textContent = orderNumber;
    
    // Set the form action
    const form = document.getElementById('partialReceiveForm');
    form.action = `/admin/supply/orders/${orderId}/partial-receive`;
    
    // Fetch order items
    fetch(`/admin/supply/orders/${orderId}/items`, {
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        return response.json();
    })
    .then(data => {
        const itemsList = document.getElementById('itemsList');
        itemsList.innerHTML = data.items.map(item => `
            <div class="list-group-item">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <h6 class="mb-1">${item.inventory_item.name}</h6>
                        <small class="text-muted">
                            Ordered: ${item.quantity} ${item.inventory_item.unit}
                        </small>
                    </div>
                    <div class="col-md-6">
                        <div class="input-group">
                            <input type="number" 
                                   class="form-control" 
                                   name="items[${item.id}][actual_received_quantity]" 
                                   min="0" 
                                   max="${item.quantity}" 
                                   value="${item.quantity}"
                                   required>
                            <span class="input-group-text">${item.inventory_item.unit}</span>
                        </div>
                        <input type="hidden" name="items[${item.id}][inventory_item_id]" value="${item.inventory_item_id}">
                    </div>
                </div>
            </div>
        `).join('');
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error loading order items. Please try again.');
    });
    
    // Show the modal
    const modal = new bootstrap.Modal(document.getElementById('partialReceiveModal'));
    modal.show();
}

// Add form submission handler for partial receive
document.getElementById('partialReceiveForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const orderId = this.action.split('/').pop();
    
    fetch(this.action, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(Object.fromEntries(formData))
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Show success message
            const alert = document.createElement('div');
            alert.className = 'alert alert-success alert-dismissible fade show';
            alert.innerHTML = `
                ${data.message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            `;
            document.querySelector('.card-body').insertBefore(alert, document.querySelector('.mt-4'));
            
            // Close modal
            bootstrap.Modal.getInstance(document.getElementById('partialReceiveModal')).hide();
            
            // Reload page after a short delay
            setTimeout(() => {
                window.location.reload();
            }, 1500);
        } else {
            throw new Error(data.message || 'Failed to process partial receive');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert(error.message || 'An error occurred while processing the partial receive.');
    });
});

// Handle cancel confirmation
document.getElementById('confirmCancelBtn').addEventListener('click', function() {
    if (currentForm) {
        currentForm.submit();
    }
});
</script>
<?php $__env->stopPush(); ?> 
<?php echo $__env->make('desktop.admin.layouts.admin', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\sabst\momo_shop\resources\views/desktop/admin/supply/orders/list.blade.php ENDPATH**/ ?>