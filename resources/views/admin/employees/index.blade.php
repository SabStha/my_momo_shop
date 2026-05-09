@extends('layouts.admin')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-center">
        <div class="w-full">
            <div class="bg-white shadow-md rounded-lg overflow-hidden">
                <div class="flex flex-col md:flex-row justify-between items-start md:items-center p-4 border-b gap-4">
                    <h4 class="text-xl font-semibold text-gray-800">Employees</h4>
                    
                    <form action="{{ route('admin.employees.index') }}" method="GET" class="flex flex-wrap items-center gap-2">
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search Name, Email, or #" class="border-gray-300 rounded-md shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50 text-sm w-48">
                        
                        <select name="branch_id" class="border-gray-300 rounded-md shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50 text-sm">
                            <option value="">All Branches</option>
                            @foreach($branches as $branch)
                                <option value="{{ $branch->id }}" {{ request('branch_id') == $branch->id ? 'selected' : '' }}>
                                    {{ $branch->name }}
                                </option>
                            @endforeach
                        </select>
                        
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-1.5 px-3 rounded-md text-sm transition-colors">
                            Filter
                        </button>
                        
                        @if(request()->hasAny(['search', 'branch_id']))
                            <a href="{{ route('admin.employees.index') }}" class="text-gray-500 hover:text-gray-700 text-sm underline">Clear</a>
                        @endif
                    </form>

                    <a href="{{ route('admin.employees.create') }}" class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded transition-colors whitespace-nowrap">
                        <i class="fas fa-plus mr-1"></i> Add New
                    </a>
                </div>
                <div class="p-4">
                    <div class="overflow-x-auto">
                        <table class="min-w-full bg-white">
                            <thead>
                                <tr>
                                    <th class="py-2 px-4 border-b">ID</th>
                                    <th class="py-2 px-4 border-b">Employee #</th>
                                    <th class="py-2 px-4 border-b">Name</th>
                                    <th class="py-2 px-4 border-b">Email</th>
                                    <th class="py-2 px-4 border-b">Position</th>
                                    <th class="py-2 px-4 border-b">Salary</th>
                                    <th class="py-2 px-4 border-b">Hire Date</th>
                                    <th class="py-2 px-4 border-b">Status</th>
                                    <th class="py-2 px-4 border-b">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($employees as $employee)
                                    <tr>
                                        <td class="py-2 px-4 border-b">{{ $employee->id }}</td>
                                        <td class="py-2 px-4 border-b">{{ $employee->employee_number }}</td>
                                        <td class="py-2 px-4 border-b">{{ $employee->user->name ?? 'N/A' }}</td>
                                        <td class="py-2 px-4 border-b">{{ $employee->user->email ?? 'N/A' }}</td>
                                        <td class="py-2 px-4 border-b">{{ $employee->position ?? 'N/A' }}</td>
                                        <td class="py-2 px-4 border-b">Rs {{ number_format($employee->salary ?? 0, 2) }}</td>
                                        <td class="py-2 px-4 border-b">{{ $employee->hire_date ? $employee->hire_date->format('M d, Y') : 'N/A' }}</td>
                                        <td class="py-2 px-4 border-b">
                                            <x-badge.status :status="$employee->status ?? 'inactive'" />
                                        </td>
                                        <td class="py-2 px-4 border-b">
                                            <div class="flex space-x-2">
                                                <a href="{{ route('admin.employees.show', $employee) }}" 
                                                   class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-1 px-2 rounded">
                                                    <i class="fas fa-eye"></i> View
                                                </a>
                                                <a href="{{ route('admin.employees.edit', $employee) }}" 
                                                   class="bg-yellow-500 hover:bg-yellow-700 text-white font-bold py-1 px-2 rounded">
                                                    <i class="fas fa-edit"></i> Edit
                                                </a>
                                                <button type="button" class="bg-red-500 hover:bg-red-700 text-white font-bold py-1 px-2 rounded"
                                                    onclick="openConfirmModal({
                                                        title: 'Delete Employee',
                                                        message: 'Are you sure you want to delete this employee? This action cannot be undone.',
                                                        url: '{{ route('admin.employees.destroy', $employee) }}',
                                                        method: 'DELETE',
                                                        confirmText: 'Delete',
                                                        confirmColor: 'red'
                                                    })">
                                                    <i class="fas fa-trash"></i> Delete
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="py-2 px-4 border-b text-center">No employees found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if($employees->hasPages())
                        <div class="mt-4">
                            {{ $employees->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
