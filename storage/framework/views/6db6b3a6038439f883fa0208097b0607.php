

<?php $__env->startSection('content'); ?>
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h2>Order Confirmed</h2>
                </div>

                <div class="card-body">
                    <div class="alert alert-success">
                        <h4 class="alert-heading">Thank you for your order!</h4>
                        <p>Your order has been successfully placed.</p>
                    </div>

                    <div class="mb-4">
                        <h4>Order Details</h4>
                        <p><strong>Order ID:</strong> #<?php echo e($order->id); ?></p>
                        <p><strong>Total Amount:</strong> Rs. <?php echo e(number_format($order->total_amount, 2)); ?></p>
                        <p><strong>Status:</strong> <?php echo e(ucfirst($order->status)); ?></p>
                    </div>

                    <div class="text-center">
                        <a href="<?php echo e(route('home')); ?>" class="btn btn-primary">Continue Shopping</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?> 
<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\sabst\momo_shop\resources\views/checkout/complete.blade.php ENDPATH**/ ?>