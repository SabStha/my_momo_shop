

<?php $__env->startSection('content'); ?>
<div class="container py-5">
    <h1 class="mb-4">Checkout</h1>
    <?php if(session('success')): ?>
        <div class="alert alert-success"><?php echo e(session('success')); ?></div>
    <?php endif; ?>
    <?php if(count($cart) && $products->count()): ?>
    <table class="table table-bordered mb-4">
        <thead>
            <tr>
                <th>Product</th>
                <th>Price</th>
                <th>Quantity</th>
                <th>Subtotal</th>
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
    <form action="<?php echo e(route('checkout.submit')); ?>" method="POST" class="mb-4">
        <?php echo csrf_field(); ?>
        <div class="row mb-3">
            <div class="col-md-6">
                <label for="name" class="form-label">Name</label>
                <input type="text" name="name" id="name" class="form-control" required value="<?php echo e(old('name', Auth::user()->name ?? '')); ?>">
            </div>
            <div class="col-md-6">
                <label for="email" class="form-label">Email</label>
                <input type="email" name="email" id="email" class="form-control" required value="<?php echo e(old('email', Auth::user()->email ?? '')); ?>">
            </div>
        </div>
        <div class="mb-3">
            <label for="address" class="form-label">Shipping Address</label>
            <input type="text" name="address" id="address" class="form-control" required value="<?php echo e(old('address')); ?>">
        </div>
        <div class="text-end">
            <button type="submit" class="btn btn-primary">Place Order</button>
        </div>
    </form>
    <?php else: ?>
    <div class="alert alert-info">Your cart is empty.</div>
    <?php endif; ?>
</div>
<?php $__env->stopSection(); ?> 
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\sabst\momo_shop\resources\views/cart/checkout.blade.php ENDPATH**/ ?>