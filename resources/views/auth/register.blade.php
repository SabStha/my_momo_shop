@extends('layouts.auth')

@section('content')
<div class="login-wrapper" style="min-height: 100vh; background: url('{{ asset('storage/products/background.png') }}') center/cover no-repeat; display: flex; justify-content: center; align-items: center; flex-direction: column;">

    {{-- Logo on top --}}
    <div class="text-center mb-4">
        <img src="{{ asset('storage/logo/momo_icon.png') }}" alt="AmaKo MOMO" style="height: 60px;">
        <h2 class="mt-2" style="font-weight: bold; color: #fff; text-shadow: 1px 1px 3px rgba(0,0,0,0.5);">AmaKo MOMO</h2>
    </div>

    {{-- Register Box --}}
    <div class="login-card" style="background-color: #fffaf3; border-radius: 20px; padding: 2rem 2.5rem; box-shadow: 0 10px 30px rgba(0,0,0,0.2); max-width: 400px; width: 100%;">

        {{-- Error and Session Feedback --}}
        @if (session('status'))
            <div class="alert alert-success">{{ session('status') }}</div>
        @endif
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('register') }}">
            @csrf

            <div class="mb-3">
                <label for="name" class="form-label" style="color: #6e3d1b;">Full Name</label>
                <input type="text" name="name" id="name" class="form-control" required autofocus value="{{ old('name') }}">
            </div>

            <div class="mb-3">
                <label for="email" class="form-label" style="color: #6e3d1b;">Email address</label>
                <input type="email" name="email" id="email" class="form-control" required value="{{ old('email') }}">
            </div>

            <div class="mb-3">
                <label for="password" class="form-label" style="color: #6e3d1b;">Password</label>
                <input type="password" name="password" id="password" class="form-control" required>
            </div>

            <div class="mb-4">
                <label for="password_confirmation" class="form-label" style="color: #6e3d1b;">Confirm Password</label>
                <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" required>
            </div>

            <button type="submit" class="btn w-100" style="background-color: #6e3d1b; color: #fff; font-weight: bold;">
                Register
            </button>
        </form>

        <div class="text-center mt-4">
            <span style="color: #6e3d1b;">Already have an account?</span>
            <a href="{{ route('login') }}" style="color: #a04d1a; font-weight: bold;">Log in</a>
        </div>
    </div>
</div>
@endsection
