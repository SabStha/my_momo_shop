@extends('layouts.admin')

@section('content')
<div class="container mx-auto p-4">
    <h1 class="text-2xl font-bold mb-4">Edit Product</h1>
    <form action="{{ route('admin.products.update', $product) }}" method="POST" enctype="multipart/form-data" class="space-y-6 bg-white p-6 rounded shadow">
        @csrf
        @method('PUT')

        @if ($errors->any())
            <div class="bg-red-100 text-red-700 p-4 rounded">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div>
            <label class="block font-medium mb-1" for="name">Name</label>
            <input type="text" name="name" id="name" value="{{ old('name', $product->name) }}" class="w-full border rounded px-3 py-2" required>
        </div>
        <div>
            <label class="block font-medium mb-1" for="code">Code</label>
            <input type="text" name="code" id="code" value="{{ old('code', $product->code) }}" class="w-full border rounded px-3 py-2" required>
        </div>
        <div>
            <label class="block font-medium mb-1" for="price">Price</label>
            <input type="number" name="price" id="price" value="{{ old('price', $product->price) }}" class="w-full border rounded px-3 py-2" step="0.01" required>
        </div>
        <div>
            <label class="block font-medium mb-1" for="stock">Stock</label>
            <input type="number" name="stock" id="stock" value="{{ old('stock', $product->stock) }}" class="w-full border rounded px-3 py-2" required>
        </div>
        <div>
            <label class="block font-medium mb-1" for="category">Category</label>
            <input type="text" name="category" id="category" value="{{ old('category', $product->category) }}" class="w-full border rounded px-3 py-2">
        </div>
        <div>
            <label class="block font-medium mb-1" for="tag">Tag</label>
            <input type="text" name="tag" id="tag" value="{{ old('tag', $product->tag) }}" class="w-full border rounded px-3 py-2">
        </div>
        <div>
            <label class="block font-medium mb-1" for="description">Description</label>
            <textarea name="description" id="description" class="w-full border rounded px-3 py-2" rows="3">{{ old('description', $product->description) }}</textarea>
        </div>
        <div>
            <label class="block font-medium mb-1" for="image">Image</label>
            @if($product->image)
                <div class="mb-2">
                    <img src="{{ asset('storage/' . $product->image) }}" alt="Current Image" class="h-20 w-20 object-cover rounded">
                </div>
            @endif
            <input type="file" name="image" id="image" class="w-full">
        </div>
        <div class="flex items-center space-x-4">
            <label class="inline-flex items-center">
                <input type="checkbox" name="is_active" value="1" {{ old('is_active', $product->is_active) ? 'checked' : '' }}>
                <span class="ml-2">Active</span>
            </label>
            <label class="inline-flex items-center">
                <input type="checkbox" name="is_featured" value="1" {{ old('is_featured', $product->is_featured) ? 'checked' : '' }}>
                <span class="ml-2">Featured</span>
            </label>
        </div>
        <div class="flex space-x-2">
            <a href="{{ route('admin.products.index') }}" class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400">Cancel</a>
            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Update Product</button>
        </div>
    </form>
</div>
@endsection