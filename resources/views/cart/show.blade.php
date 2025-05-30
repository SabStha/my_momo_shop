@extends('layouts.app')

@section('content')
<div class="container py-5">
    <h1 class="mb-4">Your Cart</h1>
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('info'))
        <div class="alert alert-info">{{ session('info') }}</div>
    @endif
    @if(count($cart) && $products->count())
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Product</th>
                <th>Price</th>
                <th>Quantity</th>
                <th>Subtotal</th>
                <th></th>
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
                    <td>
                        <form action="{{ route('cart.remove', $product) }}" method="POST" style="display:inline;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger">Remove</button>
                        </form>
                    </td>
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
    <div class="text-end">
        <a href="{{ route('checkout') }}" class="btn btn-success">Proceed to Checkout</a>
    </div>
    @else
    <div class="alert alert-info">Your cart is empty.</div>
    @endif
</div>
@endsection 