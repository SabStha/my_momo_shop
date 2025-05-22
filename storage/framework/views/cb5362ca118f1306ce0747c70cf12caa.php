<?php $__empty_1 = true; $__currentLoopData = $timeLogs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $log): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
    <tr>
        <td><?php echo e($log->employee->user->name); ?></td>
        <td><?php echo e($log->clock_in->format('H:i:s')); ?></td>
        <td><?php echo e($log->clock_out ? $log->clock_out->format('H:i:s') : '-'); ?></td>
        <td><?php echo e($log->break_start ? $log->break_start->format('H:i:s') : '-'); ?></td>
        <td><?php echo e($log->break_end ? $log->break_end->format('H:i:s') : '-'); ?></td>
        <td>
            <?php if($log->status === 'completed'): ?>
                <span class="badge bg-secondary">Completed</span>
            <?php elseif($log->status === 'on_break'): ?>
                <span class="badge bg-warning">On Break</span>
            <?php else: ?>
                <span class="badge bg-success">Active</span>
            <?php endif; ?>
        </td>
    </tr>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
    <tr>
        <td colspan="6" class="text-center">No clock records for today.</td>
    </tr>
<?php endif; ?> <?php /**PATH C:\Users\sabst\momo_shop\resources\views/admin/clock/_time_logs.blade.php ENDPATH**/ ?>