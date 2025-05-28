
<?php $__env->startSection('content'); ?>
<div class="container">
    <h2>Monthly Rewards</h2>
    <?php if(session('success')): ?>
        <div class="alert alert-success"><?php echo e(session('success')); ?></div>
    <?php endif; ?>
    <table class="table">
        <thead>
            <tr>
                <th>Month</th>
                <th>Badge</th>
                <th>Reward</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php $__currentLoopData = $rewards; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $reward): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr>
                    <td><?php echo e($reward->month); ?></td>
                    <td><span class="badge bg-<?php echo e($reward->badge); ?>"><?php echo e(ucfirst($reward->badge)); ?></span></td>
                    <td><?php echo e($reward->reward); ?></td>
                    <td><?php echo e($reward->claimed ? 'Claimed' : 'Unclaimed'); ?></td>
                    <td>
                        <?php if(!$reward->claimed): ?>
                            <form method="POST" action="<?php echo e(route('creator.rewards.claim', $reward->id)); ?>">
                                <?php echo csrf_field(); ?>
                                <button class="btn btn-success btn-sm">Claim</button>
                            </form>
                        <?php else: ?>
                            —
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </tbody>
    </table>
</div>
<?php $__env->stopSection(); ?> 
<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\sabst\momo_shop\resources\views/creators/rewards.blade.php ENDPATH**/ ?>