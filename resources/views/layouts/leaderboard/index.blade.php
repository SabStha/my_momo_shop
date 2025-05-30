@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Top Creators</h1>
    <form method="get" class="mb-3 d-flex gap-2">
        <select name="sort" class="form-select w-auto">
            <option value="referral_count" {{ $sort == 'referral_count' ? 'selected' : '' }}>Referrals</option>
            <option value="earnings" {{ $sort == 'earnings' ? 'selected' : '' }}>Earnings</option>
            <option value="points" {{ $sort == 'points' ? 'selected' : '' }}>Points</option>
        </select>
        <select name="period" class="form-select w-auto">
            <option value="all" {{ $period == 'all' ? 'selected' : '' }}>All Time</option>
            <option value="month" {{ $period == 'month' ? 'selected' : '' }}>This Month</option>
        </select>
        <button type="submit" class="btn btn-primary">Filter</button>
    </form>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Rank</th>
                <th>Badge</th>
                <th>Name</th>
                <th>Referrals</th>
                <th>Earnings</th>
                <th>Points</th>
            </tr>
        </thead>
        <tbody>
            @foreach($creators as $creator)
            <tr>
                <td>{{ $creator->rank }}</td>
                <td><span class="badge bg-{{ strtolower($creator->badge) }}">{{ $creator->badge }}</span></td>
                <td>{{ $creator->user->name }}</td>
                <td>{{ $creator->referral_count }}</td>
                <td>${{ number_format($creator->earnings, 2) }}</td>
                <td>{{ $creator->points }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection 