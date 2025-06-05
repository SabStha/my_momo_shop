

<?php $__env->startSection('content'); ?>
<div class="container py-4">
    <div class="row">
        <div class="col-12">
            <h1 class="mb-4">My Wallet</h1>
            
            <div class="card mb-4">
                <div class="card-body">
                    <h5 class="card-title">Current Balance</h5>
                    <h3 class="text-success mb-3">Rs. <?php echo e(number_format($wallet ? $wallet->balance : 0, 2)); ?></h3>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <a href="<?php echo e(route('wallet.scan')); ?>" class="btn btn-outline-success w-100">Top Up via QR</a>
                        </div>
                        <div class="col-md-6">
                            <a href="<?php echo e(route('wallet.transactions')); ?>" class="btn btn-outline-primary w-100">View All Transactions</a>
                        </div>
                    </div>
                </div>
            </div>

            <?php if($transactions->count() > 0): ?>
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Recent Transactions</h5>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Type</th>
                                    <th>Amount</th>
                                    <th>Description</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__currentLoopData = $transactions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $transaction): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr>
                                    <td><?php echo e($transaction->created_at->format('M d, Y H:i')); ?></td>
                                    <td>
                                        <span class="badge bg-<?php echo e($transaction->type === 'credit' ? 'success' : 'danger'); ?>">
                                            <?php echo e(ucfirst($transaction->type)); ?>

                                        </span>
                                    </td>
                                    <td class="<?php echo e($transaction->type === 'credit' ? 'text-success' : 'text-danger'); ?>">
                                        <?php echo e($transaction->type === 'credit' ? '+' : '-'); ?>Rs. <?php echo e(number_format($transaction->amount, 2)); ?>

                                    </td>
                                    <td><?php echo e($transaction->description); ?></td>
                                </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?> 
<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\sabst\momo_shop\resources\views/wallet/index.blade.php ENDPATH**/ ?>