
<?php $__env->startSection('content'); ?>
<div class="container">
    <h2>Order #<?php echo e($order->id); ?></h2>
    <div class="mb-3">
        <strong>Table:</strong> <?php echo e($order->table->name ?? '-'); ?><br>
        <strong>Type:</strong> <?php echo e(ucfirst($order->type)); ?><br>
        <strong>Status:</strong> <?php echo e(ucfirst($order->status)); ?><br>
        <strong>Payment Status:</strong> <?php echo e(ucfirst($order->payment_status)); ?><br>
        <strong>Created At:</strong> <?php echo e($order->created_at->format('Y-m-d H:i')); ?><br>
        <?php if($order->user): ?>
            <strong>Guest:</strong> <?php echo e($order->user->name); ?> (<?php echo e($order->user->email); ?>)<br>
        <?php endif; ?>
    </div>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Item</th><th>Qty</th><th>Price</th><th>Subtotal</th>
            </tr>
        </thead>
        <tbody>
        <?php $__currentLoopData = $order->items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <tr>
                <td><?php echo e($item->item_name); ?></td>
                <td><?php echo e($item->quantity); ?></td>
                <td><?php echo e(number_format($item->price, 2)); ?></td>
                <td><?php echo e(number_format($item->subtotal, 2)); ?></td>
            </tr>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </tbody>
    </table>
    <div class="mb-3">
        <strong>Subtotal:</strong> Rs. <?php echo e(number_format($order->total, 2)); ?><br>
        <strong>Tax (13%):</strong> Rs. <?php echo e(number_format($order->total * 0.13, 2)); ?><br>
        <strong>Total:</strong> Rs. <?php echo e(number_format($order->total * 1.13, 2)); ?>

    </div>
    <div class="mb-3">
        <strong>Created:</strong> <?php echo e($order->created_at->format('Y-m-d H:i')); ?><br>
        <strong>Updated:</strong> <?php echo e($order->updated_at->format('Y-m-d H:i')); ?>

    </div>
    <?php if($order->payment_status !== 'paid'): ?>
    <form method="POST" action="<?php echo e(route('orders.pay', $order)); ?>" class="row g-3">
        <?php echo csrf_field(); ?>
        <div class="col-md-3">
            <label>Amount Received</label>
            <input type="number" step="0.01" name="amount_received" class="form-control" required>
        </div>
        <div class="col-md-3">
            <label>Payment Method</label>
            <select name="payment_method" class="form-select" required>
                <option value="cash">Cash</option>
                <option value="card">Card</option>
                <option value="qr">QR</option>
            </select>
        </div>
        <div class="col-md-3 d-flex align-items-end">
            <button class="btn btn-success">Mark as Paid</button>
        </div>
    </form>
    <?php else: ?>
        <div class="alert alert-success">Order is paid. Change: Rs. <?php echo e(number_format($order->change, 2)); ?></div>
    <?php endif; ?>
    <div class="mt-3">
        <a href="<?php echo e(route('orders.kitchen-receipt', $order)); ?>" target="_blank" class="btn btn-secondary">Print Kitchen Receipt</a>
        <a href="<?php echo e(route('orders.receipt', $order)); ?>" target="_blank" class="btn btn-dark">Print Customer Receipt</a>
    </div>
</div>
<?php $__env->stopSection(); ?> 
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\sabst\momo_shop\resources\views/orders/show.blade.php ENDPATH**/ ?>