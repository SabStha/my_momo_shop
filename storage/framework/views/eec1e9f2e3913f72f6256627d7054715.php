
<?php $__env->startSection('content'); ?>
<div class="container">
    <h1>Generate My Coupon</h1>
    <?php if(isset($coupon)): ?>
        <div class="alert alert-success">
            <strong>Your Coupon:</strong> <?php echo e($coupon->code); ?><br>
            Type: <?php echo e($coupon->type); ?><br>
            Value: <?php echo e($coupon->value); ?><br>
            Status: <?php echo e($coupon->active ? 'Active' : 'Inactive'); ?>

        </div>
    <?php else: ?>
        <form method="POST" action="">
            <?php echo csrf_field(); ?>
            <button type="submit" class="btn btn-primary">Generate My Coupon</button>
        </form>
    <?php endif; ?>
</div>
<?php $__env->stopSection(); ?> 
<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\sabst\momo_shop\resources\views/creators/coupons/generate.blade.php ENDPATH**/ ?>