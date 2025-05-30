@extends('desktop.layouts.app')

@section('content')
<div class="login-wrapper" style="min-height: 100vh; background: url('{{ asset('storage/products/background.png') }}') center/cover no-repeat; display: flex; justify-content: center; align-items: center; flex-direction: column;">

    {{-- Logo --}}
    <div class="text-center mb-4">
        <img src="{{ asset('storage/logo/momo_icon.png') }}" alt="AmaKo MOMO" style="height: 60px;">
        <h2 class="mt-2" style="font-weight: bold; color: #fff; text-shadow: 1px 1px 3px rgba(0,0,0,0.5);">AmaKo MOMO</h2>
    </div>

    {{-- Creator Registration Card --}}
    <div class="login-card" style="background-color: #fffaf3; border-radius: 20px; padding: 2rem 2.5rem; box-shadow: 0 10px 30px rgba(0,0,0,0.2); max-width: 500px; width: 100%;">
        
        <h4 class="mb-4 text-center" style="color: #6e3d1b;">Register as Creator</h4>

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('creator.register.submit') }}">
            @csrf

            <div class="mb-3">
                <label for="name" class="form-label" style="color: #6e3d1b;">Name</label>
                <input id="name" type="text" class="form-control @error('name') is-invalid @enderror"
                       name="name" value="{{ old('name') }}" required autocomplete="name" autofocus>
                @error('name')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
            </div>

            <div class="mb-3">
                <label for="email" class="form-label" style="color: #6e3d1b;">Email Address</label>
                <input id="email" type="email" class="form-control @error('email') is-invalid @enderror"
                       name="email" value="{{ old('email') }}" required autocomplete="email">
                @error('email')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
            </div>

            <div class="mb-3">
                <label for="password" class="form-label" style="color: #6e3d1b;">Password</label>
                <input id="password" type="password" class="form-control @error('password') is-invalid @enderror"
                       name="password" required autocomplete="new-password">
                <div class="form-text">Password must be at least 8 characters and include uppercase, lowercase, numbers, and symbols.</div>
                @error('password')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
            </div>

            <div class="mb-4">
                <label for="password-confirm" class="form-label" style="color: #6e3d1b;">Confirm Password</label>
                <input id="password-confirm" type="password" class="form-control"
                       name="password_confirmation" required autocomplete="new-password">
            </div>

            <button type="submit" class="btn w-100" style="background-color: #6e3d1b; color: #fff; font-weight: bold;">
                Register as Creator
            </button>
        </form>
    </div>
</div>
@endsection
