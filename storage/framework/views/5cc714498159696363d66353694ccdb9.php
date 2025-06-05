<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Supply Order #<?php echo e($order->order_number); ?></title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; }
        .header { margin-bottom: 20px; }
        .table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .table th, .table td { border: 1px solid #ddd; padding: 8px; }
        .table th { background: #f5f5f5; }
        .right { text-align: right; }
    </style>
</head>
<body>
    <div class="header">
        <h2>Supply Order #<?php echo e($order->order_number); ?></h2>
        <p><strong>Date:</strong> <?php echo e($order->ordered_at ? $order->ordered_at->format('M d, Y') : ''); ?></p>
        <p><strong>Status:</strong> <?php echo e(ucfirst($order->status)); ?></p>
    </div>
    <div>
        <h4>Supplier Information</h4>
        <p>
            <strong>Name:</strong> <?php echo e($order->supplier->name); ?><br>
            <?php if($order->supplier->email): ?>
                <strong>Email:</strong> <?php echo e($order->supplier->email); ?><br>
            <?php endif; ?>
            <?php if($order->supplier->phone): ?>
                <strong>Phone:</strong> <?php echo e($order->supplier->phone); ?><br>
            <?php endif; ?>
            <?php if($order->supplier->address): ?>
                <strong>Address:</strong> <?php echo e($order->supplier->address); ?><br>
            <?php endif; ?>
        </p>
    </div>
    <div>
        <h4>Order Items</h4>
        <table class="table">
            <thead>
                <tr>
                    <th>Item</th>
                    <th>Quantity</th>
                    <th>Unit Price</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                <?php $__currentLoopData = $order->items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr>
                        <td><?php echo e($item->inventoryItem->name ?? ''); ?></td>
                        <td class="right"><?php echo e($item->quantity); ?></td>
                        <td class="right">$<?php echo e(number_format($item->unit_price, 2)); ?></td>
                        <td class="right">$<?php echo e(number_format($item->total_price, 2)); ?></td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>
        <p class="right"><strong>Total Amount:</strong> $<?php echo e(number_format($order->total_amount, 2)); ?></p>
    </div>
    <?php if($order->notes): ?>
        <div>
            <h4>Notes</h4>
            <p><?php echo e($order->notes); ?></p>
        </div>
    <?php endif; ?>
</body>
</html> <?php /**PATH C:\Users\sabst\momo_shop\resources\views/pdf/supply_order.blade.php ENDPATH**/ ?>