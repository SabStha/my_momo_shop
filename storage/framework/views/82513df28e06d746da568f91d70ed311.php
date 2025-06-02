

<?php $__env->startSection('title', 'Wallet Management'); ?>

<?php $__env->startSection('content'); ?>
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-success text-white fw-bold">
                    💳 Top Up Wallet
                </div>
                <div class="card-body">
                    <form method="POST" action="<?php echo e(route('admin.wallet.top-up')); ?>">
                        <?php echo csrf_field(); ?>

                        
                        <input type="hidden" name="user_id" value="<?php echo e($user->id); ?>">

                        <div class="mb-3">
                            <label class="form-label">User Name</label>
                            <input type="text" class="form-control" value="<?php echo e($user->name); ?>" readonly>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">User Email</label>
                            <input type="email" class="form-control" value="<?php echo e($user->email); ?>" readonly>
                        </div>

                        <div class="mb-3">
                            <label for="amount" class="form-label">Top-Up Amount</label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" 
                                       name="amount" 
                                       id="amount" 
                                       class="form-control" 
                                       step="0.01" 
                                       min="0.01" 
                                       required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description (Optional)</label>
                            <textarea name="description" id="description" rows="3" class="form-control" placeholder="e.g., Promotional bonus, Manual adjustment..."></textarea>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="<?php echo e(route('admin.wallet.index')); ?>" class="btn btn-secondary">← Back</a>
                            <button type="submit" class="btn btn-success">Top Up Wallet</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('desktop.admin.layouts.admin', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\sabst\momo_shop\resources\views/desktop/admin/wallet/index.blade.php ENDPATH**/ ?>