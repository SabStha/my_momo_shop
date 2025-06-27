// Clock System JavaScript
document.addEventListener('DOMContentLoaded', function() {
    console.log('Clock system initialized'); // Debug log

    // Check if we're on the clock page
    const clockInBtn = document.getElementById('clockInBtn');
    const clockOutBtn = document.getElementById('clockOutBtn');
    const startBreakBtn = document.getElementById('startBreakBtn');
    const endBreakBtn = document.getElementById('endBreakBtn');
    
    if (!clockInBtn && !clockOutBtn && !startBreakBtn && !endBreakBtn) {
        console.log('Clock buttons not found - not on clock page, skipping clock system initialization');
        return;
    }

    let selectedEmployeeId = null;
    let selectedEmployeeName = null;
    let selectedEmployeeEmail = null;
    let selectedEmployeeNumber = null;

    // Initialize clock action buttons
    function initializeClockButtons() {
        console.log('Initializing clock buttons'); // Debug log

        const buttons = {
            'clock_in': 'clockInBtn',
            'clock_out': 'clockOutBtn',
            'start_break': 'startBreakBtn',
            'end_break': 'endBreakBtn'
        };

        for (const [action, buttonId] of Object.entries(buttons)) {
            const button = document.getElementById(buttonId);
            console.log(`Looking for button ${buttonId}:`, button); // Debug log
            
            if (button) {
                button.addEventListener('click', () => handleClockAction(action));
                console.log(`Button ${buttonId} initialized`); // Debug log
            } else {
                console.error(`Button ${buttonId} not found`);
            }
        }
    }

    // Initialize buttons immediately
    initializeClockButtons();

    // Toast notification function
    function showToast(message, type = 'success') {
        const toast = document.createElement('div');
        toast.className = `fixed top-4 right-4 px-6 py-3 rounded-lg text-white ${
            type === 'success' ? 'bg-green-500' : 'bg-red-500'
        } shadow-lg z-50 transform transition-all duration-300 ease-in-out translate-y-0 opacity-100`;
        toast.innerHTML = `
            <div class="flex items-center">
                <i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'} mr-2"></i>
                <span>${message}</span>
            </div>
        `;
        document.body.appendChild(toast);
        
        setTimeout(() => {
            toast.classList.add('translate-y-2', 'opacity-0');
            setTimeout(() => toast.remove(), 300);
        }, 2700);
    }

    // Function to refresh the time logs table
    async function refreshTimeLogsTable() {
        try {
            const dateInput = document.getElementById('date');
            if (!dateInput) {
                console.error('Date input not found');
                return;
            }
            
            const date = dateInput.value;
            const response = await fetch(`/admin/clock/logs?date=${date}`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            });
            const data = await response.json();
            
            if (data) {
                const timeLogsTableBody = document.getElementById('timeLogsTableBody');
                if (timeLogsTableBody) {
                    timeLogsTableBody.innerHTML = data.map(log => `
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">${log.employee.user.name}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${log.clock_in ? new Date(log.clock_in).toLocaleTimeString() : '-'}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${log.clock_out ? new Date(log.clock_out).toLocaleTimeString() : '-'}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${log.break_start ? new Date(log.break_start).toLocaleTimeString() : '-'}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${log.break_end ? new Date(log.break_end).toLocaleTimeString() : '-'}</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                ${getStatusBadge(log.status)}
                            </td>
                        </tr>
                    `).join('') || `
                        <tr>
                            <td colspan="6" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                                <div class="flex flex-col items-center justify-center py-8">
                                    <i class="fas fa-clipboard-list text-4xl text-gray-400 mb-2"></i>
                                    <p>No clock records for today.</p>
                                </div>
                            </td>
                        </tr>
                    `;
                }
            }
        } catch (error) {
            console.error('Error refreshing table:', error);
            showToast('Error refreshing time logs', 'error');
        }
    }

    // Helper function to get status badge HTML
    function getStatusBadge(status) {
        const badges = {
            'completed': {
                class: 'bg-gray-100 text-gray-800',
                icon: 'fa-check-circle',
                text: 'Completed'
            },
            'on_break': {
                class: 'bg-yellow-100 text-yellow-800',
                icon: 'fa-coffee',
                text: 'On Break'
            },
            'active': {
                class: 'bg-green-100 text-green-800',
                icon: 'fa-clock',
                text: 'Active'
            }
        };

        const badge = badges[status] || badges['active'];
        return `
            <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full ${badge.class}">
                <i class="fas ${badge.icon} mr-1"></i>${badge.text}
            </span>
        `;
    }

    // Function to handle clock actions
    async function handleClockAction(action) {
        if (!selectedEmployeeId) {
            showToast('Please select an employee first', 'error');
            return;
        }

        // Convert action to route
        const routes = {
            'clock_in': '/admin/clock/in',
            'clock_out': '/admin/clock/out',
            'start_break': '/admin/clock/break/start',
            'end_break': '/admin/clock/break/end'
        };

        const route = routes[action];
        if (!route) {
            console.error(`Invalid action: ${action}`);
            return;
        }

        // Convert action to button ID format (e.g., 'clock_in' -> 'clockInBtn')
        const buttonId = action.split('_').map((word, index) => 
            index === 0 ? word : word.charAt(0).toUpperCase() + word.slice(1)
        ).join('') + 'Btn';

        const button = document.getElementById(buttonId);
        if (!button) {
            console.error(`Button ${buttonId} not found`);
            return;
        }

        const originalText = button.innerHTML;
        
        try {
            button.disabled = true;
            button.innerHTML = `
                <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Processing...
            `;

            const response = await fetch(route, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    employee_id: selectedEmployeeId
                })
            });

            const data = await response.json();

            if (response.ok) {
                showToast(data.message || 'Action completed successfully');
                await refreshTimeLogsTable();
            } else {
                showToast(data.message || 'An error occurred', 'error');
            }
        } catch (error) {
            showToast('An error occurred while processing your request', 'error');
            console.error('Error:', error);
        } finally {
            button.disabled = false;
            button.innerHTML = originalText;
        }
    }

    // Employee search functionality
    const employeeSearch = document.getElementById('employee_search');
    const searchResults = document.getElementById('search_results');
    let searchTimeout;

    if (employeeSearch) {
        employeeSearch.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            const query = this.value.trim();
            
            if (query.length < 2) {
                if (searchResults) {
                    searchResults.classList.add('hidden');
                }
                return;
            }

            // Show loading state
            if (searchResults) {
                searchResults.innerHTML = `
                    <div class="p-4 text-center text-gray-500">
                        <svg class="animate-spin h-5 w-5 mx-auto mb-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <p>Searching...</p>
                    </div>
                `;
                searchResults.classList.remove('hidden');
            }

            searchTimeout = setTimeout(async () => {
                try {
                    // Get branch from URL parameter
                    const urlParams = new URLSearchParams(window.location.search);
                    const branch = urlParams.get('branch');
                    
                    const response = await fetch(`/admin/clock/employees/search?query=${encodeURIComponent(query)}${branch ? '&branch=' + branch : ''}`, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        }
                    });
                    
                    const data = await response.json();
                    
                    if (searchResults) {
                        if (data && data.length > 0) {
                            searchResults.innerHTML = data.map(employee => `
                                <div class="p-2 hover:bg-gray-100 cursor-pointer border-b border-gray-100 last:border-0" 
                                     data-id="${employee.id}" 
                                     data-name="${employee.name}"
                                     data-email="${employee.email}">
                                    <div class="font-medium text-gray-900">${employee.name}</div>
                                    <div class="text-sm text-gray-500">
                                        <span><i class="fas fa-envelope mr-1"></i>${employee.email}</span>
                                    </div>
                                </div>
                            `).join('');
                        } else {
                            searchResults.innerHTML = `
                                <div class="p-4 text-center text-gray-500">
                                    <i class="fas fa-search mb-2 text-2xl"></i>
                                    <p>No employees found</p>
                                    <p class="text-sm mt-1">Try searching by name or email</p>
                                </div>
                            `;
                        }
                        searchResults.classList.remove('hidden');
                    }
                } catch (error) {
                    console.error('Error searching employees:', error);
                    if (searchResults) {
                        searchResults.innerHTML = `
                            <div class="p-4 text-center text-red-500">
                                <i class="fas fa-exclamation-circle mb-2 text-2xl"></i>
                                <p>Error searching employees</p>
                                <p class="text-sm mt-1">Please try again</p>
                            </div>
                        `;
                        searchResults.classList.remove('hidden');
                    }
                }
            }, 300);
        });
    }

    // Handle employee selection
    if (searchResults) {
        searchResults.addEventListener('click', function(e) {
            const item = e.target.closest('[data-id]');
            if (item && employeeSearch) {
                selectedEmployeeId = item.dataset.id;
                selectedEmployeeName = item.dataset.name;
                selectedEmployeeEmail = item.dataset.email;
                
                // Update the search input with a formatted display
                employeeSearch.value = `${selectedEmployeeName}`;
                searchResults.classList.add('hidden');

                // Show a success message
                showToast(`Selected employee: ${selectedEmployeeName}`);
            }
        });
    }

    // Handle date selector form submission
    const dateSelectorForm = document.getElementById('dateSelectorForm');
    if (dateSelectorForm) {
        dateSelectorForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const dateInput = document.getElementById('date');
            if (dateInput) {
                const date = dateInput.value;
                window.location.href = `/admin/clock?date=${date}`;
            }
        });
    }

    // Initial table refresh - only if we're on the clock page
    if (document.getElementById('timeLogsTableBody')) {
        refreshTimeLogsTable();
    }
}); 