@extends('desktop.layouts.app')

@section('content')
<div class="container py-5">
    <div class="row">
        <!-- Order Summary Card -->
        <div class="col-lg-4 mb-4">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="card-title mb-0">Order Summary</h5>
                </div>
                <div class="card-body">
                    @foreach($cartItems as $item)
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="d-flex align-items-center">
                                @if($item->product->image)
                                    <img src="{{ asset('storage/' . $item->product->image) }}" 
                                         alt="{{ $item->product->name }}" 
                                         class="rounded me-2" 
                                         style="width: 50px; height: 50px; object-fit: cover;">
                                @endif
                                <div>
                                    <h6 class="mb-0">{{ $item->product->name }}</h6>
                                    <small class="text-muted">Qty: {{ $item->quantity }}</small>
                                </div>
                            </div>
                            <span>${{ number_format($item->product->price * $item->quantity, 2) }}</span>
                        </div>
                    @endforeach
                    <hr>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Subtotal</span>
                        <span>${{ number_format($subtotal, 2) }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Delivery Fee</span>
                        <span>${{ number_format($deliveryFee ?? 0, 2) }}</span>
                    </div>

                    <!-- Coupon Code Section -->
                    <div class="mt-3 mb-3">
                        <form id="couponForm" class="d-flex gap-2">
                            <div class="flex-grow-1">
                                <input type="text" class="form-control" id="coupon_code" name="coupon_code" 
                                       placeholder="Enter coupon code" value="{{ old('coupon_code') }}">
                            </div>
                            <button type="submit" class="btn btn-outline-warning">Apply</button>
                        </form>
                        @if(session('coupon_error'))
                            <div class="text-danger mt-1 small">{{ session('coupon_error') }}</div>
                        @endif
                        @if(session('coupon_success'))
                            <div class="text-success mt-1 small">{{ session('coupon_success') }}</div>
                        @endif
                    </div>

                    @if(session('coupon') && session('discount_amount') > 0)
                        <div class="d-flex justify-content-between mb-2 text-success">
                            <span>Discount ({{ session('coupon.code') }})</span>
                            <span>-${{ number_format(session('discount_amount'), 2) }}</span>
                        </div>
                    @endif

                    <hr>
                    <div class="d-flex justify-content-between fw-bold">
                        <span>Total</span>
                        <span class="text-warning">${{ number_format($total, 2) }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Checkout Form -->
        <div class="col-lg-8">
            <form action="{{ route('checkout.submit') }}" method="POST" id="checkoutForm">
                @csrf
                
                <!-- Contact Information -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-white">
                        <h5 class="card-title mb-0">Contact Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="name" class="form-label">Full Name</label>
                                <input type="text" class="form-control" id="name" name="name" required 
                                       value="{{ old('name', auth()->user()->name ?? '') }}">
                            </div>
                            <div class="col-md-6">
                                <label for="phone" class="form-label">Phone Number</label>
                                <input type="tel" class="form-control" id="phone" name="phone" required 
                                       value="{{ old('phone', auth()->user()->phone ?? '') }}">
                            </div>
                            <div class="col-12">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email" required 
                                       value="{{ old('email', auth()->user()->email ?? '') }}">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Delivery Details -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-white">
                        <h5 class="card-title mb-0">Delivery Details</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Delivery Method</label>
                            <div class="d-flex gap-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="delivery_method" 
                                           id="delivery" value="delivery" checked>
                                    <label class="form-check-label" for="delivery">
                                        Delivery
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="delivery_method" 
                                           id="pickup" value="pickup">
                                    <label class="form-check-label" for="pickup">
                                        Pickup
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div id="addressFields">
                            <div class="mb-3">
                                <label for="address" class="form-label">Delivery Address</label>
                                <textarea class="form-control" id="address" name="address" rows="3" required>{{ old('address') }}</textarea>
                            </div>
                            <div class="mb-3">
                                <label for="delivery_note" class="form-label">Delivery Note (Optional)</label>
                                <textarea class="form-control" id="delivery_note" name="delivery_note" rows="2">{{ old('delivery_note') }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Payment Method -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-white">
                        <h5 class="card-title mb-0">Payment Method</h5>
                    </div>
                    <div class="card-body">
                        @auth
                        @if(auth()->user()->wallet)
                            <!-- Wallet Payment Section -->
                            <div class="mb-4">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h6 class="mb-0">Wallet Balance</h6>
                                    <span class="h5 mb-0 text-warning">${{ number_format(auth()->user()->wallet->balance, 2) }}</span>
                                </div>
                                
                                @if(auth()->user()->wallet->balance > 0)
                                    <div class="alert alert-light border mb-3">
                                        <div class="d-flex justify-content-between mb-2">
                                            <span>Total Amount:</span>
                                            <strong>${{ number_format($total, 2) }}</strong>
                                        </div>
                                        <div class="d-flex justify-content-between mb-2">
                                            <span>Wallet Payment:</span>
                                            <strong>$<span id="walletPaymentAmount">{{ number_format(min(auth()->user()->wallet->balance, $total), 2) }}</span></strong>
                                        </div>
                                        <div class="d-flex justify-content-between">
                                            <span>Remaining Amount:</span>
                                            <strong>$<span id="remainingAmount">{{ number_format($total - min(auth()->user()->wallet->balance, $total), 2) }}</span></strong>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label for="walletAmount" class="form-label">Amount to pay from wallet</label>
                                        <div class="input-group">
                                            <span class="input-group-text">$</span>
                                            <input type="number" class="form-control" id="walletAmount" name="wallet_amount" 
                                                   min="0" max="{{ auth()->user()->wallet->balance }}" step="0.01"
                                                   value="{{ min(auth()->user()->wallet->balance, $total) }}">
                                            <button class="btn btn-outline-secondary" type="button" id="useMaxWallet">
                                                Use Max
                                            </button>
                                        </div>
                                        <small class="text-muted">You can adjust the amount to pay from your wallet</small>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Pay remaining amount through</label>
                                        <select class="form-select" id="remainingPaymentMethod" name="remaining_payment_method">
                                            <option value="cod">Cash on Delivery</option>
                                            <option value="esewa">eSewa</option>
                                        </select>
                                    </div>

                                    <input type="hidden" name="payment_method" value="wallet">
                                    <input type="hidden" name="wallet_payment_type" value="custom">
                                @else
                                    <div class="alert alert-info">
                                        <i class="fas fa-info-circle me-2"></i>
                                        Your wallet balance is empty. Please choose another payment method.
                                    </div>
                                @endif
                            </div>
                        @endif
                        @endauth

                        <!-- Other Payment Methods -->
                        <div class="border-top pt-3">
                            <label class="form-label">Or pay the full amount through</label>
                            <div class="d-flex gap-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="payment_method" 
                                           id="cod" value="cod" {{ !auth()->user() || !auth()->user()->wallet || auth()->user()->wallet->balance <= 0 ? 'checked' : '' }}>
                                    <label class="form-check-label" for="cod">
                                        Cash on Delivery
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="payment_method" 
                                           id="esewa" value="esewa">
                                    <label class="form-check-label" for="esewa">
                                        eSewa
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Sticky Place Order Button -->
    <div class="position-fixed bottom-0 start-0 end-0 bg-white border-top py-3 px-4" style="z-index: 1000;">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h5 class="mb-0">Total: <span class="text-warning">${{ number_format($total, 2) }}</span></h5>
                </div>
                <div class="col-md-6 text-md-end">
                    <button type="submit" form="checkoutForm" class="btn btn-warning btn-lg px-5">
                        Place Order
                    </button>
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
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const deliveryMethod = document.querySelectorAll('input[name="delivery_method"]');
    const addressFields = document.getElementById('addressFields');
    const checkoutForm = document.getElementById('checkoutForm');
    const submitButton = document.querySelector('button[type="submit"]');

    deliveryMethod.forEach(radio => {
        radio.addEventListener('change', function() {
            if (this.value === 'pickup') {
                addressFields.style.display = 'none';
                document.getElementById('address').required = false;
            } else {
                addressFields.style.display = 'block';
                document.getElementById('address').required = true;
            }
        });
    });

    // Handle checkout form submission
    if (checkoutForm && submitButton) {
        checkoutForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Show loading state
            const originalButtonText = submitButton.innerHTML;
            submitButton.disabled = true;
            submitButton.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Processing...';
            
            // Get form data
            const formData = new FormData(checkoutForm);
            
            // Submit form via AJAX
            fetch(checkoutForm.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.redirect) {
                    window.location.href = data.redirect;
                } else {
                    // Handle error
                    alert(data.message || 'An error occurred. Please try again.');
                    submitButton.disabled = false;
                    submitButton.innerHTML = originalButtonText;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred. Please try again.');
                submitButton.disabled = false;
                submitButton.innerHTML = originalButtonText;
            });
        });
    }

    // Handle coupon form submission
    const couponForm = document.getElementById('couponForm');
    if (couponForm) {
        couponForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const couponCode = document.getElementById('coupon_code').value;
            
            fetch('{{ route("coupon.apply") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    code: couponCode,
                    price: {{ $total }},
                    referral_code: '{{ session("referral_code") }}'
                })
            })
            .then(response => {
                if (!response.ok) {
                    return response.json().then(data => {
                        throw new Error(data.message || 'Failed to apply coupon');
                    });
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    window.location.reload();
                } else {
                    alert(data.message || 'Failed to apply coupon');
                }
            })
            .catch(error => {
                alert(error.message || 'Failed to apply coupon');
            });
        });
    }

    // Handle wallet payment options
    const walletAmount = document.getElementById('walletAmount');
    const useMaxWallet = document.getElementById('useMaxWallet');
    const walletPaymentAmount = document.getElementById('walletPaymentAmount');
    const remainingAmount = document.getElementById('remainingAmount');
    const totalAmount = {{ $total }};
    const walletBalance = {{ auth()->user()->wallet ? auth()->user()->wallet->balance : 0 }};

    function updatePaymentAmounts() {
        const walletPayment = parseFloat(walletAmount.value) || 0;
        const remaining = totalAmount - walletPayment;
        
        walletPaymentAmount.textContent = walletPayment.toFixed(2);
        remainingAmount.textContent = remaining.toFixed(2);
    }

    if (walletAmount) {
        // Handle input changes
        walletAmount.addEventListener('input', function() {
            const value = parseFloat(this.value) || 0;
            if (value > walletBalance) {
                this.value = walletBalance;
            }
            updatePaymentAmounts();
        });

        // Handle "Use Max" button
        useMaxWallet.addEventListener('click', function() {
            walletAmount.value = Math.min(walletBalance, totalAmount);
            updatePaymentAmounts();
        });

        // Initialize payment amounts
        updatePaymentAmounts();
    }
});
</script>
@endsection 