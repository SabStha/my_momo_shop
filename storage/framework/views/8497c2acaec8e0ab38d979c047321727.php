<?php $__env->startSection('content'); ?>
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Manage Creators</h1>
    </div>

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

    <!-- Quick Stats -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h5 class="card-title">Total Creators</h5>
                    <h2 class="mb-0"><?php echo e($creators->count()); ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h5 class="card-title">Active Creators</h5>
                    <h2 class="mb-0"><?php echo e($creators->where('user.is_active', true)->count()); ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <h5 class="card-title">Total Referrals</h5>
                    <h2 class="mb-0"><?php echo e($creators->sum('referral_count')); ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <h5 class="card-title">Total Earnings</h5>
                    <h2 class="mb-0">$<?php echo e(number_format($creators->sum('earnings'), 2)); ?></h2>
                </div>
            </div>
        </div>
    </div>

    <!-- Creators Table -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">All Creators</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Referral Code</th>
                            <th>Referrals</th>
                            <th>Points</th>
                            <th>Earnings</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__currentLoopData = $creators; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $creator): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr>
                            <td><?php echo e($creator->id); ?></td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <?php if($creator->avatar): ?>
                                        <img src="<?php echo e(asset('storage/' . $creator->avatar)); ?>" 
                                             alt="<?php echo e($creator->user->name); ?>" 
                                             class="rounded-circle me-2"
                                             style="width: 32px; height: 32px; object-fit: cover;">
                                    <?php endif; ?>
                                    <?php echo e($creator->user->name); ?>

                                </div>
                            </td>
                            <td>
                                <span class="badge bg-primary"><?php echo e($creator->code); ?></span>
                            </td>
                            <td><?php echo e($creator->referral_count); ?></td>
                            <td><?php echo e($creator->points); ?></td>
                            <td>$<?php echo e(number_format($creator->earnings, 2)); ?></td>
                            <td>
                                <?php if($creator->referral_count > 0 || $creator->points >= 50): ?>
                                    <span class="badge bg-success">Active</span>
                                <?php else: ?>
                                    <span class="badge bg-danger">Inactive</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="btn-group">
                                    <a href="<?php echo e(route('creators.show', $creator->code)); ?>" 
                                       class="btn btn-sm btn-info" title="View Profile">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="<?php echo e(route('admin.payouts.index')); ?>" 
                                       class="btn btn-sm btn-warning" title="Manage Payouts">
                                        <i class="fas fa-money-bill"></i>
                                    </a>
                                    <a href="<?php echo e(route('creator.rewards.index')); ?>" 
                                       class="btn btn-sm btn-success" title="View Rewards">
                                        <i class="fas fa-gift"></i>
                                    </a>
                                    <button type="button" 
                                            class="btn btn-sm btn-primary" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#referralModal<?php echo e($creator->id); ?>"
                                            title="View Referrals">
                                        <i class="fas fa-users"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Render all referral modals at the end of the page -->
    <?php $__currentLoopData = $creators; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $creator): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <div class="modal fade" id="referralModal<?php echo e($creator->id); ?>" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Referrals for <?php echo e($creator->user->name); ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="table-responsive">
                        <table class="table table-striped align-middle">
                            <thead class="table-dark">
                                <tr>
                                    <th>User</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                    <th>Earnings</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__currentLoopData = $creator->referrals; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $referral): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr>
                                    <td><?php echo e($referral->referredUser ? $referral->referredUser->name : 'N/A'); ?></td>
                                    <td>
                                        <span class="badge bg-<?php echo e($referral->status === 'used' ? 'success' : ($referral->status === 'pending' ? 'warning' : 'secondary')); ?>">
                                            <?php echo e(ucfirst($referral->status)); ?>

                                        </span>
                                    </td>
                                    <td><?php echo e($referral->created_at->format('Y-m-d')); ?></td>
                                    <td>$<?php echo e(number_format($referral->earnings, 2)); ?></td>
                                </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

    <!-- Payouts Section -->
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Pending Payouts</h5>
            <a href="<?php echo e(route('admin.payouts.index')); ?>" class="btn btn-sm btn-primary">View All</a>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>Creator</th>
                            <th>Amount</th>
                            <th>Requested At</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__currentLoopData = $pendingPayouts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $payout): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr>
                            <td><?php echo e($payout->creator->user->name); ?></td>
                            <td>$<?php echo e(number_format($payout->amount, 2)); ?></td>
                            <td><?php echo e($payout->requested_at->format('Y-m-d H:i')); ?></td>
                            <td>
                                <span class="badge bg-warning">Pending</span>
                            </td>
                            <td>
                                <div class="btn-group">
                                    <form action="<?php echo e(route('admin.payouts.approve', $payout->id)); ?>" method="POST" class="d-inline">
                                        <?php echo csrf_field(); ?>
                                        <button type="submit" class="btn btn-sm btn-success">
                                            <i class="fas fa-check"></i> Approve
                                        </button>
                                    </form>
                                    <form action="<?php echo e(route('admin.payouts.reject', $payout->id)); ?>" method="POST" class="d-inline">
                                        <?php echo csrf_field(); ?>
                                        <button type="submit" class="btn btn-sm btn-danger">
                                            <i class="fas fa-times"></i> Reject
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Rewards Section -->
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Monthly Rewards</h5>
            <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#assignRewardsModal">
                Assign Rewards
            </button>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>Creator</th>
                            <th>Month</th>
                            <th>Reward Type</th>
                            <th>Amount</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__currentLoopData = $rewards; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $reward): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr>
                            <td><?php echo e($reward->creator->user->name); ?></td>
                            <td><?php echo e($reward->month->format('F Y')); ?></td>
                            <td><?php echo e(ucfirst($reward->type)); ?></td>
                            <td>$<?php echo e(number_format($reward->amount, 2)); ?></td>
                            <td>
                                <span class="badge bg-<?php echo e($reward->claimed ? 'success' : 'warning'); ?>">
                                    <?php echo e($reward->claimed ? 'Claimed' : 'Pending'); ?>

                                </span>
                            </td>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Leadership Section -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Leadership Board</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>Rank</th>
                            <th>Name</th>
                            <th>Referrals</th>
                            <th>Points</th>
                            <th>Earnings</th>
                            <th>Trend</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__currentLoopData = $topCreators; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $creator): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr>
                            <td>
                                <?php if($i === 0): ?>
                                    <i class="fas fa-crown text-warning"></i>
                                <?php elseif($i === 1): ?>
                                    <i class="fas fa-medal text-secondary"></i>
                                <?php elseif($i === 2): ?>
                                    <i class="fas fa-medal text-danger"></i>
                                <?php endif; ?>
                                #<?php echo e($i + 1); ?>

                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <?php if($creator->avatar): ?>
                                        <img src="<?php echo e(asset('storage/' . $creator->avatar)); ?>" 
                                             alt="<?php echo e($creator->user->name); ?>" 
                                             class="rounded-circle me-2"
                                             style="width: 32px; height: 32px; object-fit: cover;">
                                    <?php endif; ?>
                                    <?php echo e($creator->user->name); ?>

                                </div>
                            </td>
                            <td><?php echo e($creator->referral_count); ?></td>
                            <td><?php echo e($creator->points); ?></td>
                            <td>$<?php echo e(number_format($creator->earnings, 2)); ?></td>
                            <td>
                                <?php if($creator->isTrending()): ?>
                                    <span class="badge bg-success">
                                        <i class="fas fa-arrow-up"></i> Trending
                                    </span>
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

<!-- Assign Rewards Modal -->
<div class="modal fade" id="assignRewardsModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Assign Monthly Rewards</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?php echo e(route('test.assign-monthly-rewards')); ?>" method="POST">
                <?php echo csrf_field(); ?>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="month" class="form-label">Month</label>
                        <input type="month" class="form-control" id="month" name="month" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Assign Rewards</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\sabst\momo_shop\resources\views/creator/index.blade.php ENDPATH**/ ?>