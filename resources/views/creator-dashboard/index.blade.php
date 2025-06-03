@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row">
        <!-- Profile Section -->
        <div class="col-md-4 mb-4">
            <div class="card">
                <div class="card-body text-center">
                    <div class="mb-3">
                        @if(auth()->user()->creator && auth()->user()->creator->avatar)
                            <img src="{{ Storage::url(auth()->user()->creator->avatar) }}" 
                                 alt="Profile Picture" 
                                 class="rounded-circle img-thumbnail"
                                 style="width: 150px; height: 150px; object-fit: cover;">
                        @else
                            <img src="https://ui-avatars.com/api/?name={{ urlencode(auth()->user()->name) }}&size=150" 
                                 alt="Profile Picture" 
                                 class="rounded-circle img-thumbnail">
                        @endif
                    </div>
                    <h4>{{ auth()->user()->name }}</h4>
                    <p class="text-muted">Creator</p>
                    @if(isset($wallet))
                        <div class="my-3">
                            <span class="fw-bold">Wallet Balance:</span>
                            <span class="text-success">Rs. {{ number_format($wallet->balance, 2) }}</span>
                        </div>
                    @endif
                    <form action="{{ route('creator-dashboard.update-profile-photo') }}" 
                          method="POST" 
                          enctype="multipart/form-data"
                          class="mt-3">
                        @csrf
                        <div class="mb-3">
                            <input type="file" 
                                   name="avatar" 
                                   class="form-control" 
                                   accept="image/*">
                        </div>
                        <button type="submit" class="btn btn-primary">Update Photo</button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Stats and Referral Section -->
        <div class="col-md-8">
            <!-- Stats Cards -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card bg-primary text-white">
                        <div class="card-body">
                            <h5 class="card-title">Total Referrals</h5>
                            <h2 class="mb-0">{{ $stats['total_referrals'] }}</h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-success text-white">
                        <div class="card-body">
                            <h5 class="card-title">Completed Orders</h5>
                            <h2 class="mb-0">{{ $stats['ordered_referrals'] }}</h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-info text-white">
                        <div class="card-body">
                            <h5 class="card-title">Referral Points</h5>
                            <h2 class="mb-0">{{ $stats['referral_points'] }}</h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-warning text-white">
                        <div class="card-body">
                            <h5 class="card-title">Wallet Balance</h5>
                            <h2 class="mb-0">Rs. {{ isset($wallet) ? number_format($wallet->balance, 2) : '0.00' }}</h2>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Wallet Actions -->
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

            <!-- Referral Code Section -->
            <div class="card mb-4">
                <div class="card-body">
                    <h5 class="card-title">Share this link with your friends:</h5>
                    <div class="input-group mb-3">
                        <input type="text" class="form-control" id="referral-link" value="{{ url('/register?ref=' . Auth::user()->creator->code) }}" readonly>
                        <button class="btn btn-outline-secondary" type="button" onclick="copyReferralLink()">Copy</button>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <h6>You Earn:</h6>
                            <ul class="mb-0">
                                <li>✓ 10 points when they sign up</li>
                                <li>✓ 5 points on their first order</li>
                                <li>✓ 5 points for each of their next 9 orders</li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <h6>They Earn:</h6>
                            <ul class="mb-0">
                                <li>✓ Rs 50 discount for signing up</li>
                                <li>✓ Rs 30 discount on their first order</li>
                                <li>✓ Rs 10 discount for each of their next 9 orders</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Leaderboard Section -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Top Creators Leaderboard</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Rank</th>
                                    <th>Creator</th>
                                    <th>Points</th>
                                    <th>Referrals</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($topCreators as $index => $creator)
                                    <tr class="{{ $creator->id === auth()->user()->creator->id ? 'table-primary' : '' }}">
                                        <td>{{ $index + 1 }}</td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                @if($creator->avatar)
                                                    <img src="{{ Storage::url($creator->avatar) }}" 
                                                         alt="{{ $creator->user->name }}" 
                                                         class="rounded-circle me-2"
                                                         style="width: 40px; height: 40px; object-fit: cover;">
                                                @else
                                                    <img src="https://ui-avatars.com/api/?name={{ urlencode($creator->user->name) }}&size=40" 
                                                         alt="{{ $creator->user->name }}" 
                                                         class="rounded-circle me-2">
                                                @endif
                                                {{ $creator->user->name }}
                                            </div>
                                        </td>
                                        <td>{{ $creator->points }}</td>
                                        <td>{{ $creator->referral_count }}</td>
                                        <td>
                                            @if($creator->isTrending())
                                                <span class="badge bg-success">Trending</span>
                                            @else
                                                <span class="badge bg-secondary">Stable</span>
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
    </div>

    <!-- Your Referrals List -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Your Referrals</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>User</th>
                                    <th>Status</th>
                                    <th>Orders</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($referrals as $referral)
                                    <tr>
                                        <td>{{ $referral->referredUser ? (count(explode(' ', $referral->referredUser->name)) > 1 ? explode(' ', $referral->referredUser->name)[1] : $referral->referredUser->name) : 'N/A' }}</td>
                                        <td>
                                            <span class="badge bg-{{ $referral->status === 'ordered' ? 'success' : 'warning' }}">
                                                {{ ucfirst($referral->status) }}
                                            </span>
                                        </td>
                                        <td>{{ $referral->order_count ?? 0 }}</td>
                                        <td>{{ $referral->created_at->format('M d, Y') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center">No referrals yet</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function copyReferralLink() {
    const input = document.getElementById('referral-link');
    input.select();
    document.execCommand('copy');
    alert('Referral link copied to clipboard!');
}
</script>
@endpush
@endsection 