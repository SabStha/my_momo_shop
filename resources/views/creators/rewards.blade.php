@extends('layouts.app')
@section('content')
<div class="container">
    <h2>Monthly Rewards</h2>
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    <table class="table">
        <thead>
            <tr>
                <th>Month</th>
                <th>Badge</th>
                <th>Reward</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            @foreach($rewards as $reward)
                <tr>
                    <td>{{ $reward->month }}</td>
                    <td><span class="badge bg-{{ $reward->badge }}">{{ ucfirst($reward->badge) }}</span></td>
                    <td>{{ $reward->reward }}</td>
                    <td>{{ $reward->claimed ? 'Claimed' : 'Unclaimed' }}</td>
                    <td>
                        @if(!$reward->claimed)
                            <form method="POST" action="{{ route('creator.rewards.claim', $reward->id) }}">
                                @csrf
                                <button class="btn btn-success btn-sm">Claim</button>
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