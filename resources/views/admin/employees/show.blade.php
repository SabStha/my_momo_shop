@extends('layouts.admin')

@section('content')
<div class="max-w-4xl mx-auto py-8">
    <div class="bg-white shadow-md rounded-lg p-6">
        <h3 class="text-2xl font-bold text-gray-800 mb-6">Employee Details</h3>

        <div class="flex justify-end mb-6">
            <a href="{{ route('admin.employees.edit', $employee) }}" 
               class="bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded-md shadow-sm flex items-center gap-2">
                <i class="fas fa-edit"></i> Edit Employee
            </a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 text-gray-800 text-sm">
            <!-- Column 1 -->
            <div>
                <div class="mb-4">
                    <span class="font-semibold">👤 Employee Number:</span><br>
                    {{ $employee->employee_number }}
                </div>
                <div class="mb-4">
                    <span class="font-semibold">🧾 Name:</span><br>
                    {{ $employee->user->name }}
                </div>
                <div class="mb-4">
                    <span class="font-semibold">📧 Email:</span><br>
                    {{ $employee->user->email }}
                </div>
                <div class="mb-4">
                    <span class="font-semibold">📌 Position:</span><br>
                    {{ $employee->position }}
                </div>
                <div class="mb-4">
                    <span class="font-semibold">💰 Salary:</span><br>
                    Rs. {{ number_format($employee->salary, 2) }}
                </div>
                <div class="mb-4">
                    <span class="font-semibold">📅 Hire Date:</span><br>
                    {{ $employee->hire_date->format('M d, Y') }}
                </div>
                <div class="mb-4">
                    <span class="font-semibold">✅ Status:</span><br>
                    <span class="inline-block px-3 py-1 text-xs font-semibold rounded-full 
                        {{ $employee->status === 'active' ? 'bg-green-200 text-green-800' : 'bg-red-200 text-red-800' }}">
                        {{ ucfirst($employee->status) }}
                    </span>
                </div>
            </div>

            <!-- Column 2 -->
            <div>
                <div class="mb-4">
                    <span class="font-semibold">📞 Phone:</span><br>
                    {{ $employee->phone ?? 'Not provided' }}
                </div>
                <div class="mb-4">
                    <span class="font-semibold">🏠 Address:</span><br>
                    {{ $employee->address ?? 'Not provided' }}
                </div>
                <div class="mb-4">
                    <span class="font-semibold">🚨 Emergency Contact:</span><br>
                    {{ $employee->emergency_contact ?? 'Not provided' }}
                </div>
                <div class="mb-4">
                    <span class="font-semibold">🕒 Created At:</span><br>
                    {{ $employee->created_at->format('M d, Y H:i:s') }}
                </div>
                <div class="mb-4">
                    <span class="font-semibold">🛠 Last Updated:</span><br>
                    {{ $employee->updated_at->format('M d, Y H:i:s') }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
