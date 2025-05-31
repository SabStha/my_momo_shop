@extends('desktop.layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('POS Access') }}</div>

                <div class="card-body">
                    <form method="POST" action="{{ route('pos.login.submit') }}">
                        @csrf

                        <div class="mb-3">
                            <label for="id" class="form-label">{{ __('ID') }}</label>
                            <input id="id" type="text" class="form-control @error('id') is-invalid @enderror" 
                                   name="id" value="{{ old('id') }}" required autofocus>
                            @error('id')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">{{ __('Password') }}</label>
                            <input id="password" type="password" 
                                   class="form-control @error('password') is-invalid @enderror" 
                                   name="password" required>
                            @error('password')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">
                                {{ __('Access POS') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 