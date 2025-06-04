@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row">
        <div class="col-12">
            <h1 class="mb-4">My Wallet</h1>
            
            <div class="card mb-4">
                <div class="card-body">
                    <h5 class="card-title">Current Balance</h5>
                    <h3 class="text-success mb-3">Rs. {{ number_format($wallet ? $wallet->balance : 0, 2) }}</h3>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <a href="{{ route('wallet.scan') }}" class="btn btn-outline-success w-100">Top Up via QR</a>
                        </div>
                        <div class="col-md-6">
                            <a href="{{ route('wallet.transactions') }}" class="btn btn-outline-primary w-100">View All Transactions</a>
                        </div>
                    </div>
                </div>
            </div>

            @if($transactions->count() > 0)
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Recent Transactions</h5>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Type</th>
                                    <th>Amount</th>
                                    <th>Description</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($transactions as $transaction)
                                <tr>
                                    <td>{{ $transaction->created_at->format('M d, Y H:i') }}</td>
                                    <td>
                                        <span class="badge bg-{{ $transaction->type === 'credit' ? 'success' : 'danger' }}">
                                            {{ ucfirst($transaction->type) }}
                                        </span>
                                    </td>
                                    <td class="{{ $transaction->type === 'credit' ? 'text-success' : 'text-danger' }}">
                                        {{ $transaction->type === 'credit' ? '+' : '-' }}Rs. {{ number_format($transaction->amount, 2) }}
                                    </td>
                                    <td>{{ $transaction->description }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection 