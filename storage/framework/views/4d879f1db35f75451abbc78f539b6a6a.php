

<?php $__env->startSection('content'); ?>
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
                                        <?php $__currentLoopData = $lastSevenDays; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $day): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <option value="<?php echo e($day); ?>" <?php echo e($day == $date ? 'selected' : ''); ?>>
                                                <?php echo e(\Carbon\Carbon::parse($day)->format('F j, Y')); ?>

                                            </option>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">&nbsp;</label>
                                    <button type="submit" class="btn btn-primary d-block">View Records</button>
                                </div>
                            </form>
                        </div>
                        <div class="col-md-6 text-end">
                            <a href="<?php echo e(route('admin.clock.report')); ?>" class="btn btn-info">
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
                                        <?php echo csrf_field(); ?>
                                        <div class="form-group">
                                            <label for="employee_search">Search Employee</label>
                                            <input type="text" class="form-control" id="employee_search" name="employee_search" placeholder="Enter employee ID or name" required>
                                        </div>
                                        <button type="submit" class="btn btn-success">Clock In</button>
                                    </form>

                                    <form id="clockOutForm" class="mb-3">
                                        <?php echo csrf_field(); ?>
                                        <div class="form-group">
                                            <label for="employee_search_out">Search Employee</label>
                                            <input type="text" class="form-control" id="employee_search_out" name="employee_search" placeholder="Enter employee ID or name" required>
                                        </div>
                                        <button type="submit" class="btn btn-danger">Clock Out</button>
                                    </form>

                                    <form id="startBreakForm" class="mb-3">
                                        <?php echo csrf_field(); ?>
                                        <div class="form-group">
                                            <label for="employee_search_break_start">Search Employee</label>
                                            <input type="text" class="form-control" id="employee_search_break_start" name="employee_search" placeholder="Enter employee ID or name" required>
                                        </div>
                                        <button type="submit" class="btn btn-warning">Start Break</button>
                                    </form>

                                    <form id="endBreakForm">
                                        <?php echo csrf_field(); ?>
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
                                                <?php $__empty_1 = true; $__currentLoopData = $timeLogs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $log): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                                    <tr>
                                                        <td><?php echo e($log->employee->user->name); ?></td>
                                                        <td><?php echo e($log->clock_in->format('H:i:s')); ?></td>
                                                        <td><?php echo e($log->clock_out ? $log->clock_out->format('H:i:s') : '-'); ?></td>
                                                        <td><?php echo e($log->break_start ? $log->break_start->format('H:i:s') : '-'); ?></td>
                                                        <td><?php echo e($log->break_end ? $log->break_end->format('H:i:s') : '-'); ?></td>
                                                        <td>
                                                            <?php if($log->status === 'completed'): ?>
                                                                <span class="badge bg-secondary">Completed</span>
                                                            <?php elseif($log->status === 'on_break'): ?>
                                                                <span class="badge bg-warning">On Break</span>
                                                            <?php else: ?>
                                                                <span class="badge bg-success">Active</span>
                                                            <?php endif; ?>
                                                        </td>
                                                        <td>
                                                            <button type="button" class="btn btn-sm btn-primary edit-log" 
                                                                    data-bs-toggle="modal" 
                                                                    data-bs-target="#editModal"
                                                                    data-log-id="<?php echo e($log->id); ?>"
                                                                    data-clock-in="<?php echo e($log->clock_in ? $log->clock_in->format('Y-m-d\TH:i:s') : ''); ?>"
                                                                    data-clock-out="<?php echo e($log->clock_out ? $log->clock_out->format('Y-m-d\TH:i:s') : ''); ?>"
                                                                    data-break-start="<?php echo e($log->break_start ? $log->break_start->format('Y-m-d\TH:i:s') : ''); ?>"
                                                                    data-break-end="<?php echo e($log->break_end ? $log->break_end->format('Y-m-d\TH:i:s') : ''); ?>"
                                                                    data-notes="<?php echo e($log->notes); ?>">
                                                                Edit
                                                            </button>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                                    <tr>
                                                        <td colspan="7" class="text-center">No clock records for today.</td>
                                                    </tr>
                                                <?php endif; ?>
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
                        <input type="datetime-local" class="form-control" id="edit_clock_in" name="clock_in" required step="1" value="<?php echo e(isset($log) && $log->clock_in ? $log->clock_in->format('Y-m-d\TH:i:s') : ''); ?>">
                    </div>
                    <div class="mb-3">
                        <label for="edit_clock_out" class="form-label">Clock Out</label>
                        <input type="datetime-local" class="form-control" id="edit_clock_out" name="clock_out" step="1" value="<?php echo e(isset($log) && $log->clock_out ? $log->clock_out->format('Y-m-d\TH:i:s') : ''); ?>">
                    </div>
                    <div class="mb-3">
                        <label for="edit_break_start" class="form-label">Break Start</label>
                        <input type="datetime-local" class="form-control" id="edit_break_start" name="break_start" step="1" value="<?php echo e(isset($log) && $log->break_start ? $log->break_start->format('Y-m-d\TH:i:s') : ''); ?>">
                    </div>
                    <div class="mb-3">
                        <label for="edit_break_end" class="form-label">Break End</label>
                        <input type="datetime-local" class="form-control" id="edit_break_end" name="break_end" step="1" value="<?php echo e(isset($log) && $log->break_end ? $log->break_end->format('Y-m-d\TH:i:s') : ''); ?>">
                    </div>
                    <div class="mb-3">
                        <label for="edit_notes" class="form-label">Notes</label>
                        <textarea class="form-control" id="edit_notes" name="notes" rows="3"><?php echo e(isset($log) ? $log->notes : ''); ?></textarea>
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

<?php $__env->startPush('scripts'); ?>
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
            source: '<?php echo e(route("admin.clock.search")); ?>',
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
            window.location.href = `<?php echo e(route('admin.clock.index')); ?>?date=${date}`;
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

        // Handle Clock In form submission
        $('#clockInForm').on('submit', function(e) {
            e.preventDefault();
            const form = $(this);
            showConfirmationModal('Are you sure you want to clock in this employee?', function() {
                $.ajax({
                    url: '<?php echo e(route("admin.clock.in")); ?>',
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
                    url: '<?php echo e(route("admin.clock.out")); ?>',
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
                    url: '<?php echo e(route("admin.clock.break.start")); ?>',
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
                    url: '<?php echo e(route("admin.clock.break.end")); ?>',
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
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?> 
<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\sabst\momo_shop\resources\views/admin/clock/index.blade.php ENDPATH**/ ?>