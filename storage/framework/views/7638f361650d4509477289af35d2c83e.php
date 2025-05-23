
<?php $__env->startSection('title', 'Inventory Dashboard'); ?>
<?php $__env->startSection('content'); ?>
<div class="container py-3">
    <h2>Inventory Dashboard</h2>
    <div class="row g-3 mb-4">
        <div class="col-md-4 col-12 mb-3">
            <button class="btn btn-outline-primary w-100 py-4 inventory-tab" data-tab="count">
                <i class="fas fa-clipboard-list fa-2x mb-2"></i><br>
                Daily Inventory Count
            </button>
        </div>
        <div class="col-md-4 col-12 mb-3">
            <button class="btn btn-outline-success w-100 py-4 inventory-tab" data-tab="forecast">
                <i class="fas fa-chart-line fa-2x mb-2"></i><br>
                2-Day Forecast
            </button>
        </div>
        <div class="col-md-4 col-12 mb-3">
            <button class="btn btn-outline-warning w-100 py-4 inventory-tab" data-tab="orders">
                <i class="fas fa-truck fa-2x mb-2"></i><br>
                Orders
            </button>
        </div>
    </div>
    <div id="inventory-content">
        <div id="tab-count" class="inventory-section" style="display:none;">
            <?php echo $__env->make('admin.inventory.count-partial', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        </div>
        <div id="tab-forecast" class="inventory-section" style="display:none;">
            <?php echo $__env->make('admin.inventory.forecast-partial', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        </div>
        <div id="tab-orders" class="inventory-section" style="display:none;">
            <?php echo $__env->make('admin.inventory.orders-partial', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
$(function() {
    $('.inventory-tab').on('click', function() {
        var tab = $(this).data('tab');
        $('.inventory-section').hide();
        $('#tab-' + tab).show();
    });
    // Show the first tab by default
    $('#tab-count').show();
});
</script>
<?php $__env->stopPush(); ?> 
<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\sabst\momo_shop\resources\views/admin/inventory/dashboard.blade.php ENDPATH**/ ?>