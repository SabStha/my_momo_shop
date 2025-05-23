<?php $__env->startSection('content'); ?>
<div class="container py-5">
    <div class="row">
        <!-- Order Summary Card -->
        <div class="col-lg-4 mb-4">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="card-title mb-0">Order Summary</h5>
                </div>
                <div class="card-body">
                    <?php $__currentLoopData = $cartItems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="d-flex align-items-center">
                                <?php if($item->product->image): ?>
                                    <img src="<?php echo e(asset('storage/' . $item->product->image)); ?>" 
                                         alt="<?php echo e($item->product->name); ?>" 
                                         class="rounded me-2" 
                                         style="width: 50px; height: 50px; object-fit: cover;">
                                <?php endif; ?>
                                <div>
                                    <h6 class="mb-0"><?php echo e($item->product->name); ?></h6>
                                    <small class="text-muted">Qty: <?php echo e($item->quantity); ?></small>
                                </div>
                            </div>
                            <span>$<?php echo e(number_format($item->product->price * $item->quantity, 2)); ?></span>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    <hr>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Subtotal</span>
                        <span>$<?php echo e(number_format($subtotal, 2)); ?></span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Delivery Fee</span>
                        <span>$<?php echo e(number_format($deliveryFee ?? 0, 2)); ?></span>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between fw-bold">
                        <span>Total</span>
                        <span class="text-warning">$<?php echo e(number_format($total, 2)); ?></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Checkout Form -->
        <div class="col-lg-8">
            <form action="<?php echo e(route('checkout.submit')); ?>" method="POST" id="checkoutForm">
                <?php echo csrf_field(); ?>
                
                <!-- Contact Information -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-white">
                        <h5 class="card-title mb-0">Contact Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="name" class="form-label">Full Name</label>
                                <input type="text" class="form-control" id="name" name="name" required 
                                       value="<?php echo e(old('name', auth()->user()->name ?? '')); ?>">
                            </div>
                            <div class="col-md-6">
                                <label for="phone" class="form-label">Phone Number</label>
                                <input type="tel" class="form-control" id="phone" name="phone" required 
                                       value="<?php echo e(old('phone', auth()->user()->phone ?? '')); ?>">
                            </div>
                            <div class="col-12">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email" required 
                                       value="<?php echo e(old('email', auth()->user()->email ?? '')); ?>">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Delivery Details -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-white">
                        <h5 class="card-title mb-0">Delivery Details</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Delivery Method</label>
                            <div class="d-flex gap-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="delivery_method" 
                                           id="delivery" value="delivery" checked>
                                    <label class="form-check-label" for="delivery">
                                        Delivery
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="delivery_method" 
                                           id="pickup" value="pickup">
                                    <label class="form-check-label" for="pickup">
                                        Pickup
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div id="addressFields">
                            <div class="mb-3">
                                <label for="address" class="form-label">Delivery Address</label>
                                <textarea class="form-control" id="address" name="address" rows="3" required><?php echo e(old('address')); ?></textarea>
                            </div>
                            <div class="mb-3">
                                <label for="delivery_note" class="form-label">Delivery Note (Optional)</label>
                                <textarea class="form-control" id="delivery_note" name="delivery_note" rows="2"><?php echo e(old('delivery_note')); ?></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Payment Method -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-white">
                        <h5 class="card-title mb-0">Payment Method</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex gap-3">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="payment_method" 
                                       id="cod" value="cod" checked>
                                <label class="form-check-label" for="cod">
                                    Cash on Delivery
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="payment_method" 
                                       id="esewa" value="esewa">
                                <label class="form-check-label" for="esewa">
                                    eSewa
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Sticky Place Order Button -->
    <div class="position-fixed bottom-0 start-0 end-0 bg-white border-top py-3 px-4" style="z-index: 1000;">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h5 class="mb-0">Total: <span class="text-warning">$<?php echo e(number_format($total, 2)); ?></span></h5>
                </div>
                <div class="col-md-6 text-md-end">
                    <button type="submit" form="checkoutForm" class="btn btn-warning btn-lg px-5">
                        Place Order
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.card {
    border: none;
    border-radius: 10px;
}
.card-header {
    border-bottom: 1px solid rgba(0,0,0,.125);
    padding: 1rem;
}
.form-control:focus {
    border-color: #f97316;
    box-shadow: 0 0 0 0.25rem rgba(249, 115, 22, 0.25);
}
.btn-warning {
    background-color: #f97316;
    border-color: #f97316;
    color: white;
}
.btn-warning:hover {
    background-color: #ea580c;
    border-color: #ea580c;
    color: white;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const deliveryMethod = document.querySelectorAll('input[name="delivery_method"]');
    const addressFields = document.getElementById('addressFields');

    deliveryMethod.forEach(radio => {
        radio.addEventListener('change', function() {
            if (this.value === 'pickup') {
                addressFields.style.display = 'none';
                document.getElementById('address').required = false;
            } else {
                addressFields.style.display = 'block';
                document.getElementById('address').required = true;
            }
        });
    });
});
</script>
<?php $__env->stopSection(); ?> 
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\sabst\momo_shop\resources\views/checkout.blade.php ENDPATH**/ ?>