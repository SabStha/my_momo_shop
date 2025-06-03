@extends('desktop.layouts.app')

@section('content')
<div class="container py-5">
    <div class="row">
        <div class="col-md-8">
            <!-- Wallet Balance Card -->
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="card-title mb-1">Wallet Balance</h5>
                            <h2 class="text-warning mb-0">${{ number_format($wallet ? $wallet->balance : 0, 2) }}</h2>
                        </div>
                        <a href="{{ route('wallet.topup') }}" class="btn btn-warning">
                            Top Up Wallet
                        </a>
                    </div>
                </div>
            </div>

            <!-- Transaction History -->
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="card-title mb-0">Transaction History</h5>
                </div>
                <div class="card-body">
                    @if($transactions->isEmpty())
                        <div class="text-center py-4">
                            <p class="text-muted mb-0">No transactions yet</p>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Description</th>
                                        <th>Type</th>
                                        <th class="text-end">Amount</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($transactions as $transaction)
                                        <tr>
                                            <td>{{ $transaction->created_at->format('M d, Y H:i') }}</td>
                                            <td>{{ $transaction->description }}</td>
                                            <td>
                                                <span class="badge bg-{{ $transaction->type === 'credit' ? 'success' : 'danger' }}">
                                                    {{ ucfirst($transaction->type) }}
                                                </span>
                                            </td>
                                            <td class="text-end">
                                                <span class="{{ $transaction->type === 'credit' ? 'text-success' : 'text-danger' }}">
                                                    {{ $transaction->type === 'credit' ? '+' : '-' }}${{ number_format($transaction->amount, 2) }}
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="d-flex justify-content-center mt-4">
                            {{ $transactions->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Quick Actions Sidebar -->
        <div class="col-md-4">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="card-title mb-0">Quick Actions</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('wallet.topup') }}" class="btn btn-warning">
                            <i class="fas fa-plus-circle me-2"></i>Top Up Wallet
                        </a>
                        <a href="{{ route('wallet.scan') }}" class="btn btn-info text-white">
                            <i class="fas fa-qrcode me-2"></i>Scan QR Code
                        </a>
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
</style>
@endsection 