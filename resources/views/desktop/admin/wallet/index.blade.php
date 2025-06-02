@extends('desktop.admin.layouts.admin')

@section('title', 'Wallet Management')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-success text-white fw-bold">
                    💳 Top Up Wallet
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.wallet.top-up') }}">
                        @csrf

                        {{-- Hidden --}}
                        <input type="hidden" name="user_id" value="{{ $user->id }}">

                        <div class="mb-3">
                            <label class="form-label">User Name</label>
                            <input type="text" class="form-control" value="{{ $user->name }}" readonly>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">User Email</label>
                            <input type="email" class="form-control" value="{{ $user->email }}" readonly>
                        </div>

                        <div class="mb-3">
                            <label for="amount" class="form-label">Top-Up Amount</label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" 
                                       name="amount" 
                                       id="amount" 
                                       class="form-control" 
                                       step="0.01" 
                                       min="0.01" 
                                       required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description (Optional)</label>
                            <textarea name="description" id="description" rows="3" class="form-control" placeholder="e.g., Promotional bonus, Manual adjustment..."></textarea>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('admin.wallet.index') }}" class="btn btn-secondary">← Back</a>
                            <button type="submit" class="btn btn-success">Top Up Wallet</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
