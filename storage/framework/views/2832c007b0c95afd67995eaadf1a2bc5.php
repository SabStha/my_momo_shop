
<?php $__env->startSection('title', 'Add Inventory Item'); ?>
<?php $__env->startSection('content'); ?>
<div class="container py-3">
    <h2>Add Inventory Item</h2>
    <form action="<?php echo e(route('admin.inventory.store')); ?>" method="POST">
        <?php echo csrf_field(); ?>
        <div class="form-group">
            <label for="name">Item Name:</label>
            <input type="text" name="name" id="name" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="category">Category:</label>
            <input type="text" name="category" id="category" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="quantity">Quantity:</label>
            <input type="number" name="quantity" id="quantity" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="unit">Unit:</label>
            <input type="text" name="unit" id="unit" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="cost">Cost:</label>
            <input type="number" name="cost" id="cost" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="expiry">Expiry Date:</label>
            <input type="date" name="expiry" id="expiry" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-success">Add Item</button>
    </form>
</div>
<?php $__env->stopSection(); ?> 
<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\sabst\momo_shop\resources\views/admin/inventory/add.blade.php ENDPATH**/ ?>