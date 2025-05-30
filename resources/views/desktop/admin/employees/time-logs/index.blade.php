@extends('desktop.admin.layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Time Logs for {{ $employee->user->name }}</h3>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger">
                            {{ session('error') }}
                        </div>
                    @endif

                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Clock In</th>
                                    <th>Clock Out</th>
                                    <th>Duration</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($timeLogs as $log)
                                    <tr>
                                        <td>{{ $log->clock_in->format('Y-m-d') }}</td>
                                        <td>{{ $log->clock_in->format('H:i:s') }}</td>
                                        <td>{{ $log->clock_out ? $log->clock_out->format('H:i:s') : '-' }}</td>
                                        <td>
                                            @if($log->clock_out)
                                                {{ $log->clock_in->diffInHours($log->clock_out) }} hours
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td>
                                            <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#editModal{{ $log->id }}">
                                                Edit
                                            </button>
                                        </td>
                                    </tr>

                                    <!-- Edit Modal -->
                                    <div class="modal fade" id="editModal{{ $log->id }}" tabindex="-1" role="dialog" aria-labelledby="editModalLabel{{ $log->id }}" aria-hidden="true">
                                        <div class="modal-dialog" role="document">
                                            <div class="modal-content">
                                                <form action="{{ route('admin.employees.time-logs.update', [$employee, $log]) }}" method="POST">
                                                    @csrf
                                                    @method('PUT')
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="editModalLabel{{ $log->id }}">Edit Time Log</h5>
                                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                            <span aria-hidden="true">&times;</span>
                                                        </button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <div class="form-group">
                                                            <label for="clock_in">Clock In Time</label>
                                                            <input type="datetime-local" class="form-control" id="clock_in" name="clock_in" value="{{ $log->clock_in->format('Y-m-d\TH:i:s') }}" required>
                                                        </div>
                                                        <div class="form-group">
                                                            <label for="clock_out">Clock Out Time</label>
                                                            <input type="datetime-local" class="form-control" id="clock_out" name="clock_out" value="{{ $log->clock_out ? $log->clock_out->format('Y-m-d\TH:i:s') : '' }}">
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                                        <button type="submit" class="btn btn-primary">Save changes</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
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