

<?php $__env->startSection('content'); ?>
<div class="container py-4">
    <h2>My Orders</h2>
    <div class="table-responsive mt-4">
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Order #</th>
                    <th>Date</th>
                    <th>Status</th>
                    <th>Total</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $orders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $order): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr>
                    <td><?php echo e($order->id); ?></td>
                    <td><?php echo e($order->created_at->format('M d, Y H:i')); ?></td>
                    <td><span class="badge bg-<?php echo e($order->status === 'completed' ? 'success' : ($order->status === 'cancelled' ? 'danger' : 'warning')); ?>"><?php echo e(ucfirst($order->status)); ?></span></td>
                    <td>$<?php echo e(number_format($order->total_amount, 2)); ?></td>
                    <td><a href="<?php echo e(route('dashboard.orders.show', $order)); ?>" class="btn btn-sm btn-info">View</a></td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr>
                    <td colspan="5" class="text-center">You have no orders yet.</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
        <div class="mt-3"><?php echo e($orders->links()); ?></div>
    </div>
</div>
<?php $__env->stopSection(); ?> 
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\sabst\momo_shop\resources\views/user/orders/index.blade.php ENDPATH**/ ?>