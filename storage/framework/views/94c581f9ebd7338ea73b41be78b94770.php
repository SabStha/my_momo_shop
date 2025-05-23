<!DOCTYPE html>
<html>
<head>
    <title>Kitchen Receipt - Order #<?php echo e($order->order_number); ?></title>
    <style>
        @media print {
            body {
                font-family: 'Courier New', monospace;
                font-size: 12px;
                line-height: 1.2;
                margin: 0;
                padding: 10px;
            }
            .no-print {
                display: none;
            }
            .receipt {
                width: 80mm;
                margin: 0 auto;
            }
        }
        .receipt {
            width: 80mm;
            margin: 20px auto;
            padding: 10px;
            border: 1px solid #ccc;
        }
        .header {
            text-align: center;
            margin-bottom: 10px;
        }
        .order-info {
            margin-bottom: 10px;
            border-bottom: 1px dashed #000;
            padding-bottom: 5px;
        }
        .items {
            margin-bottom: 10px;
        }
        .item {
            margin-bottom: 5px;
        }
        .quantity {
            font-weight: bold;
        }
        .print-btn {
            display: block;
            margin: 20px auto;
            padding: 10px 20px;
            background: #4CAF50;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <div class="receipt">
        <div class="header">
            <h2>KITCHEN ORDER</h2>
            <p>Order #<?php echo e($order->order_number); ?></p>
        </div>

        <div class="order-info">
            <p><strong>Type:</strong> <?php echo e(ucfirst($order->type)); ?></p>
            <?php if($order->type === 'dine_in' && $order->table): ?>
                <p><strong>Table:</strong> <?php echo e($order->table->name); ?></p>
            <?php endif; ?>
            <p><strong>Time:</strong> <?php echo e($order->created_at->format('H:i:s')); ?></p>
        </div>

        <div class="items">
            <?php $__currentLoopData = $order->items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="item">
                    <span class="quantity"><?php echo e($item->quantity); ?>x</span>
                    <?php echo e($item->product->name); ?>

                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    </div>

    <button class="print-btn no-print" onclick="window.print()">Print Receipt</button>

    <script>
        window.onload = function() {
            window.print();
        }
    </script>
</body>
</html> <?php /**PATH C:\Users\sabst\momo_shop\resources\views/orders/kitchen-receipt.blade.php ENDPATH**/ ?>