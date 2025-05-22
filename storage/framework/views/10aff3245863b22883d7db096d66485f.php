

<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Time Logs for <?php echo e($employee->user->name); ?></h3>
                </div>
                <div class="card-body">
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

                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Clock In</th>
                                    <th>Clock Out</th>
                                    <th>Duration</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__empty_1 = true; $__currentLoopData = $timeLogs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $log): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                    <tr>
                                        <td><?php echo e($log->clock_in->format('Y-m-d')); ?></td>
                                        <td><?php echo e($log->clock_in->format('H:i:s')); ?></td>
                                        <td><?php echo e($log->clock_out ? $log->clock_out->format('H:i:s') : '-'); ?></td>
                                        <td>
                                            <?php if($log->clock_out): ?>
                                                <?php echo e($log->clock_in->diffInHours($log->clock_out)); ?> hours
                                            <?php else: ?>
                                                -
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#editModal<?php echo e($log->id); ?>">
                                                Edit
                                            </button>
                                        </td>
                                    </tr>

                                    <!-- Edit Modal -->
                                    <div class="modal fade" id="editModal<?php echo e($log->id); ?>" tabindex="-1" role="dialog" aria-labelledby="editModalLabel<?php echo e($log->id); ?>" aria-hidden="true">
                                        <div class="modal-dialog" role="document">
                                            <div class="modal-content">
                                                <form action="<?php echo e(route('admin.employees.time-logs.update', [$employee, $log])); ?>" method="POST">
                                                    <?php echo csrf_field(); ?>
                                                    <?php echo method_field('PUT'); ?>
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="editModalLabel<?php echo e($log->id); ?>">Edit Time Log</h5>
                                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                            <span aria-hidden="true">&times;</span>
                                                        </button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <div class="form-group">
                                                            <label for="clock_in">Clock In Time</label>
                                                            <input type="datetime-local" class="form-control" id="clock_in" name="clock_in" value="<?php echo e($log->clock_in->format('Y-m-d\TH:i:s')); ?>" required>
                                                        </div>
                                                        <div class="form-group">
                                                            <label for="clock_out">Clock Out Time</label>
                                                            <input type="datetime-local" class="form-control" id="clock_out" name="clock_out" value="<?php echo e($log->clock_out ? $log->clock_out->format('Y-m-d\TH:i:s') : ''); ?>">
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                                        <button type="submit" class="btn btn-primary">Save changes</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                    <tr>
                                        <td colspan="5" class="text-center">No time logs found.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4">
                        <?php echo e($timeLogs->links()); ?>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?> 
<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\sabst\momo_shop\resources\views/admin/employees/time-logs/index.blade.php ENDPATH**/ ?>