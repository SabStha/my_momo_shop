@extends('layouts.app')

@section('content')
<div class="container">
    <h2>My Account</h2>
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif
    <form method="POST" action="{{ route('my-account.update') }}">
        @csrf
        <div class="mb-3">
            <label>Name</label>
            <input name="name" class="form-control" value="{{ old('name', $user->name) }}">
        </div>
        <div class="mb-3">
            <label>Email</label>
            <input name="email" class="form-control" value="{{ old('email', $user->email) }}">
        </div>
        <button class="btn btn-primary">Update</button>
    </form>
    <hr>
    <p>Account created: {{ $user->created_at->format('M d, Y') }}</p>
    <a href="{{ route('logout') }}" class="btn btn-danger"
       onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
        Logout
    </a>
    <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
        @csrf
    </form>

    <hr>
    <h4>Wallet</h4>
    <p><b>Balance:</b> Rs. {{ $wallet ? number_format($wallet->balance, 2) : '0.00' }}</p>
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Top Up Wallet</h5>
                    <p class="card-text">Scan a QR code to add funds to your wallet.</p>
                    <a href="{{ route('wallet.scan') }}" class="btn btn-primary">
                        <i class="fas fa-qrcode"></i> Scan QR Code
                    </a>
                </div>
            </div>
        </div>
    </div>
    <h5>Recent Transactions</h5>
    <table class="table table-sm">
        <thead>
            <tr>
                <th>Date</th>
                <th>Type</th>
                <th>Amount</th>
                <th>Description</th>
            </tr>
        </thead>
        <tbody>
            @forelse($transactions as $txn)
                <tr>
                    <td>{{ $txn->created_at->format('M d, Y H:i') }}</td>
                    <td>{{ ucfirst($txn->type) }}</td>
                    <td class="{{ $txn->type === 'credit' ? 'text-success' : 'text-danger' }}">
                        {{ $txn->type === 'credit' ? '+' : '-' }}Rs. {{ number_format($txn->amount, 2) }}
                    </td>
                    <td>{{ $txn->description }}</td>
                </tr>
            @empty
                <tr><td colspan="4" class="text-center">No transactions yet.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection 