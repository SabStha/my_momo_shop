@extends('desktop.layouts.admin')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">Employees</h4>
                    <a href="{{ route('admin.employees.create') }}" class="btn btn-primary">Add New Employee</a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Position</th>
                                    <th>Salary</th>
                                    <th>Hire Date</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($employees as $employee)
                                    <tr>
                                        <td>{{ $employee->id }}</td>
                                        <td>{{ $employee->user->name ?? 'N/A' }}</td>
                                        <td>{{ $employee->user->email ?? 'N/A' }}</td>
                                        <td>{{ $employee->position ?? 'N/A' }}</td>
                                        <td>${{ number_format($employee->salary ?? 0, 2) }}</td>
                                        <td>{{ $employee->hire_date ? $employee->hire_date->format('M d, Y') : 'N/A' }}</td>
                                        <td>
                                            <span class="badge bg-{{ ($employee->status ?? 'inactive') === 'active' ? 'success' : 'secondary' }}">
                                                {{ ucfirst($employee->status ?? 'inactive') }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="btn-group">
                                                <a href="{{ route('admin.employees.show', $employee) }}" 
                                                   class="btn btn-sm btn-info">
                                                    <i class="fas fa-eye"></i> View
                                                </a>
                                                <a href="{{ route('admin.employees.edit', $employee) }}" 
                                                   class="btn btn-sm btn-warning">
                                                    <i class="fas fa-edit"></i> Edit
                                                </a>
                                                <form action="{{ route('admin.employees.destroy', $employee) }}" 
                                                      method="POST" 
                                                      class="d-inline"
                                                      onsubmit="return confirm('Are you sure you want to delete this employee?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger">
                                                        <i class="fas fa-trash"></i> Delete
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center">No employees found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 