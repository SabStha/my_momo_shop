@extends('layouts.app')

@section('content')
<div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
        <div>
            <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
                Create your account
            </h2>
        </div>

        <div id="errorMessage" class="hidden rounded-md bg-red-50 p-4 mb-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-red-800" id="errorText"></p>
                </div>
            </div>
        </div>

        <form id="registerForm" class="mt-8 space-y-6" action="{{ route('register.submit') }}" method="POST">
            @csrf
            <input type="hidden" name="_token" value="{{ csrf_token() }}">
            <div class="rounded-md shadow-sm space-y-4">
                <!-- Name -->
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700">Full Name</label>
                    <input id="name" name="name" type="text" required class="appearance-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-md focus:outline-none focus:ring-[#6E0D25] focus:border-[#6E0D25] focus:z-10 sm:text-sm" value="{{ old('name') }}">
                    <div class="text-red-500 text-xs mt-1 hidden" id="nameError"></div>
                </div>

                <!-- Email -->
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700">Email address</label>
                    <input id="email" name="email" type="email" required class="appearance-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-md focus:outline-none focus:ring-[#6E0D25] focus:border-[#6E0D25] focus:z-10 sm:text-sm" value="{{ old('email') }}">
                    <div class="text-red-500 text-xs mt-1 hidden" id="emailError"></div>
                </div>

                <!-- Phone -->
                <div>
                    <label for="phone" class="block text-sm font-medium text-gray-700">Phone Number</label>
                    <input id="phone" name="phone" type="tel" required class="appearance-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-md focus:outline-none focus:ring-[#6E0D25] focus:border-[#6E0D25] focus:z-10 sm:text-sm" value="{{ old('phone') }}">
                    <div class="text-red-500 text-xs mt-1 hidden" id="phoneError"></div>
                </div>

                <!-- Password -->
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                    <input id="password" name="password" type="password" required class="appearance-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-md focus:outline-none focus:ring-[#6E0D25] focus:border-[#6E0D25] focus:z-10 sm:text-sm">
                    <div class="text-red-500 text-xs mt-1 hidden" id="passwordError"></div>
                </div>

                <!-- Confirm Password -->
                <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-gray-700">Confirm Password</label>
                    <input id="password_confirmation" name="password_confirmation" type="password" required class="appearance-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-md focus:outline-none focus:ring-[#6E0D25] focus:border-[#6E0D25] focus:z-10 sm:text-sm">
                </div>

                <!-- Terms and Conditions -->
                <div class="flex items-center">
                    <input id="terms" name="terms" type="checkbox" required class="h-4 w-4 text-[#6E0D25] focus:ring-[#6E0D25] border-gray-300 rounded">
                    <label for="terms" class="ml-2 block text-sm text-gray-900">
                        I agree to the <a href="{{ route('terms') }}" class="text-[#6E0D25] hover:text-[#8B0F2F]">Terms and Conditions</a>
                    </label>
                </div>
                <div class="text-red-500 text-xs mt-1 hidden" id="termsError"></div>
            </div>

            <div>
                <button type="submit" class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-[#6E0D25] hover:bg-[#8B0F2F] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#6E0D25]">
                    Register
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Success Modal -->
<div id="successModal" class="fixed inset-0 bg-gray-500 bg-opacity-75 flex items-center justify-center hidden">
    <div class="bg-white p-6 rounded-lg shadow-xl max-w-sm w-full mx-4">
        <div class="text-center">
            <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-green-100">
                <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
            </div>
            <h3 class="mt-4 text-lg font-medium text-gray-900">Success!</h3>
            <p class="mt-2 text-sm text-gray-500" id="successMessage"></p>
            <div class="mt-4">
                <button type="button" onclick="document.getElementById('successModal').classList.add('hidden')" class="inline-flex justify-center px-4 py-2 text-sm font-medium text-white bg-[#6E0D25] border border-transparent rounded-md hover:bg-[#8B0F2F] focus:outline-none focus-2 focus:ring-offset-2 focus:ring-[#6E0D25]">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Error Modal -->
<div id="errorModal" class="fixed inset-0 bg-gray-500 bg-opacity-75 flex items-center justify-center hidden">
    <div class="bg-white p-6 rounded-lg shadow-xl max-w-sm w-full mx-4">
        <div class="text-center">
            <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100">
                <svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </div>
            <h3 class="mt-4 text-lg font-medium text-gray-900">Error</h3>
            <p class="mt-2 text-sm text-gray-500" id="errorModalMessage"></p>
            <div class="mt-4">
                <button type="button" onclick="document.getElementById('errorModal').classList.add('hidden')" class="inline-flex justify-center px-4 py-2 text-sm font-medium text-white bg-[#6E0D25] border border-transparent rounded-md hover:bg-[#8B0F2F] focus:outline-none focus-2 focus:ring-offset-2 focus:ring-[#6E0D25]">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<!-- Add SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
document.getElementById('registerForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    console.log('Form submitted');
    
    const formData = new FormData(this);
    console.log('Form data:', Object.fromEntries(formData));
    
    try {
        const response = await fetch('/register', {
            method: 'POST',
            body: formData,
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
        
        console.log('Response status:', response.status);
        const data = await response.json();
        console.log('Response data:', data);
        
        if (response.ok) {
            // Show success message
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: data.message || 'Registration successful!',
                showConfirmButton: false,
                timer: 2000
            }).then(() => {
                window.location.href = '/';
            });
        } else {
            // Handle validation errors
            let errorMessage = '';
            if (data.errors) {
                // Format validation errors
                errorMessage = Object.entries(data.errors)
                    .map(([field, messages]) => `${field}: ${messages.join(', ')}`)
                    .join('\n');
            } else {
                errorMessage = data.message || 'Registration failed. Please try again.';
            }
            
            Swal.fire({
                icon: 'error',
                title: 'Registration Failed',
                text: errorMessage,
                confirmButtonText: 'Try Again'
            });
        }
    } catch (error) {
        console.error('Error:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'An unexpected error occurred. Please try again.',
            confirmButtonText: 'Try Again'
        });
    }
});
</script>
@endpush
@endsection 