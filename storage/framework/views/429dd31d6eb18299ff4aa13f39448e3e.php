

<?php $__env->startSection('content'); ?>
<div class="container">
    <h2 class="mb-4">Access Logs</h2>

    <!-- POS Access Logs -->
    <div class="card mb-4">
        <div class="card-header">
            <h4>POS Access Logs</h4>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Action</th>
                            <th>Status</th>
                            <th>IP Address</th>
                            <th>Time</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__currentLoopData = $posAccessLogs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $log): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr>
                            <td><?php echo e($log->user->name); ?></td>
                            <td><?php echo e(ucfirst($log->action)); ?></td>
                            <td>
                                <?php if($log->details && isset($log->details['status']) && $log->details['status'] === 'success'): ?>
                                    <span class="badge bg-success">Success</span>
                                <?php else: ?>
                                    <span class="badge bg-danger">Failed</span>
                                    <?php if($log->details && isset($log->details['reason'])): ?>
                                        <small class="text-muted">(<?php echo e($log->details['reason']); ?>)</small>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </td>
                            <td><?php echo e($log->ip_address); ?></td>
                            <td><?php echo e($log->created_at->format('Y-m-d H:i:s')); ?></td>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Payment Manager Access Logs -->
    <div class="card mb-4">
        <div class="card-header">
            <h4>Payment Manager Access Logs</h4>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Action</th>
                            <th>Status</th>
                            <th>IP Address</th>
                            <th>Time</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__currentLoopData = $paymentManagerLogs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $log): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr>
                            <td><?php echo e($log->user->name); ?></td>
                            <td><?php echo e(ucfirst($log->action)); ?></td>
                            <td>
                                <?php if($log->details && isset($log->details['status']) && $log->details['status'] === 'success'): ?>
                                    <span class="badge bg-success">Success</span>
                                <?php else: ?>
                                    <span class="badge bg-danger">Failed</span>
                                    <?php if($log->details && isset($log->details['reason'])): ?>
                                        <small class="text-muted">(<?php echo e($log->details['reason']); ?>)</small>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </td>
                            <td><?php echo e($log->ip_address); ?></td>
                            <td><?php echo e($log->created_at->format('Y-m-d H:i:s')); ?></td>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- POS Order Logs -->
    <div class="card mb-4">
        <div class="card-header">
            <h4>POS Order Logs</h4>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Order ID</th>
                            <th>Amount</th>
                            <th>Items</th>
                            <th>Time</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__currentLoopData = $posOrderLogs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $log): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr>
                            <td><?php echo e($log->user->name); ?></td>
                            <td><?php echo e($log->details['order_id'] ?? 'N/A'); ?></td>
                            <td>₱<?php echo e(number_format($log->details['total_amount'] ?? 0, 2)); ?></td>
                            <td><?php echo e($log->details['items_count'] ?? 0); ?></td>
                            <td><?php echo e($log->created_at->format('Y-m-d H:i:s')); ?></td>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Payment Receiver Logs -->
    <div class="card mb-4">
        <div class="card-header">
            <h4>Payment Receiver Logs</h4>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Order ID</th>
                            <th>Amount</th>
                            <th>Payment Method</th>
                            <th>Time</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__currentLoopData = $paymentLogs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $log): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr>
                            <td><?php echo e($log->user->name); ?></td>
                            <td><?php echo e($log->details['order_id'] ?? 'N/A'); ?></td>
                            <td>₱<?php echo e(number_format($log->details['amount'] ?? 0, 2)); ?></td>
                            <td><?php echo e($log->details['payment_method'] ?? 'N/A'); ?></td>
                            <td><?php echo e($log->created_at->format('Y-m-d H:i:s')); ?></td>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?> 
<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\sabst\momo_shop\resources\views/admin/pos-access-logs.blade.php ENDPATH**/ ?>