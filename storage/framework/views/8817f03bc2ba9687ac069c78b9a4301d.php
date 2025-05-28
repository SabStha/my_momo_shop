
<?php $__env->startSection('content'); ?>
<div class="container">
    <h1>Create Coupon (Admin)</h1>
    <?php if(session('success')): ?>
        <div class="alert alert-success"><?php echo e(session('success')); ?></div>
    <?php endif; ?>
    <form method="POST" action="<?php echo e(route('admin.coupons.store')); ?>">
        <?php echo csrf_field(); ?>
        <div class="mb-3">
            <label for="code" class="form-label">Code</label>
            <input type="text" class="form-control" id="code" name="code" required>
        </div>
        <div class="mb-3">
            <label for="type" class="form-label">Type</label>
            <select class="form-control" id="type" name="type" required>
                <option value="fixed">Fixed</option>
                <option value="percent">Percent</option>
            </select>
        </div>
        <div class="mb-3">
            <label for="value" class="form-label">Value</label>
            <input type="number" class="form-control" id="value" name="value" required step="0.01">
        </div>
        <div class="mb-3">
            <label for="active" class="form-label">Active</label>
            <select class="form-control" id="active" name="active" required>
                <option value="1">Yes</option>
                <option value="0">No</option>
            </select>
        </div>
        <div class="mb-3">
            <label for="creator_id" class="form-label">Creator (optional)</label>
            <select class="form-control" id="creator_id" name="creator_id">
                <option value="">None</option>
                <?php $__currentLoopData = $creators; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $creator): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($creator->id); ?>"><?php echo e($creator->user->name); ?> (<?php echo e($creator->code); ?>)</option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
        </div>
        <div class="mb-3">
            <label for="campaign_name" class="form-label">Campaign Name</label>
            <input type="text" class="form-control" id="campaign_name" name="campaign_name">
        </div>
        <div class="mb-3">
            <label for="usage_limit" class="form-label">Usage Limit</label>
            <input type="number" class="form-control" id="usage_limit" name="usage_limit">
        </div>
        <div class="mb-3">
            <label for="expires_at" class="form-label">Expires At</label>
            <input type="date" class="form-control" id="expires_at" name="expires_at">
        </div>
        <button type="submit" class="btn btn-primary">Create Coupon</button>
    </form>
</div>
<?php $__env->stopSection(); ?> 
<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\sabst\momo_shop\resources\views/admin/coupons/create.blade.php ENDPATH**/ ?>