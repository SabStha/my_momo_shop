
<?php $__env->startSection('content'); ?>
<div class="container">
    <h2>Payout Requests</h2>
    <?php if(session('success')): ?>
        <div class="alert alert-success"><?php echo e(session('success')); ?></div>
    <?php endif; ?>
    <?php if(session('error')): ?>
        <div class="alert alert-danger"><?php echo e(session('error')); ?></div>
    <?php endif; ?>
    <form method="POST" action="<?php echo e(route('creator.payouts.request')); ?>" class="mb-4">
        <?php echo csrf_field(); ?>
        <div class="mb-3">
            <label>Amount</label>
            <input type="number" class="form-control" name="amount" value="<?php echo e($creator->earnings); ?>" readonly>
        </div>
        <button class="btn btn-primary" <?php echo e($creator->earnings <= 0 ? 'disabled' : ''); ?>>Request Payout</button>
    </form>
    <h4>Payout History</h4>
    <table class="table">
        <thead>
            <tr>
                <th>Amount</th>
                <th>Status</th>
                <th>Requested At</th>
                <th>Processed At</th>
            </tr>
        </thead>
        <tbody>
            <?php $__currentLoopData = $payouts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $payout): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr>
                    <td>$<?php echo e(number_format($payout->amount, 2)); ?></td>
                    <td><?php echo e(ucfirst($payout->status)); ?></td>
                    <td><?php echo e($payout->requested_at); ?></td>
                    <td><?php echo e($payout->processed_at ?? '—'); ?></td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </tbody>
    </table>
</div>
<?php $__env->stopSection(); ?> 
<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\sabst\momo_shop\resources\views/creators/payouts.blade.php ENDPATH**/ ?>