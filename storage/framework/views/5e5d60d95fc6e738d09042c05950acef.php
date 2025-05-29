

<?php $__env->startSection('content'); ?>
<div class="container py-4">
    <h2 class="mb-4">Role & Permission Management</h2>
    <?php if(session('success')): ?>
        <div class="alert alert-success"><?php echo e(session('success')); ?></div>
    <?php endif; ?>
    <div class="mb-3">
        <input type="text" id="userSearch" class="form-control" placeholder="Search users by name or email...">
    </div>
    <div class="list-group" id="userList">
        <?php $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="list-group-item d-flex justify-content-between align-items-center flex-wrap user-row" data-name="<?php echo e(strtolower($user->name)); ?>" data-email="<?php echo e(strtolower($user->email)); ?>">
                <div>
                    <span class="fw-bold"><?php echo e($user->name); ?></span>
                    <span class="text-muted small">(<?php echo e($user->email); ?>)</span>
                    <?php $__currentLoopData = $user->roles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $role): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <span class="badge bg-primary ms-1"><?php echo e($role->name); ?></span>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
                <button class="btn btn-outline-secondary btn-sm manage-access-btn" data-bs-toggle="modal" data-bs-target="#manageModal" data-user='<?php echo json_encode($user, 15, 512) ?>'>Manage Access</button>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="manageModal" tabindex="-1" aria-labelledby="manageModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form id="accessForm" method="POST">
                    <?php echo csrf_field(); ?>
                    <div class="modal-header">
                        <h5 class="modal-title" id="manageModalLabel">Manage Access</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div id="modalUserInfo" class="mb-3"></div>
                        <ul class="nav nav-tabs mb-3" id="accessTab" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="roles-tab" data-bs-toggle="tab" data-bs-target="#rolesTab" type="button" role="tab">Roles</button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="permissions-tab" data-bs-toggle="tab" data-bs-target="#permissionsTab" type="button" role="tab">Permissions</button>
                            </li>
                        </ul>
                        <div class="tab-content" id="accessTabContent">
                            <div class="tab-pane fade show active" id="rolesTab" role="tabpanel">
                                <div class="row">
                                    <?php $__currentLoopData = $roles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $role): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <div class="col-6 col-md-4 mb-2">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="roles[]" value="<?php echo e($role->name); ?>" id="modal-role-<?php echo e($role->id); ?>">
                                                <label class="form-check-label" for="modal-role-<?php echo e($role->id); ?>"><?php echo e($role->name); ?></label>
                                            </div>
                                        </div>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </div>
                            </div>
                            <div class="tab-pane fade" id="permissionsTab" role="tabpanel">
                                <?php
                                    $grouped = $permissions->groupBy(fn($p) => explode(' ', $p->name)[0]);
                                ?>
                                <?php $__currentLoopData = $grouped; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $group => $perms): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <div class="mb-2">
                                        <div class="fw-bold text-capitalize mb-1"><?php echo e($group); ?></div>
                                        <div class="row">
                                            <?php $__currentLoopData = $perms; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $permission): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <div class="col-6 col-md-4 mb-1">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" name="permissions[]" value="<?php echo e($permission->name); ?>" id="modal-perm-<?php echo e($permission->id); ?>">
                                                        <label class="form-check-label" for="modal-perm-<?php echo e($permission->id); ?>"><?php echo e($permission->name); ?></label>
                                                    </div>
                                                </div>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        </div>
                                    </div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary" id="saveBtn">
                            <span id="saveSpinner" class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                            Save
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('styles'); ?>
<style>
    .user-row { cursor: pointer; }
    .badge { font-size: 0.85em; }
    @media (max-width: 600px) {
        .modal-lg { max-width: 98vw; }
    }
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
    const users = <?php echo json_encode($users, 15, 512) ?>;
    const roles = <?php echo json_encode($roles, 15, 512) ?>;
    const permissions = <?php echo json_encode($permissions, 15, 512) ?>;
    let selectedUser = null;

    // Live search
    document.getElementById('userSearch').addEventListener('input', function() {
        const val = this.value.toLowerCase();
        document.querySelectorAll('.user-row').forEach(row => {
            const name = row.getAttribute('data-name');
            const email = row.getAttribute('data-email');
            row.style.display = (name.includes(val) || email.includes(val)) ? '' : 'none';
        });
    });

    // Modal open handler
    document.querySelectorAll('.manage-access-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const user = JSON.parse(this.getAttribute('data-user'));
            selectedUser = user;
            document.getElementById('modalUserInfo').innerHTML = `<b>${user.name}</b> <span class='text-muted small'>(${user.email})</span>`;
            // Reset all checkboxes
            document.querySelectorAll('#manageModal input[type=checkbox]').forEach(cb => cb.checked = false);
            // Set roles
            if(user.roles) {
                user.roles.forEach(role => {
                    const cb = document.querySelector(`#manageModal input[name='roles[]'][value='${role.name}']`);
                    if(cb) cb.checked = true;
                });
            }
            // Set permissions
            if(user.permissions) {
                user.permissions.forEach(perm => {
                    const cb = document.querySelector(`#manageModal input[name='permissions[]'][value='${perm.name}']`);
                    if(cb) cb.checked = true;
                });
            }
            // Set form action
            document.getElementById('accessForm').action = `/admin/roles/${user.id}`;
        });
    });

    // Show spinner on save
    document.getElementById('accessForm').addEventListener('submit', function() {
        document.getElementById('saveSpinner').classList.remove('d-none');
        document.getElementById('saveBtn').setAttribute('disabled', 'disabled');
    });
</script>
<?php $__env->stopPush(); ?> 
<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\sabst\momo_shop\resources\views/admin/roles/index.blade.php ENDPATH**/ ?>