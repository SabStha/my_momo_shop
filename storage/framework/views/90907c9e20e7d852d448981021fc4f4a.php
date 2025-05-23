<?php $__env->startSection('content'); ?>
<div class="container py-5">
    <h1 class="mb-4">Your Cart</h1>
    <?php if(session('success')): ?>
        <div class="alert alert-success"><?php echo e(session('success')); ?></div>
    <?php endif; ?>
    <?php if(session('info')): ?>
        <div class="alert alert-info"><?php echo e(session('info')); ?></div>
    <?php endif; ?>
    <?php if(count($cart) && $products->count()): ?>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Product</th>
                <th>Price</th>
                <th>Quantity</th>
                <th>Subtotal</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            <?php $total = 0; ?>
            <?php $__currentLoopData = $products; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php $qty = $cart[$product->id]['quantity']; $subtotal = $qty * $product->price; $total += $subtotal; ?>
                <tr>
                    <td><?php echo e($product->name); ?></td>
                    <td>$<?php echo e(number_format($product->price, 2)); ?></td>
                    <td><?php echo e($qty); ?></td>
                    <td>$<?php echo e(number_format($subtotal, 2)); ?></td>
                    <td>
                        <form action="<?php echo e(route('cart.remove', $product)); ?>" method="POST" style="display:inline;">
                            <?php echo csrf_field(); ?>
                            <?php echo method_field('DELETE'); ?>
                            <button type="submit" class="btn btn-sm btn-danger">Remove</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </tbody>
        <tfoot>
            <tr>
                <td colspan="3" class="text-end fw-bold">Total:</td>
                <td class="fw-bold">$<?php echo e(number_format($total, 2)); ?></td>
            </tr>
        </tfoot>
    </table>
    <div class="text-end">
        <a href="<?php echo e(route('checkout')); ?>" class="btn btn-success">Proceed to Checkout</a>
    </div>
    <?php else: ?>
    <div class="alert alert-info">Your cart is empty.</div>
    <?php endif; ?>
</div>
<?php $__env->stopSection(); ?> 
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\sabst\momo_shop\resources\views/cart/show.blade.php ENDPATH**/ ?>