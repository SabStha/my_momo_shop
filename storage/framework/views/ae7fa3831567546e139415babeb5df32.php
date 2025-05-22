

<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Time Log Reports</h3>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <form id="reportForm" class="row g-3">
                                <div class="col-md-3">
                                    <label for="employee_id" class="form-label">Employee</label>
                                    <select class="form-select" id="employee_id" name="employee_id">
                                        <option value="">All Employees</option>
                                        <?php $__currentLoopData = $employees; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $employee): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <option value="<?php echo e($employee->id); ?>"><?php echo e($employee->user->name); ?></option>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label for="report_type" class="form-label">Report Type</label>
                                    <select class="form-select" id="report_type" name="report_type">
                                        <option value="weekly">Weekly Report</option>
                                        <option value="monthly">Monthly Report</option>
                                    </select>
                                </div>
                                <div class="col-md-3 weekly-fields">
                                    <label for="start_date" class="form-label">Start Date</label>
                                    <input type="date" class="form-control" id="start_date" name="start_date" value="<?php echo e(now()->subDays(7)->format('Y-m-d')); ?>">
                                </div>
                                <div class="col-md-3 weekly-fields">
                                    <label for="end_date" class="form-label">End Date</label>
                                    <input type="date" class="form-control" id="end_date" name="end_date" value="<?php echo e(now()->format('Y-m-d')); ?>">
                                </div>
                                <div class="col-md-3 monthly-fields" style="display: none;">
                                    <label for="year" class="form-label">Year</label>
                                    <select class="form-select" id="year" name="year">
                                        <?php for($i = now()->year; $i >= now()->year - 2; $i--): ?>
                                            <option value="<?php echo e($i); ?>"><?php echo e($i); ?></option>
                                        <?php endfor; ?>
                                    </select>
                                </div>
                                <div class="col-md-3 monthly-fields" style="display: none;">
                                    <label for="month" class="form-label">Month</label>
                                    <select class="form-select" id="month" name="month">
                                        <?php $__currentLoopData = range(1, 12); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $m): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <option value="<?php echo e($m); ?>" <?php echo e($m == now()->month ? 'selected' : ''); ?>>
                                                <?php echo e(date('F', mktime(0, 0, 0, $m, 1))); ?>

                                            </option>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">&nbsp;</label>
                                    <button type="submit" class="btn btn-primary d-block">Generate Report</button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <div id="reportContent">
                        <?php echo $__env->make('admin.clock._report', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php $__env->startPush('scripts'); ?>
<script>
$(document).ready(function() {
    // Handle report type change
    $('#report_type').on('change', function() {
        if ($(this).val() === 'monthly') {
            $('.weekly-fields').hide();
            $('.monthly-fields').show();
        } else {
            $('.weekly-fields').show();
            $('.monthly-fields').hide();
        }
    });

    // Handle form submission
    $('#reportForm').on('submit', function(e) {
        e.preventDefault();
        $.get('<?php echo e(route("admin.clock.report")); ?>', $(this).serialize(), function(response) {
            $('#reportContent').html(response.html);
        });
    });
});
</script>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?> 
<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\sabst\momo_shop\resources\views/admin/clock/report.blade.php ENDPATH**/ ?>