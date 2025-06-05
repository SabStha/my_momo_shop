@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">Employee Schedules</h4>
                    @if(auth()->user()->hasRole('manager'))
                        <a href="{{ route('admin.employee-schedules.create') }}" class="btn btn-primary">Add Schedule</a>
                    @endif
                </div>

                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <form action="{{ route('admin.employee-schedules.index') }}" method="GET" class="form-inline">
                                <div class="form-group mr-2">
                                    <input type="date" name="start_date" class="form-control" value="{{ $startDate->format('Y-m-d') }}">
                                </div>
                                @if(auth()->user()->hasRole('manager'))
                                    <div class="form-group mr-2">
                                        <select name="employee_id" class="form-control">
                                            <option value="">All Employees</option>
                                            @foreach($employees as $employee)
                                                <option value="{{ $employee->id }}" {{ request('employee_id') == $employee->id ? 'selected' : '' }}>
                                                    {{ $employee->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                @endif
                                <button type="submit" class="btn btn-secondary">Filter</button>
                            </form>
                        </div>
                        <div class="col-md-6 text-right">
                            <a href="{{ route('admin.employee-schedules.export', request()->query()) }}" class="btn btn-success">Export PDF</a>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Employee</th>
                                    @for($date = $startDate->copy(); $date <= $startDate->copy()->endOfWeek(); $date->addDay())
                                        <th>{{ $date->format('D, M d') }}</th>
                                    @endfor
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($employees as $employee)
                                    <tr>
                                        <td>{{ $employee->name }}</td>
                                        @for($date = $startDate->copy(); $date <= $startDate->copy()->endOfWeek(); $date->addDay())
                                            <td>
                                                @if(isset($schedules[$date->format('Y-m-d')]))
                                                    @foreach($schedules[$date->format('Y-m-d')] as $schedule)
                                                        @if($schedule->employee_id == $employee->id)
                                                            <div class="schedule-item">
                                                                {{ $schedule->start_time->format('h:i A') }} - {{ $schedule->end_time->format('h:i A') }}
                                                                @if(auth()->user()->hasRole('manager'))
                                                                    <div class="mt-1">
                                                                        <a href="{{ route('admin.employee-schedules.edit', $schedule) }}" class="btn btn-sm btn-info">Edit</a>
                                                                        <form action="{{ route('admin.employee-schedules.destroy', $schedule) }}" method="POST" class="d-inline">
                                                                            @csrf
                                                                            @method('DELETE')
                                                                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">Delete</button>
                                                                        </form>
                                                                    </div>
                                                                @endif
                                                            </div>
                                                        @endif
                                                    @endforeach
                                                @endif
                                            </td>
                                        @endfor
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.schedule-item {
    padding: 5px;
    margin-bottom: 5px;
    background-color: #f8f9fa;
    border-radius: 4px;
}
</style>
@endsection 