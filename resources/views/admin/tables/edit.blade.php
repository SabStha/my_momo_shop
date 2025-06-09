@extends('layouts.admin')

@section('title', 'Edit Table')

@section('content')
<div class="max-w-7xl mx-auto py-10 px-4">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-semibold text-gray-800">Edit Table</h1>
        <a href="{{ route('admin.tables.index') }}" class="text-gray-600 hover:text-gray-800">
            <i class="fas fa-arrow-left mr-2"></i>Back to Tables
        </a>
    </div>

    <div class="bg-white shadow rounded-lg overflow-hidden">
        <form action="{{ route('admin.tables.update', $table) }}" method="POST" class="p-6">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Name -->
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700">Name</label>
                    <input type="text" name="name" id="name" value="{{ old('name', $table->name) }}"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    @error('name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Number -->
                <div>
                    <label for="number" class="block text-sm font-medium text-gray-700">Table Number</label>
                    <input type="text" name="number" id="number" value="{{ old('number', $table->number) }}"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    @error('number')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Branch -->
                <div>
                    <label for="branch_id" class="block text-sm font-medium text-gray-700">Branch</label>
                    <select name="branch_id" id="branch_id"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="">Select a branch</option>
                        @foreach($branches as $branch)
                            <option value="{{ $branch->id }}" 
                                {{ old('branch_id', $table->branch_id) == $branch->id ? 'selected' : '' }}>
                                {{ $branch->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('branch_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Capacity -->
                <div>
                    <label for="capacity" class="block text-sm font-medium text-gray-700">Capacity</label>
                    <input type="number" name="capacity" id="capacity" 
                           value="{{ old('capacity', $table->capacity) }}" min="1"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    @error('capacity')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Status -->
                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                    <select name="status" id="status"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="available" {{ old('status', $table->status) == 'available' ? 'selected' : '' }}>Available</option>
                        <option value="occupied" {{ old('status', $table->status) == 'occupied' ? 'selected' : '' }}>Occupied</option>
                        <option value="reserved" {{ old('status', $table->status) == 'reserved' ? 'selected' : '' }}>Reserved</option>
                    </select>
                    @error('status')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Is Active -->
                <div>
                    <label for="is_active" class="block text-sm font-medium text-gray-700">Active Status</label>
                    <div class="mt-1">
                        <label class="inline-flex items-center">
                            <input type="checkbox" name="is_active" value="1" 
                                   {{ old('is_active', $table->is_active) ? 'checked' : '' }}
                                   class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <span class="ml-2 text-sm text-gray-600">Active</span>
                        </label>
                    </div>
                    @error('is_active')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="mt-6">
                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                    Update Table
                </button>
            </div>
        </form>
    </div>
</div>
@endsection 