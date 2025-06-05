

<?php $__env->startSection('title', 'Daily Stock Check'); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-md-6">
            <h2>Daily Stock Check</h2>
        </div>
        <div class="col-md-6 text-end">
            <span class="text-muted">Date: <?php echo e(now()->format('F j, Y')); ?></span>
        </div>
    </div>

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

    <div class="card">
        <div class="card-body">
            <form action="<?php echo e(route('admin.inventory.checks.store')); ?>" method="POST" id="stockCheckForm">
                <?php echo csrf_field(); ?>
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Item Name</th>
                                <th>SKU</th>
                                <th>Current Stock</th>
                                <th>Checked Quantity</th>
                                <th>Notes</th>
                                <th>Last Checked</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr>
                                    <td><?php echo e($item->name); ?></td>
                                    <td><?php echo e($item->sku); ?></td>
                                    <td><?php echo e($item->quantity); ?> <?php echo e($item->unit); ?></td>
                                    <td>
                                        <input type="number" 
                                               name="quantities[<?php echo e($item->id); ?>]" 
                                               class="form-control form-control-sm" 
                                               step="0.01" 
                                               min="0"
                                               value="<?php echo e($item->dailyChecks->first()?->quantity_checked ?? ''); ?>"
                                               required>
                                        <input type="hidden" name="item_ids[]" value="<?php echo e($item->id); ?>">
                                    </td>
                                    <td>
                                        <input type="text" 
                                               name="notes[<?php echo e($item->id); ?>]" 
                                               class="form-control form-control-sm"
                                               value="<?php echo e($item->dailyChecks->first()?->notes ?? ''); ?>"
                                               placeholder="Optional notes">
                                    </td>
                                    <td>
                                        <?php if($item->dailyChecks->first()): ?>
                                            <?php echo e($item->dailyChecks->first()->created_at->format('H:i')); ?>

                                        <?php else: ?>
                                            <span class="text-muted">Not checked today</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Save All Checks
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php $__env->startPush('scripts'); ?>
<script>
document.getElementById('stockCheckForm').addEventListener('submit', function(e) {
    if (!confirm('Are you sure you want to save all stock checks?')) {
        e.preventDefault();
    }
});
</script>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?> 
<?php echo $__env->make('desktop.admin.layouts.admin', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\sabst\momo_shop\resources\views/desktop/admin/inventory/checks/index.blade.php ENDPATH**/ ?>