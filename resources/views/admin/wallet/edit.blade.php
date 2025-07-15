@extends('layouts.admin')

@section('title', 'Edit Wallet Transaction')

@section('content')
    <div class="container">
        <h1>Edit Wallet Transaction</h1>
        
        <form action="{{ route('admin.wallet.update', $transaction->id) }}" method="POST">
            @csrf
            @method('PUT')
            
            <div class="form-group">
                <label for="user_id">User</label>
                <select name="user_id" id="user_id" class="form-control @error('user_id') is-invalid @enderror" required>
                    <option value="">Select User</option>
                    @foreach($users as $user)
                        <option value="{{ $user->id }}" {{ (old('user_id', $transaction->user_id) == $user->id) ? 'selected' : '' }}>
                            {{ $user->name }}
                        </option>
                    @endforeach
                </select>
                @error('user_id')
                    <span class="invalid-feedback">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="amount">Amount</label>
                <input type="number" step="0.01" name="amount" id="amount" class="form-control @error('amount') is-invalid @enderror" value="{{ old('amount', $transaction->amount) }}" required>
                @error('amount')
                    <span class="invalid-feedback">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="type">Transaction Type</label>
                <select name="type" id="type" class="form-control @error('type') is-invalid @enderror" required>
                    <option value="">Select Type</option>
                    <option value="credit" {{ old('type', $transaction->type) == 'credit' ? 'selected' : '' }}>Credit</option>
                    <option value="debit" {{ old('type', $transaction->type) == 'debit' ? 'selected' : '' }}>Debit</option>
                </select>
                @error('type')
                    <span class="invalid-feedback">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="description">Description</label>
                <textarea name="description" id="description" class="form-control @error('description') is-invalid @enderror" rows="3">{{ old('description', $transaction->description) }}</textarea>
                @error('description')
                    <span class="invalid-feedback">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <button type="submit" class="btn btn-primary">Update Transaction</button>
                <a href="{{ route('wallet.index') }}" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
@endsection 