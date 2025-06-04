

<?php $__env->startSection('content'); ?>
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h4 class="mb-0">Order #<?php echo e($order->id); ?></h4>
                        <a href="<?php echo e(route('my-account')); ?>" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-2"></i>Back to My Account
                        </a>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h5>Order Details</h5>
                            <p class="mb-1"><strong>Date:</strong> <?php echo e($order->created_at->format('M d, Y H:i')); ?></p>
                            <p class="mb-1"><strong>Status:</strong> 
                                <span class="badge bg-<?php echo e($order->status_color); ?>">
                                    <?php echo e(ucfirst($order->status)); ?>

                                </span>
                            </p>
                            <p class="mb-1"><strong>Payment Status:</strong> 
                                <span class="badge bg-<?php echo e($order->payment_status === 'paid' ? 'success' : 'warning'); ?>">
                                    <?php echo e(ucfirst($order->payment_status)); ?>

                                </span>
                            </p>
                        </div>
                        <div class="col-md-6">
                            <h5>Shipping Address</h5>
                            <?php if($order->shipping_address): ?>
                                <p class="mb-1"><?php echo e($order->shipping_address['address'] ?? ''); ?></p>
                                <p class="mb-1"><?php echo e($order->shipping_address['city'] ?? ''); ?>, <?php echo e($order->shipping_address['state'] ?? ''); ?></p>
                                <p class="mb-1"><?php echo e($order->shipping_address['postal_code'] ?? ''); ?></p>
                            <?php else: ?>
                                <p class="text-muted">No shipping address provided</p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <h5>Order Items</h5>
                    <div class="table-responsive">
                        <table class="table">
                            <thead class="table-light">
                                <tr>
                                    <th>Product</th>
                                    <th>Price</th>
                                    <th>Quantity</th>
                                    <th class="text-end">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__currentLoopData = $order->items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <?php if($item->product->image): ?>
                                                    <img src="<?php echo e(asset('storage/' . $item->product->image)); ?>" 
                                                         alt="<?php echo e($item->product->name); ?>" 
                                                         class="me-2" 
                                                         style="width: 50px; height: 50px; object-fit: cover;">
                                                <?php endif; ?>
                                                <div>
                                                    <h6 class="mb-0"><?php echo e($item->product->name); ?></h6>
                                                    <?php if($item->product->description): ?>
                                                        <small class="text-muted"><?php echo e(Str::limit($item->product->description, 50)); ?></small>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </td>
                                        <td>Rs. <?php echo e(number_format($item->price, 2)); ?></td>
                                        <td><?php echo e($item->quantity); ?></td>
                                        <td class="text-end">Rs. <?php echo e(number_format($item->price * $item->quantity, 2)); ?></td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="3" class="text-end"><strong>Total:</strong></td>
                                    <td class="text-end"><strong>Rs. <?php echo e(number_format($order->total, 2)); ?></strong></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <?php if($order->notes): ?>
                        <div class="mt-4">
                            <h5>Order Notes</h5>
                            <p class="text-muted"><?php echo e($order->notes); ?></p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?> 
<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\sabst\momo_shop\resources\views/user/my-account/order-details.blade.php ENDPATH**/ ?>