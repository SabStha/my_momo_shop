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
        
        <!-- Clock History Section -->
        <div class="mt-10 border-t pt-8">
            <h4 class="text-xl font-bold text-gray-800 mb-4 inline-flex items-center">
                <i class="fas fa-clock text-indigo-500 mr-2"></i> Clock History
            </h4>
            
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Clock In</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Clock Out</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Duration</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($timeLogs as $log)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ \Carbon\Carbon::parse($log->clock_in)->format('M d, Y') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ \Carbon\Carbon::parse($log->clock_in)->format('H:i') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $log->clock_out ? \Carbon\Carbon::parse($log->clock_out)->format('H:i') : '--:--' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                @if($log->clock_out)
                                    @php
                                        $duration = \Carbon\Carbon::parse($log->clock_in)->diff(\Carbon\Carbon::parse($log->clock_out));
                                    @endphp
                                    {{ $duration->h }}h {{ $duration->i }}m
                                @else
                                    <span class="text-gray-400">In Progress</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($log->is_late)
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">Late</span>
                                @else
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">On Time</span>
                                @endif
                                
                                @if(!$log->clock_out)
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800 ml-1">Working</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="px-6 py-4 whitespace-nowrap text-sm text-center text-gray-500">
                                No clock history found for this employee.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            @if($timeLogs->hasPages())
            <div class="mt-4">
                {{ $timeLogs->links() }}
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
