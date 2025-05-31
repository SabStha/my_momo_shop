<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag; ?>
<?php foreach($attributes->onlyProps(['type' => 'pos']) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
} ?>
<?php $attributes = $attributes->exceptProps(['type' => 'pos']); ?>
<?php foreach (array_filter((['type' => 'pos']), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
} ?>
<?php $__defined_vars = get_defined_vars(); ?>
<?php foreach ($attributes as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
} ?>
<?php unset($__defined_vars); ?>

<div class="modal fade" id="verificationModal" tabindex="-1" aria-labelledby="verificationModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="verificationModalLabel">
                    <?php echo e($type === 'pos' ? 'POS Access Verification' : 'Payment Manager Access Verification'); ?>

                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="verificationForm" method="POST" action="<?php echo e(route('verify.access', ['type' => $type])); ?>">
                    <?php echo csrf_field(); ?>
                    <div class="mb-3">
                        <label for="id" class="form-label">ID</label>
                        <input type="text" class="form-control" id="id" name="id" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    <div class="alert alert-danger d-none" id="errorMessage"></div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="verifyButton">Verify</button>
            </div>
        </div>
    </div>
</div>

<?php $__env->startPush('scripts'); ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const modal = new bootstrap.Modal(document.getElementById('verificationModal'));
    const form = document.getElementById('verificationForm');
    const errorMessage = document.getElementById('errorMessage');
    const verifyButton = document.getElementById('verifyButton');

    // Show modal if verification is required
    <?php if($showVerificationModal ?? false): ?>
        modal.show();
    <?php endif; ?>

    // Prevent modal from being closed
    document.getElementById('verificationModal').addEventListener('hide.bs.modal', function (event) {
        event.preventDefault();
    });

    verifyButton.addEventListener('click', function() {
        const id = document.getElementById('id').value;
        const password = document.getElementById('password').value;

        if (!id || !password) {
            errorMessage.textContent = 'Please enter both ID and password';
            errorMessage.classList.remove('d-none');
            return;
        }

        // Submit form via AJAX
        fetch(form.action, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({
                id: id,
                password: password
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                modal.hide();
                window.location.reload();
            } else {
                errorMessage.textContent = data.message || 'Invalid credentials';
                errorMessage.classList.remove('d-none');
            }
        })
        .catch(error => {
            errorMessage.textContent = 'An error occurred. Please try again.';
            errorMessage.classList.remove('d-none');
        });
    });
});
</script>
<?php $__env->stopPush(); ?>
<?php /**PATH C:\Users\sabst\momo_shop\resources\views/components/verification-modal.blade.php ENDPATH**/ ?>