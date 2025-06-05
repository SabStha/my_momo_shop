<?php $__env->startSection('title', 'Inventory Management'); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-md-6">
            <h2>Inventory Management</h2>
        </div>
        <div class="col-md-6 text-end">
            <a href="<?php echo e(route('admin.inventory.create')); ?>" class="btn btn-primary">
                <i class="fas fa-plus"></i> Add New Item
            </a>
            <a href="<?php echo e(route('admin.inventory.categories')); ?>" class="btn btn-secondary">
                <i class="fas fa-tags"></i> Manage Categories
            </a>
            <a href="<?php echo e(route('admin.suppliers.index')); ?>" class="btn btn-info">
                <i class="fas fa-truck"></i> Manage Suppliers
            </a>
            <a href="<?php echo e(route('admin.inventory.checks.index')); ?>" class="btn btn-warning">
                <i class="fas fa-clipboard-check"></i> Daily Stock Check
            </a>
            <a href="<?php echo e(route('admin.inventory.manage')); ?>" class="btn btn-success">
                <i class="fas fa-shopping-cart"></i> Manage Inventory
            </a>
        </div>
    </div>

    <?php if(session('success')): ?>
        <div class="alert alert-success">
            <?php echo e(session('success')); ?>

        </div>
    <?php endif; ?>

    <?php if(session('error')): ?>
        <div class="alert alert-danger">
            <?php echo e(session('error')); ?>

        </div>
    <?php endif; ?>

    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <h5 class="card-title">Low Stock Items</h5>
                    <h2 class="card-text"><?php echo e($lowStockCount); ?></h2>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>SKU</th>
                            <th>Name</th>
                            <th>Category</th>
                            <th>Quantity</th>
                            <th>Unit</th>
                            <th>Unit Price</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr>
                                <td><?php echo e($item->sku); ?></td>
                                <td><?php echo e($item->name); ?></td>
                                <td><?php echo e($item->category->name ?? 'Uncategorized'); ?></td>
                                <td>
                                    <span class="<?php echo e($item->needsRestock() ? 'text-danger' : ''); ?>">
                                        <?php echo e($item->quantity); ?> <?php echo e($item->unit); ?>

                                    </span>
                                </td>
                                <td><?php echo e($item->unit); ?></td>
                                <td>$<?php echo e(number_format($item->unit_price, 2)); ?></td>
                                <td>
                                    <span class="badge bg-<?php echo e($item->status === 'active' ? 'success' : ($item->status === 'inactive' ? 'warning' : 'danger')); ?>">
                                        <?php echo e(ucfirst($item->status)); ?>

                                    </span>
                                </td>
                                <td>
                                    <div class="btn-group">
                                        <a href="<?php echo e(route('admin.inventory.show', $item)); ?>" class="btn btn-sm btn-info">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="<?php echo e(route('admin.inventory.edit', $item)); ?>" class="btn btn-sm btn-primary">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="<?php echo e(route('admin.inventory.destroy', $item)); ?>" method="POST" class="d-inline">
                                            <?php echo csrf_field(); ?>
                                            <?php echo method_field('DELETE'); ?>
                                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this item?')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                <?php echo e($items->links()); ?>

            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?> 
<?php echo $__env->make('desktop.admin.layouts.admin', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\sabst\momo_shop\resources\views/desktop/admin/inventory/index.blade.php ENDPATH**/ ?>