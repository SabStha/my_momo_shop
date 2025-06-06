@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="bg-white rounded-lg shadow-lg overflow-hidden">
        <div class="bg-blue-600 px-6 py-4 flex justify-between items-center">
            <h2 class="text-xl font-semibold text-white">Employee Schedule</h2>
                    @if(Auth::user()->hasRole('manager'))
            <button type="button" class="bg-white text-blue-600 px-4 py-2 rounded-lg hover:bg-blue-50 transition-colors" 
                    onclick="document.getElementById('scheduleModal').classList.remove('hidden')">
                <i class="fas fa-plus mr-2"></i> Add Shift
                    </button>
                    @endif
                </div>

        <div class="p-6">
                    <!-- Tabs Navigation -->
            <div class="border-b border-gray-200 mb-6">
                <nav class="flex space-x-8" aria-label="Tabs">
                    <button class="border-blue-500 text-blue-600 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm active" 
                            data-tab="weekly">
                        <i class="fas fa-calendar-week mr-2"></i> Weekly Schedule
                            </button>
                    <button class="border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm" 
                            data-tab="monthly">
                        <i class="fas fa-calendar-alt mr-2"></i> Monthly View
                            </button>
                    <button class="border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm" 
                            data-tab="availability">
                        <i class="fas fa-clock mr-2"></i> Availability
                            </button>
                        @if(Auth::user()->hasRole('manager'))
                    <button class="border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm" 
                            data-tab="reports">
                        <i class="fas fa-chart-bar mr-2"></i> Reports
                            </button>
                        @endif
                </nav>
            </div>

                    <!-- Tabs Content -->
            <div class="tab-content">
                        <!-- Weekly Schedule Tab -->
                <div class="tab-pane active" id="weekly">
                            <!-- Filter Form -->
                    <form action="/admin/employees/schedules" method="GET" class="mb-6">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
                            <div>
                                <label for="start_date" class="block text-sm font-medium text-gray-700 mb-1">Start Date</label>
                                <input type="date" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" 
                                       id="start_date" name="start_date" value="{{ request('start_date', $startDate) }}">
                                    </div>
                                    @if(Auth::user()->hasRole('manager'))
                            <div>
                                <label for="employee_id" class="block text-sm font-medium text-gray-700 mb-1">Employee</label>
                                <select class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" 
                                        id="employee_id" name="employee_id">
                                            <option value="">All Employees</option>
                                            @foreach($employees as $employee)
                                                <option value="{{ $employee->id }}" {{ request('employee_id') == $employee->id ? 'selected' : '' }}>
                                                    {{ $employee->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    @endif
                            <div>
                                <button type="submit" class="w-full bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors">
                                    <i class="fas fa-filter mr-2"></i> Filter
                                        </button>
                                    </div>
                                </div>
                            </form>

                            <!-- Schedule Table -->
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Employee</th>
                                    @php
                                        $startDateObj = \Carbon\Carbon::parse($startDate);
                                    @endphp
                                    @for($date = $startDateObj->copy(); $date <= $startDateObj->copy()->endOfWeek(); $date->addDay())
                                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                    {{ $date->format('D') }}<br>
                                            <span class="text-gray-400">{{ $date->format('M d') }}</span>
                                                </th>
                                            @endfor
                                        </tr>
                                    </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                        @foreach($employees as $employee)
                                        <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">{{ $employee->name }}</div>
                                            </td>
                                    @for($date = $startDateObj->copy(); $date <= $startDateObj->copy()->endOfWeek(); $date->addDay())
                                        <td class="px-6 py-4">
                                                    @php
                                                        $daySchedules = $schedules[$date->format('Y-m-d')] ?? collect();
                                                        $employeeSchedule = $daySchedules->firstWhere('employee_id', $employee->id);
                                                    @endphp
                                                    
                                                    @if($employeeSchedule)
                                                <div class="bg-blue-50 rounded-lg p-3">
                                                    <div class="text-sm font-medium text-blue-700">
                                                                {{ \Carbon\Carbon::parse($employeeSchedule->start_time)->format('h:i A') }} - 
                                                                {{ \Carbon\Carbon::parse($employeeSchedule->end_time)->format('h:i A') }}
                                                            </div>
                                                            @if($employeeSchedule->notes)
                                                        <div class="mt-1 text-sm text-gray-600">
                                                            {{ $employeeSchedule->notes }}
                                                                </div>
                                                            @endif
                                                            @if(Auth::user()->hasRole('manager'))
                                                        <div class="mt-2 flex space-x-2">
                                                            <button class="text-blue-600 hover:text-blue-800" 
                                                                    onclick="editShift({{ $employeeSchedule->id }})">
                                                                        <i class="fas fa-edit"></i>
                                                                    </button>
                                                            <form action="/admin/employees/schedules/{{ $employeeSchedule->id }}" 
                                                                  method="POST" class="inline">
                                                                        @csrf
                                                                        @method('DELETE')
                                                                <button type="submit" class="text-red-600 hover:text-red-800"
                                                                            onclick="return confirm('Are you sure you want to delete this shift?')">
                                                                            <i class="fas fa-trash"></i>
                                                                        </button>
                                                                    </form>
                                                                </div>
                                                            @endif
                                                        </div>
                                                    @else
                                                <div class="text-sm text-gray-400 text-center">No shift</div>
                                                    @endif
                                                </td>
                                            @endfor
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Monthly View Tab -->
                <div class="tab-pane hidden" id="monthly">
                    <div class="min-h-[400px] flex items-center justify-center">
                        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
                            </div>
                        </div>

                        <!-- Availability Tab -->
                <div class="tab-pane hidden" id="availability">
                    <div class="max-w-2xl mx-auto">
                        <form id="availabilityForm" class="space-y-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <h3 class="text-lg font-medium text-gray-900 mb-4">Preferred Working Hours</h3>
                                    <div class="space-y-4">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700">Start Time</label>
                                            <input type="time" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                            </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700">End Time</label>
                                            <input type="time" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                        </div>
                                    </div>
                                </div>
                                <div>
                                    <h3 class="text-lg font-medium text-gray-900 mb-4">Preferred Days</h3>
                                    <div class="space-y-2">
                                            @foreach(['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'] as $day)
                                            <div class="flex items-center">
                                                <input type="checkbox" id="day_{{ strtolower($day) }}" 
                                                       class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                                <label for="day_{{ strtolower($day) }}" class="ml-2 block text-sm text-gray-900">
                                                        {{ $day }}
                                                    </label>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                            </div>
                            <div class="flex justify-end">
                                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors">
                                    Save Preferences
                                </button>
                            </div>
                        </form>
                            </div>
                        </div>

                        <!-- Reports Tab -->
                        @if(Auth::user()->hasRole('manager'))
                <div class="tab-pane hidden" id="reports">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="bg-white rounded-lg shadow p-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Hours Summary</h3>
                            <canvas id="hoursChart" class="w-full h-64"></canvas>
                                            </div>
                        <div class="bg-white rounded-lg shadow p-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Shift Distribution</h3>
                            <canvas id="shiftsChart" class="w-full h-64"></canvas>
                                        </div>
                                    </div>
                    <div class="mt-6 flex space-x-4">
                        <a href="/admin/employees/schedules/export{{ request()->getQueryString() ? '?' . request()->getQueryString() : '' }}" 
                           class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition-colors">
                            <i class="fas fa-file-pdf mr-2"></i> Export Schedule
                        </a>
                        <button class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors" 
                                onclick="generateReport()">
                            <i class="fas fa-chart-line mr-2"></i> Generate Report
                                    </button>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Schedule Modal -->
<div id="scheduleModal" class="fixed inset-0 bg-gray-500 bg-opacity-75 hidden" aria-hidden="true">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Add Shift</h3>
            </div>
            <form id="scheduleForm" method="POST" action="/admin/employees/schedules" class="p-6">
                @csrf
                <div class="space-y-4">
                    <div>
                        <label for="employee_id" class="block text-sm font-medium text-gray-700">Employee</label>
                        <select class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" 
                                id="employee_id" name="employee_id" required>
                            <option value="">Select Employee</option>
                            @foreach($employees as $employee)
                                <option value="{{ $employee->id }}">{{ $employee->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="work_date" class="block text-sm font-medium text-gray-700">Date</label>
                        <input type="date" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" 
                               id="work_date" name="work_date" required>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label for="start_time" class="block text-sm font-medium text-gray-700">Start Time</label>
                            <input type="time" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" 
                                   id="start_time" name="start_time" required>
                        </div>
                        <div>
                            <label for="end_time" class="block text-sm font-medium text-gray-700">End Time</label>
                            <input type="time" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" 
                                   id="end_time" name="end_time" required>
                        </div>
                    </div>
                    <div>
                        <label for="notes" class="block text-sm font-medium text-gray-700">Notes</label>
                        <textarea class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" 
                                  id="notes" name="notes" rows="2"></textarea>
                    </div>
                </div>
                <div class="mt-6 flex justify-end space-x-3">
                    <button type="button" class="bg-gray-100 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-200 transition-colors" 
                            onclick="document.getElementById('scheduleModal').classList.add('hidden')">
                        Cancel
                    </button>
                    <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors">
                        Save Shift
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Tab switching
    document.querySelectorAll('[data-tab]').forEach(button => {
        button.addEventListener('click', function() {
            const tabId = this.dataset.tab;
            
            // Update active states
            document.querySelectorAll('[data-tab]').forEach(btn => {
                btn.classList.remove('border-blue-500', 'text-blue-600');
                btn.classList.add('border-transparent', 'text-gray-500');
            });
            this.classList.remove('border-transparent', 'text-gray-500');
            this.classList.add('border-blue-500', 'text-blue-600');
            
            // Show/hide content
            document.querySelectorAll('.tab-pane').forEach(pane => {
                pane.classList.add('hidden');
            });
            document.getElementById(tabId).classList.remove('hidden');
            
            // Load content if needed
            if (tabId === 'monthly') {
                loadMonthlyCalendar();
            } else if (tabId === 'reports') {
                initializeCharts();
            }
        });
    });
});

function editShift(scheduleId) {
    // Add your edit shift logic here
}

function loadMonthlyCalendar() {
    // Add your monthly calendar loading logic here
}

function initializeCharts() {
    // Hours Chart
    const hoursCtx = document.getElementById('hoursChart').getContext('2d');
    new Chart(hoursCtx, {
        type: 'bar',
        data: {
            labels: ['Week 1', 'Week 2', 'Week 3', 'Week 4'],
            datasets: [{
                label: 'Total Hours',
                data: [40, 38, 42, 39],
                backgroundColor: '#2563eb'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false
        }
    });

    // Shifts Chart
    const shiftsCtx = document.getElementById('shiftsChart').getContext('2d');
    new Chart(shiftsCtx, {
        type: 'pie',
        data: {
            labels: ['Morning', 'Afternoon', 'Evening', 'Night'],
            datasets: [{
                data: [30, 25, 25, 20],
                backgroundColor: ['#2563eb', '#3b82f6', '#60a5fa', '#93c5fd']
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false
        }
    });
}

function generateReport() {
    // Add your report generation logic here
}
</script>
@endpush
@endsection 