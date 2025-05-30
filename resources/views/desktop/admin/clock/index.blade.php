@extends('desktop.admin.layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Employee Clocking System</h3>
                </div>
                <div class="card-body">
                    <div id="alert-container"></div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <form id="dateSelectorForm" class="row g-3">
                                <div class="col-md-8">
                                    <label for="date" class="form-label">Select Date</label>
                                    <select class="form-select" id="date" name="date">
                                        @foreach($lastSevenDays as $day)
                                            <option value="{{ $day }}" {{ $day == $date ? 'selected' : '' }}>
                                                {{ \Carbon\Carbon::parse($day)->format('F j, Y') }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">&nbsp;</label>
                                    <button type="submit" class="btn btn-primary d-block">View Records</button>
                                </div>
                            </form>
                        </div>
                        <div class="col-md-6 text-end">
                            <a href="{{ route('admin.clock.report') }}" class="btn btn-info">
                                <i class="fas fa-chart-bar"></i> View Reports
                            </a>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h4 class="card-title">Clock In/Out</h4>
                                </div>
                                <div class="card-body">
                                    <form id="clockInForm" class="mb-3">
                                        @csrf
                                        <div class="form-group">
                                            <label for="employee_search">Search Employee</label>
                                            <input type="text" class="form-control" id="employee_search" name="employee_search" placeholder="Enter employee ID or name" required>
                                        </div>
                                        <button type="submit" class="btn btn-success">Clock In</button>
                                    </form>

                                    <form id="clockOutForm" class="mb-3">
                                        @csrf
                                        <div class="form-group">
                                            <label for="employee_search_out">Search Employee</label>
                                            <input type="text" class="form-control" id="employee_search_out" name="employee_search" placeholder="Enter employee ID or name" required>
                                        </div>
                                        <button type="submit" class="btn btn-danger">Clock Out</button>
                                    </form>

                                    <form id="startBreakForm" class="mb-3">
                                        @csrf
                                        <div class="form-group">
                                            <label for="employee_search_break_start">Search Employee</label>
                                            <input type="text" class="form-control" id="employee_search_break_start" name="employee_search" placeholder="Enter employee ID or name" required>
                                        </div>
                                        <button type="submit" class="btn btn-warning">Start Break</button>
                                    </form>

                                    <form id="endBreakForm">
                                        @csrf
                                        <div class="form-group">
                                            <label for="employee_search_break_end">Search Employee</label>
                                            <input type="text" class="form-control" id="employee_search_break_end" name="employee_search" placeholder="Enter employee ID or name" required>
                                        </div>
                                        <button type="submit" class="btn btn-info">End Break</button>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h4 class="card-title">Today's Clock Records</h4>
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
                                                    <th>Status</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody id="timeLogsTable">
                                                @forelse($timeLogs as $log)
                                                    <tr>
                                                        <td>{{ $log->employee->user->name }}</td>
                                                        <td>{{ $log->clock_in->format('H:i:s') }}</td>
                                                        <td>{{ $log->clock_out ? $log->clock_out->format('H:i:s') : '-' }}</td>
                                                        <td>{{ $log->break_start ? $log->break_start->format('H:i:s') : '-' }}</td>
                                                        <td>{{ $log->break_end ? $log->break_end->format('H:i:s') : '-' }}</td>
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
                                                                    data-clock-in="{{ $log->clock_in ? $log->clock_in->format('Y-m-d\TH:i:s') : '' }}"
                                                                    data-clock-out="{{ $log->clock_out ? $log->clock_out->format('Y-m-d\TH:i:s') : '' }}"
                                                                    data-break-start="{{ $log->break_start ? $log->break_start->format('Y-m-d\TH:i:s') : '' }}"
                                                                    data-break-end="{{ $log->break_end ? $log->break_end->format('Y-m-d\TH:i:s') : '' }}"
                                                                    data-notes="{{ $log->notes }}">
                                                                Edit
                                                            </button>
                                                        </td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="7" class="text-center">No clock records for today.</td>
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
            </div>
        </div>
    </div>
</div>

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
                        <input type="datetime-local" class="form-control" id="edit_clock_in" name="clock_in" required step="1" value="{{ isset($log) && $log->clock_in ? $log->clock_in->format('Y-m-d\TH:i:s') : '' }}">
                    </div>
                    <div class="mb-3">
                        <label for="edit_clock_out" class="form-label">Clock Out</label>
                        <input type="datetime-local" class="form-control" id="edit_clock_out" name="clock_out" step="1" value="{{ isset($log) && $log->clock_out ? $log->clock_out->format('Y-m-d\TH:i:s') : '' }}">
                    </div>
                    <div class="mb-3">
                        <label for="edit_break_start" class="form-label">Break Start</label>
                        <input type="datetime-local" class="form-control" id="edit_break_start" name="break_start" step="1" value="{{ isset($log) && $log->break_start ? $log->break_start->format('Y-m-d\TH:i:s') : '' }}">
                    </div>
                    <div class="mb-3">
                        <label for="edit_break_end" class="form-label">Break End</label>
                        <input type="datetime-local" class="form-control" id="edit_break_end" name="break_end" step="1" value="{{ isset($log) && $log->break_end ? $log->break_end->format('Y-m-d\TH:i:s') : '' }}">
                    </div>
                    <div class="mb-3">
                        <label for="edit_notes" class="form-label">Notes</label>
                        <textarea class="form-control" id="edit_notes" name="notes" rows="3">{{ isset($log) ? $log->notes : '' }}</textarea>
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

<!-- Confirmation Modal -->
<div class="modal fade" id="confirmationModal" tabindex="-1" aria-labelledby="confirmationModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="confirmationModalLabel">Please Confirm</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body" id="confirmationModalMessage">
        <!-- Message will be injected here -->
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-primary" id="confirmationModalConfirmBtn">Yes, Continue</button>
      </div>
    </div>
  </div>
</div>

@push('scripts')
<script>
    // Helper to format date to Y-m-d\TH:i:s
    function formatDateForInput(dateString) {
        if (!dateString) return '';
        // Accept both ISO and DB string
        let date = new Date(dateString);
        if (isNaN(date.getTime())) {
            // Try to parse if it's in d/m/Y H:i:s
            const parts = dateString.match(/(\d{2})\/(\d{2})\/(\d{4}) (\d{2}):(\d{2}):(\d{2})/);
            if (parts) {
                date = new Date(parts[3] + '-' + parts[2] + '-' + parts[1] + 'T' + parts[4] + ':' + parts[5] + ':' + parts[6]);
            }
        }
        if (isNaN(date.getTime())) return '';
        const pad = n => n < 10 ? '0' + n : n;
        return date.getFullYear() + '-' + pad(date.getMonth() + 1) + '-' + pad(date.getDate()) +
            'T' + pad(date.getHours()) + ':' + pad(date.getMinutes()) + ':' + pad(date.getSeconds());
    }

    $(document).ready(function() {
        // Always send CSRF token with AJAX
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        // Initialize autocomplete for all employee search inputs
        $('#employee_search, #employee_search_out, #employee_search_break_start, #employee_search_break_end').autocomplete({
            source: '{{ route("admin.clock.search") }}',
            minLength: 2,
            select: function(event, ui) {
                $(this).val(ui.item.label);
                return false;
            }
        });

        // Function to show alert message
        function showAlert(message, type) {
            const alertHtml = `
                <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                    ${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            `;
            $('#alert-container').html(alertHtml);
        }

        // Handle date selector form submission
        $('#dateSelectorForm').on('submit', function(e) {
            e.preventDefault();
            const date = $('#date').val();
            window.location.href = `{{ route('admin.clock.index') }}?date=${date}`;
        });

        // Handle edit button click
        $('.edit-log').on('click', function() {
            const logId = $(this).data('log-id');
            const clockIn = formatDateForInput($(this).data('clock-in'));
            const clockOut = formatDateForInput($(this).data('clock-out'));
            const breakStart = formatDateForInput($(this).data('break-start'));
            const breakEnd = formatDateForInput($(this).data('break-end'));
            const notes = $(this).data('notes');

            $('#log_id').val(logId);
            $('#edit_clock_in').val(clockIn);
            $('#edit_clock_out').val(clockOut);
            $('#edit_break_start').val(breakStart);
            $('#edit_break_end').val(breakEnd);
            $('#edit_notes').val(notes);
        });

        // Use event delegation for edit form submission
        $(document).on('submit', '#editLogForm', function(e) {
            e.preventDefault();
            const logId = $('#log_id').val();
            $.ajax({
                url: `/admin/clock/${logId}`,
                method: 'PUT',
                data: $(this).serialize(),
                success: function(response) {
                    $('#editModal').modal('hide');
                    showAlert(response.message, 'success');
                    window.location.reload();
                },
                error: function(xhr) {
                    showAlert(xhr.responseJSON?.message || 'An error occurred while updating the time log.', 'danger');
                }
            });
        });

        // Show a Bootstrap confirmation modal and run callback if confirmed
        function showConfirmationModal(message, onConfirm) {
            $('#confirmationModalMessage').text(message);
            const modal = new bootstrap.Modal(document.getElementById('confirmationModal'));
            $('#confirmationModalConfirmBtn').off('click').on('click', function() {
                modal.hide();
                if (typeof onConfirm === 'function') onConfirm();
            });
            modal.show();
        }

        // Handle Clock In form submission
        $('#clockInForm').on('submit', function(e) {
            e.preventDefault();
            const form = $(this);
            showConfirmationModal('Are you sure you want to clock in this employee?', function() {
                $.ajax({
                    url: '{{ route("admin.clock.in") }}',
                    method: 'POST',
                    data: form.serialize(),
                    success: function(response) {
                        showAlert(response.message || 'Employee clocked in successfully.', 'success');
                        window.location.reload();
                        form[0].reset();
                    },
                    error: function(xhr) {
                        showAlert(xhr.responseJSON?.message || 'An error occurred.', 'danger');
                    }
                });
            });
        });

        // Handle Clock Out form submission
        $('#clockOutForm').on('submit', function(e) {
            e.preventDefault();
            const form = $(this);
            showConfirmationModal('Are you sure you want to clock out this employee?', function() {
                $.ajax({
                    url: '{{ route("admin.clock.out") }}',
                    method: 'POST',
                    data: form.serialize(),
                    success: function(response) {
                        showAlert(response.message || 'Employee clocked out successfully.', 'success');
                        window.location.reload();
                        form[0].reset();
                    },
                    error: function(xhr) {
                        showAlert(xhr.responseJSON?.message || 'An error occurred.', 'danger');
                    }
                });
            });
        });

        // Handle Start Break form submission
        $('#startBreakForm').on('submit', function(e) {
            e.preventDefault();
            const form = $(this);
            showConfirmationModal('Are you sure you want to start break for this employee?', function() {
                $.ajax({
                    url: '{{ route("admin.clock.break.start") }}',
                    method: 'POST',
                    data: form.serialize(),
                    success: function(response) {
                        showAlert(response.message || 'Break started successfully.', 'success');
                        window.location.reload();
                        form[0].reset();
                    },
                    error: function(xhr) {
                        showAlert(xhr.responseJSON?.message || 'An error occurred.', 'danger');
                    }
                });
            });
        });

        // Handle End Break form submission
        $('#endBreakForm').on('submit', function(e) {
            e.preventDefault();
            const form = $(this);
            showConfirmationModal('Are you sure you want to end break for this employee?', function() {
                $.ajax({
                    url: '{{ route("admin.clock.break.end") }}',
                    method: 'POST',
                    data: form.serialize(),
                    success: function(response) {
                        showAlert(response.message || 'Break ended successfully.', 'success');
                        window.location.reload();
                        form[0].reset();
                    },
                    error: function(xhr) {
                        showAlert(xhr.responseJSON?.message || 'An error occurred.', 'danger');
                    }
                });
            });
        });
    });
</script>
@endpush
@endsection 