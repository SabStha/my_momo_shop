<?php $__env->startSection('content'); ?>

<div class="container-fluid px-1">
    <div class="hero position-relative" style="min-height: 400px; background-color: var(--background-color); overflow: hidden;">
        <?php if($featuredProducts->count()): ?>
            <div id="featuredCarousel" class="carousel carousel-fade slide h-100" data-bs-ride="carousel" data-bs-interval="3000">
                <div class="carousel-inner h-100">
                    <?php $__currentLoopData = $featuredProducts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php
                            $img = $product->image ? asset('storage/' . $product->image) : asset('storage/products/background.png');
                        ?>
                        <div class="carousel-item <?php echo e($loop->first ? 'active' : ''); ?>" style="position: relative; height: 420px; overflow: hidden;">
                            <img src="<?php echo e($img); ?>" alt="<?php echo e($product->name); ?>"
                                class="position-absolute top-0 start-0 w-100 h-100"
                                style="object-fit: cover; z-index: 1; background-color: var(--background-color); opacity:0.9 ">

                            <div class="carousel-caption d-flex flex-column justify-content-end text-start p-4"
                                style="z-index: 2; top: 0; left: 0; right: 0; bottom: 0; height: 100%;">
                                
                                <h1 class="fs-4 fs-md-2 fw-bold mb-1"><?php echo e($product->name); ?></h1>
                                <p class="fs-6 mb-2"><?php echo e($product->description); ?></p>

                                <div class="row gx-2">
                                    <div class="col-6">
                                        <form action="<?php echo e(route('checkout.buyNow', $product)); ?>" method="POST">
                                            <?php echo csrf_field(); ?>
                                            <input type="hidden" name="quantity" value="1">
                                            <button type="submit" class="btn btn-primary w-100">Buy Now</button>
                                        </form>
                                    </div>
                                    <div class="col-6">
                                        <a href="<?php echo e(route('menu')); ?>" class="btn btn-outline-light w-100">View Menu</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
                <?php if($featuredProducts->count() > 1): ?>
                <button class="carousel-control-prev" type="button" data-bs-target="#featuredCarousel" data-bs-slide="prev">
                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Previous</span>
                </button>
                <button class="carousel-control-next" type="button" data-bs-target="#featuredCarousel" data-bs-slide="next">
                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Next</span>
                </button>
                <?php endif; ?>
            </div>
        <?php else: ?>
        <img src="<?php echo e(asset('storage/products/background.png')); ?>"
             alt="Momo Bowl"
                 class="w-100 h-100"
                 style="object-fit: cover; z-index: 1; height: 400px; background-color: var(--background-color);">
        <div class="position-absolute bottom-0 start-0 p-3 p-sm-4" style="z-index: 2; color: white; background: linear-gradient(to top, rgba(0,0,0,0.7), rgba(0,0,0,0)); width: 100%;">
            <h1 class="fs-4 fs-md-2 fw-bold">Fresh, Authentic Momos<br>Delivered to Your Door</h1>
            <p class="fs-6">Enjoy our delicious dumplings at home</p>
            <div class="row gx-2">
                <div class="col-6">
                        <a href="<?php echo e(route('menu')); ?>" class="btn btn-primary w-100">Order Now</a>
                </div>
                <div class="col-6">
                        <a href="<?php echo e(route('menu')); ?>" class="btn btn-outline-light w-100">View Menu</a>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <?php if($menuHighlights->count()): ?>
    <div class="varieties-section">
        <h2 class="mb-3">MENU HIGHLIGHTS</h2>
        <div class="row justify-content-center g-3">
            <?php $__currentLoopData = $menuHighlights; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="col-6 col-sm-6 col-md-4 col-lg-3">
                    <?php if (isset($component)) { $__componentOriginal5d32661787af448fb69dd2a6f0180889 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5d32661787af448fb69dd2a6f0180889 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.momo-card','data' => ['product' => $product]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('momo-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['product' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($product)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal5d32661787af448fb69dd2a6f0180889)): ?>
<?php $attributes = $__attributesOriginal5d32661787af448fb69dd2a6f0180889; ?>
<?php unset($__attributesOriginal5d32661787af448fb69dd2a6f0180889); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal5d32661787af448fb69dd2a6f0180889)): ?>
<?php $component = $__componentOriginal5d32661787af448fb69dd2a6f0180889; ?>
<?php unset($__componentOriginal5d32661787af448fb69dd2a6f0180889); ?>
<?php endif; ?>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
                        </div>
    <?php endif; ?>
</div>

<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
<script src="https://cdn.jsdelivr.net/npm/vue@3.4.15/dist/vue.global.prod.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const { createApp } = Vue;
    createApp({
        delimiters: ['[[', ']]'],
        data() {
            return {
                featuredProducts: <?php echo json_encode($featuredProducts, 15, 512) ?>
            }
        }
    }).mount('#homeApp');
});
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\evanh\my_momo_shop\resources\views/desktop/home.blade.php ENDPATH**/ ?>