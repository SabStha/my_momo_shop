@extends('layouts.admin')

@section('content')
<div class="max-w-4xl mx-auto py-8">
    <div class="bg-white shadow rounded-lg p-6">
        <h3 class="text-2xl font-bold mb-6">Employee Details</h3>
        <div class="flex gap-2 mb-6">
            <a href="{{ route('admin.employees.edit', $employee) }}" class="bg-yellow-500 hover:bg-yellow-600 text-white px-3 py-2 rounded flex items-center"><i class="fas fa-edit mr-2"></i> Edit Employee</a>
                    </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <div>
                <div class="mb-4"><span class="font-semibold">Employee Number:</span> {{ $employee->employee_number }}</div>
                <div class="mb-4"><span class="font-semibold">Name:</span> {{ $employee->user->name }}</div>
                <div class="mb-4"><span class="font-semibold">Email:</span> {{ $employee->user->email }}</div>
                <div class="mb-4"><span class="font-semibold">Position:</span> {{ $employee->position }}</div>
                <div class="mb-4"><span class="font-semibold">Salary:</span> ${{ number_format($employee->salary, 2) }}</div>
                <div class="mb-4"><span class="font-semibold">Hire Date:</span> {{ $employee->hire_date->format('M d, Y') }}</div>
                <div class="mb-4"><span class="font-semibold">Status:</span>
                    <span class="inline-block px-2 py-1 text-xs font-semibold rounded-full {{ $employee->status === 'active' ? 'bg-green-200 text-green-800' : 'bg-red-200 text-red-800' }}">
                                        {{ ucfirst($employee->status) }}
                                    </span>
                </div>
            </div>
            <div>
                <div class="mb-4"><span class="font-semibold">Phone:</span> {{ $employee->phone ?? 'Not provided' }}</div>
                <div class="mb-4"><span class="font-semibold">Address:</span> {{ $employee->address ?? 'Not provided' }}</div>
                <div class="mb-4"><span class="font-semibold">Emergency Contact:</span> {{ $employee->emergency_contact ?? 'Not provided' }}</div>
                <div class="mb-4"><span class="font-semibold">Created At:</span> {{ $employee->created_at->format('M d, Y H:i:s') }}</div>
                <div class="mb-4"><span class="font-semibold">Last Updated:</span> {{ $employee->updated_at->format('M d, Y H:i:s') }}</div>
            </div>
        </div>
    </div>
</div>
@endsection 