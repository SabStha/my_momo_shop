@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Creator Management</h1>
    <ul class="nav nav-tabs mb-4" id="creatorTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="creators-tab" data-bs-toggle="tab" data-bs-target="#creators" type="button" role="tab" aria-controls="creators" aria-selected="true">Creators</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="leaderboard-tab" data-bs-toggle="tab" data-bs-target="#leaderboard" type="button" role="tab" aria-controls="leaderboard" aria-selected="false">Leaderboard</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="rewards-tab" data-bs-toggle="tab" data-bs-target="#rewards" type="button" role="tab" aria-controls="rewards" aria-selected="false">Creator Rewards</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="payouts-tab" data-bs-toggle="tab" data-bs-target="#payouts" type="button" role="tab" aria-controls="payouts" aria-selected="false">Creator Payouts</button>
        </li>
    </ul>
    <div class="tab-content" id="creatorTabsContent">
        <div class="tab-pane fade show active" id="creators" role="tabpanel" aria-labelledby="creators-tab">
            <h2>Creators</h2>
            <div class="row">
                @foreach($creators as $creator)
                    <div class="col-md-4 mb-4">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">{{ $creator->user->name }}</h5>
                                <p class="card-text">Code: {{ $creator->code }}</p>
                                <p class="card-text">{{ $creator->bio }}</p>
                                <a href="{{ route('creators.show', $creator->code) }}" class="btn btn-primary">View Profile</a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
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
        <div class="tab-pane fade" id="rewards" role="tabpanel" aria-labelledby="rewards-tab">
            <iframe src="{{ route('creator.rewards.index') }}" style="width:100%;min-height:600px;border:0;"></iframe>
        </div>
        <div class="tab-pane fade" id="payouts" role="tabpanel" aria-labelledby="payouts-tab">
            <iframe src="{{ route('creator.payouts.index') }}" style="width:100%;min-height:600px;border:0;"></iframe>
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