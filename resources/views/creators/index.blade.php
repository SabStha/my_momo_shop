@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Manage Creators</h1>
        <a href="{{ route('creators.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Add New Creator
        </a>
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

    <!-- Creators Table -->
    <div class="card">
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
                                    <a href="{{ route('creators.show', $creator->code) }}" 
                                       class="btn btn-sm btn-info">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.payouts.index') }}" 
                                       class="btn btn-sm btn-warning">
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