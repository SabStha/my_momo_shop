<div class="container py-3">
    <h2>2-Day Inventory Forecast</h2>
    <div class="table-responsive">
        <table class="table table-bordered table-hover">
            <thead class="table-light">
                <tr>
                    <th>Item</th>
                    <th>Current Qty</th>
                    <th>Avg Daily Usage</th>
                    <th>Trend</th>
                    <th>Safety Stock</th>
                    <th>Reorder Point</th>
                    <th>Needed (2 days)</th>
                    <th>Suggested Order</th>
                    <th>Status</th>
                    <th>Last Count</th>
                    <th>Count Frequency</th>
                    <th>Unit</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $forecast; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr class="<?php echo e($row['status'] === 'Reorder' ? 'table-warning' : ''); ?>" id="row-<?php echo e($row['id']); ?>">
                    <td><?php echo e($row['name']); ?></td>
                    <td>
                        <span class="view-mode"><?php echo e($row['current']); ?></span>
                        <input type="number" class="form-control edit-mode" style="display:none" 
                               value="<?php echo e($row['current']); ?>" name="current_quantity">
                    </td>
                    <td>
                        <span class="view-mode"><?php echo e($row['avg']); ?></span>
                        <input type="number" class="form-control edit-mode" style="display:none" 
                               value="<?php echo e($row['avg']); ?>" name="avg_usage" step="0.01">
                    </td>
                    <td>
                        <?php if($row['trend'] > 0): ?>
                            <span class="text-success view-mode">↑ <?php echo e($row['trend']); ?></span>
                        <?php elseif($row['trend'] < 0): ?>
                            <span class="text-danger view-mode">↓ <?php echo e(abs($row['trend'])); ?></span>
                        <?php else: ?>
                            <span class="text-muted view-mode">→ 0</span>
                        <?php endif; ?>
                        <input type="number" class="form-control edit-mode" style="display:none" 
                               value="<?php echo e($row['trend']); ?>" name="trend" step="0.01">
                    </td>
                    <td>
                        <span class="view-mode"><?php echo e($row['safety_stock']); ?></span>
                        <input type="number" class="form-control edit-mode" style="display:none" 
                               value="<?php echo e($row['safety_stock']); ?>" name="safety_stock" step="0.01">
                    </td>
                    <td>
                        <span class="view-mode"><?php echo e($row['reorder_point']); ?></span>
                        <input type="number" class="form-control edit-mode" style="display:none" 
                               value="<?php echo e($row['reorder_point']); ?>" name="reorder_point" step="0.01">
                    </td>
                    <td><?php echo e($row['needed']); ?></td>
                    <td>
                        <span class="view-mode"><?php echo e($row['suggested']); ?></span>
                        <input type="number" class="form-control edit-mode" style="display:none" 
                               value="<?php echo e($row['suggested']); ?>" name="suggested" step="0.01">
                    </td>
                    <td>
                        <?php if($row['status'] === 'Reorder'): ?>
                            <span class="badge bg-warning view-mode">Reorder</span>
                        <?php else: ?>
                            <span class="badge bg-success view-mode">OK</span>
                        <?php endif; ?>
                        <select class="form-select edit-mode" style="display:none" name="status">
                            <option value="OK" <?php echo e($row['status'] === 'OK' ? 'selected' : ''); ?>>OK</option>
                            <option value="Reorder" <?php echo e($row['status'] === 'Reorder' ? 'selected' : ''); ?>>Reorder</option>
                        </select>
                    </td>
                    <td><?php echo e($row['last_count']); ?></td>
                    <td><?php echo e($row['count_frequency']); ?></td>
                    <td><?php echo e($row['unit']); ?></td>
                    <td>
                        <button class="btn btn-sm btn-primary edit-btn" data-id="<?php echo e($row['id']); ?>">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-sm btn-success save-btn" data-id="<?php echo e($row['id']); ?>" style="display:none">
                            <i class="fas fa-save"></i>
                        </button>
                        <button class="btn btn-sm btn-danger cancel-btn" data-id="<?php echo e($row['id']); ?>" style="display:none">
                            <i class="fas fa-times"></i>
                        </button>
                    </td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr>
                    <td colspan="13" class="text-center">No forecast data available.</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php $__env->startPush('scripts'); ?>
<script>
$(document).ready(function() {
    // Edit button click
    $('.edit-btn').click(function() {
        const rowId = $(this).data('id');
        const row = $(`#row-${rowId}`);
        
        // Show edit mode
        row.find('.view-mode').hide();
        row.find('.edit-mode').show();
        row.find('.edit-btn').hide();
        row.find('.save-btn, .cancel-btn').show();
    });

    // Cancel button click
    $('.cancel-btn').click(function() {
        const rowId = $(this).data('id');
        const row = $(`#row-${rowId}`);
        
        // Show view mode
        row.find('.view-mode').show();
        row.find('.edit-mode').hide();
        row.find('.edit-btn').show();
        row.find('.save-btn, .cancel-btn').hide();
    });

    // Save button click
    $('.save-btn').click(function() {
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

        // Send AJAX request to update
        $.ajax({
            url: '/admin/inventory/forecast/update',
            method: 'POST',
            data: data,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    // Update view mode values
                    row.find('.view-mode').each(function() {
                        const input = row.find(`[name="${$(this).data('field')}"]`);
                        if (input.length) {
                            $(this).text(input.val());
                        }
                    });
                    
                    // Show view mode
                    row.find('.view-mode').show();
                    row.find('.edit-mode').hide();
                    row.find('.edit-btn').show();
                    row.find('.save-btn, .cancel-btn').hide();

                    // Show success message
                    alert('Forecast updated successfully');
                } else {
                    alert('Error updating forecast');
                }
            },
            error: function() {
                alert('Error updating forecast');
            }
        });
    });
});
</script>
<?php $__env->stopPush(); ?> <?php /**PATH C:\Users\sabst\momo_shop\resources\views/admin/inventory/forecast-partial.blade.php ENDPATH**/ ?>