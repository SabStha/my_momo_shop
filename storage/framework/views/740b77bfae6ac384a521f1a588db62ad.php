<?php $__env->startSection('content'); ?>
<div class="container py-4">
    <div class="row">
        <div class="col-12">
            <h1 class="mb-4">My Account</h1>
            
            <div class="card mb-4">
                <div class="card-body">
                    <h5 class="card-title">Profile Information</h5>
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Name:</strong> <?php echo e($user->name); ?></p>
                            <p><strong>Email:</strong> <?php echo e($user->email); ?></p>
                        </div>
                    </div>
                    <a href="<?php echo e(route('profile.edit')); ?>" class="btn btn-primary">Edit Profile</a>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-body">
                    <h5 class="card-title">Recent Orders</h5>
                    <p class="text-muted">View your recent orders and track their status.</p>
                    <a href="<?php echo e(route('orders')); ?>" class="btn btn-outline-primary">View Orders</a>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Wallet</h5>
                    <p class="text-muted">Manage your wallet balance and transactions.</p>
                    <a href="<?php echo e(route('wallet')); ?>" class="btn btn-outline-primary">View Wallet</a>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\sabst\momo_shop\resources\views/user/my-account.blade.php ENDPATH**/ ?>