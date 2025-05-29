@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Creators Hub</h1>
    <ul class="nav nav-tabs mb-4" id="creatorTabs" role="tablist">
        @if(Auth::user() && Auth::user()->hasRole('admin'))
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="admin-management-tab" data-bs-toggle="tab" data-bs-target="#admin-management" type="button" role="tab" aria-controls="admin-management" aria-selected="true">Creator Management</button>
        </li>
        @endif
        <li class="nav-item" role="presentation">
            <button class="nav-link @if(!(Auth::user() && Auth::user()->hasRole('admin'))) active @endif" id="creators-tab" data-bs-toggle="tab" data-bs-target="#creators" type="button" role="tab" aria-controls="creators" aria-selected="false">Creators</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="leaderboard-tab" data-bs-toggle="tab" data-bs-target="#leaderboard" type="button" role="tab" aria-controls="leaderboard" aria-selected="false">Leaderboard</button>
        </li>
        @if(Auth::user() && Auth::user()->hasRole('creator'))
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="dashboard-tab" data-bs-toggle="tab" data-bs-target="#dashboard" type="button" role="tab" aria-controls="dashboard" aria-selected="false">Creator Dashboard</button>
        </li>
        @endif
    </ul>
    <div class="tab-content" id="creatorTabsContent">
        @if(Auth::user() && Auth::user()->hasRole('admin'))
        <div class="tab-pane fade show active" id="admin-management" role="tabpanel" aria-labelledby="admin-management-tab">
            <h2>Creator Management (Admin Only)</h2>
            <ul class="list-group mb-4">
                <li class="list-group-item">
                    <a href="{{ route('creators.index') }}"><strong>Creators List</strong></a> – View and manage all creators.
                </li>
                <li class="list-group-item">
                    <a href="{{ route('creator.rewards.index') }}"><strong>Creator Rewards</strong></a> – View and manage creator rewards.
                </li>
                <li class="list-group-item">
                    <a href="{{ route('creator.payouts.index') }}"><strong>Creator Payouts</strong></a> – View and manage payout requests.
                </li>
                <li class="list-group-item">
                    <a href="{{ route('creators.create') }}"><strong>Register New Creator</strong></a> – Add a new creator profile.
                </li>
                <li class="list-group-item">
                    <a href="{{ route('creator.coupons.generate') }}"><strong>Creator Coupons</strong></a> – Generate and manage creator coupons.
                </li>
            </ul>
        </div>
        @endif
       
        <div class="tab-pane fade" id="leaderboard" role="tabpanel" aria-labelledby="leaderboard-tab">
            <h2>Top Creators</h2>
            <div class="table-responsive">
                <table class="table table-striped align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th scope="col">Rank</th>
                            <th scope="col">Creator</th>
                            <th scope="col">Points</th>
                            <th scope="col">User Count</th>
                            <th scope="col">Discount Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($topCreators as $i => $creator)
                        <tr>
                            <td>{{ $i + 1 }}</td>
                            <td>{{ $creator->user->name }}</td>
                            <td>{{ $creator->points ?? 0 }}</td>
                            <td>{{ $creator->referral_count ?? 0 }}</td>
                            <td>${{ number_format($creator->discount_amount ?? 0, 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        <div class="tab-pane fade" id="dashboard" role="tabpanel" aria-labelledby="dashboard-tab">
            <h2>Creator Dashboard</h2>
            <div class="row">
                @if($creator)
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">{{ $creator->user->name }}</h5>
                            <p class="card-text">Code: {{ $creator->code }}</p>
                            <p class="card-text">{{ $creator->bio }}</p>
                            @if($creator->avatar)
                                <img src="{{ asset('storage/' . $creator->avatar) }}" alt="{{ $creator->user->name }}" class="img-fluid mb-3">
                            @endif
                            <hr>
                            <p class="mb-1"><strong>Total Referrals:</strong> {{ $creator->referral_count }}</p>
                            <p class="mb-1"><strong>Total Earnings:</strong> ${{ number_format($creator->earnings, 2) }}</p>
                            <p class="mb-1"><strong>Points:</strong> {{ $creator->points }}</p>
                            <p class="mb-1"><strong>Badge:</strong> <span class="badge bg-{{ strtolower($creator->badge ?? 'participant') }}">{{ $creator->badge ?? 'Participant' }}</span></p>
                        </div>
                    </div>
                </div>
                <div class="col-md-8">
                    <h3>Referral Statistics</h3>
                    <p>Total Referrals: {{ $referrals->count() }}</p>
                    <p>Pending Referrals: {{ $referrals->where('status', 'pending')->count() }}</p>
                    <p>Used Referrals: {{ $referrals->where('status', 'used')->count() }}</p>
                    <p>Expired Referrals: {{ $referrals->where('status', 'expired')->count() }}</p>

                    <button id="generate-referral" class="btn btn-primary mb-3">Generate Referral Coupon</button>
                    <div id="coupon-code" class="alert alert-success d-none"></div>

                    <h3>My Referrals</h3>
                    <div class="row">
                        @foreach($referrals as $referral)
                            <div class="col-md-4 mb-4">
                                <div class="card">
                                    <div class="card-body">
                                        <h5 class="card-title">Coupon Code: {{ $referral->coupon_code }}</h5>
                                        <p class="card-text">Status: {{ $referral->status }}</p>
                                        @if($referral->referredUser)
                                            <p class="card-text">Referred User: {{ $referral->referredUser->name }}</p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
                @else
                <div class="col-12">
                    <div class="alert alert-warning">You are not registered as a creator. Please create a creator profile to access the dashboard features.</div>
                    <button class="btn btn-primary mb-3" disabled>Generate Referral Coupon</button>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    var generateBtn = document.getElementById('generate-referral');
    if (generateBtn) {
        generateBtn.addEventListener('click', function() {
            fetch('{{ route('creator-dashboard.generate-referral') }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                },
            })
            .then(response => {
                if (!response.ok) return response.json().then(err => { throw err; });
                return response.json();
            })
            .then(data => {
                document.getElementById('coupon-code').textContent = 'Coupon Code: ' + data.coupon_code;
                document.getElementById('coupon-code').classList.remove('d-none');
                document.getElementById('coupon-code').classList.remove('alert-danger');
                document.getElementById('coupon-code').classList.add('alert-success');
            })
            .catch(error => {
                let msg = error.message || (error.error || 'You are not registered as a creator. Please create a creator profile.');
                document.getElementById('coupon-code').textContent = msg;
                document.getElementById('coupon-code').classList.remove('d-none');
                document.getElementById('coupon-code').classList.remove('alert-success');
                document.getElementById('coupon-code').classList.add('alert-danger');
            });
        });
    }
</script>
@endsection 