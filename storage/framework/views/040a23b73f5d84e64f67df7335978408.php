

<?php $__env->startSection('content'); ?>
<div class="container" style="max-width: 700px; margin: 2rem auto;">

    <h1 style="font-weight: 700; margin-bottom: 1.5rem;">Notifications</h1>

    <!-- Filter Controls -->
    <div class="mb-3 d-flex gap-2">
        <a href="?filter=unread" class="btn btn-outline-danger btn-sm <?php echo e(request('filter') === 'unread' ? 'active' : ''); ?>">Unread</a>
        <a href="?filter=all" class="btn btn-outline-secondary btn-sm <?php echo e(request('filter') !== 'unread' ? 'active' : ''); ?>">All</a>
    </div>

    <!-- Notification List -->
    <?php if($notifications->isEmpty()): ?>
        <div class="text-center text-muted" style="margin-top: 3rem;">
            <i class="fas fa-bell-slash fa-2x mb-2"></i>
            <p>No notifications yet.</p>
        </div>
    <?php else: ?>
        <div class="notifications-list d-flex flex-column gap-3">
            <?php $__currentLoopData = $notifications; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $notification): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="notification-item"
                    onclick="markAsRead('<?php echo e($notification['id']); ?>', this)"
                    style="
                        background: #fff;
                        border-radius: 12px;
                        padding: 1rem;
                        box-shadow: 0 2px 8px rgba(0,0,0,0.06);
                        cursor: pointer;
                        transition: box-shadow 0.2s ease;
                        <?php echo e(!$notification['read'] ? 'border-left: 4px solid #c1440e;' : ''); ?>

                    ">
                    <div class="notification-header d-flex justify-content-between align-items-center mb-2">
                        <h3 class="mb-0" style="font-size: 1.1rem; font-weight: 600; color: #333;">
                            <?php echo e($notification['title']); ?>

                        </h3>
                        <span style="color: #888; font-size: 0.9rem;">
                            <?php echo e(\Carbon\Carbon::parse($notification['time'])->diffForHumans()); ?>

                        </span>
                    </div>
                    <p class="mb-0" style="color: #666;"><?php echo e($notification['message']); ?></p>

                    <?php if(!$notification['read']): ?>
                        <div style="margin-top: 0.5rem;">
                            <span style="background: #fff0f0; color: #c1440e; padding: 0.2rem 0.5rem; border-radius: 4px; font-size: 0.8rem;">
                                New
                            </span>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    <?php endif; ?>

    <!-- Pagination (if applicable) -->
    <div class="mt-4">
        <?php echo e($notifications->links()); ?>

    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
    function markAsRead(id, el) {
        fetch(`/notifications/${id}/mark-read`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>',
                'Content-Type': 'application/json'
            },
        })
        .then(res => {
            if (res.ok) {
                el.style.borderLeft = 'none';
                const badge = el.querySelector('span');
                if (badge) badge.remove();
            }
        });
    }
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\sabst\momo_shop\resources\views/desktop/notifications.blade.php ENDPATH**/ ?>