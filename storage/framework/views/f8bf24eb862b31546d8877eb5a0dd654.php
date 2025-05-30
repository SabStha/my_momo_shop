

<?php $__env->startSection('content'); ?>
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Manage Payouts</h1>
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

    <!-- Payouts Table -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">All Payouts</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Creator</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Requested At</th>
                            <th>Processed At</th>
                            <th>Payment Method</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__currentLoopData = $payouts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $payout): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr>
                            <td><?php echo e($payout->id); ?></td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <?php if($payout->creator->avatar): ?>
                                        <img src="<?php echo e(asset('storage/' . $payout->creator->avatar)); ?>" 
                                             alt="<?php echo e($payout->creator->user->name); ?>" 
                                             class="rounded-circle me-2"
                                             style="width: 32px; height: 32px; object-fit: cover;">
                                    <?php endif; ?>
                                    <?php echo e($payout->creator->user->name); ?>

                                </div>
                            </td>
                            <td>$<?php echo e(number_format($payout->amount, 2)); ?></td>
                            <td>
                                <span class="badge bg-<?php echo e($payout->status === 'pending' ? 'warning' : ($payout->status === 'approved' ? 'success' : ($payout->status === 'rejected' ? 'danger' : 'info'))); ?>">
                                    <?php echo e(ucfirst($payout->status)); ?>

                                </span>
                            </td>
                            <td><?php echo e($payout->requested_at->format('Y-m-d H:i')); ?></td>
                            <td><?php echo e($payout->processed_at ? $payout->processed_at->format('Y-m-d H:i') : '-'); ?></td>
                            <td><?php echo e($payout->payment_method ?? '-'); ?></td>
                            <td>
                                <?php if($payout->status === 'pending'): ?>
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
                                <?php elseif($payout->status === 'approved'): ?>
                                    <form action="<?php echo e(route('admin.payouts.mark-paid', $payout->id)); ?>" method="POST" class="d-inline">
                                        <?php echo csrf_field(); ?>
                                        <button type="submit" class="btn btn-sm btn-info">
                                            <i class="fas fa-money-bill"></i> Mark as Paid
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="d-flex justify-content-center mt-4">
                <?php echo e($payouts->links()); ?>

            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?> 
<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\sabst\momo_shop\resources\views/admin/payouts/index.blade.php ENDPATH**/ ?>