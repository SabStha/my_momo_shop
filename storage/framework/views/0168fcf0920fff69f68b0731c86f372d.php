

<?php $__env->startSection('content'); ?>
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">Employee Schedules</h4>
                    <?php if(auth()->user()->hasRole('manager')): ?>
                        <a href="<?php echo e(route('admin.employee-schedules.create')); ?>" class="btn btn-primary">Add Schedule</a>
                    <?php endif; ?>
                </div>

                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <form action="<?php echo e(route('admin.employee-schedules.index')); ?>" method="GET" class="form-inline">
                                <div class="form-group mr-2">
                                    <input type="date" name="start_date" class="form-control" value="<?php echo e($startDate->format('Y-m-d')); ?>">
                                </div>
                                <?php if(auth()->user()->hasRole('manager')): ?>
                                    <div class="form-group mr-2">
                                        <select name="employee_id" class="form-control">
                                            <option value="">All Employees</option>
                                            <?php $__currentLoopData = $employees; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $employee): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <option value="<?php echo e($employee->id); ?>" <?php echo e(request('employee_id') == $employee->id ? 'selected' : ''); ?>>
                                                    <?php echo e($employee->name); ?>

                                                </option>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        </select>
                                    </div>
                                <?php endif; ?>
                                <button type="submit" class="btn btn-secondary">Filter</button>
                            </form>
                        </div>
                        <div class="col-md-6 text-right">
                            <a href="<?php echo e(route('admin.employee-schedules.export', request()->query())); ?>" class="btn btn-success">Export PDF</a>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Employee</th>
                                    <?php for($date = $startDate->copy(); $date <= $startDate->copy()->endOfWeek(); $date->addDay()): ?>
                                        <th><?php echo e($date->format('D, M d')); ?></th>
                                    <?php endfor; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__currentLoopData = $employees; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $employee): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <tr>
                                        <td><?php echo e($employee->name); ?></td>
                                        <?php for($date = $startDate->copy(); $date <= $startDate->copy()->endOfWeek(); $date->addDay()): ?>
                                            <td>
                                                <?php if(isset($schedules[$date->format('Y-m-d')])): ?>
                                                    <?php $__currentLoopData = $schedules[$date->format('Y-m-d')]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $schedule): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                        <?php if($schedule->employee_id == $employee->id): ?>
                                                            <div class="schedule-item">
                                                                <?php echo e($schedule->start_time->format('h:i A')); ?> - <?php echo e($schedule->end_time->format('h:i A')); ?>

                                                                <?php if(auth()->user()->hasRole('manager')): ?>
                                                                    <div class="mt-1">
                                                                        <a href="<?php echo e(route('admin.employee-schedules.edit', $schedule)); ?>" class="btn btn-sm btn-info">Edit</a>
                                                                        <form action="<?php echo e(route('admin.employee-schedules.destroy', $schedule)); ?>" method="POST" class="d-inline">
                                                                            <?php echo csrf_field(); ?>
                                                                            <?php echo method_field('DELETE'); ?>
                                                                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">Delete</button>
                                                                        </form>
                                                                    </div>
                                                                <?php endif; ?>
                                                            </div>
                                                        <?php endif; ?>
                                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                <?php endif; ?>
                                            </td>
                                        <?php endfor; ?>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.schedule-item {
    padding: 5px;
    margin-bottom: 5px;
    background-color: #f8f9fa;
    border-radius: 4px;
}
</style>
<?php $__env->stopSection(); ?> 
<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\sabst\momo_shop\resources\views/employee-schedules/index.blade.php ENDPATH**/ ?>