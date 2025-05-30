@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Manage Creators</h1>
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
    <div class="card mb-4">
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
                                @if($creator->referral_count > 0 || $creator->points >= 50)
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-danger">Inactive</span>
                                @endif
                            </td>
                            <td>
                                <div class="btn-group">
                                    <a href="{{ route('creators.show', $creator->code) }}" 
                                       class="btn btn-sm btn-info" title="View Profile">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.payouts.index') }}" 
                                       class="btn btn-sm btn-warning" title="Manage Payouts">
                                        <i class="fas fa-money-bill"></i>
                                    </a>
                                    <a href="{{ route('creator.rewards.index') }}" 
                                       class="btn btn-sm btn-success" title="View Rewards">
                                        <i class="fas fa-gift"></i>
                                    </a>
                                    <button type="button" 
                                            class="btn btn-sm btn-primary" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#referralModal{{ $creator->id }}"
                                            title="View Referrals">
                                        <i class="fas fa-users"></i>
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

    <!-- Render all referral modals at the end of the page -->
    @foreach($creators as $creator)
    <div class="modal fade" id="referralModal{{ $creator->id }}" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Referrals for {{ $creator->user->name }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="table-responsive">
                        <table class="table table-striped align-middle">
                            <thead class="table-dark">
                                <tr>
                                    <th>User</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                    <th>Earnings</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($creator->referrals as $referral)
                                <tr>
                                    <td><?php echo e($referral->referredUser ? $referral->referredUser->name : 'N/A'); ?></td>
                                    <td>
                                        <span class="badge bg-{{ $referral->status === 'used' ? 'success' : ($referral->status === 'pending' ? 'warning' : 'secondary') }}">
                                            {{ ucfirst($referral->status) }}
                                        </span>
                                    </td>
                                    <td>{{ $referral->created_at->format('Y-m-d') }}</td>
                                    <td>${{ number_format($referral->earnings, 2) }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endforeach

    <!-- Payouts Section -->
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Pending Payouts</h5>
            <a href="{{ route('admin.payouts.index') }}" class="btn btn-sm btn-primary">View All</a>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>Creator</th>
                            <th>Amount</th>
                            <th>Requested At</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($pendingPayouts as $payout)
                        <tr>
                            <td>{{ $payout->creator->user->name }}</td>
                            <td>${{ number_format($payout->amount, 2) }}</td>
                            <td>{{ $payout->requested_at->format('Y-m-d H:i') }}</td>
                            <td>
                                <span class="badge bg-warning">Pending</span>
                            </td>
                            <td>
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
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Rewards Section -->
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Monthly Rewards</h5>
            <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#assignRewardsModal">
                Assign Rewards
            </button>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>Creator</th>
                            <th>Month</th>
                            <th>Reward Type</th>
                            <th>Amount</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($rewards as $reward)
                        <tr>
                            <td>{{ $reward->creator->user->name }}</td>
                            <td>{{ $reward->month->format('F Y') }}</td>
                            <td>{{ ucfirst($reward->type) }}</td>
                            <td>${{ number_format($reward->amount, 2) }}</td>
                            <td>
                                <span class="badge bg-{{ $reward->claimed ? 'success' : 'warning' }}">
                                    {{ $reward->claimed ? 'Claimed' : 'Pending' }}
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Leadership Section -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Leadership Board</h5>
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
                            <th>Trend</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($topCreators as $i => $creator)
                        <tr>
                            <td>
                                @if($i === 0)
                                    <i class="fas fa-crown text-warning"></i>
                                @elseif($i === 1)
                                    <i class="fas fa-medal text-secondary"></i>
                                @elseif($i === 2)
                                    <i class="fas fa-medal text-danger"></i>
                                @endif
                                #{{ $i + 1 }}
                            </td>
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
                            <td>{{ $creator->referral_count }}</td>
                            <td>{{ $creator->points }}</td>
                            <td>${{ number_format($creator->earnings, 2) }}</td>
                            <td>
                                @if($creator->isTrending())
                                    <span class="badge bg-success">
                                        <i class="fas fa-arrow-up"></i> Trending
                                    </span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Assign Rewards Modal -->
<div class="modal fade" id="assignRewardsModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Assign Monthly Rewards</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('test.assign-monthly-rewards') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="month" class="form-label">Month</label>
                        <input type="month" class="form-control" id="month" name="month" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Assign Rewards</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection