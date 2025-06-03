

<?php $__env->startSection('content'); ?>
<div class="container py-5">
    <h1 class="mb-4">Products</h1>
    <?php if($products->count()): ?>
        <div class="row">
            <?php $__currentLoopData = $products; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="col-md-4 mb-4">
                    <div class="card h-100">
                        <?php if($product->image): ?>
                            <img src="<?php echo e(asset('storage/' . $product->image)); ?>" class="card-img-top" alt="<?php echo e($product->name); ?>" loading="lazy" width="400" height="400">
                        <?php endif; ?>
                        <div class="card-body">
                            <h5 class="card-title"><?php echo e($product->name); ?></h5>
                            <p class="card-text"><?php echo e(Str::limit($product->description, 100)); ?></p>
                            <p class="card-text"><strong>Price: $<?php echo e(number_format($product->price, 2)); ?></strong></p>
                            <a href="<?php echo e(route('products.show', $product)); ?>" class="btn btn-primary">View Details</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
        <div class="mt-4">
            <?php echo e($products->links()); ?>

        </div>
    <?php else: ?>
        <div class="alert alert-info">No products found.</div>
    <?php endif; ?>
</div>
<?php $__env->stopSection(); ?> 
<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\sabst\momo_shop\resources\views/products/index.blade.php ENDPATH**/ ?>