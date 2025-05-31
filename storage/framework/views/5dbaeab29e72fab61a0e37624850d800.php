

<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">POS & Payment Manager Access Logs</h3>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>Date & Time</th>
                                    <th>User</th>
                                    <th>Access Type</th>
                                    <th>Action</th>
                                    <th>IP Address</th>
                                    <th>Device</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__currentLoopData = $logs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $log): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr>
                                    <td><?php echo e($log->created_at->format('M d, Y H:i:s')); ?></td>
                                    <td><?php echo e($log->user->name); ?></td>
                                    <td>
                                        <?php if($log->access_type === 'pos'): ?>
                                            <span class="badge bg-primary">POS</span>
                                        <?php else: ?>
                                            <span class="badge bg-success">Payment Manager</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if($log->action === 'login'): ?>
                                            <span class="badge bg-success">Login</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger">Logout</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo e($log->ip_address); ?></td>
                                    <td><?php echo e($log->user_agent); ?></td>
                                </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="mt-4">
                        <?php echo e($logs->links()); ?>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?> 
<?php echo $__env->make('desktop.layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\sabst\momo_shop\resources\views/desktop/admin/pos-access-logs.blade.php ENDPATH**/ ?>