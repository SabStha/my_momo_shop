@extends('layouts.app')
@section('content')
<div class="container">
    <h1>Generate My Coupon</h1>
    @if(isset($coupon))
        <div class="alert alert-success">
            <strong>Your Coupon:</strong> {{ $coupon->code }}<br>
            Type: {{ $coupon->type }}<br>
            Value: {{ $coupon->value }}<br>
            Status: {{ $coupon->active ? 'Active' : 'Inactive' }}
        </div>
    @else
        <form method="POST" action="">
            @csrf
            <button type="submit" class="btn btn-primary">Generate My Coupon</button>
        </form>
    @endif
</div>
@endsection 