@extends('layouts.admin')

@section('content')
<div class="max-w-7xl mx-auto py-8">
    <div class="bg-white shadow-lg rounded-lg overflow-hidden">
        <!-- Header Section -->
        <div class="bg-gradient-to-r from-blue-600 to-blue-800 px-6 py-4">
            <div class="flex items-center justify-between">
                <h3 class="text-2xl font-bold text-white">Time Log Reports</h3>
                <div class="flex items-center space-x-2">
                    <button onclick="window.print()" class="text-white hover:text-blue-200 transition-colors">
                        <i class="fas fa-print"></i>
                    </button>
                    <button onclick="exportToExcel()" class="text-white hover:text-blue-200 transition-colors">
                        <i class="fas fa-file-excel"></i>
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Alert Container -->
        <div id="alert-container" class="px-6 pt-4"></div>

        <!-- Report Form -->
        <form id="reportForm" class="p-6 border-b">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <!-- Employee Selection -->
                <div class="space-y-2">
                    <label for="employee_id" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-user mr-2 text-blue-600"></i>Employee
                    </label>
                    <select class="w-full h-12 px-4 bg-white border-2 border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-200 focus:ring-opacity-50 transition-all duration-200 text-gray-700 font-medium" 
                            id="employee_id" 
                            name="employee_id" 
                            required>
                        <option value="" class="text-gray-500 py-2">Select Employee</option>
                        @foreach($employees as $employee)
                            <option value="{{ $employee->id }}" class="py-2">{{ $employee->user->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Report Type -->
                <div class="space-y-2">
                    <label for="report_type" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-chart-bar mr-2 text-blue-600"></i>Report Type
                    </label>
                    <select class="w-full h-12 px-4 bg-white border-2 border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-200 focus:ring-opacity-50 transition-all duration-200 text-gray-700 font-medium" 
                            id="report_type" 
                            name="report_type" 
                            required>
                        <option value="daily" class="py-2">Daily</option>
                        <option value="weekly" class="py-2">Weekly</option>
                        <option value="monthly" class="py-2">Monthly</option>
                    </select>
                </div>

                <!-- Date Selection -->
                <div class="space-y-2">
                    <label for="date" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-calendar mr-2 text-blue-600"></i>Date
                    </label>
                    <input type="date" 
                           class="w-full h-12 px-4 bg-white border-2 border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-200 focus:ring-opacity-50 transition-all duration-200 text-gray-700 font-medium" 
                           id="date" 
                           name="date" 
                           required 
                           value="{{ date('Y-m-d') }}">
                </div>

                <!-- Generate Button -->
                <div class="flex items-end">
                    <button type="button" 
                            id="generateReportBtn" 
                            class="w-full h-12 bg-blue-600 hover:bg-blue-700 text-white font-medium px-4 rounded-lg transition-all duration-200 flex items-center justify-center shadow-sm hover:shadow-md transform hover:-translate-y-0.5">
                        <i class="fas fa-chart-line mr-2"></i>
                        Generate Report
                    </button>
                </div>
            </div>
        </form>

        <!-- Report Container -->
        <div id="reportContainer" class="p-6">
            <div class="text-center text-gray-500 py-12">
                <i class="fas fa-chart-line text-4xl mb-4"></i>
                <p class="text-lg">Select an employee and date to generate a report</p>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Function to show alert message
    function showAlert(message, type = 'success') {
        const alertContainer = document.getElementById('alert-container');
        const alertDiv = document.createElement('div');
        alertDiv.className = `p-4 rounded-lg shadow-sm ${
            type === 'success' ? 'bg-green-50 text-green-800 border border-green-200' :
            type === 'error' ? 'bg-red-50 text-red-800 border border-red-200' :
            'bg-blue-50 text-blue-800 border border-blue-200'
        }`;
        alertDiv.innerHTML = `
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    ${type === 'success' ? '<i class="fas fa-check-circle text-green-400"></i>' :
                      type === 'error' ? '<i class="fas fa-exclamation-circle text-red-400"></i>' :
                      '<i class="fas fa-info-circle text-blue-400"></i>'}
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium">${message}</p>
                </div>
                <div class="ml-auto pl-3">
                    <button onclick="this.parentElement.parentElement.parentElement.remove()" class="text-gray-400 hover:text-gray-500">
                        <i class="fas fa-times"></i>
                    </button>
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

    // Function to export to Excel
    window.exportToExcel = function() {
        const reportContainer = document.getElementById('reportContainer');
        const table = reportContainer.querySelector('table');
        if (!table) {
            showAlert('No data to export', 'error');
            return;
        }

        // Create a workbook
        const wb = XLSX.utils.book_new();
        const ws = XLSX.utils.table_to_sheet(table);
        XLSX.utils.book_append_sheet(wb, ws, "Time Log Report");
        
        // Generate and download the file
        XLSX.writeFile(wb, "time_log_report.xlsx");
    }

    // Function to generate report
    window.generateReport = function() {
        const form = document.getElementById('reportForm');
        const formData = new FormData(form);
        const reportContainer = document.getElementById('reportContainer');
        
        // Validate form
        if (!form.checkValidity()) {
            form.reportValidity();
            return;
        }
        
        // Show loading state
        reportContainer.innerHTML = `
            <div class="text-center py-12">
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
                        <div class="text-center py-12">
                            <div class="text-gray-500">
                                <i class="fas fa-clipboard-list text-6xl mb-4"></i>
                                <p class="text-lg">No time logs found for the selected period</p>
                                <p class="text-sm mt-2">Try selecting a different date range or employee</p>
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
                    <div class="text-center py-12">
                        <div class="text-red-500">
                            <i class="fas fa-exclamation-triangle text-6xl mb-4"></i>
                            <p class="text-lg">${data.message || 'Error generating report'}</p>
                            <p class="text-sm mt-2">Please try again or contact support if the problem persists</p>
                        </div>
                    </div>
                `;
                showAlert(data.message || 'Error generating report', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            reportContainer.innerHTML = `
                <div class="text-center py-12">
                    <div class="text-red-500">
                        <i class="fas fa-exclamation-triangle text-6xl mb-4"></i>
                        <p class="text-lg">Error generating report</p>
                        <p class="text-sm mt-2">${error.message}</p>
                        <p class="text-sm mt-2">Please try again or contact support if the problem persists</p>
                    </div>
                </div>
            `;
            showAlert('Error generating report: ' + error.message, 'error');
        });
    }

    // Add click event listener to the generate report button
    document.getElementById('generateReportBtn').addEventListener('click', window.generateReport);
    });
</script>
@endpush
@endsection