@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Manage Payouts</h1>
    </div>

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif

    <!-- Payouts Table -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">All Payouts</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Creator</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Requested At</th>
                            <th>Processed At</th>
                            <th>Payment Method</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($payouts as $payout)
                        <tr>
                            <td>{{ $payout->id }}</td>
                            <td>
                                <div class="d-flex align-items-center">
                                    @if($payout->creator->avatar)
                                        <img src="{{ asset('storage/' . $payout->creator->avatar) }}" 
                                             alt="{{ $payout->creator->user->name }}" 
                                             class="rounded-circle me-2"
                                             style="width: 32px; height: 32px; object-fit: cover;">
                                    @endif
                                    {{ $payout->creator->user->name }}
                                </div>
                            </td>
                            <td>${{ number_format($payout->amount, 2) }}</td>
                            <td>
                                <span class="badge bg-{{ $payout->status === 'pending' ? 'warning' : ($payout->status === 'approved' ? 'success' : ($payout->status === 'rejected' ? 'danger' : 'info')) }}">
                                    {{ ucfirst($payout->status) }}
                                </span>
                            </td>
                            <td>{{ $payout->requested_at->format('Y-m-d H:i') }}</td>
                            <td>{{ $payout->processed_at ? $payout->processed_at->format('Y-m-d H:i') : '-' }}</td>
                            <td>{{ $payout->payment_method ?? '-' }}</td>
                            <td>
                                @if($payout->status === 'pending')
                                    <div class="btn-group">
                                        <form action="{{ route('admin.payouts.approve', $payout->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-success">
                                                <i class="fas fa-check"></i> Approve
                                            </button>
                                        </form>
                                        <form action="{{ route('admin.payouts.reject', $payout->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-danger">
                                                <i class="fas fa-times"></i> Reject
                                            </button>
                                        </form>
                                    </div>
                                @elseif($payout->status === 'approved')
                                    <form action="{{ route('admin.payouts.mark-paid', $payout->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-info">
                                            <i class="fas fa-money-bill"></i> Mark as Paid
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="d-flex justify-content-center mt-4">
                {{ $payouts->links() }}
            </div>
        </div>
    </div>
</div>
@endsection 