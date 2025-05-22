@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h2 class="h4 mb-0">Salary Information</h2>
                        <a href="{{ route('employee.dashboard') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back to Dashboard
                        </a>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="card bg-light">
                                <div class="card-body text-center">
                                    <h6 class="card-subtitle mb-2 text-muted">Current Hourly Rate</h6>
                                    <h3 class="card-title">${{ number_format($employee->hourly_rate, 2) }}</h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card bg-light">
                                <div class="card-body text-center">
                                    <h6 class="card-subtitle mb-2 text-muted">This Month's Hours</h6>
                                    <h3 class="card-title">{{ $monthlyBreakdown['total_hours'] }}</h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card bg-light">
                                <div class="card-body text-center">
                                    <h6 class="card-subtitle mb-2 text-muted">This Month's Salary</h6>
                                    <h3 class="card-title">${{ number_format($monthlyBreakdown['total_salary'], 2) }}</h3>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-body">
                    <h3 class="h5 mb-4">Salary History</h3>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Month</th>
                                    <th>Hours Worked</th>
                                    <th>Amount</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($salaryHistory as $record)
                                    <tr>
                                        <td>{{ $record['month'] }}</td>
                                        <td>{{ $record['hours'] }}</td>
                                        <td>${{ number_format($record['amount'], 2) }}</td>
                                        <td>
                                            <a href="{{ route('employee.salary.index', ['month' => $record['month_number'], 'year' => $record['year']]) }}" 
                                               class="btn btn-sm btn-info">
                                                <i class="fas fa-eye"></i> View Details
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h3 class="h5 mb-0">Monthly Breakdown - {{ \Carbon\Carbon::createFromDate($selectedYear, $selectedMonth, 1)->format('F Y') }}</h3>
                        <a href="{{ route('employee.salary.download-payslip', ['month' => $selectedMonth, 'year' => $selectedYear]) }}" 
                           class="btn btn-primary" 
                           target="_blank">
                            <i class="fas fa-download"></i> Download Payslip
                        </a>
                    </div>

                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Clock In</th>
                                    <th>Clock Out</th>
                                    <th>Hours Worked</th>
                                    <th>Notes</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($monthlyBreakdown['time_logs'] as $log)
                                    <tr>
                                        <td>{{ $log->clock_in->format('M d, Y') }}</td>
                                        <td>{{ $log->clock_in->format('h:i A') }}</td>
                                        <td>{{ $log->clock_out ? $log->clock_out->format('h:i A') : 'Still Working' }}</td>
                                        <td>{{ $log->hours_worked }}</td>
                                        <td>{{ $log->notes ?? '-' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center">No time logs found for this month.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                            <tfoot>
                                <tr class="table-light">
                                    <td colspan="3" class="text-end"><strong>Total Hours:</strong></td>
                                    <td><strong>{{ $monthlyBreakdown['total_hours'] }}</strong></td>
                                    <td></td>
                                </tr>
                                <tr class="table-light">
                                    <td colspan="3" class="text-end"><strong>Hourly Rate:</strong></td>
                                    <td><strong>${{ number_format($monthlyBreakdown['hourly_rate'], 2) }}</strong></td>
                                    <td></td>
                                </tr>
                                <tr class="table-primary">
                                    <td colspan="3" class="text-end"><strong>Total Salary:</strong></td>
                                    <td><strong>${{ number_format($monthlyBreakdown['total_salary'], 2) }}</strong></td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 