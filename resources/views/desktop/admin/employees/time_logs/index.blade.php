@extends('desktop.admin.layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3">Time Logs for {{ $employee->user->name }} ({{ $employee->employee_id }})</h1>
        <a href="{{ route('admin.employees.list') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Employees
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="card mb-4">
        <div class="card-body">
            <!-- Removed clock-in/out form -->
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <h5 class="card-title mb-3">Time Log History</h5>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Clock In</th>
                            <th>Clock Out</th>
                            <th>Hours Worked</th>
                            <th>Notes</th>
                            <th>Actions</th>
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
                                <td>
                                    <!-- Edit Notes Modal Trigger -->
                                    <button type="button" class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#editNotesModal{{ $log->id }}" title="Edit Notes">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <form action="{{ route('admin.employees.time-logs.destroy', $log) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this time log?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            <!-- Edit Notes Modal -->
                            <div class="modal fade" id="editNotesModal{{ $log->id }}" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <form action="{{ route('admin.employees.time-logs.update', $log) }}" method="POST">
                                            @csrf
                                            @method('PUT')
                                            <div class="modal-header">
                                                <h5 class="modal-title">Edit Notes</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="mb-3">
                                                    <label for="notes" class="form-label">Notes</label>
                                                    <textarea class="form-control" id="notes" name="notes" rows="3">{{ $log->notes }}</textarea>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                <button type="submit" class="btn btn-primary">Save Changes</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center">No time logs found.</td>
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
@endsection 