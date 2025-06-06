@extends('layouts.admin')

@section('content')
<div class="max-w-4xl mx-auto py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Edit Employee</h1>
        <div class="flex gap-2">
            <a href="{{ route('admin.employees.show', $employee) }}" class="bg-blue-500 hover:bg-blue-700 text-white px-3 py-2 rounded flex items-center"><i class="fas fa-eye mr-2"></i> View Details</a>
            <a href="{{ route('admin.employees.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-3 py-2 rounded flex items-center"><i class="fas fa-arrow-left mr-2"></i> Back to List</a>
        </div>
    </div>
    <div class="bg-white shadow rounded-lg p-6">
            <form action="{{ route('admin.employees.update', $employee) }}" method="POST">
                @csrf
                @method('PUT')
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <h4 class="text-lg font-semibold mb-4">Account Information</h4>
                    <div class="mb-4">
                        <label for="name" class="block font-medium mb-1">Full Name</label>
                        <input type="text" id="name" name="name" value="{{ old('name', $employee->user->name) }}" required
                            class="w-full border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400 @error('name') border-red-500 @enderror">
                            @error('name')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    <div class="mb-4">
                        <label for="email" class="block font-medium mb-1">Email Address</label>
                        <input type="email" id="email" name="email" value="{{ old('email', $employee->user->email) }}" required
                            class="w-full border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400 @error('email') border-red-500 @enderror">
                            @error('email')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                <div>
                    <h4 class="text-lg font-semibold mb-4">Employment Details</h4>
                    <div class="mb-4">
                        <label for="position" class="block font-medium mb-1">Position</label>
                        <input type="text" id="position" name="position" value="{{ old('position', $employee->position) }}" required
                            class="w-full border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400 @error('position') border-red-500 @enderror">
                            @error('position')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    <div class="mb-4">
                        <label for="salary" class="block font-medium mb-1">Salary</label>
                        <input type="number" step="0.01" id="salary" name="salary" value="{{ old('salary', $employee->salary) }}" required
                            class="w-full border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400 @error('salary') border-red-500 @enderror">
                        @error('salary')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    <div class="mb-4">
                        <label for="hire_date" class="block font-medium mb-1">Hire Date</label>
                        <input type="date" id="hire_date" name="hire_date" value="{{ old('hire_date', $employee->hire_date ? $employee->hire_date->format('Y-m-d') : '') }}" required
                            class="w-full border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400 @error('hire_date') border-red-500 @enderror">
                        @error('hire_date')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    <div class="mb-4">
                        <label for="status" class="block font-medium mb-1">Status</label>
                        <select id="status" name="status" required
                            class="w-full border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400 @error('status') border-red-500 @enderror">
                            <option value="active" {{ old('status', $employee->status) == 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ old('status', $employee->status) == 'inactive' ? 'selected' : '' }}>Inactive</option>
                        </select>
                        @error('status')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                    </div>
                </div>
            </div>
            <div class="text-end mt-6">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded"><i class="fas fa-save mr-2"></i> Update Employee</button>
                </div>
            </form>
    </div>
</div>
@endsection 