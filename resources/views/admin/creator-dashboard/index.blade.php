@extends('desktop.admin.layouts.admin')

@section('title', 'Creator Management')

@section('content')
<div class="container-fluid">
    <!-- Quick Stats -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h5 class="card-title">Total Creators</h5>
                    <h2 class="mb-0">{{ $creators->count() }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h5 class="card-title">Active Creators</h5>
                    <h2 class="mb-0">{{ $creators->where('user.is_active', true)->count() }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <h5 class="card-title">Total Referrals</h5>
                    <h2 class="mb-0">{{ $creators->sum('referral_count') }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <h5 class="card-title">Total Earnings</h5>
                    <h2 class="mb-0">${{ number_format($creators->sum('earnings'), 2) }}</h2>
                </div>
            </div>
        </div>
    </div>

    <!-- Creators Table -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">All Creators</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Referral Code</th>
                            <th>Referrals</th>
                            <th>Points</th>
                            <th>Earnings</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($creators as $creator)
                        <tr>
                            <td>{{ $creator->id }}</td>
                            <td>
                                <div class="d-flex align-items-center">
                                    @if($creator->avatar)
                                        <img src="{{ asset('storage/' . $creator->avatar) }}" 
                                             alt="{{ $creator->user->name }}" 
                                             class="rounded-circle me-2"
                                             style="width: 32px; height: 32px; object-fit: cover;">
                                    @endif
                                    {{ $creator->user->name }}
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-primary">{{ $creator->code }}</span>
                            </td>
                            <td>{{ $creator->referral_count }}</td>
                            <td>{{ $creator->points }}</td>
                            <td>${{ number_format($creator->earnings, 2) }}</td>
                            <td>
                                @if($creator->user->is_active)
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-danger">Inactive</span>
                                @endif
                            </td>
                            <td>
                                <div class="btn-group">
                                    <a href="{{ route('admin.creator-dashboard.creators.show', $creator->id) }}" 
                                       class="btn btn-sm btn-info" title="View Profile">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.payouts.index') }}" 
                                       class="btn btn-sm btn-warning" title="Manage Payouts">
                                        <i class="fas fa-money-bill"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Top Creators Section -->
    <div class="card mt-4">
        <div class="card-header">
            <h5 class="mb-0">Top Performing Creators</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>Rank</th>
                            <th>Name</th>
                            <th>Referrals</th>
                            <th>Points</th>
                            <th>Earnings</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($topCreators as $i => $creator)
                        <tr>
                            <td>#{{ $i + 1 }}</td>
                            <td>{{ $creator->user->name }}</td>
                            <td>{{ $creator->referral_count }}</td>
                            <td>{{ $creator->points }}</td>
                            <td>${{ number_format($creator->earnings, 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection 