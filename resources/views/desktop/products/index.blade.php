@extends('desktop.layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h2>Products</h2>
                    @can('manage products')
                    <a href="{{ route('products.create') }}" class="btn btn-primary">Add New Product</a>
                    @endcan
                </div>

                <div class="card-body">
                    @if (session('success'))
                        <div class="alert alert-success" role="alert">
                            {{ session('success') }}
                        </div>
                    @endif

                    <div class="row">
                        @foreach($products as $product)
                            <div class="col-md-4 mb-4">
                                <div class="card h-100">
                                    <img src="{{ asset('storage/' . $product->image) }}" class="card-img-top" alt="{{ $product->name }}" loading="lazy" width="400" height="400">
                                    <div class="card-body">
                                        <h5 class="card-title">{{ $product->name }}</h5>
                                        <p class="card-text">{{ Str::limit($product->description, 100) }}</p>
                                        <p class="card-text"><strong>Price: ${{ number_format($product->price, 2) }}</strong></p>
                                        <p class="card-text">Stock: {{ $product->stock }}</p>
                                        
                                        <div class="d-flex justify-content-between">
                                            <a href="{{ route('products.show', $product) }}" class="btn btn-info">View Details</a>
                                            
                                            @can('manage products')
                                            <div>
                                                <a href="{{ route('products.edit', $product) }}" class="btn btn-warning">Edit</a>
                                                @can('delete products')
                                                <form action="{{ route('products.destroy', $product) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this product?')">Delete</button>
                                                </form>
                                                @endcan
                                            </div>
                                            @endcan
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 