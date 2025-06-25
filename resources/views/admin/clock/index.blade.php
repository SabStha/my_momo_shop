@extends('layouts.admin')

@section('content')
<div class="max-w-7xl mx-auto py-8">
    <div class="bg-white shadow-lg rounded-lg overflow-hidden">
        <!-- Header Section -->
        <div class="bg-gradient-to-r from-blue-600 to-blue-800 px-6 py-4">
            <div class="flex items-center justify-between">
                <h3 class="text-2xl font-bold text-white">Employee Clocking System</h3>
                <a href="{{ route('admin.clock.report') }}" class="inline-flex items-center px-4 py-2 bg-white text-blue-600 rounded-lg hover:bg-blue-50 transition-colors duration-200">
                    <i class="fas fa-chart-bar mr-2"></i>
                View Reports
            </a>
            </div>
        </div>

        <!-- Alert Container -->
        <div id="alert-container" class="px-6 pt-4"></div>

        <!-- Date Selector -->
        <div class="p-6 border-b">
            <form id="dateSelectorForm" class="flex flex-col sm:flex-row gap-4 items-end">
                <div class="flex-1">
                    <label for="date" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-calendar mr-2 text-blue-600"></i>Select Date
                    </label>
                    <input type="date" 
                           class="w-full h-12 px-4 bg-white border-2 border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-200 focus:ring-opacity-50 transition-all duration-200 text-gray-700 font-medium" 
                           id="date" 
                           name="date"
                           value="{{ date('Y-m-d') }}">
                </div>
                <button type="submit" class="h-12 px-6 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-all duration-200 flex items-center justify-center shadow-sm hover:shadow-md transform hover:-translate-y-0.5">
                    <i class="fas fa-eye mr-2"></i>
                    View Records
                </button>
            </form>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 p-6">
            <!-- Clock Actions Section -->
            <div class="bg-white shadow rounded-lg p-6 space-y-6">
                <h4 class="text-lg font-semibold text-gray-900 flex items-center">
                    <i class="fas fa-clock mr-2 text-blue-600"></i>
                    Clock Actions
                </h4>

                <!-- Employee Search -->
                <div class="mb-6">
                    <label for="employee_search" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-user mr-2 text-blue-600"></i>Search Employee
                    </label>
                    <div class="relative">
                        <input type="text" 
                               id="employee_search" 
                               class="w-full h-12 px-4 bg-white border-2 border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-200 focus:ring-opacity-50 transition-all duration-200 text-gray-700 font-medium" 
                               placeholder="Enter employee name or ID"
                               autocomplete="off">
                        <div id="search_results" class="absolute z-10 w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-lg hidden"></div>
                    </div>
                </div>

                <!-- Clock Actions -->
                <div class="grid grid-cols-2 gap-4">
                    <!-- Clock In -->
                    <button type="button" id="clockInBtn" class="h-12 px-4 bg-green-600 hover:bg-green-700 text-white font-medium rounded-lg transition-all duration-200 flex items-center justify-center shadow-sm hover:shadow-md transform hover:-translate-y-0.5">
                        <i class="fas fa-sign-in-alt mr-2"></i>
                        Clock In
                    </button>

                    <!-- Clock Out -->
                    <button type="button" id="clockOutBtn" class="h-12 px-4 bg-red-600 hover:bg-red-700 text-white font-medium rounded-lg transition-all duration-200 flex items-center justify-center shadow-sm hover:shadow-md transform hover:-translate-y-0.5">
                        <i class="fas fa-sign-out-alt mr-2"></i>
                            Clock Out
                        </button>

                    <!-- Start Break -->
                    <button type="button" id="startBreakBtn" class="h-12 px-4 bg-yellow-600 hover:bg-yellow-700 text-white font-medium rounded-lg transition-all duration-200 flex items-center justify-center shadow-sm hover:shadow-md transform hover:-translate-y-0.5">
                        <i class="fas fa-coffee mr-2"></i>
                            Start Break
                        </button>

                    <!-- End Break -->
                    <button type="button" id="endBreakBtn" class="h-12 px-4 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-all duration-200 flex items-center justify-center shadow-sm hover:shadow-md transform hover:-translate-y-0.5">
                        <i class="fas fa-stop-circle mr-2"></i>
                            End Break
                        </button>
                    </div>
            </div>

            <!-- Clock Records Section -->
            <div class="bg-white shadow rounded-lg p-6">
                <h4 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                    <i class="fas fa-list-alt mr-2 text-blue-600"></i>
                    Today's Clock Records
                </h4>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Employee</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Clock In</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Clock Out</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Break Start</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Break End</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            </tr>
                        </thead>
                        <tbody id="timeLogsTableBody" class="bg-white divide-y divide-gray-200">
                            @forelse($employees as $employee)
                                @php
                                    $log = $employee->timeLogs->first();
                                @endphp
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $employee->user->name }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $log && $log->clock_in ? optional($log->clock_in)->format('H:i:s') : '-' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $log && $log->clock_out ? optional($log->clock_out)->format('H:i:s') : '-' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $log && $log->break_start ? optional($log->break_start)->format('H:i:s') : '-' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $log && $log->break_end ? optional($log->break_end)->format('H:i:s') : '-' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($log)
                                            @if($log->status === 'completed')
                                                <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                                    <i class="fas fa-check-circle mr-1"></i>Completed
                                                </span>
                                            @elseif($log->status === 'on_break')
                                                <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                                    <i class="fas fa-coffee mr-1"></i>On Break
                                                </span>
                                            @else
                                                <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                    <i class="fas fa-clock mr-1"></i>Active
                                                </span>
                                            @endif
                                        @else
                                            <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-50 text-gray-400">
                                                <i class="fas fa-minus-circle mr-1"></i>Not Clocked In
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                                        <div class="flex flex-col items-center justify-center py-8">
                                            <i class="fas fa-clipboard-list text-4xl text-gray-400 mb-2"></i>
                                            <p>No employees found for this branch.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
    @vite(['resources/js/clock-system.js'])
@endpush
