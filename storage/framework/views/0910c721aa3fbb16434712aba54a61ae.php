

<?php $__env->startSection('content'); ?>
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h2><?php echo e($product->name); ?></h2>
                </div>

                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <img src="<?php echo e(asset('storage/' . $product->image)); ?>" class="img-fluid" alt="<?php echo e($product->name); ?>">
                        </div>
                        <div class="col-md-6">
                            <h4>Description</h4>
                            <p><?php echo e($product->description); ?></p>
                            
                            <h4>Price</h4>
                            <p class="h3">$<?php echo e(number_format($product->price, 2)); ?></p>
                            
                            <h4>Stock</h4>
                            <p><?php echo e($product->stock); ?> units available</p>

                            <div class="mt-4">
                                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('manage products')): ?>
                                <div class="mb-3">
                                    <a href="<?php echo e(route('products.edit', $product)); ?>" class="btn btn-warning">Edit Product</a>
                                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('delete products')): ?>
                                    <form action="<?php echo e(route('products.destroy', $product)); ?>" method="POST" class="d-inline">
                                        <?php echo csrf_field(); ?>
                                        <?php echo method_field('DELETE'); ?>
                                        <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this product?')">Delete Product</button>
                                    </form>
                                    <?php endif; ?>
                                </div>
                                <?php endif; ?>

                                <form action="<?php echo e(route('cart.add', $product)); ?>" method="POST" class="mb-2">
                                    <?php echo csrf_field(); ?>
                                    <div class="form-group">
                                        <label for="quantity">Quantity:</label>
                                        <input type="number" name="quantity" id="quantity" class="form-control" value="1" min="1" max="<?php echo e($product->stock); ?>">
                                    </div>
                                    <div class="d-flex gap-2 mt-3">
                                        <button type="submit" class="btn btn-primary">Add to Cart</button>
                                        <button type="submit" formaction="<?php echo e(route('checkout.buyNow', $product)); ?>" class="btn btn-success">Buy Now</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?> 
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\sabst\momo_shop\resources\views/products/show.blade.php ENDPATH**/ ?>