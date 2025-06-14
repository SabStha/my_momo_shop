@extends('layouts.pos')

@section('content')
<div class="min-h-screen bg-gray-100 flex items-center justify-center px-4">
    <div class="w-full max-w-md">
        <div class="bg-white shadow-md rounded-lg overflow-hidden">
            <div class="bg-indigo-600 px-6 py-4">
                <h2 class="text-white text-xl font-semibold flex items-center">
                    <i class="fas fa-lock mr-2"></i> POS Access Verification
                </h2>
            </div>

            <div class="px-6 py-6">
                @if(session('error'))
                    <div class="mb-4 text-sm text-red-600 bg-red-100 px-4 py-2 rounded">
                        {{ session('error') }}
                    </div>
                @endif

                <form id="posLoginForm" method="POST" action="{{ route('pos.login.submit') }}" class="space-y-5">
                    @csrf
                    <input type="hidden" name="branch_id" value="{{ $branch->id }}">

                    <div>
                        <label for="identifier" class="block text-sm font-medium text-gray-700">Email or ID</label>
                        <input id="identifier" name="identifier" type="text" autocomplete="off"
                               class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500
                               @error('identifier') border-red-500 @enderror"
                               value="{{ old('identifier') }}" required>

                        @error('identifier')
                            <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                        <input id="password" name="password" type="password" autocomplete="current-password"
                               class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500
                               @error('password') border-red-500 @enderror"
                               required>

                        @error('password')
                            <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="text-sm text-gray-500">
                        Logged in to Branch: <span class="font-semibold text-gray-700">{{ $branch->name }}</span>
                    </div>

                    <div>
                        <button type="submit"
                                class="w-full flex justify-center items-center px-4 py-2 bg-indigo-600 text-white font-semibold rounded-md hover:bg-indigo-700 transition disabled:opacity-50"
                                id="posLoginButton">
                            <i class="fas fa-sign-in-alt mr-2"></i> Login
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('posLoginForm');
    const submitButton = document.getElementById('posLoginButton');

    form.addEventListener('submit', function(e) {
        e.preventDefault();

        submitButton.disabled = true;
        submitButton.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Logging in...';

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
                localStorage.setItem('pos_token', data.token);
                localStorage.setItem('pos_user', JSON.stringify(data.user));
                localStorage.setItem('pos_branch', JSON.stringify(data.branch));
                window.location.href = data.redirect;
            } else {
                alert(data.message || 'Login failed. Please try again.');
                submitButton.disabled = false;
                submitButton.innerHTML = '<i class="fas fa-sign-in-alt mr-2"></i> Login';
            }
        })
        .catch(error => {
            console.error('Login error:', error);
            alert('An error occurred. Please try again.');
            submitButton.disabled = false;
            submitButton.innerHTML = '<i class="fas fa-sign-in-alt mr-2"></i> Login';
        });
    });
});
</script>
@endpush
