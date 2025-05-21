@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h2>{{ $product->name }}</h2>
                </div>

                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <img src="{{ asset('storage/' . $product->image) }}" class="img-fluid" alt="{{ $product->name }}">
                        </div>
                        <div class="col-md-6">
                            <h4>Description</h4>
                            <p>{{ $product->description }}</p>
                            
                            <h4>Price</h4>
                            <p class="h3">${{ number_format($product->price, 2) }}</p>
                            
                            <h4>Stock</h4>
                            <p>{{ $product->stock }} units available</p>

                            <div class="mt-4">
                                @can('manage products')
                                <div class="mb-3">
                                    <a href="{{ route('products.edit', $product) }}" class="btn btn-warning">Edit Product</a>
                                    @can('delete products')
                                    <form action="{{ route('products.destroy', $product) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this product?')">Delete Product</button>
                                    </form>
                                    @endcan
                                </div>
                                @endcan

                                <form action="{{ route('cart.add', $product) }}" method="POST" class="mb-2">
                                    @csrf
                                    <div class="form-group">
                                        <label for="quantity">Quantity:</label>
                                        <input type="number" name="quantity" id="quantity" class="form-control" value="1" min="1" max="{{ $product->stock }}">
                                    </div>
                                    <div class="d-flex gap-2 mt-3">
                                        <button type="submit" class="btn btn-primary">Add to Cart</button>
                                        <button type="submit" formaction="{{ route('checkout.buyNow', $product) }}" class="btn btn-success">Buy Now</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 