@extends('desktop.layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Employee Details</h3>
                    <div class="card-tools">
                        <a href="{{ route('admin.employees.edit', $employee) }}" class="btn btn-primary btn-sm">
                            Edit Employee
                        </a>
                        <a href="{{ route('admin.employees.time-logs.index', $employee) }}" class="btn btn-info btn-sm">
                            View Time Logs
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <strong>Name:</strong>
                                <p>{{ $employee->user->name }}</p>
                            </div>
                            <div class="mb-3">
                                <strong>Email:</strong>
                                <p>{{ $employee->user->email }}</p>
                            </div>
                            <div class="mb-3">
                                <strong>Position:</strong>
                                <p>{{ $employee->position }}</p>
                            </div>
                            <div class="mb-3">
                                <strong>Salary:</strong>
                                <p>${{ number_format($employee->salary, 2) }}</p>
                            </div>
                            <div class="mb-3">
                                <strong>Hire Date:</strong>
                                <p>{{ $employee->hire_date->format('M d, Y') }}</p>
                            </div>
                            <div class="mb-3">
                                <strong>Status:</strong>
                                <p>
                                    <span class="badge badge-{{ $employee->status === 'active' ? 'success' : 'danger' }}">
                                        {{ ucfirst($employee->status) }}
                                    </span>
                                </p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <strong>Phone:</strong>
                                <p>{{ $employee->phone ?? 'Not provided' }}</p>
                            </div>
                            <div class="mb-3">
                                <strong>Address:</strong>
                                <p>{{ $employee->address ?? 'Not provided' }}</p>
                            </div>
                            <div class="mb-3">
                                <strong>Emergency Contact:</strong>
                                <p>{{ $employee->emergency_contact ?? 'Not provided' }}</p>
                            </div>
                            <div class="mb-3">
                                <strong>Created At:</strong>
                                <p>{{ $employee->created_at->format('M d, Y H:i:s') }}</p>
                            </div>
                            <div class="mb-3">
                                <strong>Last Updated:</strong>
                                <p>{{ $employee->updated_at->format('M d, Y H:i:s') }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 