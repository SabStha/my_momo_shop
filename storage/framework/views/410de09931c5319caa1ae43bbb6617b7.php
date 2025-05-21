<?php $__env->startSection('content'); ?>
<style>
    .hero-section {
        background: url('<?php echo e(asset('images/background.png')); ?>') no-repeat center center/cover;
        color: white;
        min-height: 90vh;
        display: flex;
        align-items: center;
        text-shadow: 1px 1px 3px black;
    }

    .btn-order {
        background-color: #e74c3c;
        color: white;
        border: none;
    }

    .btn-order:hover {
        background-color: #c0392b;
    }

    .btn-view {
        border: 2px solid white;
        color: white;
        background: transparent;
    }

    .btn-view:hover {
        background: white;
        color: #333;
    }

    .dark-section {
        background-color: #0f0f0f;
        color: white;
        padding: 4rem 0;
    }

    .filter-btn {
        background-color: #1c1c1c;
        color: white;
        border: none;
        margin-right: 10px;
    }

    .filter-btn:hover {
        background-color: #333;
    }

    .card-dark {
        background-color: #1a1a1a;
        color: white;
        border: none;
    }

    .card-dark img {
        height: 200px;
        object-fit: cover;
    }
</style>

<!-- Hero Section -->
<section class="hero-section">
    <div class="container text-center">
        <h1 class="display-4 fw-bold">Fresh, Authentic Momos Delivered to Your Door</h1>
        <p class="lead my-4">Enjoy our delicious dumplings at home</p>
        <a href="<?php echo e(route('products.index')); ?>" class="btn btn-order btn-lg me-3">Order Now</a>
        <a href="#varieties" class="btn btn-view btn-lg">View Menu</a>
    </div>
</section>

<!-- Product Varieties -->
<section id="varieties" class="dark-section">
    <div class="container text-center">
        <h2 class="mb-4">Our Varieties</h2>

        <div class="mb-4">
            <button class="btn filter-btn">Steamed</button>
            <button class="btn filter-btn">Fried</button>
            <button class="btn filter-btn">Kothey</button>
            <button class="btn filter-btn">Jhol</button>
        </div>

        <div class="row justify-content-center">
            <?php $__currentLoopData = $featuredProducts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="col-md-3 mb-4">
                <div class="card card-dark h-100">
                    <img src="<?php echo e(asset('storage/' . $product->image)); ?>" class="card-img-top" alt="<?php echo e($product->name); ?>">
                    <div class="card-body">
                        <h5 class="card-title"><?php echo e($product->name); ?></h5>
                        <p class="card-text">From <strong>¥<?php echo e(number_format($product->price, 0)); ?></strong></p>
                        <a href="<?php echo e(route('products.show', $product)); ?>" class="btn btn-outline-light btn-sm mt-2">View Details</a>
                    </div>
                </div>
            </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    </div>
</section>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\sabst\momo_shop\resources\views/home.blade.php ENDPATH**/ ?>