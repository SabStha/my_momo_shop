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
        $.get(`/wallet/search?term=${encodeURIComponent(query)}`, function(data) {
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
                showToast('success', response.message);
                closeModal();
                form[0].reset();
                // Refresh the table or update the specific row
                location.reload();
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

// Toast notification function
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