

<?php $__env->startSection('content'); ?>
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">Employees</h4>
                    <a href="<?php echo e(route('admin.employees.create')); ?>" class="btn btn-primary">Add New Employee</a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Position</th>
                                    <th>Salary</th>
                                    <th>Hire Date</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__empty_1 = true; $__currentLoopData = $employees; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $employee): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                    <tr>
                                        <td><?php echo e($employee->id); ?></td>
                                        <td><?php echo e($employee->user->name ?? 'N/A'); ?></td>
                                        <td><?php echo e($employee->user->email ?? 'N/A'); ?></td>
                                        <td><?php echo e($employee->position ?? 'N/A'); ?></td>
                                        <td>$<?php echo e(number_format($employee->salary ?? 0, 2)); ?></td>
                                        <td><?php echo e($employee->hire_date ? $employee->hire_date->format('M d, Y') : 'N/A'); ?></td>
                                        <td>
                                            <span class="badge bg-<?php echo e(($employee->status ?? 'inactive') === 'active' ? 'success' : 'secondary'); ?>">
                                                <?php echo e(ucfirst($employee->status ?? 'inactive')); ?>

                                            </span>
                                        </td>
                                        <td>
                                            <div class="btn-group">
                                                <a href="<?php echo e(route('admin.employees.show', $employee)); ?>" 
                                                   class="btn btn-sm btn-info">
                                                    <i class="fas fa-eye"></i> View
                                                </a>
                                                <a href="<?php echo e(route('admin.employees.edit', $employee)); ?>" 
                                                   class="btn btn-sm btn-warning">
                                                    <i class="fas fa-edit"></i> Edit
                                                </a>
                                                <form action="<?php echo e(route('admin.employees.destroy', $employee)); ?>" 
                                                      method="POST" 
                                                      class="d-inline"
                                                      onsubmit="return confirm('Are you sure you want to delete this employee?');">
                                                    <?php echo csrf_field(); ?>
                                                    <?php echo method_field('DELETE'); ?>
                                                    <button type="submit" class="btn btn-sm btn-danger">
                                                        <i class="fas fa-trash"></i> Delete
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                    <tr>
                                        <td colspan="8" class="text-center">No employees found.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?> 
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\sabst\momo_shop\resources\views/admin/employees/index.blade.php ENDPATH**/ ?>