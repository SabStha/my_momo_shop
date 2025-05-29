<?php if(isset($forecast) && count($forecast) > 0): ?>
<div class="table-responsive">
    <table class="table table-striped">
        <thead>
            <tr>
                <th>Item</th>
                <th>Current</th>
                <th>Avg Usage</th>
                <th>Needed</th>
                <th>Suggested</th>
                <th>Unit</th>
                <th>Trend</th>
                <th>Safety Stock</th>
                <th>Reorder Point</th>
                <th>Status</th>
                <th>Last Count</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php $__currentLoopData = $forecast; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <tr id="row-<?php echo e($item['id']); ?>">
                <td><?php echo e($item['name']); ?></td>
                <td>
                    <span class="view-mode" data-field="current_quantity"><?php echo e(number_format($item['current'], 2)); ?></span>
                    <div class="edit-mode" style="display:none;">
                        <input type="number" class="form-control form-control-sm" name="current_quantity" value="<?php echo e($item['current']); ?>">
                    </div>
                </td>
                <td>
                    <span class="view-mode" data-field="avg_usage"><?php echo e(number_format($item['avg'], 2)); ?></span>
                    <div class="edit-mode" style="display:none;">
                        <input type="number" class="form-control form-control-sm" name="avg_usage" value="<?php echo e($item['avg']); ?>">
                    </div>
                </td>
                <td><?php echo e(number_format($item['needed'], 2)); ?></td>
                <td>
                    <span class="view-mode" data-field="suggested"><?php echo e(number_format($item['suggested'], 2)); ?></span>
                    <div class="edit-mode" style="display:none;">
                        <input type="number" class="form-control form-control-sm suggested" name="suggested" value="<?php echo e($item['suggested']); ?>" data-id="<?php echo e($item['id']); ?>">
                    </div>
                </td>
                <td><?php echo e($item['unit']); ?></td>
                <td>
                    <span class="view-mode" data-field="trend"><?php echo e(number_format($item['trend'], 2)); ?></span>
                    <div class="edit-mode" style="display:none;">
                        <input type="number" class="form-control form-control-sm" name="trend" value="<?php echo e($item['trend']); ?>">
                    </div>
                </td>
                <td>
                    <span class="view-mode" data-field="safety_stock"><?php echo e(number_format($item['safety_stock'], 2)); ?></span>
                    <div class="edit-mode" style="display:none;">
                        <input type="number" class="form-control form-control-sm" name="safety_stock" value="<?php echo e($item['safety_stock']); ?>">
                    </div>
                </td>
                <td>
                    <span class="view-mode" data-field="reorder_point"><?php echo e(number_format($item['reorder_point'], 2)); ?></span>
                    <div class="edit-mode" style="display:none;">
                        <input type="number" class="form-control form-control-sm" name="reorder_point" value="<?php echo e($item['reorder_point']); ?>">
                    </div>
                </td>
                <td>
                    <span class="view-mode" data-field="status">
                        <span class="badge bg-<?php echo e($item['status'] === 'OK' ? 'success' : 'danger'); ?>">
                            <?php echo e($item['status']); ?>

                        </span>
                    </span>
                    <div class="edit-mode" style="display:none;">
                        <select class="form-select form-select-sm" name="status">
                            <option value="OK" <?php echo e($item['status'] === 'OK' ? 'selected' : ''); ?>>OK</option>
                            <option value="Reorder" <?php echo e($item['status'] === 'Reorder' ? 'selected' : ''); ?>>Reorder</option>
                        </select>
                    </div>
                </td>
                <td><?php echo e($item['last_count']); ?></td>
                <td>
                    <div class="btn-group">
                        <button class="btn btn-sm btn-primary edit-btn" data-id="<?php echo e($item['id']); ?>">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-sm btn-success save-btn" data-id="<?php echo e($item['id']); ?>" style="display:none;">
                            <i class="fas fa-save"></i>
                        </button>
                        <button class="btn btn-sm btn-secondary cancel-btn" data-id="<?php echo e($item['id']); ?>" style="display:none;">
                            <i class="fas fa-times"></i>
                        </button>
                        <button class="btn btn-sm btn-danger delete-btn" data-id="<?php echo e($item['id']); ?>">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </td>
            </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </tbody>
    </table>
</div>
<?php else: ?>
<div class="alert alert-info">
    No forecast data available.
</div>
<?php endif; ?>

<div class="mt-3">
    <button class="btn btn-success" id="create-order">
        <i class="fas fa-shopping-cart"></i> Create Order
    </button>
</div>

<!-- Order Modal -->
<div class="modal fade" id="orderModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Create Inventory Order</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="orderForm">
                    <div class="mb-3">
                        <label class="form-label">Supplier</label>
                        <select class="form-select" name="supplier_id" required>
                            <option value="">Select Supplier</option>
                            <?php $__currentLoopData = $suppliers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $supplier): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($supplier->id); ?>"><?php echo e($supplier->name); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Expected Delivery</label>
                        <input type="date" class="form-control" name="expected_delivery" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Notes</label>
                        <textarea class="form-control" name="notes"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="submitOrder">Create Order</button>
            </div>
        </div>
    </div>
</div>

<?php $__env->startPush('scripts'); ?>
<script>
$(function() {
    // Edit button click
    $('.edit-btn').on('click', function() {
        const rowId = $(this).data('id');
        const row = $(`#row-${rowId}`);
        row.find('.view-mode').hide();
        row.find('.edit-mode').show();
        row.find('.edit-btn').hide();
        row.find('.save-btn, .cancel-btn').show();
    });
    // Cancel button click
    $('.cancel-btn').on('click', function() {
        const rowId = $(this).data('id');
        const row = $(`#row-${rowId}`);
        row.find('.view-mode').show();
        row.find('.edit-mode').hide();
        row.find('.edit-btn').show();
        row.find('.save-btn, .cancel-btn').hide();
    });
    // Save button click
    $('.save-btn').on('click', function() {
        const rowId = $(this).data('id');
        const row = $(`#row-${rowId}`);
        const data = {
            id: rowId,
            current_quantity: row.find('[name="current_quantity"]').val(),
            avg_usage: row.find('[name="avg_usage"]').val(),
            trend: row.find('[name="trend"]').val(),
            safety_stock: row.find('[name="safety_stock"]').val(),
            reorder_point: row.find('[name="reorder_point"]').val(),
            suggested: row.find('[name="suggested"]').val(),
            status: row.find('[name="status"]').val()
        };
        $.ajax({
            url: '<?php echo e(route("admin.inventory.forecast.update")); ?>',
            method: 'POST',
            data: data,
            headers: {
                'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>'
            },
            success: function(response) {
                if (response.success) {
                    // Update view mode values
                    row.find('.view-mode[data-field="current_quantity"]').text(data.current_quantity);
                    row.find('.view-mode[data-field="avg_usage"]').text(data.avg_usage);
                    row.find('.view-mode[data-field="suggested"]').text(data.suggested);
                    row.find('.view-mode[data-field="trend"]').text(data.trend);
                    row.find('.view-mode[data-field="safety_stock"]').text(data.safety_stock);
                    row.find('.view-mode[data-field="reorder_point"]').text(data.reorder_point);
                    row.find('.view-mode[data-field="status"] .badge').text(data.status);
                    row.find('.view-mode').show();
                    row.find('.edit-mode').hide();
                    row.find('.edit-btn').show();
                    row.find('.save-btn, .cancel-btn').hide();
                    toastr.success('Forecast updated successfully');
                }
            },
            error: function() {
                toastr.error('Failed to update forecast');
            }
        });
    });
    // Delete button click
    $('.delete-btn').on('click', function() {
        const id = $(this).data('id');
        if (confirm('Are you sure you want to delete this item?')) {
            $.ajax({
                url: `/admin/inventory/${id}`,
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>'
                },
                success: function(response) {
                    toastr.success('Item deleted successfully');
                    location.reload();
                },
                error: function() {
                    toastr.error('Failed to delete item');
                }
            });
        }
    });
    // Create order button click
    let orderItems = [];
    $('#create-order').on('click', function() {
        orderItems = [];
        $('.suggested').each(function() {
            const id = $(this).data('id');
            const quantity = parseFloat($(this).val());
            if (quantity > 0) {
                orderItems.push({ id: id, quantity: quantity });
            }
        });
        if (orderItems.length === 0) {
            toastr.warning('No items selected for order');
            return;
        }
        $('#orderModal').modal('show');
    });
    // Submit order
    $('#submitOrder').on('click', function() {
        const data = {
            supplier_id: $('#orderForm [name="supplier_id"]').val(),
            expected_delivery: $('#orderForm [name="expected_delivery"]').val(),
            notes: $('#orderForm [name="notes"]').val(),
            items: orderItems
        };
        $.ajax({
            url: '<?php echo e(route("admin.inventory.order.create")); ?>',
            method: 'POST',
            data: JSON.stringify(data),
            contentType: 'application/json',
            dataType: 'json',
            headers: {
                'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>',
                'Accept': 'application/json'
            },
            success: function(response) {
                if (response.success) {
                    toastr.success('Order created successfully');
                    $('#orderModal').modal('hide');
                    location.reload();
                }
            },
            error: function(xhr) {
                toastr.error('Failed to create order');
                if (xhr.responseJSON && xhr.responseJSON.errors) {
                    console.log(xhr.responseJSON.errors);
                }
            }
        });
    });
});
</script>
<?php $__env->stopPush(); ?> <?php /**PATH C:\Users\sabst\momo_shop\resources\views/desktop/admin/inventory/forecast-partial.blade.php ENDPATH**/ ?>