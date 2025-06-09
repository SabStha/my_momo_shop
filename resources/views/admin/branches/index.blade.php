@extends('layouts.admin')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Branches</h1>
        <button onclick="showCreateModal()" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg">
            Add Branch
        </button>
    </div>

    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
            <span class="block sm:inline">{{ session('success') }}</span>
        </div>
    @endif

    @if(session('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
            <span class="block sm:inline">{{ session('error') }}</span>
        </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($branches as $branch)
            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex justify-between items-start mb-4">
                    <div>
                        <h2 class="text-xl font-semibold text-gray-800">{{ $branch->name }}</h2>
                        <p class="text-gray-600">Code: {{ $branch->code }}</p>
                    </div>
                    <span class="px-2 py-1 text-sm rounded-full {{ $branch->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                        {{ $branch->is_active ? 'Active' : 'Inactive' }}
                    </span>
                </div>
                
                <div class="space-y-2 mb-4">
                    <p class="text-gray-600"><span class="font-medium">Address:</span> {{ $branch->address }}</p>
                    <p class="text-gray-600"><span class="font-medium">Contact:</span> {{ $branch->contact_person }}</p>
                    <p class="text-gray-600"><span class="font-medium">Email:</span> {{ $branch->email }}</p>
                    <p class="text-gray-600"><span class="font-medium">Phone:</span> {{ $branch->phone }}</p>
                </div>

                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div class="text-center p-2 bg-gray-50 rounded">
                        <p class="text-sm text-gray-600">Products</p>
                        <p class="text-lg font-semibold">{{ $branch->products_count }}</p>
                    </div>
                    <div class="text-center p-2 bg-gray-50 rounded">
                        <p class="text-sm text-gray-600">Orders</p>
                        <p class="text-lg font-semibold">{{ $branch->orders_count }}</p>
                    </div>
                    <div class="text-center p-2 bg-gray-50 rounded">
                        <p class="text-sm text-gray-600">Employees</p>
                        <p class="text-lg font-semibold">{{ $branch->employees_count }}</p>
                    </div>
                    <div class="text-center p-2 bg-gray-50 rounded">
                        <p class="text-sm text-gray-600">Tables</p>
                        <p class="text-lg font-semibold">{{ $branch->tables_count }}</p>
                    </div>
                </div>

                <div class="flex justify-between items-center">
                    <a href="{{ route('admin.dashboard', ['branch' => $branch->id]) }}" class="text-blue-500 hover:text-blue-600">
                        View Dashboard
                    </a>
                    <div class="space-x-2">
                        @if($branch->id !== session('current_branch_id'))
                            <button onclick="showBranchSwitchModal({{ $branch->id }}, '{{ addslashes($branch->name) }}', {{ $branch->requires_password ? 'true' : 'false' }})"
                                class="text-indigo-600 hover:text-indigo-900">Switch to this branch</button>
                        @else
                            <span class="text-gray-500">Current branch</span>
                        @endif
                        <button onclick="showEditModal({{ $branch->id }})" class="text-gray-600 hover:text-gray-800">
                            Edit
                        </button>
                        <button onclick="showDeleteModal({{ $branch->id }})" class="text-red-500 hover:text-red-600">
                            Delete
                        </button>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full text-center py-8">
                <p class="text-gray-500">No branches found.</p>
            </div>
        @endforelse
    </div>
</div>

<!-- Create Branch Modal -->
<div id="createModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Create New Branch</h3>
            <form id="createForm" onsubmit="handleCreate(event)">
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="name">
                        Name
                    </label>
                    <input type="text" name="name" id="name" required
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                </div>
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="code">
                        Code
                    </label>
                    <input type="text" name="code" id="code" required
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                </div>
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="address">
                        Address
                    </label>
                    <textarea name="address" id="address" required
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"></textarea>
                </div>
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="contact_person">
                        Contact Person
                    </label>
                    <input type="text" name="contact_person" id="contact_person" required
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                </div>
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="email">
                        Email
                    </label>
                    <input type="email" name="email" id="email" required
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                </div>
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="phone">
                        Phone
                    </label>
                    <input type="text" name="phone" id="phone" required
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                </div>
                <div class="mb-4">
                    <label class="flex items-center">
                        <input type="checkbox" name="is_active" value="1" checked
                            class="form-checkbox h-5 w-5 text-blue-600">
                        <span class="ml-2 text-gray-700">Active</span>
                    </label>
                </div>
                <div class="mb-4">
                    <label class="flex items-center">
                        <input type="checkbox" name="is_main" value="1"
                            class="form-checkbox h-5 w-5 text-blue-600">
                        <span class="ml-2 text-gray-700">Main Branch</span>
                    </label>
                </div>
                <div class="flex justify-end space-x-2">
                    <button type="button" onclick="hideCreateModal()"
                        class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded">
                        Cancel
                    </button>
                    <button type="submit"
                        class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded">
                        Create
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Branch Modal -->
<div id="editModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Edit Branch</h3>
            <form id="editForm" onsubmit="handleEdit(event)">
                <input type="hidden" name="branch_id" id="edit_branch_id">
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="edit_name">
                        Name
                    </label>
                    <input type="text" name="name" id="edit_name" required
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                </div>
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="edit_code">
                        Code
                    </label>
                    <input type="text" name="code" id="edit_code" required
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                </div>
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="edit_address">
                        Address
                    </label>
                    <textarea name="address" id="edit_address" required
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"></textarea>
                </div>
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="edit_contact_person">
                        Contact Person
                    </label>
                    <input type="text" name="contact_person" id="edit_contact_person" required
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                </div>
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="edit_email">
                        Email
                    </label>
                    <input type="email" name="email" id="edit_email" required
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                </div>
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="edit_phone">
                        Phone
                    </label>
                    <input type="text" name="phone" id="edit_phone" required
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                </div>
                <div class="mb-4">
                    <label class="flex items-center">
                        <input type="checkbox" name="is_active" id="edit_is_active" value="1"
                            class="form-checkbox h-5 w-5 text-blue-600">
                        <span class="ml-2 text-gray-700">Active</span>
                    </label>
                </div>
                <div class="mb-4">
                    <label class="flex items-center">
                        <input type="checkbox" name="requires_password" id="edit_requires_password" value="1"
                            class="form-checkbox h-5 w-5 text-blue-600" onchange="togglePasswordFields()">
                        <span class="ml-2 text-gray-700">Require Password to Access</span>
                    </label>
                </div>
                <div id="passwordFields" class="hidden space-y-4">
                    <div>
                        <label for="edit_access_password" class="block text-sm font-medium text-gray-700">Access Password</label>
                        <input type="password" name="access_password" id="edit_access_password" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    </div>
                    <div>
                        <label for="edit_confirm_password" class="block text-sm font-medium text-gray-700">Confirm Password</label>
                        <input type="password" name="confirm_password" id="edit_confirm_password" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    </div>
                    <div>
                        <button type="button" onclick="resetBranchPassword()" class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                            Reset Password
                        </button>
                    </div>
                </div>
                <div class="flex justify-end space-x-2">
                    <button type="button" onclick="hideEditModal()"
                        class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded">
                        Cancel
                    </button>
                    <button type="submit"
                        class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded">
                        Update
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div id="deleteModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Delete Branch</h3>
            <p class="text-gray-600 mb-4">Are you sure you want to delete this branch? This action cannot be undone.</p>
            <input type="hidden" id="delete_branch_id">
            <div class="flex justify-end space-x-2">
                <button onclick="hideDeleteModal()"
                    class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded">
                    Cancel
                </button>
                <button onclick="handleDelete()"
                    class="bg-red-500 hover:bg-red-600 text-white font-bold py-2 px-4 rounded">
                    Delete
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Password Verification Modal -->
<div id="passwordModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Enter Branch Password</h3>
            <form id="passwordForm" onsubmit="handlePasswordVerification(event)">
                <input type="hidden" id="password_branch_id">
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="branch_password">
                        Password
                    </label>
                    <input type="password" id="branch_password" required
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                </div>
                <div class="flex justify-end space-x-2">
                    <button type="button" onclick="hidePasswordModal()"
                        class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded">
                        Cancel
                    </button>
                    <button type="submit"
                        class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded">
                        Verify
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function showCreateModal() {
    document.getElementById('createModal').classList.remove('hidden');
}

function hideCreateModal() {
    document.getElementById('createModal').classList.add('hidden');
}

function showEditModal(branchId) {
    fetch(`/admin/branches/${branchId}`)
        .then(response => {
            if (!response.ok) {
                return response.json().then(err => {
                    throw new Error(err.message || 'Failed to load branch details');
                });
            }
            return response.json();
        })
        .then(data => {
            console.log('Branch data:', data); // Debug log
            
            // Populate all form fields with the branch data
            const form = document.getElementById('editForm');
            form.querySelector('[name="branch_id"]').value = data.id;
            form.querySelector('[name="name"]').value = data.name;
            form.querySelector('[name="code"]').value = data.code;
            form.querySelector('[name="address"]').value = data.address;
            form.querySelector('[name="contact_person"]').value = data.contact_person;
            form.querySelector('[name="email"]').value = data.email;
            form.querySelector('[name="phone"]').value = data.phone;
            form.querySelector('[name="is_active"]').checked = data.is_active;
            form.querySelector('[name="requires_password"]').checked = data.requires_password;
            
            // Clear password fields for security
            form.querySelector('[name="access_password"]').value = '';
            form.querySelector('[name="confirm_password"]').value = '';
            
            // Toggle password fields based on requires_password
            togglePasswordFields();
            
            document.getElementById('editModal').classList.remove('hidden');
        })
        .catch(error => {
            console.error('Error:', error);
            alert(error.message || 'Failed to load branch details. Please try again.');
        });
}

function hideEditModal() {
    document.getElementById('editModal').classList.add('hidden');
    document.getElementById('editForm').reset();
}

function showDeleteModal(branchId) {
    document.getElementById('delete_branch_id').value = branchId;
    document.getElementById('deleteModal').classList.remove('hidden');
}

function hideDeleteModal() {
    document.getElementById('deleteModal').classList.add('hidden');
}

function handleCreate(event) {
    event.preventDefault();
    const formData = new FormData(event.target);
    const data = Object.fromEntries(formData.entries());
    data.is_active = formData.get('is_active') === '1';
    data.is_main = formData.get('is_main') === '1';

    fetch('/admin/branches', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        },
        body: JSON.stringify(data)
    })
    .then(response => {
        // If the response is a redirect, reload the page
        if (response.redirected) {
            window.location.href = response.url;
            return;
        }
        return response.json();
    })
    .then(data => {
        if (data && data.success) {
            window.location.reload();
        } else if (data && data.message) {
            alert(data.message);
        } else {
            // If we get here, the branch was created but the response wasn't in the expected format
            window.location.reload();
        }
    })
    .catch(error => {
        // If we get an error but the branch was created, just reload the page
        console.log('Operation completed, reloading page...');
        window.location.reload();
    });
}

function handleEdit(event) {
    event.preventDefault();
    
    const form = event.target;
    const formData = new FormData(form);
    const data = Object.fromEntries(formData.entries());
    const branchId = data.branch_id;
    
    // Remove branch_id from data as it's not needed in the request body
    delete data.branch_id;
    
    // Validate passwords if required
    if (data.requires_password === '1') {
        if (data.access_password !== data.confirm_password) {
            alert('Passwords do not match');
            return;
        }
        if (data.access_password && data.access_password.length < 6) {
            alert('Password must be at least 6 characters long');
            return;
        }
    }
    
    // Remove confirm_password as it's not needed in the request
    delete data.confirm_password;
    
    // Convert checkbox values to boolean
    data.is_active = data.is_active === '1';
    data.requires_password = data.requires_password === '1';
    
    // Show loading state
    const submitButton = form.querySelector('button[type="submit"]');
    const originalText = submitButton.innerHTML;
    submitButton.disabled = true;
    submitButton.innerHTML = 'Saving...';
    
    fetch(`/admin/branches/${branchId}`, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify(data)
    })
    .then(response => {
        if (!response.ok) {
            return response.json().then(err => {
                if (err.errors) {
                    // Handle validation errors
                    const errorMessages = Object.values(err.errors).flat();
                    throw new Error(errorMessages.join('\n'));
                }
                throw new Error(err.message || 'Failed to update branch');
            });
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            // Show success message and close modal
            hideEditModal();
            
            // Show success message in a more user-friendly way
            const successMessage = document.createElement('div');
            successMessage.className = 'fixed top-4 right-4 bg-green-500 text-white px-6 py-3 rounded shadow-lg z-50';
            successMessage.textContent = 'Branch updated successfully';
            document.body.appendChild(successMessage);
            
            // Remove success message after 3 seconds
            setTimeout(() => {
                successMessage.remove();
                // Reload the page after the message is gone
                window.location.reload();
            }, 3000);
        } else {
            alert(data.message || 'Failed to update branch');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert(error.message || 'Failed to update branch. Please try again.');
    })
    .finally(() => {
        // Reset button state
        submitButton.disabled = false;
        submitButton.innerHTML = originalText;
    });
}

function handleDelete() {
    const branchId = document.getElementById('delete_branch_id').value;

    fetch(`/admin/branches/${branchId}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            window.location.reload();
        } else {
            alert(data.message || 'Failed to delete branch');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Failed to delete branch');
    });
}

function togglePasswordFields() {
    const requiresPassword = document.getElementById('edit_requires_password').checked;
    const passwordFields = document.getElementById('passwordFields');
    passwordFields.classList.toggle('hidden', !requiresPassword);
    
    if (requiresPassword) {
        document.getElementById('edit_access_password').required = true;
        document.getElementById('edit_confirm_password').required = true;
    } else {
        document.getElementById('edit_access_password').required = false;
        document.getElementById('edit_confirm_password').required = false;
    }
}

function showPasswordModal(branchId) {
    document.getElementById('password_branch_id').value = branchId;
    document.getElementById('passwordModal').classList.remove('hidden');
}

function hidePasswordModal() {
    document.getElementById('passwordModal').classList.add('hidden');
    document.getElementById('passwordForm').reset();
}

function handlePasswordVerification(event) {
    event.preventDefault();
    const branchId = document.getElementById('password_branch_id').value;
    if (!branchId) {
        console.error('Branch ID is required');
        return;
    }

    const password = document.getElementById('branch_password').value;

    fetch(`/admin/branches/${branchId}/verify`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({ password })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            hidePasswordModal();
            // Switch to the branch
            window.location.href = `/admin/branches/${branchId}/switch`;
        } else {
            alert('Invalid password');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while verifying the password');
    });
}

// Update the branch switching logic
function showBranchSwitchModal(branchId, branchName, requiresPassword) {
    if (!branchId) {
        console.error('Branch ID is required');
        return;
    }

    if (requiresPassword) {
        showPasswordModal(branchId);
    } else {
        window.location.href = `/admin/branches/${branchId}/switch`;
    }
}

function resetBranchPassword() {
    const branchId = document.querySelector('[name="branch_id"]').value;
    const newPassword = prompt('Enter new password (minimum 6 characters):');
    
    if (!newPassword) {
        return;
    }
    
    if (newPassword.length < 6) {
        alert('Password must be at least 6 characters long');
        return;
    }
    
    fetch(`/admin/branches/${branchId}/reset-password`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({ password: newPassword })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Password reset successfully');
            // Update the password fields
            document.getElementById('edit_access_password').value = newPassword;
            document.getElementById('edit_confirm_password').value = newPassword;
        } else {
            alert(data.message || 'Failed to reset password');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Failed to reset password. Please try again.');
    });
}
</script>
@endsection 