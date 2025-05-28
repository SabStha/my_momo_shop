
<?php $__env->startSection('content'); ?>
<div class="container">
    <h1>Creator System Test Panel</h1>

    <!-- Referral Test Section -->
    <div class="card mb-4">
        <div class="card-header">
            <h3>Referral Testing</h3>
        </div>
        <div class="card-body">
            <p><strong>Current Referral Code:</strong> <?php echo e(session('referral_code') ?? 'None'); ?></p>
            <div class="mb-3">
                <label class="form-label">Test Referral Links:</label>
                <div class="d-flex gap-2">
                    <a href="<?php echo e(url('/?ref=creator123')); ?>" class="btn btn-primary">Test Valid Creator</a>
                    <a href="<?php echo e(url('/?ref=invalid123')); ?>" class="btn btn-warning">Test Invalid Creator</a>
                    <a href="<?php echo e(url('/')); ?>" class="btn btn-secondary">Clear Referral</a>
                </div>
            </div>
            <?php if(session('referral_error')): ?>
                <div class="alert alert-danger">
                    <?php echo e(session('referral_error')); ?>

                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Coupon Test Section -->
    <div class="card mb-4">
        <div class="card-header">
            <h3>Coupon Testing</h3>
        </div>
        <div class="card-body">
            <?php if(session('coupon_error')): ?>
                <div class="alert alert-danger">
                    <?php echo e(session('coupon_error')); ?>

                </div>
            <?php endif; ?>
            <?php if(session('coupon_success')): ?>
                <div class="alert alert-success">
                    <?php echo e(session('coupon_success')); ?>

                </div>
            <?php endif; ?>
            <form action="<?php echo e(route('coupon.apply')); ?>" method="POST">
                <?php echo csrf_field(); ?>
                <div class="mb-3">
                    <label for="code" class="form-label">Coupon Code</label>
                    <input type="text" class="form-control <?php $__errorArgs = ['code'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                           id="code" name="code" value="<?php echo e(old('code')); ?>" required>
                    <?php $__errorArgs = ['code'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                        <div class="invalid-feedback"><?php echo e($message); ?></div>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>
                <div class="mb-3">
                    <label for="price" class="form-label">Price</label>
                    <input type="number" class="form-control <?php $__errorArgs = ['price'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                           id="price" name="price" value="<?php echo e(old('price', 100)); ?>" step="0.01" required>
                    <?php $__errorArgs = ['price'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                        <div class="invalid-feedback"><?php echo e($message); ?></div>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>
                <div class="mb-3">
                    <label for="referral_code" class="form-label">Referral Code (Optional)</label>
                    <input type="text" class="form-control" 
                           id="referral_code" name="referral_code" 
                           value="<?php echo e(session('referral_code') ?? old('referral_code')); ?>">
                </div>
                <button type="submit" class="btn btn-primary">Test Coupon</button>
            </form>
        </div>
    </div>

    <!-- Monthly Rewards Section -->
    <div class="card mb-4">
        <div class="card-header">
            <h3>Monthly Rewards</h3>
        </div>
        <div class="card-body">
            <a href="<?php echo e(route('creator.rewards.index')); ?>" class="btn btn-primary mb-3">View Creator Rewards</a>
            <form action="<?php echo e(route('test.assign-monthly-rewards')); ?>" method="POST">
                <?php echo csrf_field(); ?>
                <button type="submit" class="btn btn-success">Trigger Monthly Rewards Assignment</button>
            </form>
        </div>
    </div>

    <!-- Payout System Section -->
    <div class="card mb-4">
        <div class="card-header">
            <h3>Payout System</h3>
        </div>
        <div class="card-body">
            <a href="<?php echo e(route('creator.payouts.index')); ?>" class="btn btn-primary mb-3">View Creator Payouts</a>
            <a href="<?php echo e(route('admin.payouts.index')); ?>" class="btn btn-secondary">View Admin Payout Requests</a>
        </div>
    </div>

    <!-- Creator Registration Button -->
    <div class="card mb-4">
        <div class="card-header">
            <h3>Creator Registration</h3>
        </div>
        <div class="card-body">
            <a href="<?php echo e(route('creators.create')); ?>" class="btn btn-info">Register as a Creator</a>
        </div>
    </div>

    <!-- Session Messages -->
    <?php if(session('success')): ?>
        <div class="alert alert-success">
            <?php echo e(session('success')); ?>

        </div>
    <?php endif; ?>
    <?php if(session('error')): ?>
        <div class="alert alert-danger">
            <?php echo e(session('error')); ?>

        </div>
    <?php endif; ?>
</div>
<?php $__env->stopSection(); ?> 
<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\sabst\momo_shop\resources\views/test/devpanel.blade.php ENDPATH**/ ?>