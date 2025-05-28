@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Creator Dashboard</h1>
    <div class="row">
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">{{ $creator->user->name }}</h5>
                    <p class="card-text">Code: {{ $creator->code }}</p>
                    <p class="card-text">{{ $creator->bio }}</p>
                    @if($creator->avatar)
                        <img src="{{ asset('storage/' . $creator->avatar) }}" alt="{{ $creator->user->name }}" class="img-fluid mb-3">
                    @endif
                </div>
            </div>
        </div>
        <div class="col-md-8">
            <h2>Referral Statistics</h2>
            <p>Total Referrals: {{ $referrals->count() }}</p>
            <p>Pending Referrals: {{ $referrals->where('status', 'pending')->count() }}</p>
            <p>Used Referrals: {{ $referrals->where('status', 'used')->count() }}</p>
            <p>Expired Referrals: {{ $referrals->where('status', 'expired')->count() }}</p>

            <button id="generate-referral" class="btn btn-primary mb-3">Generate Referral Coupon</button>
            <div id="coupon-code" class="alert alert-success d-none"></div>

            <h2>My Referrals</h2>
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
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.getElementById('generate-referral').addEventListener('click', function() {
        fetch('{{ route('creator-dashboard.generate-referral') }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
            },
        })
        .then(response => response.json())
        .then(data => {
            document.getElementById('coupon-code').textContent = 'Coupon Code: ' + data.coupon_code;
            document.getElementById('coupon-code').classList.remove('d-none');
        });
    });
</script>
@endsection 