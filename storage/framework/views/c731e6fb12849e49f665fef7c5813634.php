

<?php $__env->startSection('title', 'Simple Reports & Analytics'); ?>

<?php $__env->startSection('content'); ?>
<div class="container py-4">
    <h2 class="mb-4">Reports & Analytics</h2>
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card card-body mb-2">
                <b>Total Sales</b>
                <div class="display-6">Rs. <?php echo e(number_format($totalSales)); ?></div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card card-body mb-2">
                <b>Total Orders</b>
                <div class="display-6"><?php echo e(number_format($totalOrders)); ?></div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card card-body mb-2">
                <b>Total Profit</b>
                <div class="display-6">Rs. <?php echo e(number_format($totalProfit)); ?></div>
            </div>
        </div>
    </div>
    <h4 class="mt-4">Employee Working Hours</h4>
    <table class="table table-striped mb-4">
        <thead>
            <tr>
                <th>Employee</th>
                <th>Total Hours</th>
                <th>Overtime</th>
                <th>Total Pay</th>
            </tr>
        </thead>
        <tbody>
            <?php $__currentLoopData = $employeeHours; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $emp): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <tr>
                <td><?php echo e($emp['name']); ?></td>
                <td><?php echo e($emp['totalHours']); ?></td>
                <td><?php echo e($emp['overtime']); ?></td>
                <td>Rs. <?php echo e(number_format($emp['totalPay'])); ?></td>
            </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </tbody>
    </table>
    <h4 class="mt-4">Profit Analysis</h4>
    <table class="table table-striped mb-4">
        <thead>
            <tr>
                <th>Date</th>
                <th>Revenue</th>
                <th>Cost</th>
                <th>Profit</th>
                <th>Profit Margin</th>
            </tr>
        </thead>
        <tbody>
            <?php $__currentLoopData = $profitAnalysis; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <tr>
                <td><?php echo e($row['date']); ?></td>
                <td>Rs. <?php echo e(number_format($row['revenue'])); ?></td>
                <td>Rs. <?php echo e(number_format($row['cost'])); ?></td>
                <td>Rs. <?php echo e(number_format($row['profit'])); ?></td>
                <td><?php echo e($row['margin']); ?>%</td>
            </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </tbody>
    </table>
</div>
<?php $__env->stopSection(); ?> 
<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\sabst\momo_shop\resources\views/admin/simple-report.blade.php ENDPATH**/ ?>