@extends('desktop.admin.layouts.admin')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('POS Access Verification') }}</div>

                <div class="card-body">
                    <form id="posLoginForm" method="POST" action="{{ route('pos.login.submit') }}">
                        @csrf

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

@push('scripts')
<script>
$(document).ready(function() {
    $('#posLoginForm').on('submit', function(e) {
        e.preventDefault();
        
        $.ajax({
            url: $(this).attr('action'),
            method: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                if (response.success) {
                    // Store the token
                    localStorage.setItem('pos_token', response.token);
                    localStorage.setItem('pos_user', JSON.stringify(response.user));
                    
                    // Redirect to POS
                    window.location.href = '{{ route("pos") }}';
                }
            },
            error: function(xhr) {
                let message = 'An error occurred. Please try again.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    message = xhr.responseJSON.message;
                }
                alert(message);
            }
        });
    });
});
</script>
@endpush
@endsection 