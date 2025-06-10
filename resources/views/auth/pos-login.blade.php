@extends('layouts.pos')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('POS Access Verification') }}</div>

                <div class="card-body">
                    <form id="posLoginForm" method="POST" action="{{ route('pos.login.submit') }}">
                        @csrf
                        <input type="hidden" name="branch_id" value="{{ $branch->id }}">

                        <div class="mb-3">
                            <label for="identifier" class="form-label">{{ __('Email or ID') }}</label>
                            <input id="identifier" type="text" class="form-control @error('identifier') is-invalid @enderror" name="identifier" required autocomplete="off">

                            @error('identifier')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">{{ __('Password') }}</label>
                            <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" required autocomplete="current-password">

                            @error('password')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <p class="text-muted">Branch: <strong>{{ $branch->name }}</strong></p>
                        </div>

                        <div class="mb-0">
                            <button type="submit" class="btn btn-primary">
                                {{ __('Login') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('posLoginForm');
    
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        fetch(form.action, {
            method: 'POST',
            body: new FormData(form),
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Store the token and branch info
                localStorage.setItem('pos_token', data.token);
                localStorage.setItem('pos_user', JSON.stringify(data.user));
                localStorage.setItem('pos_branch', JSON.stringify(data.branch));
                
                // Redirect to POS with branch ID using the server-provided URL
                window.location.href = data.redirect;
            } else {
                alert(data.message || 'Login failed. Please try again.');
            }
        })
        .catch(error => {
            console.error('Login error:', error);
            alert('An error occurred. Please try again.');
        });
    });
});
</script>
@endpush 