// Import jQuery
import jQuery from 'jquery';

// Make jQuery available globally
window.$ = window.jQuery = jQuery;

// Modal management
let lastFocusedElement = null;

function openModal() {
    const modal = document.getElementById('topUpModal');
    lastFocusedElement = document.activeElement;
    modal.classList.remove('hidden');
    document.body.style.overflow = 'hidden';
    // Focus the first input
    document.getElementById('userSearch').focus();
}

function closeModal() {
    const modal = document.getElementById('topUpModal');
    modal.classList.add('hidden');
    document.body.style.overflow = '';
    // Restore focus
    if (lastFocusedElement) {
        lastFocusedElement.focus();
    }
}

// Make functions globally available
window.topUpUser = function(userId, userName) {
    $('#topUpUserId').val(userId);
    $('#topUpUserName').val(userName);
    openModal();
};

window.closeModal = closeModal;

// Handle escape key to close modal
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape' && !document.getElementById('topUpModal').classList.contains('hidden')) {
        closeModal();
    }
});

// User Search Functionality
let searchTimeout;
$('#userSearch').on('input', function() {
    clearTimeout(searchTimeout);
    const query = $(this).val().trim();
    
    if (query.length < 2) {
        $('#searchResults').addClass('hidden');
        return;
    }

    // Show loading state
    $('#searchResults').html(`
        <div class="p-4 text-center text-gray-500">
            <svg class="animate-spin h-5 w-5 mx-auto mb-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <p>Searching...</p>
        </div>
    `).removeClass('hidden');

    searchTimeout = setTimeout(() => {
        $.get(`/amako-credits/search?term=${encodeURIComponent(query)}`, function(data) {
            if (data.success && data.users.length > 0) {
                const results = data.users.map(user => `
                    <div class="p-2 hover:bg-gray-100 cursor-pointer border-b border-gray-100 last:border-0" 
                         data-id="${user.id}" 
                         data-name="${user.name}"
                         role="option"
                         tabindex="0">
                        <div class="flex justify-between items-center">
                            <div class="flex-1">
                                <div class="font-medium text-gray-900">${user.name}</div>
                                <div class="text-sm text-gray-500">
                                    <div class="flex items-center">
                                        <i class="fas fa-envelope w-4 text-gray-400"></i>
                                        <span class="ml-2">${user.email}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="ml-4 flex flex-col items-end">
                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800 mb-2">
                                    Rs ${parseFloat(user.wallet.balance).toFixed(2)}
                                </span>
                                <span class="text-xs text-gray-500">Click to select</span>
                            </div>
                        </div>
                    </div>
                `).join('');
                $('#searchResults').html(results).removeClass('hidden');
            } else {
                $('#searchResults').html(`
                    <div class="p-4 text-center text-gray-500">
                        <i class="fas fa-search mb-2 text-2xl"></i>
                        <p>No users found</p>
                        <p class="text-sm mt-1">Try searching by name or email</p>
                    </div>
                `).removeClass('hidden');
            }
        }).fail(function(xhr) {
            const errorMessage = xhr.responseJSON?.message || 'An error occurred while searching';
            $('#searchResults').html(`
                <div class="p-4 text-center text-red-500">
                    <i class="fas fa-exclamation-circle mb-2 text-2xl"></i>
                    <p>Error searching users</p>
                    <p class="text-sm mt-1">${errorMessage}</p>
                </div>
            `).removeClass('hidden');
        });
    }, 300);
});

// Handle user selection from search results
$('#searchResults').on('click', '[data-id]', function(e) {
    e.preventDefault();
    const userId = $(this).data('id');
    const userName = $(this).data('name');
    $('#topUpUserId').val(userId);
    $('#topUpUserName').val(userName);
    $('#userSearch').val('');
    $('#searchResults').addClass('hidden');
    // Focus the amount input
    document.getElementById('amount').focus();
});

// Handle keyboard navigation in search results
$('#searchResults').on('keydown', '[data-id]', function(e) {
    if (e.key === 'Enter' || e.key === ' ') {
        e.preventDefault();
        $(this).click();
    }
});

// Handle form submission
$('#topUpForm').on('submit', function(e) {
    e.preventDefault();
    const form = $(this);
    const submitBtn = form.find('button[type="submit"]');
    const originalBtnText = submitBtn.html();

    submitBtn.prop('disabled', true)
            .html('<svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white inline-block" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>Processing...');

    $.ajax({
        url: form.attr('action'),
        method: 'POST',
        data: form.serialize(),
        success: function(response) {
            if (response.success) {
                // Close the top-up modal first
                closeModal();
                form[0].reset();
                
                // Show success modal with smooth animation
                setTimeout(() => {
                    showSuccessModal(response.message.replace('wallet', 'credits').replace('Wallet', 'Credits'));
                }, 300); // Wait for modal close animation to complete
                
                // Don't reload the page - just update the UI smoothly
                setTimeout(() => {
                    // Update the page content without full reload
                    updateWalletDisplay();
                }, 1500);
            } else {
                showToast('error', response.message);
            }
        },
        error: function(xhr) {
            const message = xhr.responseJSON?.message || 'An error occurred while processing your request.';
            showToast('error', message);
        },
        complete: function() {
            submitBtn.prop('disabled', false).html(originalBtnText);
        }
    });
});

// Close search results when clicking outside
$(document).on('click', function(e) {
    if (!$(e.target).closest('#userSearch, #searchResults').length) {
        $('#searchResults').addClass('hidden');
    }
});

// Success Modal with Sound
function showSuccessModal(message) {
    // Remove any existing success modal first
    const existingModal = document.querySelector('#successModalOverlay');
    if (existingModal) {
        existingModal.remove();
    }
    
    // Play success sound
    playSuccessSound();
    
    // Create modal overlay with unique ID
    const modalOverlay = document.createElement('div');
    modalOverlay.id = 'successModalOverlay';
    modalOverlay.className = 'fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50';
    modalOverlay.style.opacity = '0';
    modalOverlay.style.transition = 'opacity 0.3s ease-in-out';
    
    modalOverlay.innerHTML = `
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full mx-4 transform transition-all duration-300 scale-75 opacity-0" id="successModalContent">
            <div class="p-6 text-center">
                <!-- Success Icon -->
                <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-green-100 mb-4 animate-pulse">
                    <svg class="h-8 w-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                </div>
                
                <!-- Success Message -->
                <h3 class="text-lg font-medium text-gray-900 mb-2">🎉 Success!</h3>
                <p class="text-sm text-gray-500 mb-6">${message}</p>
                
                <!-- Action Button -->
                <button onclick="closeSuccessModal()" class="w-full px-4 py-2 text-sm font-medium text-white bg-green-600 border border-transparent rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors duration-200">
                    <i class="fas fa-check mr-2"></i>
                    Great!
                </button>
            </div>
        </div>
    `;
    
    document.body.appendChild(modalOverlay);
    
    // Smooth fade-in for overlay
    requestAnimationFrame(() => {
        modalOverlay.style.opacity = '1';
    });
    
    // Animate modal content in
    requestAnimationFrame(() => {
        const modalContent = document.getElementById('successModalContent');
        if (modalContent) {
            modalContent.classList.remove('scale-75', 'opacity-0');
            modalContent.classList.add('scale-100', 'opacity-100');
        }
    });
    
    // Auto-close after 4 seconds (longer for better UX)
    window.successModalTimer = setTimeout(() => {
        closeSuccessModal();
    }, 4000);
}

// Close success modal
function closeSuccessModal() {
    // Clear the auto-close timer
    if (window.successModalTimer) {
        clearTimeout(window.successModalTimer);
        window.successModalTimer = null;
    }
    
    const modalOverlay = document.querySelector('#successModalOverlay');
    if (modalOverlay) {
        const modalContent = modalOverlay.querySelector('#successModalContent');
        if (modalContent) {
            // Smooth scale down animation
            modalContent.classList.remove('scale-100', 'opacity-100');
            modalContent.classList.add('scale-75', 'opacity-0');
        }
        
        // Fade out overlay
        modalOverlay.style.opacity = '0';
        
        // Remove modal after animation completes
        setTimeout(() => {
            modalOverlay.remove();
        }, 300);
    }
}

// Play success sound
function playSuccessSound() {
    try {
        // Create audio context
        const audioContext = new (window.AudioContext || window.webkitAudioContext)();
        
        // Create a pleasant success sound (two ascending notes)
        const playNote = (frequency, startTime, duration) => {
            const oscillator = audioContext.createOscillator();
            const gainNode = audioContext.createGain();
            
            oscillator.connect(gainNode);
            gainNode.connect(audioContext.destination);
            
            oscillator.frequency.setValueAtTime(frequency, startTime);
            oscillator.type = 'sine';
            
            // Envelope for smooth sound
            gainNode.gain.setValueAtTime(0, startTime);
            gainNode.gain.linearRampToValueAtTime(0.3, startTime + 0.01);
            gainNode.gain.exponentialRampToValueAtTime(0.01, startTime + duration);
            
            oscillator.start(startTime);
            oscillator.stop(startTime + duration);
        };
        
        const now = audioContext.currentTime;
        playNote(523.25, now, 0.2);      // C5
        playNote(659.25, now + 0.15, 0.3); // E5
        
    } catch (error) {
        console.log('Could not play success sound:', error);
    }
}

// Toast notification function (for errors)
function showToast(type, message) {
    const toast = document.createElement('div');
    toast.className = `fixed top-4 right-4 px-6 py-3 rounded-lg text-white ${
        type === 'success' ? 'bg-green-500' : 'bg-red-500'
    } shadow-lg z-50 transform transition-all duration-300 ease-in-out translate-y-0 opacity-100`;
    toast.setAttribute('role', 'alert');
    toast.setAttribute('aria-live', 'assertive');
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

// Update credits display without page reload
function updateWalletDisplay() {
    // This function can be used to update specific parts of the credits page
    // For now, we'll just refresh the page data without showing additional notifications
    // since the success modal already shows the success message
    
    // You can add specific UI updates here in the future
    // For example: update balance display, refresh transaction list, etc.
    console.log('Credits display updated');
}

// Transaction History Functions
function showTransactionHistory() {
    const modal = document.getElementById('transactionHistoryModal');
    if (modal) {
        modal.classList.remove('hidden');
        
        // Set default date range (last 30 days)
        const today = new Date();
        const thirtyDaysAgo = new Date(today.getTime() - (30 * 24 * 60 * 60 * 1000));
        
        document.getElementById('historyToDate').value = today.toISOString().split('T')[0];
        document.getElementById('historyFromDate').value = thirtyDaysAgo.toISOString().split('T')[0];
        
        // Load initial transactions
        filterTransactionHistory();
    }
}

function closeTransactionHistoryModal() {
    const modal = document.getElementById('transactionHistoryModal');
    if (modal) {
        modal.classList.add('hidden');
    }
}

function filterTransactionHistory() {
    const fromDate = document.getElementById('historyFromDate').value;
    const toDate = document.getElementById('historyToDate').value;
    const loadingState = document.getElementById('historyLoadingState');
    const tbody = document.getElementById('transactionHistoryBody');
    
    if (!fromDate || !toDate) {
        alert('Please select both from and to dates.');
        return;
    }
    
    // Show loading state
    loadingState.classList.remove('hidden');
    tbody.innerHTML = '';
    
    // Make AJAX request to get transactions
    $.ajax({
        url: '/amako-credits/api/transactions',
        method: 'GET',
        data: {
            from_date: fromDate,
            to_date: toDate,
            user_id: window.currentUserId || null
        },
        success: function(response) {
            loadingState.classList.add('hidden');
            
            if (response.success && response.transactions.length > 0) {
                tbody.innerHTML = response.transactions.map(transaction => `
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            <div>
                                <div class="font-medium">${new Date(transaction.created_at).toLocaleDateString()}</div>
                                <div class="text-gray-500">${new Date(transaction.created_at).toLocaleTimeString()}</div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full ${transaction.type === 'credit' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'}">
                                ${transaction.type.charAt(0).toUpperCase() + transaction.type.slice(1)}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium ${transaction.type === 'credit' ? 'text-green-600' : 'text-red-600'}">
                            ${transaction.type === 'credit' ? '+' : '-'}Rs ${parseFloat(transaction.credits_amount).toFixed(2)}
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500">
                            ${transaction.description || 'N/A'}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 font-mono">
                            ${transaction.reference_number || 'N/A'}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                ${transaction.status || 'Completed'}
                            </span>
                        </td>
                    </tr>
                `).join('');
            } else {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                            No transactions found for the selected date range.
                        </td>
                    </tr>
                `;
            }
        },
        error: function(xhr) {
            loadingState.classList.add('hidden');
            console.error('Error loading transactions:', xhr);
            tbody.innerHTML = `
                <tr>
                    <td colspan="6" class="px-6 py-4 text-center text-red-500">
                        Error loading transactions. Please try again.
                    </td>
                </tr>
            `;
        }
    });
}

// Make functions globally available
window.showSuccessModal = showSuccessModal;
window.closeSuccessModal = closeSuccessModal;
window.updateWalletDisplay = updateWalletDisplay;
window.showTransactionHistory = showTransactionHistory;
window.closeTransactionHistoryModal = closeTransactionHistoryModal;
window.filterTransactionHistory = filterTransactionHistory; 