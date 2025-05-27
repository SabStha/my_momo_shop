@foreach($timeLogs as $date => $logs)
    <div class="card mb-4">
        <div class="card-header">
            <h4 class="card-title">{{ \Carbon\Carbon::parse($date)->format('F j, Y') }}</h4>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Employee</th>
                            <th>Clock In</th>
                            <th>Clock Out</th>
                            <th>Break Start</th>
                            <th>Break End</th>
                            <th>Total Hours</th>
                            <th>Break Hours</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($logs as $log)
                            <tr>
                                <td>{{ $log->employee->user->name }}</td>
                                <td>{{ $log->clock_in->format('H:i:s') }}</td>
                                <td>{{ $log->clock_out ? $log->clock_out->format('H:i:s') : '-' }}</td>
                                <td>{{ $log->break_start ? $log->break_start->format('H:i:s') : '-' }}</td>
                                <td>{{ $log->break_end ? $log->break_end->format('H:i:s') : '-' }}</td>
                                <td>{{ $log->getTotalWorkHours() }}</td>
                                <td>{{ $log->getBreakHours() }}</td>
                                <td>
                                    @if($log->status === 'completed')
                                        <span class="badge bg-secondary">Completed</span>
                                    @elseif($log->status === 'on_break')
                                        <span class="badge bg-warning">On Break</span>
                                    @else
                                        <span class="badge bg-success">Active</span>
                                    @endif
                                </td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-primary edit-log" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#editModal"
                                            data-log-id="{{ $log->id }}"
                                            data-clock-in="{{ $log->clock_in->format('Y-m-d\TH:i:s') }}"
                                            data-clock-out="{{ $log->clock_out ? $log->clock_out->format('Y-m-d\TH:i:s') : '' }}"
                                            data-break-start="{{ $log->break_start ? $log->break_start->format('Y-m-d\TH:i:s') : '' }}"
                                            data-break-end="{{ $log->break_end ? $log->break_end->format('Y-m-d\TH:i:s') : '' }}"
                                            data-notes="{{ $log->notes }}">
                                        Edit
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endforeach

<!-- Edit Modal -->
<div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editModalLabel">Edit Time Log</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editLogForm">
                <div class="modal-body">
                    <input type="hidden" id="log_id" name="log_id">
                    <div class="mb-3">
                        <label for="edit_clock_in" class="form-label">Clock In</label>
                        <input type="datetime-local" class="form-control" id="edit_clock_in" name="clock_in" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_clock_out" class="form-label">Clock Out</label>
                        <input type="datetime-local" class="form-control" id="edit_clock_out" name="clock_out">
                    </div>
                    <div class="mb-3">
                        <label for="edit_break_start" class="form-label">Break Start</label>
                        <input type="datetime-local" class="form-control" id="edit_break_start" name="break_start">
                    </div>
                    <div class="mb-3">
                        <label for="edit_break_end" class="form-label">Break End</label>
                        <input type="datetime-local" class="form-control" id="edit_break_end" name="break_end">
                    </div>
                    <div class="mb-3">
                        <label for="edit_notes" class="form-label">Notes</label>
                        <textarea class="form-control" id="edit_notes" name="notes" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    // Handle edit button click
    $('.edit-log').on('click', function() {
        const logId = $(this).data('log-id');
        const clockIn = $(this).data('clock-in');
        const clockOut = $(this).data('clock-out');
        const breakStart = $(this).data('break-start');
        const breakEnd = $(this).data('break-end');
        const notes = $(this).data('notes');

        $('#log_id').val(logId);
        $('#edit_clock_in').val(clockIn);
        $('#edit_clock_out').val(clockOut);
        $('#edit_break_start').val(breakStart);
        $('#edit_break_end').val(breakEnd);
        $('#edit_notes').val(notes);
    });

    // Handle form submission
    $('#editLogForm').on('submit', function(e) {
        e.preventDefault();
        const logId = $('#log_id').val();
        
        $.ajax({
            url: `/admin/clock/${logId}`,
            method: 'PUT',
            data: $(this).serialize(),
            success: function(response) {
                $('#editModal').modal('hide');
                // Refresh the report
                $('#reportForm').submit();
            },
            error: function(xhr) {
                alert(xhr.responseJSON?.message || 'An error occurred while updating the time log.');
            }
        });
    });
});
</script>
@endpush 