<?php $__env->startSection('content'); ?>
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h2>Products</h2>
                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('manage products')): ?>
                    <a href="<?php echo e(route('products.create')); ?>" class="btn btn-primary">Add New Product</a>
                    <?php endif; ?>
                </div>

                <div class="card-body">
                    <?php if(session('success')): ?>
                        <div class="alert alert-success" role="alert">
                            <?php echo e(session('success')); ?>

                        </div>
                    <?php endif; ?>

                    <div class="row">
                        <?php $__currentLoopData = $products; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="col-md-4 mb-4">
                                <div class="card h-100">
                                    <img src="<?php echo e(asset('storage/' . $product->image)); ?>" class="card-img-top" alt="<?php echo e($product->name); ?>">
                                    <div class="card-body">
                                        <h5 class="card-title"><?php echo e($product->name); ?></h5>
                                        <p class="card-text"><?php echo e(Str::limit($product->description, 100)); ?></p>
                                        <p class="card-text"><strong>Price: $<?php echo e(number_format($product->price, 2)); ?></strong></p>
                                        <p class="card-text">Stock: <?php echo e($product->stock); ?></p>
                                        
                                        <div class="d-flex justify-content-between">
                                            <a href="<?php echo e(route('products.show', $product)); ?>" class="btn btn-info">View Details</a>
                                            
                                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('manage products')): ?>
                                            <div>
                                                <a href="<?php echo e(route('products.edit', $product)); ?>" class="btn btn-warning">Edit</a>
                                                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('delete products')): ?>
                                                <form action="<?php echo e(route('products.destroy', $product)); ?>" method="POST" class="d-inline">
                                                    <?php echo csrf_field(); ?>
                                                    <?php echo method_field('DELETE'); ?>
                                                    <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this product?')">Delete</button>
                                                </form>
                                                <?php endif; ?>
                                            </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?> 
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\sabst\momo_shop\resources\views/products/index.blade.php ENDPATH**/ ?>