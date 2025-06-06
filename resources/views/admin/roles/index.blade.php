@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <h2 class="text-2xl font-bold mb-6">Role & Permission Management</h2>
    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
            <span class="block sm:inline">{{ session('success') }}</span>
        </div>
    @endif
    <div class="mb-6">
        <input type="text" id="userSearch" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500" placeholder="Search users by name or email...">
    </div>
    <div class="space-y-4" id="userList">
        @foreach($users as $user)
            <div class="bg-white rounded-lg shadow p-4 flex justify-between items-center flex-wrap user-row" data-name="{{ strtolower($user->name) }}" data-email="{{ strtolower($user->email) }}">
                <div>
                    <span class="font-bold">{{ $user->name }}</span>
                    <span class="text-gray-500 text-sm">({{ $user->email }})</span>
                    @foreach($user->roles as $role)
                        <span class="ml-2 px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">{{ $role->name }}</span>
                    @endforeach
                </div>
                <button class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 manage-access-btn" data-bs-toggle="modal" data-bs-target="#manageModal" data-user='@json($user)'>Manage Access</button>
            </div>
        @endforeach
    </div>

    <!-- Modal -->
    <div class="fixed inset-0 bg-gray-500 bg-opacity-75 hidden" id="manageModal" tabindex="-1" aria-labelledby="manageModalLabel" aria-hidden="true">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-lg shadow-xl max-w-4xl w-full">
                <form id="accessForm" method="POST">
                    @csrf
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h5 class="text-lg font-medium text-gray-900" id="manageModalLabel">Manage Access</h5>
                    </div>
                    <div class="p-6">
                        <div id="modalUserInfo" class="mb-6"></div>
                        <div class="border-b border-gray-200">
                            <nav class="flex space-x-8" aria-label="Tabs">
                                <button type="button" class="border-blue-500 text-blue-600 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm active" id="roles-tab" data-tab="rolesTab">Roles</button>
                                <button type="button" class="border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm" id="permissions-tab" data-tab="permissionsTab">Permissions</button>
                            </nav>
                        </div>
                        <div class="mt-6">
                            <div class="tab-pane active" id="rolesTab">
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    @foreach($roles as $role)
                                        <div class="flex items-center">
                                            <input class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded" type="checkbox" name="roles[]" value="{{ $role->name }}" id="modal-role-{{ $role->id }}">
                                            <label class="ml-2 block text-sm text-gray-900" for="modal-role-{{ $role->id }}">{{ $role->name }}</label>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                            <div class="tab-pane hidden" id="permissionsTab">
                                @php
                                    $grouped = $permissions->groupBy(fn($p) => explode(' ', $p->name)[0]);
                                @endphp
                                @foreach($grouped as $group => $perms)
                                    <div class="mb-6">
                                        <div class="font-bold text-gray-900 capitalize mb-2">{{ $group }}</div>
                                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                            @foreach($perms as $permission)
                                                <div class="flex items-center">
                                                    <input class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded" type="checkbox" name="permissions[]" value="{{ $permission->name }}" id="modal-perm-{{ $permission->id }}">
                                                    <label class="ml-2 block text-sm text-gray-900" for="modal-perm-{{ $permission->id }}">{{ $permission->name }}</label>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex justify-end space-x-3">
                        <button type="button" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500" onclick="document.getElementById('manageModal').classList.add('hidden')">Close</button>
                        <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500" id="saveBtn">
                            <span id="saveSpinner" class="hidden inline-block animate-spin rounded-full h-4 w-4 border-2 border-white border-t-transparent" role="status" aria-hidden="true"></span>
                            Save
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const users = @json($users);
    const roles = @json($roles);
    const permissions = @json($permissions);
    let selectedUser = null;

    // Live search
    document.getElementById('userSearch').addEventListener('input', function() {
        const val = this.value.toLowerCase();
        document.querySelectorAll('.user-row').forEach(row => {
            const name = row.getAttribute('data-name');
            const email = row.getAttribute('data-email');
            row.style.display = (name.includes(val) || email.includes(val)) ? '' : 'none';
        });
    });

    // Tab switching
    document.querySelectorAll('[data-tab]').forEach(button => {
        button.addEventListener('click', function() {
            const tabId = this.dataset.tab;
            
            // Update active states
            document.querySelectorAll('[data-tab]').forEach(btn => {
                btn.classList.remove('border-blue-500', 'text-blue-600');
                btn.classList.add('border-transparent', 'text-gray-500');
            });
            this.classList.remove('border-transparent', 'text-gray-500');
            this.classList.add('border-blue-500', 'text-blue-600');
            
            // Show/hide content
            document.querySelectorAll('.tab-pane').forEach(pane => {
                pane.classList.add('hidden');
            });
            document.getElementById(tabId).classList.remove('hidden');
        });
    });

    // Modal open handler
    document.querySelectorAll('.manage-access-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const user = JSON.parse(this.getAttribute('data-user'));
            selectedUser = user;
            document.getElementById('modalUserInfo').innerHTML = `<b>${user.name}</b> <span class='text-gray-500 text-sm'>(${user.email})</span>`;
            // Reset all checkboxes
            document.querySelectorAll('#manageModal input[type=checkbox]').forEach(cb => cb.checked = false);
            // Set roles
            if(user.roles) {
                user.roles.forEach(role => {
                    const cb = document.querySelector(`#manageModal input[name='roles[]'][value='${role.name}']`);
                    if(cb) cb.checked = true;
                });
            }
            // Set permissions
            if(user.permissions) {
                user.permissions.forEach(perm => {
                    const cb = document.querySelector(`#manageModal input[name='permissions[]'][value='${perm.name}']`);
                    if(cb) cb.checked = true;
                });
            }
            // Set form action
            document.getElementById('accessForm').action = `/admin/roles/${user.id}`;
            // Add method field for PUT
            let methodField = document.createElement('input');
            methodField.type = 'hidden';
            methodField.name = '_method';
            methodField.value = 'PUT';
            document.getElementById('accessForm').appendChild(methodField);
            // Show modal
            document.getElementById('manageModal').classList.remove('hidden');
        });
    });

    // Show spinner on save
    document.getElementById('accessForm').addEventListener('submit', function() {
        document.getElementById('saveSpinner').classList.remove('hidden');
        document.getElementById('saveBtn').setAttribute('disabled', 'disabled');
    });
</script>
@endpush 