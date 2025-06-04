

<?php $__env->startSection('content'); ?>
<div class="container py-4">
    <h2 class="mb-4">Product Search</h2>
    <form method="GET" action="<?php echo e(route('search')); ?>" class="mb-4 position-relative" autocomplete="off">
        <div class="input-group">
            <input type="text" name="q" id="searchInput" class="form-control" placeholder="Search products..." value="<?php echo e(request('q', $query ?? '')); ?>" autocomplete="off">
            <button class="btn btn-primary" type="submit"><i class="fas fa-search"></i> Search</button>
        </div>
        <div id="autocompleteResults" class="list-group position-absolute w-100" style="z-index: 1000; display: none;"></div>
    </form>

    <?php if(isset($products) && $products->count()): ?>
        <div class="row">
            <?php $__currentLoopData = $products; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="col-md-4 mb-4">
                    <div class="card h-100">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo e($product->name); ?></h5>
                            <p class="card-text"><?php echo e(Str::limit($product->description, 80)); ?></p>
                            <p class="card-text fw-bold">Rs. <?php echo e(number_format($product->price, 2)); ?></p>
                            <a href="<?php echo e(route('products.show', $product)); ?>" class="btn btn-outline-primary">View Details</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    <?php elseif(empty(request('q', $query ?? ''))): ?>
        <div class="alert alert-info text-center">
            <h4 class="mb-2">Welcome to Product Search!</h4>
            <p>Start typing above to find your favorite products.</p>
        </div>
    <?php else: ?>
        <div class="alert alert-warning">No products found. Try a different search.</div>
    <?php endif; ?>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
const searchInput = document.getElementById('searchInput');
const resultsBox = document.getElementById('autocompleteResults');
let debounceTimeout;

searchInput.addEventListener('input', function() {
    clearTimeout(debounceTimeout);
    const query = this.value.trim();
    if (query.length < 1) {
        resultsBox.style.display = 'none';
        resultsBox.innerHTML = '';
        return;
    }
    debounceTimeout = setTimeout(() => {
        fetch(`/api/products/autocomplete?q=${encodeURIComponent(query)}`)
            .then(res => res.json())
            .then(data => {
                if (data.length > 0) {
                    resultsBox.innerHTML = data.map(product =>
                        `<a href="/products/${product.id}" class="list-group-item list-group-item-action d-flex align-items-center">
                            <img src="/storage/${product.image}" alt="${product.name}" style="width:40px;height:40px;object-fit:cover;border-radius:5px;margin-right:10px;">
                            <span>${product.name}</span>
                        </a>`
                    ).join('');
                    resultsBox.style.display = 'block';
                } else {
                    resultsBox.innerHTML = '<div class="list-group-item">No products found</div>';
                    resultsBox.style.display = 'block';
                }
            });
    }, 200);
});

document.addEventListener('click', function(e) {
    if (!searchInput.contains(e.target) && !resultsBox.contains(e.target)) {
        resultsBox.style.display = 'none';
    }
});
</script>
<?php $__env->stopPush(); ?> 
<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\sabst\momo_shop\resources\views/products/search.blade.php ENDPATH**/ ?>