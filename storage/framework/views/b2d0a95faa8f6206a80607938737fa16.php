<?php $__env->startSection('content'); ?>
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="card-title mb-0">Admin Wallet QR Generator</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <form id="topUpForm" class="mb-4">
                                <?php echo csrf_field(); ?>
                                <div class="mb-3">
                                    <label for="amount" class="form-label">Amount to Top-Up</label>
                                    <div class="input-group">
                                        <span class="input-group-text">$</span>
                                        <input type="text"
                                               class="form-control"
                                               id="amount"
                                               name="amount"
                                               placeholder="Enter amount"
                                               required>
                                    </div>
                                    <small class="text-muted">Enter amount between $1 and $10,000</small>
                                </div>
                                <div class="mb-3">
                                    <label for="password" class="form-label">Admin Password</label>
                                    <input type="password"
                                           class="form-control"
                                           id="password"
                                           name="password"
                                           placeholder="Enter admin password"
                                           required>
                                </div>
                                <button type="submit" class="btn btn-warning">Generate QR Code</button>
                            </form>
                        </div>
                        <div class="col-md-6 text-center">
                            <div id="qrCodeContainer" class="d-none">
                                <div class="mb-3">
                                    <img id="qrImage" src="" alt="QR Code" class="img-fluid">
                                </div>
                                <div class="alert alert-info">
                                    <p class="mb-0">Scan this QR code to top up wallet</p>
                                    <p class="mb-0">QR code expires in <span id="countdown">15:00</span></p>
                                </div>
                                <button id="downloadQR" class="btn btn-outline-warning mt-2">
                                    <i class="fas fa-download me-2"></i>Download QR Code
                                </button>
                            </div>
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
.btn-outline-warning {
    color: #f97316;
    border-color: #f97316;
}
.btn-outline-warning:hover {
    background-color: #f97316;
    border-color: #f97316;
    color: white;
}
.input-group-text {
    background-color: #f8f9fa;
    border: 1px solid #ced4da;
}
#amount {
    border: 1px solid #ced4da;
    padding: 0.375rem 0.75rem;
    font-size: 1rem;
    line-height: 1.5;
    color: #212529;
    background-color: #fff;
}
</style>

<!-- ✅ Include the correct JS QR Library -->
<script src="https://cdn.jsdelivr.net/npm/qrcode/build/qrcode.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const topUpForm = document.getElementById('topUpForm');
    const qrCodeContainer = document.getElementById('qrCodeContainer');
    const qrImage = document.getElementById('qrImage');
    const countdownElement = document.getElementById('countdown');
    const amountInput = document.getElementById('amount');
    const passwordInput = document.getElementById('password');
    const downloadQRBtn = document.getElementById('downloadQR');
    let countdownInterval;

    amountInput.addEventListener('input', function (e) {
        let value = e.target.value.replace(/[^\d.]/g, '');
        const parts = value.split('.');
        if (parts.length > 2) {
            value = parts[0] + '.' + parts.slice(1).join('');
        }
        if (parts.length > 1) {
            value = parts[0] + '.' + parts[1].slice(0, 2);
        }
        e.target.value = value;
    });

    function startCountdown() {
        let timeLeft = 15 * 60;
        clearInterval(countdownInterval);
        countdownInterval = setInterval(() => {
            const minutes = Math.floor(timeLeft / 60);
            const seconds = timeLeft % 60;
            countdownElement.textContent = `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
            if (timeLeft <= 0) {
                clearInterval(countdownInterval);
                qrCodeContainer.classList.add('d-none');
                qrImage.src = '';
            }
            timeLeft--;
        }, 1000);
    }

    function downloadQRCode() {
        const link = document.createElement('a');
        link.download = `wallet-topup-${amountInput.value}.png`;
        link.href = qrImage.src;
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }

    downloadQRBtn.addEventListener('click', downloadQRCode);

    topUpForm.addEventListener('submit', function (e) {
        e.preventDefault();
        const amount = parseFloat(amountInput.value);
        const password = passwordInput.value;

        if (isNaN(amount) || amount < 1 || amount > 10000) {
            alert('Please enter a valid amount between $1 and $10,000');
            return;
        }

        const submitButton = this.querySelector('button[type="submit"]');
        const originalText = submitButton.innerHTML;
        submitButton.disabled = true;
        submitButton.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Generating...';

        // Send request to server
        fetch('<?php echo e(route("wallet.generate-qr")); ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                amount: amount,
                password: password
            })
        })
        .then(response => response.json())
        .then(data => {
            submitButton.disabled = false;
            submitButton.innerHTML = originalText;

            if (data.success) {
                qrImage.src = data.qr_code;
                qrCodeContainer.classList.remove('d-none');
                startCountdown();
            } else {
                alert(data.message || 'Failed to generate QR code');
            }
        })
        .catch(error => {
            submitButton.disabled = false;
            submitButton.innerHTML = originalText;
            alert('Failed to generate QR code. Please try again.');
            console.error('Error:', error);
        });
    });
});
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('desktop.layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\sabst\momo_shop\resources\views/desktop/wallet/topup.blade.php ENDPATH**/ ?>