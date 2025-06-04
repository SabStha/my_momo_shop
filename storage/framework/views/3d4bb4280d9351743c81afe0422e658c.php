

<?php $__env->startSection('content'); ?>
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card shadow-sm">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Transaction History</h5>
                    <a href="<?php echo e(route('wallet')); ?>" class="btn btn-outline-primary btn-sm">Back to Wallet</a>
                </div>
                <div class="card-body">
                    <?php if($transactions->count() > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
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
                                                <span class="badge <?php echo e($transaction->type === 'credit' ? 'bg-success' : 'bg-danger'); ?>">
                                                    <?php echo e(ucfirst($transaction->type)); ?>

                                                </span>
                                            </td>
                                            <td><?php echo e(number_format($transaction->amount, 2)); ?></td>
                                            <td><?php echo e($transaction->description); ?></td>
                                        </tr>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="d-flex justify-content-center mt-4">
                            <?php echo e($transactions->links()); ?>

                        </div>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <p class="text-muted mb-0">No transactions found.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.card {
    border: none;
    border-radius: 10px;
}
.card-header {
    border-bottom: 1px solid rgba(0,0,0,.125);
    padding: 1rem;
}
.table th {
    border-top: none;
    font-weight: 600;
}
.badge {
    padding: 0.5em 0.75em;
}
</style>
<?php $__env->stopSection(); ?> 
<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\sabst\momo_shop\resources\views/wallet/transactions.blade.php ENDPATH**/ ?>