@extends('desktop.admin.layouts.admin')

@section('title', 'Manage Wallets')

@section('content')
<div class="container-fluid">
    <!-- Header Section -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Manage Wallets</h1>
        <div>
            <button type="button" class="btn btn-success me-2" data-bs-toggle="modal" data-bs-target="#topUpModal">
                <i class="fas fa-plus-circle"></i> Top Up Wallet
            </button>
            <button type="button" class="btn btn-danger me-2" data-bs-toggle="modal" data-bs-target="#withdrawModal">
                <i class="fas fa-minus-circle"></i> Withdraw
            </button>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h5 class="card-title">Total Users with Wallets</h5>
                    <h2 class="mb-0">{{ $wallets->count() }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h5 class="card-title">Total Balance</h5>
                    <h2 class="mb-0">${{ number_format($wallets->sum('balance'), 2) }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <h5 class="card-title">Average Balance</h5>
                    <h2 class="mb-0">${{ number_format($wallets->avg('balance'), 2) }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <h5 class="card-title">Highest Balance</h5>
                    <h2 class="mb-0">${{ number_format($wallets->max('balance'), 2) }}</h2>
                </div>
            </div>
        </div>
    </div>

    <!-- Wallets Table -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table id="walletsTable" class="table table-striped">
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Balance</th>
                            <th>Last Transaction</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($wallets as $wallet)
                            <tr>
                                <td>{{ $wallet->user->name }}</td>
                                <td>${{ number_format($wallet->balance, 2) }}</td>
                                <td>
                                    @php
                                        $lastTransaction = $wallet->transactions()->latest()->first();
                                    @endphp
                                    @if($lastTransaction)
                                        {{ $lastTransaction->created_at->format('M d, Y H:i') }}
                                        ({{ ucfirst($lastTransaction->type) }}: ${{ number_format($lastTransaction->amount, 2) }})
                                    @else
                                        No transactions
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <button type="button" 
                                                class="btn btn-sm btn-success" 
                                                onclick="topUpUser({{ $wallet->user_id }}, '{{ $wallet->user->name }}')"
                                                title="Top Up">
                                            <i class="fas fa-plus-circle"></i>
                                        </button>
                                        <button type="button" 
                                                class="btn btn-sm btn-danger" 
                                                onclick="withdrawUser({{ $wallet->user_id }}, '{{ $wallet->user->name }}')"
                                                title="Withdraw">
                                            <i class="fas fa-minus-circle"></i>
                                        </button>
                                        <button type="button" 
                                                class="btn btn-sm btn-info" 
                                                onclick="viewTransactions({{ $wallet->user_id }})"
                                                title="View Transactions">
                                            <i class="fas fa-history"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Top Up Modal -->
<div class="modal fade" id="topUpModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('my-account.top-up') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Top Up Wallet</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="user_id" class="form-label">User</label>
                        <select name="user_id" id="user_id" class="form-control" required>
                            <option value="">Select User</option>
                            @foreach($wallets as $wallet)
                                <option value="{{ $wallet->user_id }}">{{ $wallet->user->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="amount" class="form-label">Amount</label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" step="0.01" min="0.01" name="amount" id="amount" class="form-control" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea name="description" id="description" class="form-control" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Top Up</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Withdraw Modal -->
<div class="modal fade" id="withdrawModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('my-account.withdraw') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Withdraw from Wallet</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="withdraw_user_id" class="form-label">User</label>
                        <select name="user_id" id="withdraw_user_id" class="form-control" required>
                            <option value="">Select User</option>
                            @foreach($wallets as $wallet)
                                <option value="{{ $wallet->user_id }}">{{ $wallet->user->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="withdraw_amount" class="form-label">Amount</label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" step="0.01" min="0.01" name="amount" id="withdraw_amount" class="form-control" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="withdraw_description" class="form-label">Description</label>
                        <textarea name="description" id="withdraw_description" class="form-control" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Withdraw</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
$(document).ready(function() {
    // Initialize DataTables
    $('#walletsTable').DataTable({
        order: [[1, 'desc']], // Sort by balance by default
        pageLength: 25,
        language: {
            search: "_INPUT_",
            searchPlaceholder: "Search users..."
        }
    });
});

// User-specific functions
function topUpUser(userId, userName) {
    $('#user_id').val(userId);
    $('#topUpModal').modal('show');
}

function withdrawUser(userId, userName) {
    $('#withdraw_user_id').val(userId);
    $('#withdrawModal').modal('show');
}

function viewTransactions(userId) {
    window.location.href = "{{ route('admin.wallet.index') }}?user_id=" + userId;
}
</script>
@endsection 