@extends('layouts.admin')
@section('content')
<div class="container">
    <h2>Payout Requests (Admin)</h2>
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    <table class="table">
        <thead>
            <tr>
                <th>Creator</th>
                <th>Amount</th>
                <th>Status</th>
                <th>Requested At</th>
                <th>Processed At</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            @foreach($payouts as $payout)
                <tr>
                    <td>{{ $payout->creator->user->name ?? '—' }}</td>
                    <td>Rs {{ number_format($payout->amount, 2) }}</td>
                    <td>{{ ucfirst($payout->status) }}</td>
                    <td>{{ $payout->requested_at }}</td>
                    <td>{{ $payout->processed_at ?? '—' }}</td>
                    <td>
                        @if($payout->status == 'pending')
                            <form method="POST" action="{{ route('admin.payouts.approve', $payout->id) }}" style="display:inline-block;">
                                @csrf
                                <button class="btn btn-success btn-sm">Approve</button>
                            </form>
                            <form method="POST" action="{{ route('admin.payouts.reject', $payout->id) }}" style="display:inline-block;">
                                @csrf
                                <button class="btn btn-danger btn-sm">Reject</button>
                            </form>
                        @else
                            —
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection 