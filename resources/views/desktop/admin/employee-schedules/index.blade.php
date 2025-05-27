@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Employee Schedule</h5>
                    @if(Auth::user()->hasRole('manager'))
                    <button type="button" class="btn btn-light" data-bs-toggle="modal" data-bs-target="#scheduleModal">
                        <i class="fas fa-plus"></i> Add Shift
                    </button>
                    @endif
                </div>

                <div class="card-body">
                    <!-- Tabs Navigation -->
                    <ul class="nav nav-tabs mb-4" id="scheduleTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="weekly-tab" data-bs-toggle="tab" data-bs-target="#weekly" type="button" role="tab">
                                <i class="fas fa-calendar-week"></i> Weekly Schedule
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="monthly-tab" data-bs-toggle="tab" data-bs-target="#monthly" type="button" role="tab">
                                <i class="fas fa-calendar-alt"></i> Monthly View
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="availability-tab" data-bs-toggle="tab" data-bs-target="#availability" type="button" role="tab">
                                <i class="fas fa-clock"></i> Availability
                            </button>
                        </li>
                        @if(Auth::user()->hasRole('manager'))
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="reports-tab" data-bs-toggle="tab" data-bs-target="#reports" type="button" role="tab">
                                <i class="fas fa-chart-bar"></i> Reports
                            </button>
                        </li>
                        @endif
                    </ul>

                    <!-- Tabs Content -->
                    <div class="tab-content" id="scheduleTabsContent">
                        <!-- Weekly Schedule Tab -->
                        <div class="tab-pane fade show active" id="weekly" role="tabpanel">
                            <!-- Filter Form -->
                            <form action="{{ route('employee-schedules.index') }}" method="GET" class="mb-4">
                                <div class="row g-3 align-items-end">
                                    <div class="col-md-4">
                                        <label for="start_date" class="form-label">Start Date</label>
                                        <input type="date" class="form-control" id="start_date" name="start_date" 
                                            value="{{ request('start_date', $startDate->format('Y-m-d')) }}">
                                    </div>
                                    @if(Auth::user()->hasRole('manager'))
                                    <div class="col-md-4">
                                        <label for="employee_id" class="form-label">Employee</label>
                                        <select class="form-select" id="employee_id" name="employee_id">
                                            <option value="">All Employees</option>
                                            @foreach($employees as $employee)
                                                <option value="{{ $employee->id }}" {{ request('employee_id') == $employee->id ? 'selected' : '' }}>
                                                    {{ $employee->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    @endif
                                    <div class="col-md-4">
                                        <button type="submit" class="btn btn-primary w-100">
                                            <i class="fas fa-filter"></i> Filter
                                        </button>
                                    </div>
                                </div>
                            </form>

                            <!-- Schedule Table -->
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Employee</th>
                                            @for($date = $startDate->copy(); $date <= $startDate->copy()->endOfWeek(); $date->addDay())
                                                <th class="text-center">
                                                    {{ $date->format('D') }}<br>
                                                    <small>{{ $date->format('M d') }}</small>
                                                </th>
                                            @endfor
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($employees as $employee)
                                        <tr>
                                            <td class="align-middle">
                                                <strong>{{ $employee->name }}</strong>
                                            </td>
                                            @for($date = $startDate->copy(); $date <= $startDate->copy()->endOfWeek(); $date->addDay())
                                                <td class="schedule-slot">
                                                    @php
                                                        $daySchedules = $schedules[$date->format('Y-m-d')] ?? collect();
                                                        $employeeSchedule = $daySchedules->firstWhere('employee_id', $employee->id);
                                                    @endphp
                                                    
                                                    @if($employeeSchedule)
                                                        <div class="shift-block">
                                                            <div class="shift-time">
                                                                {{ \Carbon\Carbon::parse($employeeSchedule->start_time)->format('h:i A') }} - 
                                                                {{ \Carbon\Carbon::parse($employeeSchedule->end_time)->format('h:i A') }}
                                                            </div>
                                                            @if($employeeSchedule->notes)
                                                                <div class="shift-notes">
                                                                    <small>{{ $employeeSchedule->notes }}</small>
                                                                </div>
                                                            @endif
                                                            @if(Auth::user()->hasRole('manager'))
                                                                <div class="shift-actions mt-2">
                                                                    <button class="btn btn-sm btn-outline-primary edit-shift" 
                                                                        data-schedule="{{ $employeeSchedule->id }}"
                                                                        data-employee="{{ $employeeSchedule->employee_id }}"
                                                                        data-date="{{ $employeeSchedule->work_date }}"
                                                                        data-start="{{ $employeeSchedule->start_time }}"
                                                                        data-end="{{ $employeeSchedule->end_time }}"
                                                                        data-notes="{{ $employeeSchedule->notes }}">
                                                                        <i class="fas fa-edit"></i>
                                                                    </button>
                                                                    <form action="{{ route('employee-schedules.destroy', $employeeSchedule) }}" 
                                                                        method="POST" class="d-inline">
                                                                        @csrf
                                                                        @method('DELETE')
                                                                        <button type="submit" class="btn btn-sm btn-outline-danger" 
                                                                            onclick="return confirm('Are you sure you want to delete this shift?')">
                                                                            <i class="fas fa-trash"></i>
                                                                        </button>
                                                                    </form>
                                                                </div>
                                                            @endif
                                                        </div>
                                                    @else
                                                        <div class="no-shift text-muted">
                                                            <small>No shift</small>
                                                        </div>
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
                        <div class="tab-pane fade" id="monthly" role="tabpanel">
                            <div class="monthly-calendar">
                                <!-- Monthly calendar will be loaded here via AJAX -->
                                <div class="text-center py-5">
                                    <div class="spinner-border text-primary" role="status">
                                        <span class="visually-hidden">Loading...</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Availability Tab -->
                        <div class="tab-pane fade" id="availability" role="tabpanel">
                            <div class="availability-settings">
                                <form id="availabilityForm" class="mb-4">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <h6>Preferred Working Hours</h6>
                                            <div class="mb-3">
                                                <label class="form-label">Start Time</label>
                                                <input type="time" class="form-control" name="preferred_start_time">
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">End Time</label>
                                                <input type="time" class="form-control" name="preferred_end_time">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <h6>Preferred Days</h6>
                                            @foreach(['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'] as $day)
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" name="preferred_days[]" value="{{ strtolower($day) }}" id="day_{{ strtolower($day) }}">
                                                    <label class="form-check-label" for="day_{{ strtolower($day) }}">
                                                        {{ $day }}
                                                    </label>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                    <button type="submit" class="btn btn-primary mt-3">Save Preferences</button>
                                </form>
                            </div>
                        </div>

                        <!-- Reports Tab -->
                        @if(Auth::user()->hasRole('manager'))
                        <div class="tab-pane fade" id="reports" role="tabpanel">
                            <div class="reports-section">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="card mb-4">
                                            <div class="card-body">
                                                <h6 class="card-title">Hours Summary</h6>
                                                <canvas id="hoursChart"></canvas>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="card mb-4">
                                            <div class="card-body">
                                                <h6 class="card-title">Shift Distribution</h6>
                                                <canvas id="shiftsChart"></canvas>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="mt-3">
                                    <a href="{{ route('employee-schedules.export', request()->query()) }}" class="btn btn-success">
                                        <i class="fas fa-file-pdf"></i> Export Schedule
                                    </a>
                                    <button class="btn btn-primary" id="generateReport">
                                        <i class="fas fa-chart-line"></i> Generate Report
                                    </button>
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Schedule Modal -->
<div class="modal fade" id="scheduleModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Shift</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="scheduleForm" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="employee_id" class="form-label">Employee</label>
                        <select class="form-select" id="employee_id" name="employee_id" required>
                            <option value="">Select Employee</option>
                            @foreach($employees as $employee)
                                <option value="{{ $employee->id }}">{{ $employee->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="work_date" class="form-label">Date</label>
                        <input type="date" class="form-control" id="work_date" name="work_date" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="start_time" class="form-label">Start Time</label>
                                <input type="time" class="form-control" id="start_time" name="start_time" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="end_time" class="form-label">End Time</label>
                                <input type="time" class="form-control" id="end_time" name="end_time" required>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="notes" class="form-label">Notes</label>
                        <textarea class="form-control" id="notes" name="notes" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save Shift</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('styles')
<style>
    .schedule-slot {
        min-width: 150px;
        height: 120px;
        vertical-align: top;
        padding: 8px;
    }
    
    .shift-block {
        background-color: #e3f2fd;
        border-radius: 4px;
        padding: 8px;
        height: 100%;
    }
    
    .shift-time {
        font-weight: 500;
        color: #1976d2;
    }
    
    .shift-notes {
        margin-top: 4px;
        color: #666;
    }
    
    .no-shift {
        display: flex;
        align-items: center;
        justify-content: center;
        height: 100%;
    }
    
    .shift-actions {
        display: flex;
        gap: 4px;
    }
    
    .table th {
        background-color: #f8f9fa;
    }
    
    .card {
        border: none;
        border-radius: 8px;
    }
    
    .card-header {
        border-radius: 8px 8px 0 0 !important;
    }

    .nav-tabs .nav-link {
        color: #495057;
    }

    .nav-tabs .nav-link.active {
        color: #1976d2;
        font-weight: 500;
    }

    .monthly-calendar {
        min-height: 400px;
    }

    .availability-settings {
        max-width: 800px;
        margin: 0 auto;
    }

    .reports-section canvas {
        width: 100% !important;
        height: 300px !important;
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('scheduleModal');
    const form = document.getElementById('scheduleForm');
    const modalTitle = modal.querySelector('.modal-title');
    
    // Handle edit buttons
    document.querySelectorAll('.edit-shift').forEach(button => {
        button.addEventListener('click', function() {
            const scheduleId = this.dataset.schedule;
            const employeeId = this.dataset.employee;
            const date = this.dataset.date;
            const startTime = this.dataset.start;
            const endTime = this.dataset.end;
            const notes = this.dataset.notes;
            
            modalTitle.textContent = 'Edit Shift';
            form.action = `/schedules/${scheduleId}`;
            form.insertAdjacentHTML('afterbegin', '<input type="hidden" name="_method" value="PUT">');
            
            form.querySelector('#employee_id').value = employeeId;
            form.querySelector('#work_date').value = date;
            form.querySelector('#start_time').value = startTime;
            form.querySelector('#end_time').value = endTime;
            form.querySelector('#notes').value = notes;
            
            new bootstrap.Modal(modal).show();
        });
    });
    
    // Reset form when modal is closed
    modal.addEventListener('hidden.bs.modal', function() {
        form.reset();
        form.action = '{{ route("employee-schedules.store") }}';
        modalTitle.textContent = 'Add Shift';
        const methodInput = form.querySelector('input[name="_method"]');
        if (methodInput) methodInput.remove();
    });

    // Load monthly calendar when tab is clicked
    document.getElementById('monthly-tab').addEventListener('click', function() {
        loadMonthlyCalendar();
    });

    // Initialize charts when reports tab is clicked
    document.getElementById('reports-tab').addEventListener('click', function() {
        initializeCharts();
    });

    // Handle availability form submission
    document.getElementById('availabilityForm').addEventListener('submit', function(e) {
        e.preventDefault();
        // Add your availability form submission logic here
    });

    // Handle report generation
    document.getElementById('generateReport').addEventListener('click', function() {
        // Add your report generation logic here
    });
});

function loadMonthlyCalendar() {
    // Add your monthly calendar loading logic here
    // This should make an AJAX call to fetch the monthly view data
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
                backgroundColor: '#1976d2'
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
                backgroundColor: ['#1976d2', '#2196f3', '#64b5f6', '#90caf9']
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false
        }
    });
}
</script>
@endpush
@endsection 