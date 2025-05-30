<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag; ?>
<?php foreach($attributes->onlyProps(['product']) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
} ?>
<?php $attributes = $attributes->exceptProps(['product']); ?>
<?php foreach (array_filter((['product']), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
} ?>
<?php $__defined_vars = get_defined_vars(); ?>
<?php foreach ($attributes as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
} ?>
<?php unset($__defined_vars); ?>

<div class="momo-card">
    <img src="<?php echo e(asset('storage/' . $product->image)); ?>" class="img-fluid" alt="<?php echo e($product->name); ?>">
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start mt-2 gap-2">
        <div class="w-100">
            <h5><?php echo e($product->name); ?></h5>
            <p>From <strong>Rs. <?php echo e(number_format($product->price, 2)); ?></strong></p>
            <?php if($product->is_featured): ?>
                <span class="badge bg-warning text-dark">Featured</span>
            <?php endif; ?>
        </div>
        <a href="<?php echo e(route('products.show', $product)); ?>" class="btn btn-sm btn-success w-100 w-sm-auto">Buy Now</a>
    </div>
</div> <?php /**PATH C:\Users\sabst\momo_shop\resources\views/components/momo-card.blade.php ENDPATH**/ ?>