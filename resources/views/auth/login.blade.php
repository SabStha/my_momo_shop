@extends('layouts.app')

@section('content')
<div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gray-50">
    <div class="w-full sm:max-w-md mt-6 px-6 py-4 bg-white shadow-md overflow-hidden sm:rounded-lg">
        <div class="mb-4 text-center">
            <h2 class="text-2xl font-bold text-[#6E0D25]">Login</h2>
        </div>

        @if(session('error'))
            <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                <span class="block sm:inline">{{ session('error') }}</span>
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}">
            @csrf

            <!-- Contact (Email or Phone) -->
            <div>
                <label for="contact" class="block text-sm font-medium text-gray-700">Email or Phone Number</label>
                <input id="contact" type="text" name="contact" value="{{ old('contact') }}" required autofocus
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#6E0D25] focus:ring focus:ring-[#6E0D25] focus:ring-opacity-50 @error('contact') border-red-500 @enderror"
                    placeholder="Enter your email or phone number">
                @error('contact')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Password -->
            <div class="mt-4">
                <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                <input id="password" type="password" name="password" required
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#6E0D25] focus:ring focus:ring-[#6E0D25] focus:ring-opacity-50 @error('password') border-red-500 @enderror">
                @error('password')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Remember Me -->
            <div class="block mt-4">
                <label for="remember_me" class="inline-flex items-center">
                    <input id="remember_me" type="checkbox" name="remember"
                        class="rounded border-gray-300 text-[#6E0D25] shadow-sm focus:border-[#6E0D25] focus:ring focus:ring-[#6E0D25] focus:ring-opacity-50">
                    <span class="ml-2 text-sm text-gray-600">Remember me</span>
                </label>
            </div>

            <div class="flex items-center justify-between mt-4">
                <a href="{{ route('password.request') }}" class="text-sm text-[#6E0D25] hover:text-[#8B0D25]">
                    Forgot your password?
                </a>

                <button type="submit"
                    class="inline-flex items-center px-4 py-2 bg-[#6E0D25] border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-[#8B0D25] focus:bg-[#8B0D25] active:bg-[#8B0D25] focus:outline-none focus:ring-2 focus:ring-[#6E0D25] focus:ring-offset-2 transition ease-in-out duration-150">
                    Login
                </button>
            </div>
        </form>

        <div class="mt-6 text-center">
            <p class="text-sm text-gray-600">
                Don't have an account?
                <a href="{{ route('register') }}" class="font-medium text-[#6E0D25] hover:text-[#8B0D25]">
                    Register here
                </a>
            </p>
        </div>
    </div>
</div>
@endsection
