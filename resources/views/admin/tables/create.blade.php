@extends('layouts.admin')

@section('title', 'Add Table')

@section('content')
<div class="max-w-lg mx-auto py-10 px-4">
    <div class="mb-6">
        <a href="{{ route('admin.tables.index') }}" class="text-sm text-indigo-600 hover:underline">
            <i class="fas fa-arrow-left mr-1"></i>Back to Tables
        </a>
        <h1 class="text-2xl font-bold text-gray-900 mt-2">Add Table</h1>
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        <form method="POST" action="{{ route('admin.tables.store') }}">
            @csrf

            {{-- Branch --}}
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Branch <span class="text-red-500">*</span></label>
                <select name="branch_id" required
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500 @error('branch_id') border-red-400 @enderror">
                    <option value="">— Select branch —</option>
                    @foreach($branches as $branch)
                        <option value="{{ $branch->id }}"
                            {{ (old('branch_id', request('branch_id')) == $branch->id) ? 'selected' : '' }}>
                            {{ $branch->name }}
                        </option>
                    @endforeach
                </select>
                @error('branch_id')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            {{-- Name --}}
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Table Name <span class="text-red-500">*</span></label>
                <input type="text" name="name" value="{{ old('name') }}"
                       placeholder="e.g. Table 1"
                       required maxlength="191"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500 @error('name') border-red-400 @enderror">
                @error('name')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            {{-- Number --}}
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Table Number <span class="text-red-500">*</span></label>
                <input type="text" name="number" value="{{ old('number') }}"
                       placeholder="e.g. M-1 (must be unique across all branches)"
                       required maxlength="191"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500 @error('number') border-red-400 @enderror">
                <p class="text-xs text-gray-400 mt-1">Must be unique across all branches. Tip: prefix with branch initial — M-1, N-1, S-1.</p>
                @error('number')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            {{-- Capacity --}}
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-1">Capacity (seats) <span class="text-red-500">*</span></label>
                <input type="number" name="capacity" value="{{ old('capacity', 4) }}"
                       min="1" max="99" required
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500 @error('capacity') border-red-400 @enderror">
                @error('capacity')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            <div class="flex gap-3">
                <button type="submit"
                        class="flex-1 bg-indigo-600 text-white py-2 px-4 rounded-lg text-sm font-medium hover:bg-indigo-700 transition">
                    Create Table
                </button>
                <a href="{{ route('admin.tables.index') }}"
                   class="flex-1 text-center bg-gray-100 text-gray-700 py-2 px-4 rounded-lg text-sm font-medium hover:bg-gray-200 transition">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
