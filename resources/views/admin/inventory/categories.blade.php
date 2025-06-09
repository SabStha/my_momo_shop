@extends('layouts.admin')

@section('title', isset($branch) ? "{$branch->name} - Categories" : 'Inventory Categories')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-6">
    @if(isset($branch))
    <div class="mb-6 bg-white shadow rounded-lg p-4">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-4">
                <div class="bg-purple-100 p-3 rounded-full">
                    <i class="fas fa-store text-purple-600 text-xl"></i>
                </div>
                <div>
                    <h2 class="text-xl font-semibold text-gray-800">{{ $branch->name }}</h2>
                    <div class="flex items-center space-x-2">
                        <span class="text-sm text-gray-600">Branch Code: {{ $branch->code }}</span>
                        @if($branch->is_main)
                            <span class="px-2 py-1 text-xs font-semibold bg-green-100 text-green-800 rounded-full">Main Branch</span>
                        @endif
                    </div>
                </div>
            </div>
            <div class="flex items-center space-x-4">
                <form action="{{ route('admin.inventory.index') }}" method="GET" class="inline">
                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2">
                        <i class="fas fa-sign-out-alt mr-2"></i>Exit Branch View
                    </button>
                </form>
            </div>
        </div>
    </div>
    @endif

    <div class="flex items-center justify-between">
        <h2 class="text-2xl font-semibold text-gray-800">Inventory Categories</h2>
        <div class="flex space-x-3">
            <button type="button" class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500" onclick="document.getElementById('addCategoryModal').classList.remove('hidden')">
                <i class="fas fa-plus"></i> Add Category
            </button>
            <a href="{{ route('admin.inventory.index', isset($branch) ? ['branch' => $branch->id] : []) }}" class="inline-flex items-center gap-2 px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
                <i class="fas fa-arrow-left"></i> Back to Inventory
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-800 px-4 py-3 rounded-md shadow-sm">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="bg-red-100 border border-red-400 text-red-800 px-4 py-3 rounded-md shadow-sm">
            {{ session('error') }}
        </div>
    @endif

    <div class="overflow-x-auto bg-white shadow rounded-lg">
        <table class="min-w-full text-sm text-left">
            <thead class="bg-gray-100 text-gray-700 uppercase">
                <tr>
                    <th class="px-6 py-3">Code</th>
                    <th class="px-6 py-3">Name</th>
                    <th class="px-6 py-3">Description</th>
                    <th class="px-6 py-3">Items</th>
                    <th class="px-6 py-3">Status</th>
                    <th class="px-6 py-3">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @foreach($categories as $category)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4">{{ $category->code }}</td>
                    <td class="px-6 py-4">{{ $category->name }}</td>
                    <td class="px-6 py-4">{{ $category->description ?? 'No description' }}</td>
                    <td class="px-6 py-4">{{ $category->items_count }}</td>
                    <td class="px-6 py-4">
                        <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $category->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                            {{ $category->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex space-x-2">
                            <button type="button" class="px-3 py-1.5 bg-blue-600 text-white rounded hover:bg-blue-700" onclick="document.getElementById('editCategoryModal{{ $category->id }}').classList.remove('hidden')">
                                <i class="fas fa-edit"></i>
                            </button>
                            @if($category->items_count == 0)
                            <form action="{{ route('admin.inventory.categories.delete', $category) }}{{ isset($branch) ? '?branch=' . $branch->id : '' }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="px-3 py-1.5 bg-red-600 text-white rounded hover:bg-red-700" onclick="return confirm('Are you sure?')">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                            @endif
                        </div>

                        <!-- Edit Modal -->
                        <div class="modal fixed inset-0 z-50 bg-black bg-opacity-50 hidden items-center justify-center" id="editCategoryModal{{ $category->id }}">
                            <div class="bg-white p-6 rounded-lg shadow-lg w-full max-w-lg">
                                <form action="{{ route('admin.inventory.categories.update', $category) }}{{ isset($branch) ? '?branch=' . $branch->id : '' }}" method="POST">
                                    @csrf
                                    @method('PUT')
                                    <div class="mb-4">
                                        <h3 class="text-lg font-semibold mb-4">Edit Category</h3>
                                        <label class="block text-sm font-medium mb-1">Name</label>
                                        <input type="text" name="name" value="{{ $category->name }}" required class="w-full border-gray-300 rounded px-3 py-2">
                                    </div>
                                    <div class="mb-4">
                                        <label class="block text-sm font-medium mb-1">Code</label>
                                        <input type="text" name="code" value="{{ $category->code }}" required class="w-full border-gray-300 rounded px-3 py-2">
                                    </div>
                                    <div class="mb-4">
                                        <label class="block text-sm font-medium mb-1">Description</label>
                                        <textarea name="description" rows="3" class="w-full border-gray-300 rounded px-3 py-2">{{ $category->description }}</textarea>
                                    </div>
                                    <div class="mb-4">
                                        <label class="flex items-center">
                                            <input type="checkbox" name="is_active" value="1" {{ $category->is_active ? 'checked' : '' }} class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                                            <span class="ml-2">Active</span>
                                        </label>
                                    </div>
                                    <div class="flex justify-end gap-2">
                                        <button type="button" onclick="document.getElementById('editCategoryModal{{ $category->id }}').classList.add('hidden')" class="px-4 py-2 bg-gray-500 text-white rounded hover:bg-gray-600">Cancel</button>
                                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Update Category</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<!-- Add Category Modal -->
<div class="modal fixed inset-0 z-50 bg-black bg-opacity-50 hidden items-center justify-center" id="addCategoryModal">
    <div class="bg-white p-6 rounded-lg shadow-lg w-full max-w-lg">
        <form action="{{ route('admin.inventory.categories.store') }}{{ isset($branch) ? '?branch=' . $branch->id : '' }}" method="POST">
            @csrf
            <h3 class="text-lg font-semibold mb-4">Add Category</h3>
            <div class="mb-4">
                <label class="block text-sm font-medium mb-1">Name</label>
                <input type="text" name="name" required class="w-full border-gray-300 rounded px-3 py-2">
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium mb-1">Code</label>
                <input type="text" name="code" required class="w-full border-gray-300 rounded px-3 py-2">
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium mb-1">Description</label>
                <textarea name="description" rows="3" class="w-full border-gray-300 rounded px-3 py-2"></textarea>
            </div>
            <div class="flex justify-end gap-2">
                <button type="button" onclick="document.getElementById('addCategoryModal').classList.add('hidden')" class="px-4 py-2 bg-gray-500 text-white rounded hover:bg-gray-600">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Create Category</button>
            </div>
        </form>
    </div>
</div>
@endsection
