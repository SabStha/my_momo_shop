

<?php $__env->startSection('content'); ?>
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h4 class="mb-0">My Orders</h4>
                        <a href="<?php echo e(route('account')); ?>" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-2"></i>Back to My Account
                        </a>
                    </div>

                    <?php if($orders->isEmpty()): ?>
                        <div class="text-center py-4">
                            <i class="fas fa-shopping-bag fa-3x text-muted mb-3"></i>
                            <h5>No Orders Yet</h5>
                            <p class="text-muted">You haven't placed any orders yet.</p>
                            <a href="<?php echo e(route('menu')); ?>" class="btn btn-primary">Start Shopping</a>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Order #</th>
                                        <th>Date</th>
                                        <th>Total</th>
                                        <th>Status</th>
                                        <th>Payment</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $__currentLoopData = $orders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $order): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <tr>
                                            <td><?php echo e($order->order_number); ?></td>
                                            <td><?php echo e($order->created_at->format('M d, Y H:i')); ?></td>
                                            <td>Rs. <?php echo e(number_format($order->grand_total, 2)); ?></td>
                                            <td>
                                                <span class="badge bg-<?php echo e($order->status === 'completed' ? 'success' : ($order->status === 'pending' ? 'warning' : 'secondary')); ?>">
                                                    <?php echo e(ucfirst($order->status)); ?>

                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge bg-<?php echo e($order->payment_status === 'paid' ? 'success' : 'warning'); ?>">
                                                    <?php echo e(ucfirst($order->payment_status)); ?>

                                                </span>
                                            </td>
                                            <td>
                                                <a href="<?php echo e(route('orders.show', $order)); ?>" class="btn btn-sm btn-outline-primary">
                                                    View Details
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </tbody>
                            </table>
                        </div>

                        <div class="d-flex justify-content-center mt-4">
                            <?php echo e($orders->links()); ?>

                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?> 
<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\sabst\momo_shop\resources\views/user/my-account/orders.blade.php ENDPATH**/ ?>