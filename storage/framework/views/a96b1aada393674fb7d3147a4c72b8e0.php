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
                            <img src="<?php echo e(asset('storage/' . $product->image)); ?>" class="img-fluid" alt="<?php echo e($product->name); ?>" loading="lazy" width="400" height="400">
                            <div class="mt-3">
                                <h5>Average Rating:
                                    <?php $avg = round($product->average_rating, 1); ?>
                                    <span class="text-warning">
                                        <?php for($i = 1; $i <= 5; $i++): ?>
                                            <?php if($i <= $avg): ?>
                                                ★
                                            <?php else: ?>
                                                ☆
                                            <?php endif; ?>
                                        <?php endfor; ?>
                                    </span>
                                    <small>(<?php echo e(number_format($avg, 1)); ?>/5)</small>
                                </h5>
                                <h6 class="mt-3">Recent Reviews</h6>
                                <?php $__empty_1 = true; $__currentLoopData = $product->ratings()->latest()->take(5)->get(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $rating): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                    <div class="border rounded p-2 mb-2">
                                        <strong><?php echo e($rating->user->name ?? 'User'); ?></strong>:
                                        <span class="text-warning">
                                            <?php for($i = 1; $i <= 5; $i++): ?>
                                                <?php if($i <= $rating->rating): ?>
                                                    ★
                                                <?php else: ?>
                                                    ☆
                                                <?php endif; ?>
                                            <?php endfor; ?>
                                        </span>
                                        <br>
                                        <span><?php echo e($rating->review); ?></span>
                                        <br>
                                        <small class="text-muted"><?php echo e($rating->created_at->diffForHumans()); ?></small>
                                    </div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                    <div class="text-muted">No reviews yet.</div>
                                <?php endif; ?>
                            </div>
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

                                <form action="<?php echo e(route('cart.add', $product)); ?>" method="POST" class="mb-2" id="addToCartForm">
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

                                <!-- Modal for Add to Cart Popup -->
                                <div class="modal fade" id="addToCartModal" tabindex="-1" aria-labelledby="addToCartModalLabel" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content">
                                            <div class="modal-header border-0 pb-0">
                                                <h5 class="modal-title w-100 text-center" id="addToCartModalLabel">Added to Cart</h5>
                                                <button type="button" class="btn-close position-absolute end-0 me-3 mt-2" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body text-center">
                                                <img src="<?php echo e(asset('storage/' . $product->image)); ?>" alt="<?php echo e($product->name); ?>" class="rounded mb-3" style="width: 120px; height: 120px; object-fit: cover; box-shadow: 0 2px 8px rgba(0,0,0,0.08);" loading="lazy" width="120" height="120">
                                                <h5 class="mb-1"><?php echo e($product->name); ?></h5>
                                                <div class="mb-2 text-muted">Quantity: <span id="modal-qty">1</span></div>
                                                <p class="text-success mb-0">Your item has been added to the cart.</p>
                                            </div>
                                            <div class="modal-footer justify-content-center border-0 pt-0 pb-4">
                                                <a href="<?php echo e(route('checkout')); ?>" class="btn btn-primary px-4">Continue to Payment</a>
                                                <a href="<?php echo e(route('products.index')); ?>" class="btn btn-outline-secondary px-4">View Other Items</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <?php if(auth()->guard()->check()): ?>
                                    <?php if($product->canBeRatedBy(auth()->user())): ?>
                                    <div class="mt-4">
                                        <h5>Rate this product</h5>
                                        <form action="<?php echo e(route('products.rate', $product)); ?>" method="POST" id="rating-form">
                                            <?php echo csrf_field(); ?>
                                            <div class="mb-2">
                                                <label>Rating:</label>
                                                <div id="star-rating" class="star-rating" style="font-size:2rem; cursor:pointer;">
                                                    <?php for($i=1; $i<=5; $i++): ?>
                                                        <span class="star" data-value="<?php echo e($i); ?>">☆</span>
                                                    <?php endfor; ?>
                                                </div>
                                                <input type="hidden" name="rating" id="rating-value" value="3">
                                            </div>
                                            <div class="mb-2">
                                                <label for="review">Review (optional):</label>
                                                <textarea name="review" id="review" class="form-control" rows="2"></textarea>
                                            </div>
                                            <button type="submit" class="btn btn-outline-primary">Submit Rating</button>
                                        </form>
                                        <script>
                                        document.addEventListener('DOMContentLoaded', function() {
                                            const stars = document.querySelectorAll('#star-rating .star');
                                            const ratingInput = document.getElementById('rating-value');
                                            let currentRating = 3;
                                            function setStars(rating) {
                                                stars.forEach((star, idx) => {
                                                    star.textContent = idx < rating ? '★' : '☆';
                                                });
                                            }
                                            setStars(currentRating);
                                            stars.forEach(star => {
                                                star.addEventListener('mouseover', function() {
                                                    setStars(this.dataset.value);
                                                });
                                                star.addEventListener('mouseout', function() {
                                                    setStars(currentRating);
                                                });
                                                star.addEventListener('click', function() {
                                                    currentRating = this.dataset.value;
                                                    ratingInput.value = currentRating;
                                                    setStars(currentRating);
                                                });
                                            });
                                        });
                                        </script>
                                    </div>
                                    <?php elseif($product->ratings()->where('user_id', auth()->id())->exists()): ?>
                                    <div class="alert alert-info mt-4">You have already rated this product.</div>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('addToCartForm').addEventListener('submit', function(e) {
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
            document.getElementById('modal-qty').textContent = qty;
            var modalEl = document.getElementById('addToCartModal');
            var modal = new bootstrap.Modal(modalEl);
            modal.show();
        }
    });
});
</script>

<style>
#addToCartModal .modal-content {
    border-radius: 18px;
    box-shadow: 0 4px 24px rgba(0,0,0,0.12);
    padding-top: 0.5rem;
}
#addToCartModal .modal-header {
    border-bottom: none;
}
#addToCartModal .modal-footer {
    border-top: none;
}
#addToCartModal .btn-primary {
    background: #007bff;
    border: none;
}
#addToCartModal .btn-outline-secondary {
    border: 1px solid #ced4da;
}
</style>
<?php $__env->stopSection(); ?> 
<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\sabst\momo_shop\resources\views/desktop/products/show.blade.php ENDPATH**/ ?>