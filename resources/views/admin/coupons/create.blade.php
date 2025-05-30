@extends('layouts.app')
@section('content')
<div class="container">
    <h1>Create Coupon (Admin)</h1>
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    <form method="POST" action="{{ route('admin.coupons.store') }}">
        @csrf
        <div class="mb-3">
            <label for="code" class="form-label">Code</label>
            <input type="text" class="form-control" id="code" name="code" required>
        </div>
        <div class="mb-3">
            <label for="type" class="form-label">Type</label>
            <select class="form-control" id="type" name="type" required>
                <option value="fixed">Fixed</option>
                <option value="percent">Percent</option>
            </select>
        </div>
        <div class="mb-3">
            <label for="value" class="form-label">Value</label>
            <input type="number" class="form-control" id="value" name="value" required step="0.01">
        </div>
        <div class="mb-3">
            <label for="active" class="form-label">Active</label>
            <select class="form-control" id="active" name="active" required>
                <option value="1">Yes</option>
                <option value="0">No</option>
            </select>
        </div>
        <div class="mb-3">
            <label for="creator_id" class="form-label">Creator (optional)</label>
            <select class="form-control" id="creator_id" name="creator_id">
                <option value="">None</option>
                @foreach($creators as $creator)
                    <option value="{{ $creator->id }}">{{ $creator->user->name }} ({{ $creator->code }})</option>
                @endforeach
            </select>
        </div>
        <div class="mb-3">
            <label for="campaign_name" class="form-label">Campaign Name</label>
            <input type="text" class="form-control" id="campaign_name" name="campaign_name">
        </div>
        <div class="mb-3">
            <label for="usage_limit" class="form-label">Usage Limit</label>
            <input type="number" class="form-control" id="usage_limit" name="usage_limit">
        </div>
        <div class="mb-3">
            <label for="expires_at" class="form-label">Expires At</label>
            <input type="date" class="form-control" id="expires_at" name="expires_at">
        </div>
        <button type="submit" class="btn btn-primary">Create Coupon</button>
    </form>
</div>
@endsection 