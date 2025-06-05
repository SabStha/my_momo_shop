

<?php $__env->startSection('content'); ?>
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body text-center">
                    <h1 class="mb-4">Scan QR Code</h1>
                    <p class="text-muted mb-4">Scan the QR code to top up your wallet</p>
                    
                    <div id="reader" class="mb-4"></div>
                    
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        Make sure the QR code is clear and well-lit
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script src="https://unpkg.com/html5-qrcode"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const html5QrCode = new Html5Qrcode("reader");
    
    html5QrCode.start(
        { facingMode: "environment" },
        {
            fps: 10,
            qrbox: { width: 250, height: 250 }
        },
        (decodedText, decodedResult) => {
            // Handle the scanned code
            html5QrCode.stop().then(() => {
                // Send the scanned data to the server
                fetch('<?php echo e(route("wallet.top-up")); ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>'
                    },
                    body: JSON.stringify({
                        qr_data: decodedText
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        window.location.href = '<?php echo e(route("wallet")); ?>';
                    } else {
                        alert(data.message || 'Failed to process QR code');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while processing the QR code');
                });
            });
        },
        (errorMessage) => {
            // Handle scan error
        }
    );
});
</script>
<?php $__env->stopPush(); ?> 
<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\sabst\momo_shop\resources\views/wallet/scan.blade.php ENDPATH**/ ?>