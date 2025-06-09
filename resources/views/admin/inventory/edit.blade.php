@extends('layouts.admin')

@section('title', 'Edit Inventory Item')

@section('content')
<div class="max-w-5xl mx-auto py-10 px-4">
    <div class="bg-white shadow rounded-lg overflow-hidden">
        <div class="bg-gray-100 px-6 py-4 border-b">
            <h2 class="text-xl font-semibold text-gray-800 flex items-center">
                <i class="fas fa-edit text-blue-500 mr-2"></i> Edit Inventory Item
            </h2>
        </div>

        <div class="px-6 py-6">
            @if(session('error'))
                <div class="bg-red-100 text-red-700 px-4 py-3 rounded mb-4">
                    {{ session('error') }}
                </div>
            @endif

            @if(session('success'))
                <div class="bg-green-100 text-green-700 px-4 py-3 rounded mb-4">
                    {{ session('success') }}
                </div>
                <div id="successModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
                    <div class="bg-white rounded-lg p-8 max-w-md w-full mx-4">
                        <div class="text-center mb-6">
                            <i class="fas fa-check-circle text-green-500 text-5xl mb-4"></i>
                            <h3 class="text-xl font-semibold text-gray-800 mb-2">Success!</h3>
                            <p class="text-gray-600">Item has been updated successfully.</p>
                        </div>
                        <div class="flex flex-col gap-3">
                            <a href="{{ route('admin.inventory.index') }}" class="bg-blue-600 text-white px-6 py-3 rounded hover:bg-blue-700 text-center">
                                <i class="fas fa-box mr-2"></i>Return to Inventory
                            </a>
                            <a href="{{ route('admin.supply.orders.index') }}" class="bg-green-600 text-white px-6 py-3 rounded hover:bg-green-700 text-center">
                                <i class="fas fa-truck mr-2"></i>Go to Supply Orders
                            </a>
                            <button onclick="closeSuccessModal()" class="bg-gray-500 text-white px-6 py-3 rounded hover:bg-gray-600">
                                <i class="fas fa-times mr-2"></i>Stay on this page
                            </button>
                        </div>
                    </div>
                </div>
            @endif

            <form action="{{ url('/admin/inventory/' . $item->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="name" class="block text-base font-semibold text-gray-800 mb-2">Item Name</label>
                        <input type="text" id="name" name="name" value="{{ old('name', $item->name) }}"
                               class="block w-full border-2 border-blue-300 bg-blue-50 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 text-base p-3 @error('name') border-red-500 bg-red-50 @enderror" required>
                        @error('name')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="sku" class="block text-base font-semibold text-gray-800 mb-2">SKU</label>
                        <input type="text" id="sku" name="sku" value="{{ old('sku', $item->sku) }}"
                               class="block w-full border-2 border-blue-300 bg-blue-50 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 text-base p-3 @error('sku') border-red-500 bg-red-50 @enderror" required>
                        @error('sku')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="md:col-span-2">
                        <label for="description" class="block text-base font-semibold text-gray-800 mb-2">Description</label>
                        <textarea id="description" name="description" rows="4"
                                  class="block w-full border-2 border-blue-300 bg-blue-50 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 text-base p-3 @error('description') border-red-500 bg-red-50 @enderror">{{ old('description', $item->description) }}</textarea>
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
                                <option value="{{ $category->id }}" {{ old('category_id', $item->category_id) == $category->id ? 'selected' : '' }}>
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
                                <option value="{{ $supplier->id }}" {{ old('supplier_id', $item->supplier_id) == $supplier->id ? 'selected' : '' }}>
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
                        <input type="number" step="0.01" id="unit_price" name="unit_price" value="{{ old('unit_price', $item->unit_price) }}"
                               class="block w-full border-2 border-blue-300 bg-blue-50 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 text-base p-3 @error('unit_price') border-red-500 bg-red-50 @enderror" required>
                        @error('unit_price')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="reorder_point" class="block text-base font-semibold text-gray-800 mb-2">Reorder Point</label>
                        <input type="number" id="reorder_point" name="reorder_point" value="{{ old('reorder_point', $item->reorder_point) }}"
                               class="block w-full border-2 border-blue-300 bg-blue-50 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 text-base p-3 @error('reorder_point') border-red-500 bg-red-50 @enderror" required>
                        @error('reorder_point')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="current_stock" class="block text-base font-semibold text-gray-800 mb-2">Current Stock</label>
                        <input type="number" id="current_stock" name="current_stock" value="{{ old('current_stock', $item->current_stock) }}"
                               class="block w-full border-2 border-blue-300 bg-blue-50 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 text-base p-3 @error('current_stock') border-red-500 bg-red-50 @enderror" required>
                        @error('current_stock')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="mt-8 flex justify-start gap-3">
                    <button type="submit" class="bg-blue-600 text-white px-6 py-3 rounded hover:bg-blue-700">
                        <i class="fas fa-save mr-2"></i>Update Item
                    </button>
                    <a href="{{ route('admin.inventory.index') }}" class="bg-gray-500 text-white px-6 py-3 rounded hover:bg-gray-600">
                        <i class="fas fa-times mr-2"></i>Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
function closeSuccessModal() {
    document.getElementById('successModal').style.display = 'none';
}
</script>
@endpush
@endsection
