<?php $__env->startSection('content'); ?>
<div class="container">
    <h1>Creator Management</h1>
    <ul class="nav nav-tabs mb-4" id="creatorTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="creators-tab" data-bs-toggle="tab" data-bs-target="#creators" type="button" role="tab" aria-controls="creators" aria-selected="true">Creators</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="leaderboard-tab" data-bs-toggle="tab" data-bs-target="#leaderboard" type="button" role="tab" aria-controls="leaderboard" aria-selected="false">Leaderboard</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="rewards-tab" data-bs-toggle="tab" data-bs-target="#rewards" type="button" role="tab" aria-controls="rewards" aria-selected="false">Creator Rewards</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="payouts-tab" data-bs-toggle="tab" data-bs-target="#payouts" type="button" role="tab" aria-controls="payouts" aria-selected="false">Creator Payouts</button>
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
            <div class="table-responsive">
                <table class="table table-striped align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th scope="col">Rank</th>
                            <th scope="col">Creator</th>
                            <th scope="col">Points</th>
                            <th scope="col">User Count</th>
                            <th scope="col">Discount Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__currentLoopData = $topCreators; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $creator): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr>
                            <td><?php echo e($i + 1); ?></td>
                            <td><?php echo e($creator->user->name); ?></td>
                            <td><?php echo e($creator->points ?? 0); ?></td>
                            <td><?php echo e($creator->referral_count ?? 0); ?></td>
                            <td>$<?php echo e(number_format($creator->discount_amount ?? 0, 2)); ?></td>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="tab-pane fade" id="rewards" role="tabpanel" aria-labelledby="rewards-tab">
            <iframe src="<?php echo e(route('creator.rewards.index')); ?>" style="width:100%;min-height:600px;border:0;"></iframe>
        </div>
        <div class="tab-pane fade" id="payouts" role="tabpanel" aria-labelledby="payouts-tab">
            <iframe src="<?php echo e(route('creator.payouts.index')); ?>" style="width:100%;min-height:600px;border:0;"></iframe>
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