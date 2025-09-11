@extends('layouts.app')

@section('content')
<div class="min-h-screen flex items-center justify-center bg-gray-50 px-4 py-8">
    <div class="w-full max-w-sm bg-white rounded-xl shadow-lg overflow-hidden">
        <!-- Header -->
        <div class="bg-blue-600 text-white px-6 py-4 text-center">
            <h2 class="text-lg font-semibold flex items-center justify-center">
                <i class="fas fa-credit-card mr-2"></i> Payment Management Access
            </h2>
            <p class="text-sm mt-1 opacity-80">Secure Payment System Login</p>
        </div>

        <!-- Form -->
        <div class="px-6 py-6">
            @if(session('error'))
                <div class="mb-4 text-sm text-red-600 bg-red-100 px-3 py-2 rounded">
                    {{ session('error') }}
                </div>
            @endif

            <form id="paymentLoginForm" method="POST" action="{{ route('payment.login.submit') }}" class="space-y-5">
                @csrf
                <input type="hidden" name="branch_id" value="{{ $branch->id }}">

                <!-- Identifier -->
                <div>
                    <label for="identifier" class="block text-sm font-medium text-gray-700 mb-1">Email or ID</label>
                    <input id="identifier" name="identifier" type="text" inputmode="text" autocomplete="off"
                           class="w-full px-3 py-2 rounded-md border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:outline-none
                                  @error('identifier') border-red-500 @enderror"
                           value="{{ old('identifier') }}" required>

                    @error('identifier')
                        <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Password -->
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                    <input id="password" name="password" type="password" autocomplete="current-password"
                           class="w-full px-3 py-2 rounded-md border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:outline-none
                                  @error('password') border-red-500 @enderror"
                           required>

                    @error('password')
                        <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Branch Info -->
                <div class="text-sm text-gray-600">
                    <span class="font-semibold text-gray-800">Branch:</span> {{ $branch->name }}
                </div>

                <!-- Submit -->
                <div>
                    <button type="submit"
                            class="w-full py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-md shadow-sm
                                   flex justify-center items-center transition disabled:opacity-50"
                            id="paymentLoginButton">
                        <i class="fas fa-sign-in-alt mr-2"></i> Login to Payment System
                    </button>
                </div>
            </form>

            <!-- Back to Admin -->
            <div class="mt-4 text-center">
                <a href="{{ route('admin.branches.index') }}" 
                   class="text-sm text-gray-500 hover:text-gray-700 flex items-center justify-center">
                    <i class="fas fa-arrow-left mr-1"></i> Back to Admin Dashboard
                </a>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('paymentLoginForm');
    const submitButton = document.getElementById('paymentLoginButton');

    form.addEventListener('submit', function(e) {
        e.preventDefault();

        submitButton.disabled = true;
        submitButton.innerHTML = '<svg class="animate-spin h-5 w-5 mr-2 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path></svg> Logging in...';

        fetch(form.action, {
            method: 'POST',
            body: new FormData(form),
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                localStorage.setItem('payment_token', data.token);
                localStorage.setItem('payment_user', JSON.stringify(data.user));
                localStorage.setItem('payment_branch', JSON.stringify(data.branch));
                window.location.href = data.redirect;
            } else {
                alert(data.message || 'Login failed. Please try again.');
                submitButton.disabled = false;
                submitButton.innerHTML = '<i class="fas fa-sign-in-alt mr-2"></i> Login to Payment System';
            }
        })
        .catch(error => {
            console.error('Login error:', error);
            alert('An error occurred. Please try again.');
            submitButton.disabled = false;
            submitButton.innerHTML = '<i class="fas fa-sign-in-alt mr-2"></i> Login to Payment System';
        });
    });
});
</script>
@endpush
