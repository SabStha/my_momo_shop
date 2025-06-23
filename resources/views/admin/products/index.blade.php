@extends('layouts.admin')

@section('title', 'Products')

@section('content')
<div class="container mx-auto p-4">
    <div class="flex justify-between items-center mb-8">
        <h1 class="text-xl font-semibold">Products</h1>
        <a href="{{ route('admin.products.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-medium">
            <i class="fas fa-plus mr-2"></i> Add Product
        </a>
    </div>

    @if(session('success'))
        <div class="bg-green-100 text-green-800 p-4 rounded mb-6">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="bg-red-100 text-red-800 p-4 rounded mb-6">
            {{ session('error') }}
        </div>
    @endif

    <div class="bg-white shadow-md rounded-lg">
        <div class="p-4">
            <div class="overflow-x-auto">
                <table class="min-w-full bg-white">
                    <thead>
                        <tr>
                            <th class="py-2 px-4 border-b">ID</th>
                            <th class="py-2 px-4 border-b">Image</th>
                            <th class="py-2 px-4 border-b">Name</th>
                            <th class="py-2 px-4 border-b">Code</th>
                            <th class="py-2 px-4 border-b">Price</th>
                            <th class="py-2 px-4 border-b">Stock</th>
                            <th class="py-2 px-4 border-b">Category</th>
                            <th class="py-2 px-4 border-b">Status</th>
                            <th class="py-2 px-4 border-b">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($products as $product)
                            <tr class="hover:bg-gray-100 text-center">
                                <td class="py-2 px-4 border-b font-medium">#{{ $product->id }}</td>
                                <td class="py-2 px-4 border-b">
                                    @if($product->image)
                                        <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}" class="mx-auto rounded" style="width: 45px; height: 45px; object-fit: cover;">
                                    @else
                                        <div class="w-11 h-11 bg-gray-100 flex items-center justify-center rounded">
                                            <i class="fas fa-image text-gray-400"></i>
                                        </div>
                                    @endif
                                </td>
                                <td class="py-2 px-4 border-b text-left">{{ $product->name }}</td>
                                <td class="py-2 px-4 border-b font-mono text-sm">{{ $product->code }}</td>
                                <td class="py-2 px-4 border-b">Rs. {{ number_format($product->price, 2) }}</td>
                                <td class="py-2 px-4 border-b">
                                    <span class="px-2 py-1 rounded-full text-xs font-medium
                                        {{ $product->stock > 10 ? 'bg-green-100 text-green-800' :
                                           ($product->stock > 0 ? 'bg-yellow-100 text-yellow-800' :
                                           'bg-red-100 text-red-800') }}">
                                        {{ $product->stock }}
                                    </span>
                                </td>
                                <td class="py-2 px-4 border-b">
                                    <span class="px-2 py-1 rounded-full text-white text-xs font-medium bg-blue-500">
                                        {{ $product->category ?: 'Uncategorized' }}
                                    </span>
                                </td>
                                <td class="py-2 px-4 border-b">
                                    <span class="px-2 py-1 rounded-full text-xs font-medium
                                        {{ $product->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                        {{ $product->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td class="py-2 px-4 border-b">
                                    <div class="flex justify-center space-x-2">
                                        <a href="{{ route('admin.products.edit', $product) }}" class="text-blue-600 hover:text-blue-800" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('admin.products.destroy', $product) }}" method="POST" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-800" onclick="return confirm('Are you sure you want to delete this product?')" title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-4">No products found</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $products->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
