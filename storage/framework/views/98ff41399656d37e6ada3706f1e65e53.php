

<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col">
            <h1>Wallet Management</h1>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Generate Top-Up QR Code</h5>
                </div>
                <div class="card-body">
                    <form id="qrForm" class="mb-4">
                        <?php echo csrf_field(); ?>
                        <div class="mb-3">
                            <label for="amount" class="form-label">Amount to Top Up</label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" class="form-control" id="amount" name="amount" step="0.01" min="0.01" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">Admin Password</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>

                        <button type="submit" class="btn btn-primary" id="generateBtn">
                            <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true" id="generateSpinner"></span>
                            Generate QR Code
                        </button>
                    </form>

                    <div id="qrCodeContainer" class="text-center" style="display: none;">
                        <div class="mb-3">
                            <img id="qrCode" src="" alt="Top-up QR Code" class="img-fluid">
                        </div>
                        <div class="alert alert-info">
                            <p class="mb-0">This QR code will expire in 15 minutes.</p>
                            <p class="mb-0">Amount: $<span id="qrAmount">0.00</span></p>
                        </div>
                        <button class="btn btn-success" onclick="window.print()">
                            <i class="fas fa-print"></i> Print QR Code
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Recent Top-Ups</h5>
                </div>
                <div class="card-body">
                    <div id="recentTransactions">
                        <div class="text-center text-muted">
                            <p>No recent transactions</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('styles'); ?>
<link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" rel="stylesheet">
<?php $__env->stopPush(); ?>

<?php $__env->startPush('scripts'); ?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const qrForm = document.getElementById('qrForm');
    const generateBtn = document.getElementById('generateBtn');
    const generateSpinner = document.getElementById('generateSpinner');
    const qrCodeContainer = document.getElementById('qrCodeContainer');
    const qrCode = document.getElementById('qrCode');
    const qrAmount = document.getElementById('qrAmount');

    // Configure toastr
    toastr.options = {
        "closeButton": true,
        "progressBar": true,
        "positionClass": "toast-top-right",
        "timeOut": "3000"
    };

    qrForm.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        try {
            generateBtn.disabled = true;
            generateSpinner.classList.remove('d-none');

            const formData = new FormData(this);
            const response = await fetch('<?php echo e(route("admin.wallet.generate-qr")); ?>', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>',
                    'Accept': 'application/json'
                }
            });

            const data = await response.json();

            if (!response.ok) {
                throw new Error(data.message || 'Failed to generate QR code');
            }

            if (data.success) {
                qrCode.src = data.qr_code;
                qrAmount.textContent = formData.get('amount');
                qrCodeContainer.style.display = 'block';
                toastr.success('QR code generated successfully');
            } else {
                throw new Error(data.message || 'Failed to generate QR code');
            }
        } catch (error) {
            console.error('Error generating QR code:', error);
            toastr.error(error.message || 'An error occurred while generating the QR code');
        } finally {
            generateBtn.disabled = false;
            generateSpinner.classList.add('d-none');
        }
    });
});
</script>
<?php $__env->stopPush(); ?> 
<?php echo $__env->make('layouts.admin', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\sabst\momo_shop\resources\views/admin/wallet/manage.blade.php ENDPATH**/ ?>