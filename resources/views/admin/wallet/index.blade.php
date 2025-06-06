@extends('layouts.admin')

@section('title', 'Wallet Management')

@push('styles')
<!-- DataTables CSS -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/datatables/1.10.21/css/dataTables.bootstrap5.min.css">
<style>
.modal-header {
    background-color: #f8f9fa;
    border-bottom: 1px solid #dee2e6;
}

.modal-footer {
    background-color: #f8f9fa;
    border-top: 1px solid #dee2e6;
}

.balance-badge {
    font-size: 0.9rem;
    padding: 0.5rem 0.75rem;
}

.action-buttons .btn {
    padding: 0.25rem 0.5rem;
    font-size: 0.875rem;
}

#searchResults {
    max-height: 200px;
    overflow-y: auto;
    border: 1px solid #dee2e6;
    border-radius: 0.25rem;
    margin-top: 0.5rem;
}

#searchResults .list-group-item {
    cursor: pointer;
    padding: 0.5rem 1rem;
}

#searchResults .list-group-item:hover {
    background-color: #f8f9fa;
}

.search-loading {
    text-align: center;
    padding: 1rem;
    color: #6c757d;
}

.no-results {
    text-align: center;
    padding: 1rem;
    color: #6c757d;
}

.toast-container {
    position: fixed;
    top: 20px;
    right: 20px;
    z-index: 1060;
}

.toast {
    min-width: 300px;
}
</style>
@endpush

@section('content')
<!-- Toast Container -->
<div class="toast-container">
    <div class="toast align-items-center text-white bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true" id="successToast">
        <div class="d-flex">
            <div class="toast-body">
                <i class="fas fa-check-circle me-2"></i>
                <span id="successMessage">Operation completed successfully!</span>
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    </div>
    <div class="toast align-items-center text-white bg-danger border-0" role="alert" aria-live="assertive" aria-atomic="true" id="errorToast">
        <div class="d-flex">
            <div class="toast-body">
                <i class="fas fa-exclamation-circle me-2"></i>
                <span id="errorMessage">An error occurred!</span>
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    </div>
</div>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">💳 User Wallets</h5>
                        <div>
                            <a href="{{ route('admin.wallet.manage') }}" class="btn btn-info me-2">
                                <i class="fas fa-qrcode"></i> QR Top-Up
                            </a>
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#topUpModal">
                                <i class="fas fa-plus"></i> Top Up Wallet
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover" id="usersTable">
                            <thead>
                                <tr>
                                    <th>User</th>
                                    <th>Email</th>
                                    <th>Balance</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($users as $user)
                                <tr data-user-id="{{ $user->id }}">
                                    <td>{{ $user->name }}</td>
                                    <td>{{ $user->email }}</td>
                                    <td>
                                        <span class="badge bg-success balance-badge">
                                            ${{ number_format($user->wallet->balance ?? 0, 2) }}
                                        </span>
                                    </td>
                                    <td class="action-buttons">
                                        <button type="button" 
                                                class="btn btn-sm btn-primary" 
                                                onclick="topUpUser({{ $user->id }}, '{{ $user->name }}')">
                                            <i class="fas fa-plus"></i> Top Up
                                        </button>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Top Up Modal -->
<div class="modal fade" id="topUpModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admin.wallet.topup') }}" method="POST" id="topUpForm">
                @csrf
                <input type="hidden" name="user_id" id="topUpUserId">
                
                <div class="modal-header">
                    <h5 class="modal-title">Top Up Wallet</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Search User</label>
                        <input type="text" 
                               class="form-control" 
                               id="userSearch" 
                               placeholder="Type to search users..."
                               autocomplete="off">
                        <div id="searchResults" class="list-group" style="display: none;"></div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Selected User</label>
                        <input type="text" class="form-control" id="topUpUserName" readonly>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Amount</label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" 
                                   step="0.01" 
                                   min="0.01" 
                                   name="amount" 
                                   class="form-control" 
                                   required>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" 
                                  class="form-control" 
                                  rows="2" 
                                  placeholder="Optional description for this transaction"></textarea>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="submitTopUp" disabled>
                        <i class="fas fa-plus me-1"></i> Top Up
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<!-- DataTables JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/datatables/1.10.21/js/jquery.dataTables.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/datatables/1.10.21/js/dataTables.bootstrap5.min.js"></script>

<script>
// Setup CSRF token for all AJAX requests
$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
});

document.addEventListener('DOMContentLoaded', function() {
    // Initialize DataTable without search
    $('#usersTable').DataTable({
        order: [[0, 'asc']],
        pageLength: 10,
        searching: false,
        language: {
            search: "_INPUT_",
            searchPlaceholder: "🔍 Search users..."
        }
    });

    // Initialize toasts
    const successToast = new bootstrap.Toast(document.getElementById('successToast'));
    const errorToast = new bootstrap.Toast(document.getElementById('errorToast'));

    // Function to show success message
    function showSuccess(message) {
        $('#successMessage').text(message);
        successToast.show();
    }

    // Function to show error message
    function showError(message) {
        $('#errorMessage').text(message);
        errorToast.show();
    }

    // User search functionality
    let searchTimeout;
    const searchResults = $('#searchResults');
    const userSearch = $('#userSearch');
    const topUpUserId = $('#topUpUserId');
    const topUpUserName = $('#topUpUserName');
    const submitTopUp = $('#submitTopUp');

    userSearch.on('input', function() {
        clearTimeout(searchTimeout);
        const query = $(this).val().trim();

        if (query.length < 1) {
            searchResults.hide();
            return;
        }

        searchResults.html('<div class="search-loading">Searching...</div>').show();

        searchTimeout = setTimeout(() => {
            $.ajax({
                url: '{{ route("admin.wallet.search") }}',
                method: 'GET',
                data: { query: query },
                success: function(response) {
                    searchResults.empty();
                    
                    if (response.success && response.users && response.users.length > 0) {
                        response.users.forEach(user => {
                            const balance = user.wallet ? 
                                `$${parseFloat(user.wallet.balance).toFixed(2)}` : 
                                'No Wallet';
                            
                            searchResults.append(`
                                <a href="#" class="list-group-item list-group-item-action" 
                                   data-user-id="${user.id}" 
                                   data-user-name="${user.name}">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <strong>${user.name}</strong>
                                            <br>
                                            <small class="text-muted">${user.email}</small>
                                        </div>
                                        <span class="badge bg-success">${balance}</span>
                                    </div>
                                </a>
                            `);
                        });
                    } else {
                        searchResults.html('<div class="no-results">No users found</div>');
                    }
                },
                error: function(xhr) {
                    console.error('Search error:', xhr);
                    searchResults.html('<div class="no-results">Error searching users. Please try again.</div>');
                }
            });
        }, 100);
    });

    // Handle user selection
    searchResults.on('click', 'a', function(e) {
        e.preventDefault();
        const userId = $(this).data('user-id');
        const userName = $(this).data('user-name');

        topUpUserId.val(userId);
        topUpUserName.val(userName);
        userSearch.val(userName);
        searchResults.hide();
        submitTopUp.prop('disabled', false);
    });

    // Clear search results when clicking outside
    $(document).on('click', function(e) {
        if (!$(e.target).closest('#userSearch, #searchResults').length) {
            searchResults.hide();
        }
    });

    // Handle form submission
    $('#topUpForm').on('submit', function(e) {
        e.preventDefault();
        const form = $(this);
        const submitBtn = form.find('button[type="submit"]');
        
        submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i> Processing...');
        
        $.ajax({
            url: form.attr('action'),
            method: 'POST',
            data: form.serialize(),
            success: function(response) {
                $('#topUpModal').modal('hide');
                showSuccess('Wallet topped up successfully!');
                
                // Reload the page after a short delay to show the success message
                setTimeout(function() {
                    window.location.reload();
                }, 1000);
            },
            error: function(xhr) {
                showError(xhr.responseJSON?.message || 'Failed to top up wallet. Please try again.');
                submitBtn.prop('disabled', false).html('<i class="fas fa-plus me-1"></i> Top Up');
            }
        });
    });

    // Reset form when modal is closed
    $('#topUpModal').on('hidden.bs.modal', function() {
        $('#topUpForm')[0].reset();
        topUpUserId.val('');
        topUpUserName.val('');
        userSearch.val('');
        submitTopUp.prop('disabled', true).html('<i class="fas fa-plus me-1"></i> Top Up');
        searchResults.hide();
    });
});

// Global topUpUser function to fix ReferenceError
function topUpUser(userId, userName) {
    $('#topUpUserId').val(userId);
    $('#topUpUserName').val(userName);
    $('#userSearch').val(userName);
    $('#submitTopUp').prop('disabled', false);
    $('#topUpModal').modal('show');
}
</script>
@endpush
