@extends('layouts.app')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="card-title mb-0">Admin QR Code Generator</h5>
                </div>
                <div class="card-body">
                    <ul class="nav nav-tabs mb-4" id="qrTabs" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" id="wallet-tab" data-bs-toggle="tab" href="#wallet" role="tab">
                                Wallet Top-up
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="pwa-tab" data-bs-toggle="tab" href="#pwa" role="tab">
                                PWA Install
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="product-tab" data-bs-toggle="tab" href="#product" role="tab">
                                Product Info
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="orders-tab" data-bs-toggle="tab" href="#orders" role="tab">
                                Order Details
                            </a>
                        </li>
                    </ul>

                    <div class="tab-content" id="qrTabContent">
                        <!-- Wallet Top-up Tab -->
                        <div class="tab-pane fade show active" id="wallet" role="tabpanel">
                            <form id="walletForm" class="mb-4">
                                @csrf
                                <div class="mb-3">
                                    <label for="amount" class="form-label">Amount to Top-Up</label>
                                    <div class="input-group">
                                        <span class="input-group-text">$</span>
                                        <input type="text" class="form-control" id="amount" name="amount" required>
                                    </div>
                                    <small class="text-muted">Enter amount between $1 and $10,000</small>
                                </div>
                                <div class="mb-3">
                                    <label for="password" class="form-label">Admin Password</label>
                                    <input type="password" class="form-control" id="password" name="password" required>
                                </div>
                                <button type="submit" class="btn btn-warning">Generate QR Code</button>
                            </form>
                        </div>

                        <!-- PWA Install Tab -->
                        <div class="tab-pane fade" id="pwa" role="tabpanel">
                            <div class="text-center">
                                <p class="mb-4">Generate a QR code for users to install the PWA</p>
                                <button type="button" id="generatePWAQR" class="btn btn-primary">Generate PWA QR Code</button>
                            </div>
                        </div>

                        <!-- Product Info Tab -->
                        <div class="tab-pane fade" id="product" role="tabpanel">
                            <form id="productForm" class="mb-4">
                                @csrf
                                <div class="mb-3">
                                    <label for="product_id" class="form-label">Select Product</label>
                                    <select class="form-select" id="product_id" name="product_id" required>
                                        <option value="">Choose a product...</option>
                                        @foreach($products as $product)
                                            <option value="{{ $product->id }}">{{ $product->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <button type="submit" class="btn btn-primary">Generate Product QR Code</button>
                            </form>
                        </div>

                        <!-- Order Details Tab -->
                        <div class="tab-pane fade" id="orders" role="tabpanel">
                            <form id="orderForm" class="mb-4">
                                @csrf
                                <div class="mb-3">
                                    <label for="user_id" class="form-label">Select User</label>
                                    <select class="form-select" id="user_id" name="user_id" required>
                                        <option value="">Choose a user...</option>
                                        @foreach($users as $user)
                                            <option value="{{ $user->id }}">{{ $user->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <button type="submit" class="btn btn-primary">View Order Details</button>
                            </form>
                            <div id="orderDetails" class="mt-4"></div>
                        </div>
                    </div>

                    <!-- QR Code Display -->
                    <div id="qrCodeContainer" class="text-center mt-4 d-none">
                        <div class="mb-3">
                            <img id="qrImage" src="" alt="QR Code" class="img-fluid">
                        </div>
                        <div class="alert alert-info">
                            <p class="mb-0" id="qrLabel">QR Code</p>
                            <p class="mb-0" id="qrExpiry"></p>
                        </div>
                        <button id="downloadQR" class="btn btn-outline-primary mt-2">
                            <i class="fas fa-download me-2"></i>Download QR Code
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const qrCodeContainer = document.getElementById('qrCodeContainer');
    const qrImage = document.getElementById('qrImage');
    const qrLabel = document.getElementById('qrLabel');
    const qrExpiry = document.getElementById('qrExpiry');
    const downloadQRBtn = document.getElementById('downloadQR');
    let countdownInterval;

    // Get CSRF token from meta tag
    const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    function startCountdown() {
        let timeLeft = 15 * 60;
        clearInterval(countdownInterval);
        countdownInterval = setInterval(() => {
            const minutes = Math.floor(timeLeft / 60);
            const seconds = timeLeft % 60;
            qrExpiry.textContent = `QR code expires in ${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
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
        link.download = `qr-code-${Date.now()}.png`;
        link.href = qrImage.src;
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }

    downloadQRBtn.addEventListener('click', downloadQRCode);

    // Wallet Top-up Form
    document.getElementById('walletForm').addEventListener('submit', function (e) {
        e.preventDefault();
        const amount = parseFloat(this.amount.value);
        const password = this.password.value;

        if (isNaN(amount) || amount < 1 || amount > 10000) {
            alert('Please enter a valid amount between $1 and $10,000');
            return;
        }

        fetch('{{ route("wallet.generate-qr") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': token,
                'Accept': 'application/json'
            },
            body: JSON.stringify({ amount, password })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                qrImage.src = data.qr_code;
                qrLabel.textContent = `Wallet Top-up: $${amount}`;
                qrCodeContainer.classList.remove('d-none');
                startCountdown();
            } else {
                alert(data.message || 'Failed to generate QR code');
            }
        })
        .catch(error => {
            alert('Failed to generate QR code. Please try again.');
            console.error('Error:', error);
        });
    });

    // PWA QR Generation
    document.getElementById('generatePWAQR').addEventListener('click', function (e) {
        e.preventDefault();
        fetch('{{ route("wallet.generate-pwa-qr") }}', {
            method: 'GET',
            headers: {
                'X-CSRF-TOKEN': token,
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                qrImage.src = data.qr_code;
                qrLabel.textContent = 'PWA Installation QR Code';
                qrCodeContainer.classList.remove('d-none');
                qrExpiry.textContent = 'This QR code does not expire';
            } else {
                alert(data.message || 'Failed to generate QR code');
            }
        })
        .catch(error => {
            alert('Failed to generate QR code. Please try again.');
            console.error('Error:', error);
        });
    });

    // Product QR Generation
    document.getElementById('productForm').addEventListener('submit', function (e) {
        e.preventDefault();
        const productId = this.product_id.value;

        if (!productId) {
            alert('Please select a product');
            return;
        }

        fetch('{{ route("wallet.generate-product-qr") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': token,
                'Accept': 'application/json'
            },
            body: JSON.stringify({ product_id: productId })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                qrImage.src = data.qr_code;
                qrLabel.textContent = 'Product Information QR Code';
                qrCodeContainer.classList.remove('d-none');
                qrExpiry.textContent = 'This QR code does not expire';
            } else {
                alert(data.message || 'Failed to generate QR code');
            }
        })
        .catch(error => {
            alert('Failed to generate QR code. Please try again.');
            console.error('Error:', error);
        });
    });

    // Order Details
    document.getElementById('orderForm').addEventListener('submit', function (e) {
        e.preventDefault();
        const userId = this.user_id.value;

        if (!userId) {
            alert('Please select a user');
            return;
        }

        fetch(`{{ route("wallet.order-details") }}?user_id=${userId}`, {
            method: 'GET',
            headers: {
                'X-CSRF-TOKEN': token,
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const orderDetails = document.getElementById('orderDetails');
                orderDetails.innerHTML = `
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Order ID</th>
                                    <th>Date</th>
                                    <th>Total</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${data.orders.data.map(order => `
                                    <tr>
                                        <td>${order.id}</td>
                                        <td>${new Date(order.created_at).toLocaleDateString()}</td>
                                        <td>$${order.total}</td>
                                        <td>${order.status}</td>
                                    </tr>
                                `).join('')}
                            </tbody>
                        </table>
                    </div>
                `;
            } else {
                alert(data.message || 'Failed to fetch order details');
            }
        })
        .catch(error => {
            alert('Failed to fetch order details. Please try again.');
            console.error('Error:', error);
        });
    });
});
</script>
@endpush
@endsection 