@extends('layouts.admin')

@section('content')
<div class="max-w-4xl mx-auto py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Edit Employee</h1>
        <div class="flex gap-2">
            <a href="{{ route('admin.employees.show', $employee) }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md shadow-sm flex items-center gap-2">
                <i class="fas fa-eye"></i> View Details
            </a>
            <a href="{{ route('admin.employees.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-800 px-4 py-2 rounded-md shadow-sm flex items-center gap-2">
                <i class="fas fa-arrow-left"></i> Back to List
            </a>
        </div>
    </div>

    <div class="bg-white shadow-md rounded-lg p-6">
        <form action="{{ route('admin.employees.update', $employee) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <h4 class="text-lg font-semibold mb-4 text-gray-700">Account Information</h4>

                    <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
                    <input type="text" name="name" id="name" value="{{ old('name', $employee->user->name) }}" required
                        class="w-full border border-gray-300 rounded-md px-4 py-2 focus:ring-2 focus:ring-blue-500 @error('name') border-red-500 @enderror">
                    @error('name')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror

                    <label for="email" class="block text-sm font-medium text-gray-700 mt-4 mb-1">Email Address</label>
                    <input type="email" name="email" id="email" value="{{ old('email', $employee->user->email) }}" required
                        class="w-full border border-gray-300 rounded-md px-4 py-2 focus:ring-2 focus:ring-blue-500 @error('email') border-red-500 @enderror">
                    @error('email')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <h4 class="text-lg font-semibold mb-4 text-gray-700">Employment Details</h4>

                    <label for="position" class="block text-sm font-medium text-gray-700 mb-1">Position</label>
                    <input type="text" name="position" id="position" value="{{ old('position', $employee->position) }}" required
                        class="w-full border border-gray-300 rounded-md px-4 py-2 focus:ring-2 focus:ring-blue-500 @error('position') border-red-500 @enderror">
                    @error('position')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror

                    <label for="salary" class="block text-sm font-medium text-gray-700 mt-4 mb-1">Salary</label>
                    <input type="number" name="salary" step="0.01" id="salary" value="{{ old('salary', $employee->salary) }}" required
                        class="w-full border border-gray-300 rounded-md px-4 py-2 focus:ring-2 focus:ring-blue-500 @error('salary') border-red-500 @enderror">
                    @error('salary')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror

                    <label for="hire_date" class="block text-sm font-medium text-gray-700 mt-4 mb-1">Hire Date</label>
                    <input type="date" name="hire_date" id="hire_date" value="{{ old('hire_date', $employee->hire_date ? $employee->hire_date->format('Y-m-d') : '') }}" required
                        class="w-full border border-gray-300 rounded-md px-4 py-2 focus:ring-2 focus:ring-blue-500 @error('hire_date') border-red-500 @enderror">
                    @error('hire_date')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror

                    <label for="status" class="block text-sm font-medium text-gray-700 mt-4 mb-1">Status</label>
                    <select name="status" id="status" required
                        class="w-full border border-gray-300 rounded-md px-4 py-2 focus:ring-2 focus:ring-blue-500 @error('status') border-red-500 @enderror">
                        <option value="active" {{ old('status', $employee->status) == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ old('status', $employee->status) == 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                    @error('status')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="text-end mt-6">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-medium px-5 py-2 rounded-md shadow-sm">
                    <i class="fas fa-save mr-2"></i> Update Employee
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
