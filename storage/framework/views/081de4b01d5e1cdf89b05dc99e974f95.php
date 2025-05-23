<?php $__env->startSection('content'); ?>
<div class="container py-5 text-center">
    <h1 class="mb-4">Thank You for Your Order!</h1>
    <?php if(session('success')): ?>
        <div class="alert alert-success"><?php echo e(session('success')); ?></div>
    <?php endif; ?>
    <div class="card mx-auto" style="max-width: 400px;">
        <div class="card-body">
            <h4 class="card-title">Order #<?php echo e($order->id); ?></h4>
            <p class="card-text">Total: <strong>$<?php echo e(number_format($order->total_amount, 2)); ?></strong></p>
            <p class="card-text">We have received your order and will process it soon.</p>
            <a href="<?php echo e(route('home')); ?>" class="btn btn-primary mt-3">Back to Home</a>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?> 
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\sabst\momo_shop\resources\views/cart/confirmation.blade.php ENDPATH**/ ?>