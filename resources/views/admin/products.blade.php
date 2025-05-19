@extends('layouts.app')

@section('content')
<div class="container">
    <div class="admin-header">
        <h1>Manage Products</h1>
        <a href="{{ route('admin.products.create') }}" class="btn">Add New Product</a>
    </div>

    <div class="products-table">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Image</th>
                    <th>Name</th>
                    <th>Price</th>
                    <th>Category</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($products as $product)
                    <tr>
                        <td>{{ $product->id }}</td>
                        <td>
                            <img src="{{ asset($product->image) }}" alt="{{ $product->name }}" class="product-thumbnail">
                        </td>
                        <td>{{ $product->name }}</td>
                        <td>${{ number_format($product->price, 2) }}</td>
                        <td>{{ $product->category }}</td>
                        <td class="actions">
                            <a href="{{ route('admin.products.edit', $product) }}" class="btn-edit">Edit</a>
                            <form action="{{ route('admin.products.destroy', $product) }}" method="POST" class="delete-form">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn-delete">Delete</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection 