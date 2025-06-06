@extends('layouts.admin')

@section('content')
<div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
    <div class="bg-white shadow-lg rounded-lg p-6">
        <div class="flex justify-between items-center mb-6">
            <h3 class="text-2xl font-bold text-gray-900">Employee Clocking System</h3>
            <a href="{{ route('admin.clock.report') }}" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                </svg>
                View Reports
            </a>
        </div>

        <div id="alert-container"></div>

        <div class="mb-6">
            <form id="dateSelectorForm" class="flex flex-col sm:flex-row gap-4 items-end">
                <div class="flex-1">
                    <label for="date" class="block text-sm font-medium text-gray-700 mb-1">Select Date</label>
                    <select class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md" id="date" name="date">
                        @foreach($lastSevenDays as $day)
                            <option value="{{ $day }}" {{ $day == $date ? 'selected' : '' }}>
                                {{ \Carbon\Carbon::parse($day)->format('F j, Y') }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    View Records
                </button>
            </form>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="bg-white shadow rounded-lg p-6 space-y-6">
                <h4 class="text-lg font-semibold text-gray-900">Clock Actions</h4>

                {{-- Clock In Form --}}
                <form id="clockInForm" class="clock-form">
                    @csrf
                    <input type="hidden" name="action" value="clock_in">
                    <div class="space-y-2">
                        <label class="block text-sm font-medium text-gray-700">Clock In</label>
                        <input type="text" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm" name="employee_identifier" required placeholder="Enter employee ID or name">
                        <button type="button" class="clock-action-btn w-full inline-flex justify-center items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                            Clock In
                        </button>
                    </div>
                </form>

                {{-- Clock Out Form --}}
                <form id="clockOutForm" class="clock-form">
                    @csrf
                    <input type="hidden" name="action" value="clock_out">
                    <div class="space-y-2">
                        <label class="block text-sm font-medium text-gray-700">Clock Out</label>
                        <input type="text" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm" name="employee_identifier" required placeholder="Enter employee ID or name">
                        <button type="button" class="clock-action-btn w-full inline-flex justify-center items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                            Clock Out
                        </button>
                    </div>
                </form>

                {{-- Start Break Form --}}
                <form id="startBreakForm" class="clock-form">
                    @csrf
                    <input type="hidden" name="action" value="start_break">
                    <div class="space-y-2">
                        <label class="block text-sm font-medium text-gray-700">Start Break</label>
                        <input type="text" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm" name="employee_identifier" required placeholder="Enter employee ID or name">
                        <button type="button" class="clock-action-btn w-full inline-flex justify-center items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-yellow-600 hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500">
                            Start Break
                        </button>
                    </div>
                </form>

                {{-- End Break Form --}}
                <form id="endBreakForm" class="clock-form">
                    @csrf
                    <input type="hidden" name="action" value="end_break">
                    <div class="space-y-2">
                        <label class="block text-sm font-medium text-gray-700">End Break</label>
                        <input type="text" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm" name="employee_identifier" required placeholder="Enter employee ID or name">
                        <button type="button" class="clock-action-btn w-full inline-flex justify-center items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            End Break
                        </button>
                    </div>
                </form>
            </div>

            {{-- Clock Records Table --}}
            <div class="bg-white shadow rounded-lg p-6">
                <h4 class="text-lg font-semibold text-gray-900 mb-4">Today's Clock Records</h4>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Employee</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Clock In</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Clock Out</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Break Start</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Break End</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="timeLogsTableBody" class="bg-white divide-y divide-gray-200">
                            @forelse($timeLogs as $log)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $log->employee->user->name }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ optional($log->clock_in)->format('H:i:s') }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ optional($log->clock_out)->format('H:i:s') ?: '-' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ optional($log->break_start)->format('H:i:s') ?: '-' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ optional($log->break_end)->format('H:i:s') ?: '-' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($log->status === 'completed')
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">Completed</span>
                                        @elseif($log->status === 'on_break')
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">On Break</span>
                                        @else
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Active</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <button class="edit-log inline-flex items-center px-3 py-1 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                                            data-log-id="{{ $log->id }}"
                                            data-clock-in="{{ optional($log->clock_in)->format('Y-m-d\TH:i:s') }}"
                                            data-clock-out="{{ optional($log->clock_out)->format('Y-m-d\TH:i:s') }}"
                                            data-break-start="{{ optional($log->break_start)->format('Y-m-d\TH:i:s') }}"
                                            data-break-end="{{ optional($log->break_end)->format('Y-m-d\TH:i:s') }}"
                                            data-notes="{{ $log->notes }}">
                                            Edit
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">No clock records for today.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Toast notification function
    function showToast(message, type = 'success') {
        const toast = document.createElement('div');
        toast.className = `fixed top-4 right-4 px-6 py-3 rounded-lg text-white ${
            type === 'success' ? 'bg-green-500' : 'bg-red-500'
        } shadow-lg z-50 transform transition-all duration-300 ease-in-out translate-y-0 opacity-100`;
        toast.textContent = message;
        document.body.appendChild(toast);
        
        // Animate out
        setTimeout(() => {
            toast.classList.add('translate-y-2', 'opacity-0');
            setTimeout(() => toast.remove(), 300);
        }, 2700);
    }

    // Handle date selector form submission
    document.getElementById('dateSelectorForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const date = document.getElementById('date').value;
        window.location.href = `{{ route('admin.clock.index') }}?date=${date}`;
    });

    // Handle all clock action buttons
    document.querySelectorAll('.clock-action-btn').forEach(button => {
        button.addEventListener('click', async function() {
            const form = this.closest('form');
            const action = form.querySelector('input[name="action"]').value;
            const employeeIdentifier = form.querySelector('input[name="employee_identifier"]').value;
            
            if (!employeeIdentifier) {
                showToast('Please enter an employee ID or name', 'error');
                return;
            }
            
            // Show confirmation dialog
            if (!confirm(`Are you sure you want to ${action.replace('_', ' ')} for this employee?`)) {
                return;
            }

            // Disable the button and show loading state
            this.disabled = true;
            const originalText = this.innerHTML;
            this.innerHTML = `
                <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Processing...
            `;

            try {
                const response = await fetch('{{ route("admin.clock.action") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        action: action,
                        employee_identifier: employeeIdentifier
                    })
                });

                const data = await response.json();

                if (response.ok) {
                    showToast(data.message || 'Action completed successfully');
                    form.reset();
                    location.reload();
                } else {
                    showToast(data.message || 'An error occurred', 'error');
                }
            } catch (error) {
                showToast('An error occurred while processing your request', 'error');
                console.error('Error:', error);
            } finally {
                this.disabled = false;
                this.innerHTML = originalText;
            }
        });
    });

    // Handle edit log button click
    document.querySelectorAll('.edit-log').forEach(button => {
        button.addEventListener('click', function() {
            const logId = this.dataset.logId;
            const clockIn = this.dataset.clockIn;
            const clockOut = this.dataset.clockOut;
            const breakStart = this.dataset.breakStart;
            const breakEnd = this.dataset.breakEnd;
            const notes = this.dataset.notes;

            // Populate the edit modal with the log data
            document.getElementById('log_id').value = logId;
            document.getElementById('edit_clock_in').value = clockIn;
            document.getElementById('edit_clock_out').value = clockOut;
            document.getElementById('edit_break_start').value = breakStart;
            document.getElementById('edit_break_end').value = breakEnd;
            document.getElementById('edit_notes').value = notes;
        });
    });
</script>
@endpush

@endsection


