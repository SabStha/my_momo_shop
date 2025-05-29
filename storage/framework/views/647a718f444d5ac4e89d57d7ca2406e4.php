<?php $__env->startSection('content'); ?>
<div class="container py-4">
    <h2>Welcome, <?php echo e($user->name); ?>!</h2>
    <div class="row mt-4">
        <div class="col-md-6">
            <div class="card mb-3">
                <div class="card-body">
                    <h5 class="card-title">Order History</h5>
                    <p class="card-text">View your past orders and rate products you've received.</p>
                    <a href="<?php echo e(route('dashboard.orders')); ?>" class="btn btn-primary">View Orders</a>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card mb-3">
                <div class="card-body">
                    <h5 class="card-title">Profile</h5>
                    <p class="card-text">Update your account information and password.</p>
                    <a href="#" class="btn btn-secondary disabled">Coming Soon</a>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?> 
<?php echo $__env->make('desktop.layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\sabst\momo_shop\resources\views/desktop/user/dashboard.blade.php ENDPATH**/ ?>