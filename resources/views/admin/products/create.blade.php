@extends('layouts.admin')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 bg-white border-b border-gray-200">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-2xl font-semibold text-gray-800">Create New Product</h2>
                    <a href="{{ route('admin.products.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-md text-sm font-medium">
                        Back to Products
                    </a>
                </div>

                <!-- Alert Container -->
                <div id="alert-container"></div>

                <form id="createProductForm" action="{{ route('admin.products.store') }}" method="POST" enctype="multipart/form-data" data-ajax="true" class="space-y-6">
                    @csrf

                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700">Product Name</label>
                        <input type="text" name="name" id="name" required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#6E0D25] focus:ring focus:ring-[#6E0D25] focus:ring-opacity-50">
                        <div id="name-error" class="text-red-500 text-sm mt-1"></div>
                    </div>

                    <div>
                        <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                        <textarea name="description" id="description" rows="3" required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#6E0D25] focus:ring focus:ring-[#6E0D25] focus:ring-opacity-50"></textarea>
                        <div id="description-error" class="text-red-500 text-sm mt-1"></div>
                    </div>

                    <div>
                        <label for="price" class="block text-sm font-medium text-gray-700">Price</label>
                        <input type="number" name="price" id="price" step="0.01" required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#6E0D25] focus:ring focus:ring-[#6E0D25] focus:ring-opacity-50">
                        <div id="price-error" class="text-red-500 text-sm mt-1"></div>
                    </div>

                    <div>
                        <label for="stock" class="block text-sm font-medium text-gray-700">Stock</label>
                        <input type="number" name="stock" id="stock" required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#6E0D25] focus:ring focus:ring-[#6E0D25] focus:ring-opacity-50">
                        <div id="stock-error" class="text-red-500 text-sm mt-1"></div>
                    </div>

                    

                    <div>
                        <label for="image" class="block text-sm font-medium text-gray-700">Product Image</label>
                        <input type="file" name="image" id="image" accept="image/*" required
                            class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-[#6E0D25] file:text-white hover:file:bg-[#8B0D2F]">
                        <div id="image-error" class="text-red-500 text-sm mt-1"></div>
                    </div>

                    <div class="flex items-center">
                        <input type="checkbox" name="is_active" id="is_active" value="1"
                            class="h-4 w-4 text-[#6E0D25] focus:ring-[#6E0D25] border-gray-300 rounded" checked>
                        <label for="is_active" class="ml-2 block text-sm text-gray-700">
                            Active
                        </label>
                    </div>
                    <div id="is_active-error" class="text-red-500 text-sm mt-1"></div>

                    <div>
                        <label for="tag" class="block text-sm font-medium text-gray-700">Tag</label>
                        <input type="text" name="tag" id="tag"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#6E0D25] focus:ring focus:ring-[#6E0D25] focus:ring-opacity-50">
                        <div id="tag-error" class="text-red-500 text-sm mt-1"></div>
                    </div>

                    <div class="flex items-center">
                        <input type="checkbox" name="is_featured" id="is_featured" value="1"
                            class="h-4 w-4 text-[#6E0D25] focus:ring-[#6E0D25] border-gray-300 rounded">
                        <label for="is_featured" class="ml-2 block text-sm text-gray-700">
                            Featured Product
                        </label>
                    </div>
                    <div id="is_featured-error" class="text-red-500 text-sm mt-1"></div>

                    <div class="flex justify-end">
                        <button type="submit"
                            class="bg-[#6E0D25] hover:bg-[#8B0D2F] text-white px-4 py-2 rounded-md text-sm font-medium focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#6E0D25]">
                            Create Product
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection 