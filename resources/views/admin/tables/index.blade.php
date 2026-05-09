@extends('layouts.admin')

@section('title', 'Table Management')

@section('content')
<div class="max-w-7xl mx-auto py-10 px-4">

    {{-- Header --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Table Management</h1>
            <p class="text-gray-500 text-sm mt-1">Manage dine-in tables per branch</p>
        </div>
        <a href="{{ route('admin.tables.create') }}"
           class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition">
            <i class="fas fa-plus mr-2"></i> Add Table
        </a>
    </div>

    {{-- Flash messages --}}
    @if(session('success'))
        <div class="mb-4 px-4 py-3 bg-green-50 border border-green-200 text-green-800 rounded-lg text-sm">
            {{ session('success') }}
        </div>
    @endif

    @if($branches->isEmpty())
        <div class="bg-white rounded-lg shadow p-8 text-center text-gray-500">
            No branches found. Create a branch first.
        </div>
    @else
        @foreach($branches as $branch)
            @php $branchTables = $tables->get($branch->id, collect()); @endphp
            <div class="bg-white rounded-lg shadow mb-6">
                <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <i class="fas fa-building text-indigo-500"></i>
                        <h2 class="font-semibold text-gray-800">{{ $branch->name }}</h2>
                        <span class="text-xs text-gray-400">({{ $branchTables->count() }} table{{ $branchTables->count() === 1 ? '' : 's' }})</span>
                    </div>
                    <a href="{{ route('admin.tables.create', ['branch_id' => $branch->id]) }}"
                       class="text-sm text-indigo-600 hover:underline">
                        <i class="fas fa-plus mr-1"></i>Add to this branch
                    </a>
                </div>

                @if($branchTables->isEmpty())
                    <div class="px-6 py-5 text-sm text-gray-400">No tables yet for this branch.</div>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 text-sm">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Number</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Capacity</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Active</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 bg-white">
                                @foreach($branchTables as $table)
                                <tr class="{{ $table->is_active ? '' : 'bg-gray-50 opacity-60' }}">
                                    <td class="px-6 py-3 font-medium text-gray-900">{{ $table->name }}</td>
                                    <td class="px-6 py-3 text-gray-600 font-mono">{{ $table->number }}</td>
                                    <td class="px-6 py-3 text-gray-600">{{ $table->capacity }} seats</td>
                                    <td class="px-6 py-3">
                                        <span class="px-2 py-0.5 rounded-full text-xs font-medium
                                            {{ $table->status === 'available' ? 'bg-green-100 text-green-700'
                                             : ($table->status === 'occupied' ? 'bg-red-100 text-red-700'
                                             : 'bg-yellow-100 text-yellow-700') }}">
                                            {{ ucfirst($table->status) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-3">
                                        <form method="POST" action="{{ route('admin.tables.toggle', $table) }}">
                                            @csrf
                                            <button type="submit"
                                                    class="px-2 py-0.5 rounded-full text-xs font-medium
                                                        {{ $table->is_active ? 'bg-green-100 text-green-700 hover:bg-green-200' : 'bg-gray-100 text-gray-500 hover:bg-gray-200' }}">
                                                {{ $table->is_active ? 'Active' : 'Inactive' }}
                                            </button>
                                        </form>
                                    </td>
                                    <td class="px-6 py-3 text-right space-x-3 whitespace-nowrap">
                                        <a href="{{ route('admin.tables.edit', $table) }}"
                                           class="text-indigo-600 hover:text-indigo-800 text-xs font-medium">
                                            <i class="fas fa-edit mr-1"></i>Edit
                                        </a>
                                        <button type="button" class="text-red-600 hover:text-red-800 text-xs font-medium"
                                            onclick="openConfirmModal({
                                                title: 'Delete Table',
                                                message: 'Delete {{ addslashes($table->name) }} from {{ addslashes($branch->name) }}? This cannot be undone.',
                                                url: '{{ route('admin.tables.destroy', $table) }}',
                                                method: 'DELETE',
                                                confirmText: 'Delete',
                                                confirmColor: 'red'
                                            })">
                                            <i class="fas fa-trash mr-1"></i>Delete
                                        </button>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        @endforeach
    @endif
</div>
@endsection
