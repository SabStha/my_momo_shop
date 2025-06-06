@extends('layouts.admin')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-center">
        <div class="w-full">
            <div class="bg-white shadow-md rounded-lg overflow-hidden">
                <div class="flex justify-between items-center p-4 border-b">
                    <h4 class="text-xl font-semibold">Employees</h4>
                    <a href="{{ route('admin.employees.create') }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">Add New Employee</a>
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
                                        <td class="py-2 px-4 border-b">${{ number_format($employee->salary ?? 0, 2) }}</td>
                                        <td class="py-2 px-4 border-b">{{ $employee->hire_date ? $employee->hire_date->format('M d, Y') : 'N/A' }}</td>
                                        <td class="py-2 px-4 border-b">
                                            <span class="inline-block px-2 py-1 text-xs font-semibold rounded-full {{ ($employee->status ?? 'inactive') === 'active' ? 'bg-green-200 text-green-800' : 'bg-gray-200 text-gray-800' }}">
                                                {{ ucfirst($employee->status ?? 'inactive') }}
                                            </span>
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
                                                <form action="{{ route('admin.employees.destroy', $employee) }}" 
                                                      method="POST" 
                                                      class="inline"
                                                      onsubmit="return confirm('Are you sure you want to delete this employee?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="bg-red-500 hover:bg-red-700 text-white font-bold py-1 px-2 rounded">
                                                        <i class="fas fa-trash"></i> Delete
                                                    </button>
                                                </form>
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
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
