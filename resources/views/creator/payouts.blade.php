@extends('layouts.app')
@section('content')
<div class="container">
    <h2>Payout Requests</h2>
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif
    <form method="POST" action="{{ route('creator.payouts.request') }}" class="mb-4">
        @csrf
        <div class="mb-3">
            <label>Amount</label>
            <input type="number" class="form-control" name="amount" value="{{ $creator->earnings }}" readonly>
        </div>
        <button class="btn btn-primary" {{ $creator->earnings <= 0 ? 'disabled' : '' }}>Request Payout</button>
    </form>
    <h4>Payout History</h4>
    <table class="table">
        <thead>
            <tr>
                <th>Amount</th>
                <th>Status</th>
                <th>Requested At</th>
                <th>Processed At</th>
            </tr>
        </thead>
        <tbody>
            @foreach($payouts as $payout)
                <tr>
                    <td>${{ number_format($payout->amount, 2) }}</td>
                    <td>{{ ucfirst($payout->status) }}</td>
                    <td>{{ $payout->requested_at }}</td>
                    <td>{{ $payout->processed_at ?? '—' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection 