@extends('layouts.admin')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Order Details #{{ $order->id }}</h1>
        <a href="{{ route('admin.payments.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back to Payments
        </a>
    </div>

    <div class="row">
        <!-- Order Information -->
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-info-circle me-1"></i>
                    Order Information
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h5>Customer Details</h5>
                            <p>
                                <strong>Name:</strong> {{ $order->user ? $order->user->name : 'Guest' }}<br>
                                @if($order->user && $order->user->phone)
                                    <strong>Phone:</strong> {{ $order->user->phone }}<br>
                                @endif
                                @if($order->user && $order->user->email)
                                    <strong>Email:</strong> {{ $order->user->email }}<br>
                                @endif
                            </p>
                        </div>
                        <div class="col-md-6">
                            <h5>Order Details</h5>
                            <p>
                                <strong>Order Type:</strong> {{ ucfirst(str_replace('_', ' ', $order->order_type)) }}<br>
                                <strong>Status:</strong> {{ ucfirst($order->status) }}<br>
                                <strong>Payment Status:</strong> {{ ucfirst($order->payment_status) }}<br>
                                <strong>Created:</strong> {{ $order->created_at->format('M d, Y H:i:s') }}<br>
                                @if($order->table)
                                    <strong>Table:</strong> {{ $order->table->name }}
                                @endif
                            </p>
                        </div>
                    </div>

                    <h5 class="mt-4">Order Items</h5>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Item</th>
                                    <th>Quantity</th>
                                    <th>Price</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($order->items as $item)
                                    <tr>
                                        <td>{{ $item->product->name }}</td>
                                        <td>{{ $item->quantity }}</td>
                                        <td>₹{{ number_format($item->price, 2) }}</td>
                                        <td>₹{{ number_format($item->quantity * $item->price, 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="3" class="text-end"><strong>Subtotal:</strong></td>
                                    <td>₹{{ number_format($order->subtotal, 2) }}</td>
                                </tr>
                                @if($order->tax_amount > 0)
                                    <tr>
                                        <td colspan="3" class="text-end"><strong>Tax:</strong></td>
                                        <td>₹{{ number_format($order->tax_amount, 2) }}</td>
                                    </tr>
                                @endif
                                @if($order->discount_amount > 0)
                                    <tr>
                                        <td colspan="3" class="text-end"><strong>Discount:</strong></td>
                                        <td>-₹{{ number_format($order->discount_amount, 2) }}</td>
                                    </tr>
                                @endif
                                <tr>
                                    <td colspan="3" class="text-end"><strong>Total:</strong></td>
                                    <td><strong>₹{{ number_format($order->total, 2) }}</strong></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Payment Information -->
        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-money-bill-wave me-1"></i>
                    Payment Information
                </div>
                <div class="card-body">
                    @if($order->payments->isNotEmpty())
                        @foreach($order->payments as $payment)
                            <div class="alert alert-success">
                                <h6>Payment #{{ $payment->id }}</h6>
                                <p class="mb-0">
                                    <strong>Amount:</strong> ₹{{ number_format($payment->amount, 2) }}<br>
                                    <strong>Method:</strong> {{ ucfirst($payment->payment_method) }}<br>
                                    <strong>Status:</strong> {{ ucfirst($payment->status) }}<br>
                                    <strong>Date:</strong> {{ $payment->created_at->format('M d, Y H:i:s') }}
                                </p>
                            </div>
                        @endforeach
                    @else
                        @if($order->payment_status !== 'paid')
                            <div class="alert alert-warning">
                                No payments recorded yet.
                            </div>
                            <button class="btn btn-primary w-100" onclick="processPayment({{ $order->id }})">
                                <i class="fas fa-money-bill-wave me-1"></i> Process Payment
                            </button>
                        @else
                            <div class="alert alert-info">
                                Order marked as paid but no payment records found.
                            </div>
                        @endif
                    @endif
                </div>
            </div>

            @if($order->user)
                <div class="card mb-4">
                    <div class="card-header">
                        <i class="fas fa-wallet me-1"></i>
                        Wallet Balance
                    </div>
                    <div class="card-body">
                        <div id="walletBalance" class="alert alert-info">
                            Loading wallet balance...
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    @if($order->user)
        // Fetch wallet balance
        fetch('{{ route("admin.payments.wallet.balance", $order) }}')
            .then(response => response.json())
            .then(data => {
                const balanceDiv = document.getElementById('walletBalance');
                if (data.success) {
                    balanceDiv.innerHTML = `Current wallet balance: ₹${data.balance}`;
                } else {
                    balanceDiv.innerHTML = 'Unable to fetch wallet balance';
                    balanceDiv.className = 'alert alert-warning';
                }
            });
    @endif
});

function processPayment(orderId) {
    // Show payment modal
    document.getElementById('orderIdInput').value = orderId;
}
</script>
@endpush
@endsection 