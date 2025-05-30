@extends('desktop.layouts.app') 

@section('content')
{{-- Logo --}}
        <div class="text-center mb-4">
            <img src="{{ asset('storage/logo/momo_icon.png') }}" alt="AmaKo MOMO" style="height: 50px;">
            <h2 class="mt-2" style="font-weight: bold; color: #6e3d1b;">AmaKo MOMO</h2>
        </div>
<div class="login-wrapper" style="min-height: 100vh; background: url('{{ asset('storage/products/background.png') }}') center/cover no-repeat; display: flex; justify-content: center; align-items: center;">
    
    <div class="login-card" style="background-color: #fffaf3; border-radius: 20px; padding: 2rem 2.5rem; box-shadow: 0 10px 30px rgba(0,0,0,0.1); max-width: 400px; width: 100%;">
        
       

        {{-- Login Form --}}
        <form method="POST" action="{{ route('login') }}">
            @csrf

            <div class="mb-3">
                <label for="email" class="form-label" style="color: #6e3d1b;">Email address</label>
                <input type="email" name="email" id="email" class="form-control" required autofocus>
            </div>

            <div class="mb-4">
                <label for="password" class="form-label" style="color: #6e3d1b;">Password</label>
                <input type="password" name="password" id="password" class="form-control" required>
            </div>

            <button type="submit" class="btn w-100" style="background-color: #6e3d1b; color: #fff; font-weight: bold;">
                Log in
            </button>
        </form>

        <div class="text-center mt-4">
            <span style="color: #6e3d1b;">Don’t have an account?</span>
            <a href="{{ route('register') }}" style="color: #a04d1a; font-weight: bold;">Sign up</a>
        </div>
    </div>
</div>
@endsection
