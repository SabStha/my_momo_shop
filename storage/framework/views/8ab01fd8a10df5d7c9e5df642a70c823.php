

<?php $__env->startSection('content'); ?>
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="card-title mb-0">Scan QR Code</h5>
                </div>
                <div class="card-body">
                    <div class="text-center mb-4">
                        <p class="text-muted">Scan a wallet top-up QR code to process the payment</p>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mx-auto">
                            <!-- Camera Scanner -->
                            <div id="reader" class="mb-4"></div>
                            
                            <!-- Image Upload -->
                            <div class="text-center mb-4">
                                <p class="text-muted">Or upload a QR code image</p>
                                <div class="input-group">
                                    <input type="file" 
                                           class="form-control" 
                                           id="qrImageInput" 
                                           accept="image/*"
                                           aria-label="Upload QR Code">
                                    <button class="btn btn-warning" type="button" id="scanImageBtn">
                                        Scan Image
                                    </button>
                                </div>
                                <small class="text-muted d-block mt-2">Supported formats: JPG, PNG, GIF</small>
                            </div>

                            <div id="result" class="alert d-none"></div>
                        </div>
                    </div>
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
#reader {
    width: 100%;
    max-width: 400px;
    margin: 0 auto;
}
#reader video {
    border-radius: 10px;
}
.alert {
    border-radius: 10px;
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

<!-- Include HTML5 QR Code library -->
<script src="https://unpkg.com/html5-qrcode"></script>
<!-- Include jsQR library -->
<script src="https://cdn.jsdelivr.net/npm/jsqr@1.4.0/dist/jsQR.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const resultDiv = document.getElementById('result');
    const qrImageInput = document.getElementById('qrImageInput');
    const scanImageBtn = document.getElementById('scanImageBtn');
    let html5QrcodeScanner = null;

    function processQRCode(decodedText) {
        try {
            // Show loading state
            resultDiv.className = 'alert alert-info';
            resultDiv.textContent = 'Processing QR code...';
            resultDiv.classList.remove('d-none');

            // Send to server
            fetch('<?php echo e(route("wallet.process-topup")); ?>', {
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
                    resultDiv.className = 'alert alert-success';
                    resultDiv.innerHTML = `
                        <h5>Top-up Successful!</h5>
                        <p>Amount: $${data.amount}</p>
                        <p>New Balance: $${data.new_balance}</p>
                    `;
                } else {
                    resultDiv.className = 'alert alert-danger';
                    resultDiv.textContent = data.message;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                resultDiv.className = 'alert alert-danger';
                resultDiv.textContent = 'Failed to process QR code. Please try again.';
            })
            .finally(() => {
                // Restart scanner after 3 seconds
                setTimeout(() => {
                    resultDiv.classList.add('d-none');
                    startScanner();
                }, 3000);
            });

        } catch (error) {
            console.error('Error:', error);
            resultDiv.className = 'alert alert-danger';
            resultDiv.textContent = 'Invalid QR code format';
            
            // Restart scanner after 3 seconds
            setTimeout(() => {
                resultDiv.classList.add('d-none');
                startScanner();
            }, 3000);
        }
    }

    function onScanSuccess(decodedText, decodedResult) {
        // Stop scanning
        if (html5QrcodeScanner) {
            html5QrcodeScanner.stop();
        }
        processQRCode(decodedText);
    }

    function onScanFailure(error) {
        // Handle scan failure, usually ignore
        console.warn(`QR code scanning failed: ${error}`);
    }

    function startScanner() {
        if (html5QrcodeScanner) {
            html5QrcodeScanner.clear();
        }

        html5QrcodeScanner = new Html5QrcodeScanner(
            "reader",
            { 
                fps: 10,
                qrbox: { width: 250, height: 250 },
                aspectRatio: 1.0
            }
        );
        html5QrcodeScanner.render(onScanSuccess, onScanFailure);
    }

    // Handle image upload and scanning
    scanImageBtn.addEventListener('click', function() {
        const file = qrImageInput.files[0];
        if (!file) {
            alert('Please select an image file');
            return;
        }

        // Show loading state
        resultDiv.className = 'alert alert-info';
        resultDiv.textContent = 'Processing image...';
        resultDiv.classList.remove('d-none');

        const reader = new FileReader();
        reader.onload = function(e) {
            const img = new Image();
            img.onload = function() {
                // Create a canvas to draw the image
                const canvas = document.createElement('canvas');
                const context = canvas.getContext('2d');
                
                // Set canvas size to match image
                canvas.width = img.width;
                canvas.height = img.height;
                
                // Draw image on canvas
                context.drawImage(img, 0, 0);
                
                // Get image data
                const imageData = context.getImageData(0, 0, canvas.width, canvas.height);
                
                // Scan for QR code
                const code = jsQR(imageData.data, imageData.width, imageData.height, {
                    inversionAttempts: "dontInvert",
                });

                if (code) {
                    // QR code found
                    processQRCode(code.data);
                } else {
                    // No QR code found
                    resultDiv.className = 'alert alert-danger';
                    resultDiv.textContent = 'No QR code found in the image. Please try again with a clearer image.';
                    resultDiv.classList.remove('d-none');
                    
                    // Restart scanner after 3 seconds
                    setTimeout(() => {
                        resultDiv.classList.add('d-none');
                        startScanner();
                    }, 3000);
                }
            };
            img.src = e.target.result;
        };
        reader.readAsDataURL(file);
    });

    // Start scanner
    startScanner();
});
</script>
<?php $__env->stopSection(); ?> 
<?php echo $__env->make('desktop.layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\sabst\momo_shop\resources\views/desktop/wallet/scan.blade.php ENDPATH**/ ?>