@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="card">
                <div class="card-body">
                    <h1 class="card-title">{{ $creator->user->name }}</h1>
                    <p class="card-text">Code: {{ $creator->code }}</p>
                    <p class="card-text">{{ $creator->bio }}</p>
                    @if($creator->avatar)
                        <img src="{{ asset('storage/' . $creator->avatar) }}" alt="{{ $creator->user->name }}" class="img-fluid mb-3">
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 