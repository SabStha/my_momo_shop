<?php $__env->startSection('content'); ?>
<div class="container">
    <div class="thank-you">
        <h1>Thank You for Your Order!</h1>
        <p>Your order has been successfully placed.</p>
        <p>Order Number: #<?php echo e($order->id); ?></p>
        <p>We'll send you an email confirmation shortly.</p>
        <a href="<?php echo e(route('home')); ?>" class="btn">Continue Shopping</a>
    </div>
</div>
<?php $__env->stopSection(); ?> 
<?php echo $__env->make('desktop.layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\sabst\momo_shop\resources\views/desktop/thankyou.blade.php ENDPATH**/ ?>