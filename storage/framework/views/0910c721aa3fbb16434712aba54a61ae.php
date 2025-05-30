

<?php $__env->startSection('content'); ?>
<div class="container">
    <div class="product-detail">
        <div class="product-image">
            <img src="<?php echo e(asset('storage/' . $product->image)); ?>" alt="<?php echo e($product->name); ?>" loading="lazy" width="400" height="400">
        </div>
        <div class="product-info">
            <h1><?php echo e($product->name); ?></h1>
            <p class="description"><?php echo e($product->description); ?></p>
            <p class="price">$<?php echo e(number_format($product->price, 2)); ?></p>
            <form action="<?php echo e(route('cart.add', $product)); ?>" method="POST">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="product_id" value="<?php echo e($product->id); ?>">
                <div class="quantity">
                    <label for="quantity">Quantity:</label>
                    <input type="number" name="quantity" id="quantity" value="1" min="1">
                </div>
                <button type="submit" class="btn">Add to Cart</button>
            </form>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form[action*="cart/add"]');
    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            const qty = document.getElementById('quantity').value;
            fetch(this.action, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    quantity: qty
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Product added to cart!');
                } else {
                    alert(data.message || 'Could not add to cart.');
                }
            });
        });
    }
});
</script>
<?php $__env->stopSection(); ?> 
<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\sabst\momo_shop\resources\views/products/show.blade.php ENDPATH**/ ?>