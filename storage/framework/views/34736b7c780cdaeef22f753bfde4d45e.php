<?php $__env->startSection('content'); ?>
<div class="container">
    <h1>Creators Hub</h1>
    <ul class="nav nav-tabs mb-4" id="creatorTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="creators-tab" data-bs-toggle="tab" data-bs-target="#creators" type="button" role="tab" aria-controls="creators" aria-selected="true">Creators</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="leaderboard-tab" data-bs-toggle="tab" data-bs-target="#leaderboard" type="button" role="tab" aria-controls="leaderboard" aria-selected="false">Leaderboard</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="dashboard-tab" data-bs-toggle="tab" data-bs-target="#dashboard" type="button" role="tab" aria-controls="dashboard" aria-selected="false">Creator Dashboard</button>
        </li>
    </ul>
    <div class="tab-content" id="creatorTabsContent">
        <div class="tab-pane fade show active" id="creators" role="tabpanel" aria-labelledby="creators-tab">
            <h2>Creators</h2>
            <div class="row">
                <?php $__currentLoopData = $creators; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $creator): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="col-md-4 mb-4">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title"><?php echo e($creator->user->name); ?></h5>
                                <p class="card-text">Code: <?php echo e($creator->code); ?></p>
                                <p class="card-text"><?php echo e($creator->bio); ?></p>
                                <a href="<?php echo e(route('creators.show', $creator->code)); ?>" class="btn btn-primary">View Profile</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>
        <div class="tab-pane fade" id="leaderboard" role="tabpanel" aria-labelledby="leaderboard-tab">
            <h2>Top Creators</h2>
            <div class="row">
                <?php $__currentLoopData = $topCreators; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $creator): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="col-md-4 mb-4">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title"><?php echo e($creator->user->name); ?></h5>
                                <p class="card-text">Referral Count: <?php echo e($creator->referral_count); ?></p>
                            </div>
                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>
        <div class="tab-pane fade" id="dashboard" role="tabpanel" aria-labelledby="dashboard-tab">
            <h2>Creator Dashboard</h2>
            <div class="row">
                <?php if($creator): ?>
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo e($creator->user->name); ?></h5>
                            <p class="card-text">Code: <?php echo e($creator->code); ?></p>
                            <p class="card-text"><?php echo e($creator->bio); ?></p>
                            <?php if($creator->avatar): ?>
                                <img src="<?php echo e(asset('storage/' . $creator->avatar)); ?>" alt="<?php echo e($creator->user->name); ?>" class="img-fluid mb-3">
                            <?php endif; ?>
                            <hr>
                            <p class="mb-1"><strong>Total Referrals:</strong> <?php echo e($creator->referral_count); ?></p>
                            <p class="mb-1"><strong>Total Earnings:</strong> $<?php echo e(number_format($creator->earnings, 2)); ?></p>
                            <p class="mb-1"><strong>Points:</strong> <?php echo e($creator->points); ?></p>
                            <p class="mb-1"><strong>Badge:</strong> <span class="badge bg-<?php echo e(strtolower($creator->badge ?? 'participant')); ?>"><?php echo e($creator->badge ?? 'Participant'); ?></span></p>
                        </div>
                    </div>
                </div>
                <div class="col-md-8">
                    <h3>Referral Statistics</h3>
                    <p>Total Referrals: <?php echo e($referrals->count()); ?></p>
                    <p>Pending Referrals: <?php echo e($referrals->where('status', 'pending')->count()); ?></p>
                    <p>Used Referrals: <?php echo e($referrals->where('status', 'used')->count()); ?></p>
                    <p>Expired Referrals: <?php echo e($referrals->where('status', 'expired')->count()); ?></p>

                    <button id="generate-referral" class="btn btn-primary mb-3">Generate Referral Coupon</button>
                    <div id="coupon-code" class="alert alert-success d-none"></div>

                    <h3>My Referrals</h3>
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
                <?php else: ?>
                <div class="col-12">
                    <div class="alert alert-warning">You are not registered as a creator. Please create a creator profile to access the dashboard features.</div>
                    <button class="btn btn-primary mb-3" disabled>Generate Referral Coupon</button>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
<script>
    var generateBtn = document.getElementById('generate-referral');
    if (generateBtn) {
        generateBtn.addEventListener('click', function() {
            fetch('<?php echo e(route('creator-dashboard.generate-referral')); ?>', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>',
                    'Accept': 'application/json',
                },
            })
            .then(response => {
                if (!response.ok) return response.json().then(err => { throw err; });
                return response.json();
            })
            .then(data => {
                document.getElementById('coupon-code').textContent = 'Coupon Code: ' + data.coupon_code;
                document.getElementById('coupon-code').classList.remove('d-none');
                document.getElementById('coupon-code').classList.remove('alert-danger');
                document.getElementById('coupon-code').classList.add('alert-success');
            })
            .catch(error => {
                let msg = error.message || (error.error || 'You are not registered as a creator. Please create a creator profile.');
                document.getElementById('coupon-code').textContent = msg;
                document.getElementById('coupon-code').classList.remove('d-none');
                document.getElementById('coupon-code').classList.remove('alert-success');
                document.getElementById('coupon-code').classList.add('alert-danger');
            });
        });
    }
</script>
<?php $__env->stopSection(); ?> 
<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\sabst\momo_shop\resources\views/creators/index.blade.php ENDPATH**/ ?>