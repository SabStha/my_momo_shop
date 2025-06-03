<?php $__env->startSection('content'); ?>
<div class="container">
    <h2>My Account</h2>
    <?php if(session('success')): ?>
        <div class="alert alert-success"><?php echo e(session('success')); ?></div>
    <?php endif; ?>
    <?php if(session('error')): ?>
        <div class="alert alert-danger"><?php echo e(session('error')); ?></div>
    <?php endif; ?>
    <form method="POST" action="<?php echo e(route('my-account.update')); ?>">
        <?php echo csrf_field(); ?>
        <div class="mb-3">
            <label>Name</label>
            <input name="name" class="form-control" value="<?php echo e(old('name', $user->name)); ?>">
        </div>
        <div class="mb-3">
            <label>Email</label>
            <input name="email" class="form-control" value="<?php echo e(old('email', $user->email)); ?>">
        </div>
        <button class="btn btn-primary">Update</button>
    </form>
    <hr>
    <p>Account created: <?php echo e($user->created_at->format('M d, Y')); ?></p>
    <a href="<?php echo e(route('logout')); ?>" class="btn btn-danger"
       onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
        Logout
    </a>
    <form id="logout-form" action="<?php echo e(route('logout')); ?>" method="POST" style="display: none;">
        <?php echo csrf_field(); ?>
    </form>

    <hr>
    <h4>Wallet</h4>
    <p><b>Balance:</b> Rs. <?php echo e($wallet ? number_format($wallet->balance, 2) : '0.00'); ?></p>
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Top Up Wallet</h5>
                    <p class="card-text">Scan a QR code to add funds to your wallet.</p>
                    <a href="<?php echo e(route('wallet.scan')); ?>" class="btn btn-primary">
                        <i class="fas fa-qrcode"></i> Scan QR Code
                    </a>
                </div>
            </div>
        </div>
    </div>
    <h5>Recent Transactions</h5>
    <table class="table table-sm">
        <thead>
            <tr>
                <th>Date</th>
                <th>Type</th>
                <th>Amount</th>
                <th>Description</th>
            </tr>
        </thead>
        <tbody>
            <?php $__empty_1 = true; $__currentLoopData = $transactions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $txn): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr>
                    <td><?php echo e($txn->created_at->format('M d, Y H:i')); ?></td>
                    <td><?php echo e(ucfirst($txn->type)); ?></td>
                    <td class="<?php echo e($txn->type === 'credit' ? 'text-success' : 'text-danger'); ?>">
                        <?php echo e($txn->type === 'credit' ? '+' : '-'); ?>Rs. <?php echo e(number_format($txn->amount, 2)); ?>

                    </td>
                    <td><?php echo e($txn->description); ?></td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr><td colspan="4" class="text-center">No transactions yet.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
<?php $__env->stopSection(); ?> 
<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\sabst\momo_shop\resources\views/user/my-account.blade.php ENDPATH**/ ?>