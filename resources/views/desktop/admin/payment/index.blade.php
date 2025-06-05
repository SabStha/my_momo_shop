@extends('desktop.admin.layouts.admin')

@section('title', 'Payment Management')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Payment Management</h2>
        <div>
            <a href="{{ route('admin.payment-manager.transactions') }}" class="btn btn-primary">
                <i class="fas fa-list"></i> View All Transactions
            </a>
            <a href="{{ route('admin.payment-manager.export') }}" class="btn btn-success">
                <i class="fas fa-file-export"></i> Export Data
            </a>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title mb-0">Total Payments</h6>
                            <h2 class="mt-2 mb-0">${{ number_format($stats['total_amount'], 2) }}</h2>
                            <small>{{ $stats['total_count'] }} transactions</small>
                        </div>
                        <div class="fs-1">
                            <i class="fas fa-money-bill-wave"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title mb-0">Today's Payments</h6>
                            <h2 class="mt-2 mb-0">${{ number_format($stats['today_amount'], 2) }}</h2>
                            <small>{{ $stats['today_count'] }} transactions</small>
                        </div>
                        <div class="fs-1">
                            <i class="fas fa-calendar-day"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title mb-0">This Month</h6>
                            <h2 class="mt-2 mb-0">${{ number_format($stats['month_amount'], 2) }}</h2>
                            <small>{{ $stats['month_count'] }} transactions</small>
                        </div>
                        <div class="fs-1">
                            <i class="fas fa-calendar-alt"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Payments -->
    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">Recent Payments</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>User</th>
                            <th>Order</th>
                            <th>Amount</th>
                            <th>Method</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentPayments as $payment)
                        <tr>
                            <td>#{{ $payment->id }}</td>
                            <td>
                                <div class="d-flex align-items-center">
                                    @if($payment->user->avatar)
                                        <img src="{{ asset('storage/' . $payment->user->avatar) }}" 
                                             alt="{{ $payment->user->name }}" 
                                             class="rounded-circle me-2"
                                             style="width: 32px; height: 32px; object-fit: cover;">
                                    @else
                                        <div class="rounded-circle bg-secondary me-2 d-flex align-items-center justify-content-center"
                                             style="width: 32px; height: 32px;">
                                            <i class="fas fa-user text-white"></i>
                                        </div>
                                    @endif
                                    <div>
                                        <div>{{ $payment->user->name }}</div>
                                        <small class="text-muted">{{ $payment->user->email }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                @if($payment->order)
                                    <a href="{{ route('admin.orders.show', $payment->order) }}">
                                        #{{ $payment->order->id }}
                                    </a>
                                @else
                                    <span class="text-muted">N/A</span>
                                @endif
                            </td>
                            <td>${{ number_format($payment->amount, 2) }}</td>
                            <td>{{ ucfirst($payment->payment_method) }}</td>
                            <td>
                                <span class="badge bg-{{ $payment->status === 'completed' ? 'success' : 
                                    ($payment->status === 'pending' ? 'warning' : 
                                    ($payment->status === 'failed' ? 'danger' : 'info')) }}">
                                    {{ ucfirst($payment->status) }}
                                </span>
                            </td>
                            <td>{{ $payment->created_at->format('M d, Y H:i') }}</td>
                            <td>
                                <button type="button" 
                                        class="btn btn-sm btn-info" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#paymentModal{{ $payment->id }}">
                                    <i class="fas fa-info-circle"></i>
                                </button>

                                <!-- Payment Details Modal -->
                                <div class="modal fade" id="paymentModal{{ $payment->id }}" tabindex="-1">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Payment Details #{{ $payment->id }}</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="mb-3">
                                                    <h6>User Information</h6>
                                                    <p class="mb-1"><strong>Name:</strong> {{ $payment->user->name }}</p>
                                                    <p class="mb-1"><strong>Email:</strong> {{ $payment->user->email }}</p>
                                                    <p class="mb-1"><strong>Phone:</strong> {{ $payment->user->phone ?? 'N/A' }}</p>
                                                </div>
                                                <div class="mb-3">
                                                    <h6>Payment Information</h6>
                                                    <p class="mb-1"><strong>Amount:</strong> ${{ number_format($payment->amount, 2) }}</p>
                                                    <p class="mb-1"><strong>Method:</strong> {{ ucfirst($payment->payment_method) }}</p>
                                                    <p class="mb-1"><strong>Status:</strong> 
                                                        <span class="badge bg-{{ $payment->status === 'completed' ? 'success' : 
                                                            ($payment->status === 'pending' ? 'warning' : 
                                                            ($payment->status === 'failed' ? 'danger' : 'info')) }}">
                                                            {{ ucfirst($payment->status) }}
                                                        </span>
                                                    </p>
                                                    <p class="mb-1"><strong>Date:</strong> {{ $payment->created_at->format('M d, Y H:i:s') }}</p>
                                                </div>
                                                @if($payment->order)
                                                <div class="mb-3">
                                                    <h6>Order Information</h6>
                                                    <p class="mb-1"><strong>Order ID:</strong> #{{ $payment->order->id }}</p>
                                                    <p class="mb-1"><strong>Order Status:</strong> {{ ucfirst($payment->order->status) }}</p>
                                                    <p class="mb-1"><strong>Order Date:</strong> {{ $payment->order->created_at->format('M d, Y H:i') }}</p>
                                                </div>
                                                @endif
                                                @if($payment->transaction_id)
                                                <div class="mb-3">
                                                    <h6>Transaction Details</h6>
                                                    <p class="mb-1"><strong>Transaction ID:</strong> {{ $payment->transaction_id }}</p>
                                                    @if($payment->payment_method === 'card')
                                                        <p class="mb-1"><strong>Card Last 4:</strong> {{ $payment->card_last_four }}</p>
                                                        <p class="mb-1"><strong>Card Brand:</strong> {{ ucfirst($payment->card_brand) }}</p>
                                                    @endif
                                                </div>
                                                @endif
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center">No recent payments found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-center mt-4">
                {{ $recentPayments->links() }}
            </div>
        </div>
    </div>
</div>
@endsection 