
<?php $__env->startSection('title', 'Daily Inventory Count'); ?>
<?php $__env->startSection('content'); ?>
<div class="container py-3">
    <h2>Daily Inventory Count</h2>
    <div class="alert alert-info">No items to count yet. Add stock items to get started!</div>
    <form action="<?php echo e(route('admin.inventory.count')); ?>" method="GET" class="mb-3">
        <div class="form-group">
            <label for="date">Select Date:</label>
            <input type="date" name="date" id="date" class="form-control" value="<?php echo e(request('date', now()->format('Y-m-d'))); ?>">
        </div>
        <button type="submit" class="btn btn-primary">Filter</button>
    </form>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Item</th>
                <th>Category</th>
                <th>Quantity</th>
                <th>Unit</th>
                <th>Cost</th>
                <th>Expiry</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php $__empty_1 = true; $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <tr>
                <td><?php echo e($item->name); ?></td>
                <td><?php echo e($item->category); ?></td>
                <td><?php echo e($item->quantity); ?></td>
                <td><?php echo e($item->unit); ?></td>
                <td><?php echo e($item->cost); ?></td>
                <td><?php echo e($item->expiry); ?></td>
                <td>
                    <a href="<?php echo e(route('admin.inventory.edit', $item->id)); ?>" class="btn btn-sm btn-warning">Edit</a>
                </td>
            </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <tr>
                <td colspan="7" class="text-center">No stock items found.</td>
            </tr>
            <?php endif; ?>
        </tbody>
    </table>
    <a href="<?php echo e(route('admin.inventory.add')); ?>" class="btn btn-success">Add New Item</a>
    <a href="<?php echo e(route('admin.inventory.dashboard')); ?>" class="btn btn-primary">Back to Dashboard</a>
</div>
<?php $__env->stopSection(); ?> 
<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\sabst\momo_shop\resources\views/admin/inventory/count.blade.php ENDPATH**/ ?>