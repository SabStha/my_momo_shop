@extends('layouts.app')

@section('content')
<div class="container">
    <h1>My Referrals</h1>
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
@endsection 