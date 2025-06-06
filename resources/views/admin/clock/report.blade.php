@extends('layouts.admin')

@section('content')
<div class="max-w-7xl mx-auto py-8">
    <div class="bg-white shadow rounded-lg p-6">
        <h3 class="text-2xl font-bold mb-6">Time Log Reports</h3>
        
        <!-- Alert Container -->
        <div id="alert-container" class="mb-4"></div>

        <!-- Report Form -->
        <form id="reportForm" class="mb-6">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label for="employee_id" class="block font-medium mb-1">Employee</label>
                    <select class="w-full border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400" id="employee_id" name="employee_id" required>
                        <option value="">Select Employee</option>
                        @foreach($employees as $employee)
                            <option value="{{ $employee->id }}">{{ $employee->user->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="report_type" class="block font-medium mb-1">Report Type</label>
                    <select class="w-full border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400" id="report_type" name="report_type" required>
                        <option value="daily">Daily</option>
                        <option value="weekly">Weekly</option>
                        <option value="monthly">Monthly</option>
                    </select>
                </div>

                <div>
                    <label for="date" class="block font-medium mb-1">Date</label>
                    <input type="date" class="w-full border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400" id="date" name="date" required value="{{ date('Y-m-d') }}">
                </div>

                <div class="flex items-end">
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">Generate Report</button>
                </div>
            </div>
        </form>

        <!-- Report Container -->
        <div id="reportContainer" class="mt-6">
            <div class="text-center text-gray-500">
                Select an employee and date to generate a report
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Function to show alert message
    function showAlert(message, type = 'success') {
        const alertContainer = document.getElementById('alert-container');
        const alertDiv = document.createElement('div');
        alertDiv.className = `p-4 rounded-lg ${type === 'success' ? 'bg-green-100 text-green-700' : type === 'error' ? 'bg-red-100 text-red-700' : 'bg-blue-100 text-blue-700'}`;
        alertDiv.innerHTML = `
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    ${type === 'success' ? '✓' : type === 'error' ? '✕' : 'ℹ'}
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium">${message}</p>
                </div>
            </div>
        `;
        alertContainer.innerHTML = ''; // Clear previous alerts
        alertContainer.appendChild(alertDiv);
        setTimeout(() => alertDiv.remove(), 5000);
    }

    // Handle report type change
    document.getElementById('report_type').addEventListener('change', function() {
        const dateInput = document.getElementById('date');
        const reportType = this.value;
        
        // Store the current date value
        const currentDate = dateInput.value;
        
        if (reportType === 'weekly') {
            // Convert date to week format (YYYY-Www)
            if (currentDate) {
                const date = new Date(currentDate);
                const weekNumber = getWeekNumber(date);
                dateInput.value = `${date.getFullYear()}-W${weekNumber.toString().padStart(2, '0')}`;
            }
            dateInput.type = 'week';
        } else if (reportType === 'monthly') {
            // Convert date to month format (YYYY-MM)
            if (currentDate) {
                const date = new Date(currentDate);
                dateInput.value = `${date.getFullYear()}-${(date.getMonth() + 1).toString().padStart(2, '0')}`;
            }
            dateInput.type = 'month';
        } else {
            // Keep the current date for daily reports
            dateInput.type = 'date';
        }
    });

    // Helper function to get week number
    function getWeekNumber(date) {
        const d = new Date(Date.UTC(date.getFullYear(), date.getMonth(), date.getDate()));
        const dayNum = d.getUTCDay() || 7;
        d.setUTCDate(d.getUTCDate() + 4 - dayNum);
        const yearStart = new Date(Date.UTC(d.getUTCFullYear(), 0, 1));
        return Math.ceil((((d - yearStart) / 86400000) + 1) / 7);
    }

    // Handle form submission
    document.getElementById('reportForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        const reportContainer = document.getElementById('reportContainer');
        
        // Show loading state
        reportContainer.innerHTML = `
            <div class="text-center py-8">
                <div class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
                <p class="mt-4 text-gray-600 text-lg">Generating report...</p>
            </div>
        `;

        // Get CSRF token from meta tag
        const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        // Convert FormData to URLSearchParams for better debugging
        const params = new URLSearchParams(formData);

        fetch('{{ route("admin.clock.report.generate") }}', {
            method: 'POST',
            body: params,
            headers: {
                'X-CSRF-TOKEN': token,
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            credentials: 'same-origin'
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                if (data.html.includes('No time logs found')) {
                    reportContainer.innerHTML = `
                        <div class="text-center py-8">
                            <div class="text-gray-500 text-lg">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <p class="mt-2">No time logs found for the selected period</p>
                            </div>
                        </div>
                    `;
                    showAlert('No time logs found for the selected period', 'info');
                } else {
                    reportContainer.innerHTML = data.html;
                    showAlert('Report generated successfully');
                }
            } else {
                reportContainer.innerHTML = `
                    <div class="text-center py-8">
                        <div class="text-red-500 text-lg">
                            <svg class="mx-auto h-12 w-12 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <p class="mt-2">${data.message || 'Error generating report'}</p>
                        </div>
                    </div>
                `;
                showAlert(data.message || 'Error generating report', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            reportContainer.innerHTML = `
                <div class="text-center py-8">
                    <div class="text-red-500 text-lg">
                        <svg class="mx-auto h-12 w-12 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <p class="mt-2">Error generating report: ${error.message}</p>
                    </div>
                </div>
            `;
            showAlert('Error generating report: ' + error.message, 'error');
        });
    });
</script>
@endpush
@endsection