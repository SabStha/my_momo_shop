<?php $__env->startSection('content'); ?>
<div class="container py-4">
    <h1 class="text-center mb-4">Special Offers & Promotions</h1>

    <?php if($products->isEmpty()): ?>
        <div class="text-center py-5">
            <p class="text-muted">No special offers available at the moment.</p>
        </div>
    <?php else: ?>
        <div class="row g-4">
            <?php $__currentLoopData = $products; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="col-md-6 col-lg-4">
                    <div class="card h-100 shadow-sm">
                        <img src="<?php echo e($product->image_url ?? asset('storage/default.jpg')); ?>" 
                             class="card-img-top" 
                             alt="<?php echo e($product->name); ?>"
                             style="height: 200px; object-fit: cover;">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo e($product->name); ?></h5>
                            <p class="card-text"><?php echo e($product->description); ?></p>
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <span class="text-decoration-line-through text-muted">
                                        $<?php echo e(number_format($product->price, 2)); ?>

                                    </span>
                                    <span class="ms-2 text-danger fw-bold">
                                        $<?php echo e(number_format($product->discount_price, 2)); ?>

                                    </span>
                                </div>
                                <form action="<?php echo e(route('cart.add', $product)); ?>" method="POST">
                                    <?php echo csrf_field(); ?>
                                    <button type="submit" class="btn btn-primary">
                                        Add to Cart
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    <?php endif; ?>
</div>
<?php $__env->stopSection(); ?> 
<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\evanh\my_momo_shop\resources\views/desktop/offers.blade.php ENDPATH**/ ?>