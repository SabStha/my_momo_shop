@extends('layouts.app')

@section('content')
<div class="container py-5">
    <h1 class="mb-4">Checkout</h1>
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(count($cart) && $products->count())
    <table class="table table-bordered mb-4">
        <thead>
            <tr>
                <th>Product</th>
                <th>Price</th>
                <th>Quantity</th>
                <th>Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @php $total = 0; @endphp
            @foreach($products as $product)
                @php $qty = $cart[$product->id]['quantity']; $subtotal = $qty * $product->price; $total += $subtotal; @endphp
                <tr>
                    <td>{{ $product->name }}</td>
                    <td>${{ number_format($product->price, 2) }}</td>
                    <td>{{ $qty }}</td>
                    <td>${{ number_format($subtotal, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="3" class="text-end fw-bold">Total:</td>
                <td class="fw-bold">${{ number_format($total, 2) }}</td>
            </tr>
        </tfoot>
    </table>
    <form action="{{ route('checkout.submit') }}" method="POST" class="mb-4">
        @csrf
        <div class="row mb-3">
            <div class="col-md-6">
                <label for="name" class="form-label">Name</label>
                <input type="text" name="name" id="name" class="form-control" required value="{{ old('name', Auth::user()->name ?? '') }}">
            </div>
            <div class="col-md-6">
                <label for="email" class="form-label">Email</label>
                <input type="email" name="email" id="email" class="form-control" required value="{{ old('email', Auth::user()->email ?? '') }}">
            </div>
        </div>
        <div class="mb-3">
            <label for="address" class="form-label">Shipping Address</label>
            <input type="text" name="address" id="address" class="form-control" required value="{{ old('address') }}">
        </div>
        <div class="text-end">
            <button type="submit" class="btn btn-primary">Place Order</button>
        </div>
    </form>
    @else
    <div class="alert alert-info">Your cart is empty.</div>
    @endif
</div>
@endsection 