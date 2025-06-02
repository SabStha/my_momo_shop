<?php $__env->startSection('content'); ?>
<div class="container py-4">
    <div class="row">
        <!-- Profile Section -->
        <div class="col-md-4 mb-4">
            <div class="card">
                <div class="card-body text-center">
                    <div class="mb-3">
                        <?php if(auth()->user()->creator && auth()->user()->creator->avatar): ?>
                            <img src="<?php echo e(Storage::url(auth()->user()->creator->avatar)); ?>" 
                                 alt="Profile Picture" 
                                 class="rounded-circle img-thumbnail"
                                 style="width: 150px; height: 150px; object-fit: cover;">
                        <?php else: ?>
                            <img src="https://ui-avatars.com/api/?name=<?php echo e(urlencode(auth()->user()->name)); ?>&size=150" 
                                 alt="Profile Picture" 
                                 class="rounded-circle img-thumbnail">
                        <?php endif; ?>
                    </div>
                    <h4><?php echo e(auth()->user()->name); ?></h4>
                    <p class="text-muted">Creator</p>
                    <?php if(isset($wallet)): ?>
                        <div class="my-3">
                            <span class="fw-bold">Wallet Balance:</span>
                            <span class="text-success">Rs. <?php echo e(number_format($wallet->balance, 2)); ?></span>
                        </div>
                    <?php endif; ?>
                    <form action="<?php echo e(route('creator-dashboard.update-profile-photo')); ?>" 
                          method="POST" 
                          enctype="multipart/form-data"
                          class="mt-3">
                        <?php echo csrf_field(); ?>
                        <div class="mb-3">
                            <input type="file" 
                                   name="avatar" 
                                   class="form-control" 
                                   accept="image/*">
                        </div>
                        <button type="submit" class="btn btn-primary">Update Photo</button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Stats and Referral Section -->
        <div class="col-md-8">
            <!-- Stats Cards -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card bg-primary text-white">
                        <div class="card-body">
                            <h5 class="card-title">Total Referrals</h5>
                            <h2 class="mb-0"><?php echo e($stats['total_referrals']); ?></h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-success text-white">
                        <div class="card-body">
                            <h5 class="card-title">Completed Orders</h5>
                            <h2 class="mb-0"><?php echo e($stats['ordered_referrals']); ?></h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-info text-white">
                        <div class="card-body">
                            <h5 class="card-title">Referral Points</h5>
                            <h2 class="mb-0"><?php echo e($stats['referral_points']); ?></h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-warning text-white">
                        <div class="card-body">
                            <h5 class="card-title">Wallet Balance</h5>
                            <h2 class="mb-0">Rs. <?php echo e(isset($wallet) ? number_format($wallet->balance, 2) : '0.00'); ?></h2>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Referral Code Section -->
            <div class="card mb-4">
                <div class="card-body">
                    <h5 class="card-title">Share this link with your friends:</h5>
                    <div class="input-group mb-3">
                        <input type="text" class="form-control" id="referral-link" value="<?php echo e(url('/register?ref=' . Auth::user()->creator->code)); ?>" readonly>
                        <button class="btn btn-outline-secondary" type="button" onclick="copyReferralLink()">Copy</button>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <h6>You Earn:</h6>
                            <ul class="mb-0">
                                <li>✓ 10 points when they sign up</li>
                                <li>✓ 5 points on their first order</li>
                                <li>✓ 5 points for each of their next 9 orders</li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <h6>They Earn:</h6>
                            <ul class="mb-0">
                                <li>✓ Rs 50 discount for signing up</li>
                                <li>✓ Rs 30 discount on their first order</li>
                                <li>✓ Rs 10 discount for each of their next 9 orders</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Leaderboard Section -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Top Creators Leaderboard</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Rank</th>
                                    <th>Creator</th>
                                    <th>Points</th>
                                    <th>Referrals</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__currentLoopData = $topCreators; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $creator): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <tr class="<?php echo e($creator->id === auth()->user()->creator->id ? 'table-primary' : ''); ?>">
                                        <td><?php echo e($index + 1); ?></td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <?php if($creator->avatar): ?>
                                                    <img src="<?php echo e(Storage::url($creator->avatar)); ?>" 
                                                         alt="<?php echo e($creator->user->name); ?>" 
                                                         class="rounded-circle me-2"
                                                         style="width: 40px; height: 40px; object-fit: cover;">
                                                <?php else: ?>
                                                    <img src="https://ui-avatars.com/api/?name=<?php echo e(urlencode($creator->user->name)); ?>&size=40" 
                                                         alt="<?php echo e($creator->user->name); ?>" 
                                                         class="rounded-circle me-2">
                                                <?php endif; ?>
                                                <?php echo e($creator->user->name); ?>

                                            </div>
                                        </td>
                                        <td><?php echo e($creator->points); ?></td>
                                        <td><?php echo e($creator->referral_count); ?></td>
                                        <td>
                                            <?php if($creator->isTrending()): ?>
                                                <span class="badge bg-success">Trending</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary">Stable</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Your Referrals List -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Your Referrals</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>User</th>
                                    <th>Status</th>
                                    <th>Orders</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__empty_1 = true; $__currentLoopData = $referrals; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $referral): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                    <tr>
                                        <td><?php echo e($referral->referredUser ? (count(explode(' ', $referral->referredUser->name)) > 1 ? explode(' ', $referral->referredUser->name)[1] : $referral->referredUser->name) : 'N/A'); ?></td>
                                        <td>
                                            <span class="badge bg-<?php echo e($referral->status === 'ordered' ? 'success' : 'warning'); ?>">
                                                <?php echo e(ucfirst($referral->status)); ?>

                                            </span>
                                        </td>
                                        <td><?php echo e($referral->order_count ?? 0); ?></td>
                                        <td><?php echo e($referral->created_at->format('M d, Y')); ?></td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                    <tr>
                                        <td colspan="4" class="text-center">No referrals yet</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php $__env->startPush('scripts'); ?>
<script>
function copyReferralLink() {
    const input = document.getElementById('referral-link');
    input.select();
    document.execCommand('copy');
    alert('Referral link copied to clipboard!');
}
</script>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?> 
<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\sabst\momo_shop\resources\views/creator-dashboard/index.blade.php ENDPATH**/ ?>