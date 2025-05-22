@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h2 class="h4 mb-0">Welcome, {{ $employee->user->name }}</h2>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="card bg-light">
                                <div class="card-body text-center">
                                    <h6 class="card-subtitle mb-2 text-muted">Current Status</h6>
                                    <h3 class="card-title">
                                        @if($isWorking)
                                            <span class="badge bg-success">Working</span>
                                            <small class="d-block mt-2">
                                                Since {{ $currentShift->clock_in->format('h:i A') }}
                                            </small>
                                        @else
                                            <span class="badge bg-secondary">Off Duty</span>
                                        @endif
                                    </h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card bg-light">
                                <div class="card-body text-center">
                                    <h6 class="card-subtitle mb-2 text-muted">This Month's Hours</h6>
                                    <h3 class="card-title">{{ $workHours }}</h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card bg-light">
                                <div class="card-body text-center">
                                    <h6 class="card-subtitle mb-2 text-muted">This Month's Salary</h6>
                                    <h3 class="card-title">${{ number_format($salary, 2) }}</h3>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h3 class="h5 mb-0">Recent Time Logs</h3>
                        <a href="{{ route('employee.time-logs') }}" class="btn btn-sm btn-outline-primary">
                            View All
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
                                @forelse($timeLogs as $log)
                                    <tr>
                                        <td>{{ $log->clock_in->format('M d, Y') }}</td>
                                        <td>{{ $log->clock_in->format('h:i A') }}</td>
                                        <td>{{ $log->clock_out ? $log->clock_out->format('h:i A') : 'Still Working' }}</td>
                                        <td>{{ $log->hours_worked }}</td>
                                        <td>{{ $log->notes ?? '-' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center">No time logs found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4">
                        {{ $timeLogs->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 