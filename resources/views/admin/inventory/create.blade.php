@extends('layouts.admin')

@section('title', isset($branch) ? "Add Item to {$branch->name}" : 'Add New Item')

@section('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
@endsection

@section('content')
<div class="max-w-5xl mx-auto py-10 px-4">
    @if(isset($branch))
    <div class="mb-6 bg-white shadow rounded-lg p-4">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-semibold text-gray-800">{{ $branch->name }}</h2>
                <p class="text-sm text-gray-600">Branch Code: {{ $branch->code }}</p>
            </div>
            <a href="{{ route('admin.inventory.index', ['branch' => $branch->id]) }}" class="text-blue-600 hover:text-blue-800">
                <i class="fas fa-arrow-left mr-2"></i>Back to Inventory
            </a>
        </div>
    </div>
    @endif

    <div class="bg-white shadow rounded-lg overflow-hidden">
        <div class="bg-gray-100 px-6 py-4 border-b">
            <h2 class="text-xl font-semibold text-gray-800 flex items-center">
                <i class="fas fa-plus text-blue-500 mr-2"></i> Add New Item
            </h2>
        </div>

        <div class="px-6 py-6">
            @if(session('error'))
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <span class="block sm:inline">{{ session('error') }}</span>
                </div>
            @endif

            @if($errors->any())
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <ul class="list-disc list-inside">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('admin.inventory.store') }}" method="POST">
                @csrf
                @if(isset($branch))
                    <input type="hidden" name="branch_id" value="{{ $branch->id }}">
                @endif

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="name" class="block text-base font-semibold text-gray-800 mb-2">Item Name</label>
                        <input type="text" id="name" name="name" value="{{ old('name') }}"
                               class="block w-full border-2 border-blue-300 bg-blue-50 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 text-base p-3 @error('name') border-red-500 bg-red-50 @enderror" required>
                        @error('name')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="sku" class="block text-base font-semibold text-gray-800 mb-2">SKU</label>
                        <input type="text" id="sku" name="sku" value="{{ old('sku') }}"
                               class="block w-full border-2 border-blue-300 bg-blue-50 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 text-base p-3 @error('sku') border-red-500 bg-red-50 @enderror" required>
                        @error('sku')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="unit" class="block text-base font-semibold text-gray-800 mb-2">Unit</label>
                        <input type="text" id="unit" name="unit" value="{{ old('unit') }}"
                               class="block w-full border-2 border-blue-300 bg-blue-50 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 text-base p-3 @error('unit') border-red-500 bg-red-50 @enderror" required>
                        @error('unit')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="md:col-span-2">
                        <label for="description" class="block text-base font-semibold text-gray-800 mb-2">Description</label>
                        <textarea id="description" name="description" rows="4"
                                  class="block w-full border-2 border-blue-300 bg-blue-50 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 text-base p-3 @error('description') border-red-500 bg-red-50 @enderror">{{ old('description') }}</textarea>
                        @error('description')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="category_id" class="block text-base font-semibold text-gray-800 mb-2">Category</label>
                        <select id="category_id" name="category_id"
                                class="block w-full border-2 border-blue-300 bg-blue-50 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 text-base p-3 @error('category_id') border-red-500 bg-red-50 @enderror" required>
                            <option value="">Select Category</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('category_id')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="supplier_id" class="block text-base font-semibold text-gray-800 mb-2">Supplier</label>
                        <select id="supplier_id" name="supplier_id"
                                class="block w-full border-2 border-blue-300 bg-blue-50 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 text-base p-3 @error('supplier_id') border-red-500 bg-red-50 @enderror">
                            <option value="">Select Supplier</option>
                            @foreach($suppliers as $supplier)
                                <option value="{{ $supplier->id }}" {{ old('supplier_id') == $supplier->id ? 'selected' : '' }}>
                                    {{ $supplier->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('supplier_id')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="unit_price" class="block text-base font-semibold text-gray-800 mb-2">Unit Price ($)</label>
                        <input type="number" step="0.01" id="unit_price" name="unit_price" value="{{ old('unit_price') }}"
                               class="block w-full border-2 border-blue-300 bg-blue-50 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 text-base p-3 @error('unit_price') border-red-500 bg-red-50 @enderror" required>
                        @error('unit_price')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="reorder_point" class="block text-base font-semibold text-gray-800 mb-2">Reorder Point</label>
                        <input type="number" id="reorder_point" name="reorder_point" value="{{ old('reorder_point') }}"
                               class="block w-full border-2 border-blue-300 bg-blue-50 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 text-base p-3 @error('reorder_point') border-red-500 bg-red-50 @enderror" required>
                        @error('reorder_point')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="current_stock" class="block text-base font-semibold text-gray-800 mb-2">Current Stock</label>
                        <input type="number" id="current_stock" name="current_stock" value="{{ old('current_stock') }}"
                               class="block w-full border-2 border-blue-300 bg-blue-50 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 text-base p-3 @error('current_stock') border-red-500 bg-red-50 @enderror" required>
                        @error('current_stock')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="mt-8 flex justify-start gap-3">
                    <button type="submit" class="bg-blue-600 text-white px-6 py-3 rounded hover:bg-blue-700">
                        <i class="fas fa-save mr-2"></i>Add Item
                    </button>
                    <a href="{{ route('admin.inventory.index', isset($branch) ? ['branch' => $branch->id] : []) }}" 
                       class="bg-gray-500 text-white px-6 py-3 rounded hover:bg-gray-600">
                        <i class="fas fa-times mr-2"></i>Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    $(document).ready(function() {
        // Initialize Select2 for category and supplier
        $('#category_id, #supplier_id').select2({
            width: '100%'
        });
    });
</script>
@endsection
