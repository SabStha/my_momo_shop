

<?php $__env->startSection('content'); ?>
<div class="container">
    <h1>Creator Dashboard</h1>
    <div class="row">
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title"><?php echo e($creator->user->name); ?></h5>
                    <p class="card-text">Code: <?php echo e($creator->code); ?></p>
                    <p class="card-text"><?php echo e($creator->bio); ?></p>
                    <?php if($creator->avatar): ?>
                        <img src="<?php echo e(asset('storage/' . $creator->avatar)); ?>" alt="<?php echo e($creator->user->name); ?>" class="img-fluid mb-3">
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="col-md-8">
            <h2>Referral Statistics</h2>
            <p>Total Referrals: <?php echo e($referrals->count()); ?></p>
            <p>Pending Referrals: <?php echo e($referrals->where('status', 'pending')->count()); ?></p>
            <p>Used Referrals: <?php echo e($referrals->where('status', 'used')->count()); ?></p>
            <p>Expired Referrals: <?php echo e($referrals->where('status', 'expired')->count()); ?></p>

            <button id="generate-referral" class="btn btn-primary mb-3">Generate Referral Coupon</button>
            <div id="coupon-code" class="alert alert-success d-none"></div>

            <h2>My Referrals</h2>
            <div class="row">
                <?php $__currentLoopData = $referrals; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $referral): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="col-md-4 mb-4">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">Coupon Code: <?php echo e($referral->coupon_code); ?></h5>
                                <p class="card-text">Status: <?php echo e($referral->status); ?></p>
                                <?php if($referral->referredUser): ?>
                                    <p class="card-text">Referred User: <?php echo e($referral->referredUser->name); ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
<script>
    document.getElementById('generate-referral').addEventListener('click', function() {
        fetch('<?php echo e(route('creator-dashboard.generate-referral')); ?>', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>',
                'Accept': 'application/json',
            },
        })
        .then(response => response.json())
        .then(data => {
            document.getElementById('coupon-code').textContent = 'Coupon Code: ' + data.coupon_code;
            document.getElementById('coupon-code').classList.remove('d-none');
        });
    });
</script>
<?php $__env->stopSection(); ?> 
<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\sabst\momo_shop\resources\views/creator-dashboard/index.blade.php ENDPATH**/ ?>