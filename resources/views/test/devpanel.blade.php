@extends('layouts.app')
@section('content')
<div class="container">
    <h1>Creator System Test Panel</h1>

    <!-- Referral Test Section -->
    <div class="card mb-4">
        <div class="card-header">
            <h3>Referral Testing</h3>
        </div>
        <div class="card-body">
            <p><strong>Current Referral Code:</strong> {{ session('referral_code') ?? 'None' }}</p>
            <div class="mb-3">
                <label class="form-label">Test Referral Links:</label>
                <div class="d-flex gap-2">
                    <a href="{{ url('/test-panel?ref=creator123') }}" class="btn btn-primary">Test Valid Creator</a>
                    <a href="{{ url('/test-panel?ref=invalid123') }}" class="btn btn-warning">Test Invalid Creator</a>
                    <a href="{{ url('/test-panel') }}" class="btn btn-secondary">Clear Referral</a>
                </div>
            </div>
            @if(session('referral_error'))
                <div class="alert alert-danger">
                    {{ session('referral_error') }}
                </div>
            @endif
        </div>
    </div>

    <!-- Coupon Test Section -->
    <div class="card mb-4">
        <div class="card-header">
            <h3>Coupon Testing</h3>
        </div>
        <div class="card-body">
            @if(session('coupon_error'))
                <div class="alert alert-danger">
                    {{ session('coupon_error') }}
                </div>
            @endif
            @if(session('coupon_success'))
                <div class="alert alert-success">
                    {{ session('coupon_success') }}
                </div>
            @endif
            <form action="{{ route('coupon.apply') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label for="code" class="form-label">Coupon Code</label>
                    <input type="text" class="form-control @error('code') is-invalid @enderror" 
                           id="code" name="code" value="{{ old('code') }}" required>
                    @error('code')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="mb-3">
                    <label for="price" class="form-label">Price</label>
                    <input type="number" class="form-control @error('price') is-invalid @enderror" 
                           id="price" name="price" value="{{ old('price', 100) }}" step="0.01" required>
                    @error('price')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="mb-3">
                    <label for="referral_code" class="form-label">Referral Code (Optional)</label>
                    <input type="text" class="form-control" 
                           id="referral_code" name="referral_code" 
                           value="{{ session('referral_code') ?? old('referral_code') }}">
                </div>
                <button type="submit" class="btn btn-primary">Test Coupon</button>
            </form>
        </div>
    </div>

    <!-- Monthly Rewards Section -->
    <div class="card mb-4">
        <div class="card-header">
            <h3>Monthly Rewards</h3>
        </div>
        <div class="card-body">
            <a href="{{ route('creator.rewards.index') }}" class="btn btn-primary mb-3">View Creator Rewards</a>
            <form action="{{ route('test.assign-monthly-rewards') }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-success">Trigger Monthly Rewards Assignment</button>
            </form>
        </div>
    </div>

    <!-- Payout System Section -->
    <div class="card mb-4">
        <div class="card-header">
            <h3>Payout System</h3>
        </div>
        <div class="card-body">
            <a href="{{ route('creator.payouts.index') }}" class="btn btn-primary mb-3">View Creator Payouts</a>
            <a href="{{ route('admin.payouts.index') }}" class="btn btn-secondary">View Admin Payout Requests</a>
        </div>
    </div>

    <!-- Creator Registration Button -->
    <div class="card mb-4">
        <div class="card-header">
            <h3>Creator Registration</h3>
        </div>
        <div class="card-body">
            <a href="{{ route('creators.create') }}" class="btn btn-info">Register as a Creator</a>
        </div>
    </div>

    <!-- Session Messages -->
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
</div>
@endsection 