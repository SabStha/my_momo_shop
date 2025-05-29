<?php $__env->startSection('title', 'Dashboard'); ?>

<?php $__env->startSection('content'); ?>
<!-- <style>
    body {
        background-color: #5c2c11; /* dark orange-brown */
    }

    .dashboard-section {
        background-color: #f9c784; /* warm light orange */
        padding: 30px;
        border-radius: 10px;
        box-shadow: 0 0 12px rgba(0, 0, 0, 0.1);
        margin-bottom: 30px;
    }

    .stat-card {
        background-color: #f4a259; /* lighter brown/orange */
        color: #3b1f0d;
        border-radius: 8px;
        padding: 25px;
        margin-bottom: 20px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
    }

    .stat-card h3 {
        font-weight: bold;
        margin-bottom: 10px;
    }

    .stat-card .value {
        font-size: 2.5rem;
        font-weight: bold;
    }

    .section-title {
        font-size: 1.5rem;
        font-weight: 600;
        color: #3b1f0d;
        margin-bottom: 15px;
    }

    .custom-table th {
        background-color: #e76f51;
        color: #fff;
    }

    .custom-table td, .custom-table th {
        color: #3b1f0d;
    }

    .custom-table {
        background-color: #fff3e0;
        border-radius: 8px;
        overflow: hidden;
    }
</style> -->

<div class="container-fluid dashboard-section">
    <div class="row mb-4">
        <div class="col-12 text-end">
            <a href="<?php echo e(route('admin.employees.index')); ?>" class="btn btn-primary btn-lg">
                <i class="fas fa-users"></i> Manage Employees
            </a>
            <a href="<?php echo e(route('admin.clock.index')); ?>" class="btn btn-success btn-lg ms-2">
                <i class="fas fa-clock"></i> Employee Clock In/Out
            </a>
            <form method="POST" action="<?php echo e(route('logout')); ?>" class="d-inline ms-2">
                <?php echo csrf_field(); ?>
                <button type="submit" class="btn btn-danger btn-lg">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </button>
            </form>
        </div>
    </div>
</div>

<div class="mt-4">
    <h3 class="section-title">Reports & Analytics</h3>
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
                <div class="display-6"><?php echo e(number_format($totalOrdersReport)); ?></div>
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
</div>
<div class="container-fluid dashboard-section">
    <div class="row mt-5">
        <div class="col-md-6">
            <h4 class="section-title">Recent Orders</h4>
            <table class="table custom-table table-striped">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Customer</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__currentLoopData = $recentOrders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $order): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr>
                        <td><?php echo e($order->id); ?></td>
                        <td><?php echo e($order->user->name ?? 'Guest'); ?></td>
                        <td><?php echo e(ucfirst($order->status)); ?></td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tbody>
            </table>
        </div>

        <div class="col-md-6">
            <h4 class="section-title">Top Selling Products</h4>
            <table class="table custom-table table-striped">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Sold</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__currentLoopData = $topProducts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr>
                        <td><?php echo e($product->name); ?></td>
                        <td><?php echo e($product->sold_count); ?></td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="container-fluid dashboard-section mt-4">
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

<?php $__env->startPush('scripts'); ?>
<!-- Scripts -->
<script src="<?php echo e(mix('js/app.js')); ?>" defer></script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('desktop.admin.layouts.admin', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\sabst\momo_shop\resources\views/desktop/admin/dashboard.blade.php ENDPATH**/ ?>