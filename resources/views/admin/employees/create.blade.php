@extends('layouts.admin')

@section('content')
<div class="max-w-2xl mx-auto py-10 px-4">
    <div class="bg-white shadow-md rounded-lg  p-6 max-w-xl mx-auto">
        <h2 class="text-xl font-bold text-gray-800 mb-6"> Add New Employee</h2>

        @if(session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6 text-sm">
                {{ session('error') }}
            </div>
        @endif

        <form method="POST" action="{{ route('admin.employees.store') }}" class="space-y-5">
            @csrf

            <!-- Name -->
            <div>
                <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Name</label>
                <input type="text" id="name" name="name" value="{{ old('name') }}" required
                    class="w-full text-sm rounded-md border-gray-300 shadow-sm px-4 py-2 bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('name') border-red-500 @enderror">
                @error('name')
                    <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Email -->
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                <input type="email" id="email" name="email" value="{{ old('email') }}" required
                    class="w-full text-sm rounded-md border-gray-300 shadow-sm px-4 py-2 bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('email') border-red-500 @enderror">
                @error('email')
                    <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Password -->
            <div>
                <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                <input type="password" id="password" name="password" required
                    class="w-full text-sm rounded-md border-gray-300 shadow-sm px-4 py-2 bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('password') border-red-500 @enderror">
                @error('password')
                    <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Position -->
            <div>
                <label for="position" class="block text-sm font-medium text-gray-700 mb-1">Position</label>
                <input type="text" id="position" name="position" value="{{ old('position') }}" required
                    class="w-full text-sm rounded-md border-gray-300 shadow-sm px-4 py-2 bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('position') border-red-500 @enderror">
                @error('position')
                    <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Salary -->
            <div>
                <label for="salary" class="block text-sm font-medium text-gray-700 mb-1">Salary</label>
                <input type="number" step="0.01" id="salary" name="salary" value="{{ old('salary') }}" required
                    class="w-full text-sm rounded-md border-gray-300 shadow-sm px-4 py-2 bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('salary') border-red-500 @enderror">
                @error('salary')
                    <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Hire Date -->
            <div>
                <label for="hire_date" class="block text-sm font-medium text-gray-700 mb-1">Hire Date</label>
                <input type="date" id="hire_date" name="hire_date" value="{{ old('hire_date') }}" required
                    class="w-full text-sm rounded-md border-gray-300 shadow-sm px-4 py-2 bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('hire_date') border-red-500 @enderror">
                @error('hire_date')
                    <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Status -->
            <div>
                <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                <select id="status" name="status" required
                    class="w-full text-sm rounded-md border-gray-300 shadow-sm px-4 py-2 bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('status') border-red-500 @enderror">
                    <option value="active" {{ old('status') == 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
                @error('status')
                    <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Action Buttons -->
            <div class="flex justify-between pt-4">
                <a href="{{ route('admin.employees.index') }}"
                   class="text-sm bg-gray-200 text-gray-800 hover:bg-gray-300 px-4 py-2 rounded-md transition">Cancel</a>
                <button type="submit"
                        class="text-sm bg-blue-600 text-white hover:bg-blue-700 px-4 py-2 rounded-md shadow transition">Create Employee</button>
            </div>
        </form>
    </div>
</div>
@endsection
