@extends('layouts.admin')

@section('title', $branch ? $branch->name . ' - Role & Permission Management' : 'Role & Permission Management')

@section('content')
<div class="container mx-auto px-4 py-8">
    @if($branch)
    <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-gray-900">{{ $branch->name }}</h2>
                <p class="text-sm text-gray-600">{{ $branch->address }}</p>
            </div>
            <div class="text-sm text-gray-600">
                <span class="font-medium">Branch Code:</span> {{ $branch->code }}
            </div>
        </div>
    </div>
    @endif

    <h2 class="text-2xl font-bold mb-6">Role & Permission Management</h2>

    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            {{ session('error') }}
        </div>
    @endif
    @if($errors->any())
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            <ul class="list-disc list-inside">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Roles Section -->
    <div class="mb-8">
        <h3 class="text-xl font-bold mb-4 text-gray-800 border-b pb-2">Roles & Permissions</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($roles as $role)
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 flex flex-col h-full">
                    <div class="mb-3">
                        <form action="{{ route('admin.roles.update', $role) }}" method="POST" class="flex gap-2 items-center">
                            @csrf
                            @method('PUT')
                            <input type="text" name="name" value="{{ $role->name }}" class="flex-1 font-bold text-lg text-gray-900 px-2 py-1 rounded border-transparent hover:border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200 transition-colors" required>
                            @foreach($role->permissions as $perm)
                                <input type="hidden" name="permissions[]" value="{{ $perm->id }}">
                            @endforeach
                            <button type="submit" class="text-blue-600 hover:text-blue-800 bg-blue-50 hover:bg-blue-100 px-2 py-1 rounded text-sm transition-colors" title="Save changes">Save</button>
                        </form>
                    </div>
                    
                    <div class="mb-4 flex-1">
                        <div class="text-xs text-gray-500 uppercase font-semibold mb-2">Permissions</div>
                        <div class="flex flex-wrap gap-1.5">
                            @forelse($role->permissions as $perm)
                                <span class="px-2 py-1 text-xs font-medium bg-blue-50 text-blue-700 rounded-md border border-blue-100">
                                    {{ $perm->name }}
                                </span>
                            @empty
                                <span class="text-xs text-gray-400 italic">No permissions assigned.</span>
                            @endforelse
                        </div>
                    </div>
                    
                    <div class="flex justify-between items-center border-t border-gray-100 pt-3 mt-auto">
                        <div class="text-xs text-gray-500">
                            {{ $role->users()->count() }} User(s)
                        </div>
                        <button type="button" class="text-red-600 hover:text-red-900 text-sm font-medium flex items-center transition-colors px-2 py-1 rounded hover:bg-red-50"
                            onclick="openConfirmModal({
                                title: 'Delete Role',
                                message: 'Are you sure you want to delete the role {{ addslashes($role->name) }}? This cannot be undone.',
                                url: '{{ route('admin.roles.destroy', $role) }}',
                                method: 'DELETE',
                                confirmText: 'Delete',
                                confirmColor: 'red'
                            })">
                            <i class="fas fa-trash mr-1.5"></i> Delete
                        </button>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Users Access Section -->
    <h3 class="text-xl font-bold mb-4 text-gray-800 border-b pb-2">User Access control</h3>

    <div class="mb-6">
        <input type="text" id="userSearch" class="w-full px-4 py-2 border rounded-lg focus:ring focus:ring-blue-500" placeholder="Search users by name or email...">
    </div>

    <div class="space-y-4" id="userList">
        @foreach($users as $user)
            <div class="bg-white rounded shadow p-4 flex justify-between items-center flex-wrap user-row" data-name="{{ strtolower($user->name) }}" data-email="{{ strtolower($user->email) }}">
                <div>
                    <div class="font-semibold text-gray-800">{{ $user->name }}</div>
                    <div class="text-sm text-gray-500">{{ $user->email }}</div>
                    @foreach($user->roles as $role)
                        <span class="inline-block mt-1 mr-2 px-2 py-0.5 text-xs bg-blue-100 text-blue-800 rounded-full">{{ $role->name }}</span>
                    @endforeach
                </div>
                <button class="mt-2 md:mt-0 px-4 py-2 bg-gray-100 border rounded hover:bg-gray-200 text-sm text-gray-700 manage-access-btn" data-bs-toggle="modal" data-bs-target="#manageModal" data-user='@json($user)'>Manage Access</button>
            </div>
        @endforeach
    </div>

    <!-- Modal -->
    <div class="fixed inset-0 bg-black bg-opacity-50 hidden z-50" id="manageModal">
        <div class="flex items-center justify-center min-h-screen">
            <div class="bg-white rounded shadow-lg w-full max-w-4xl mx-4">
                <form id="accessForm" method="POST">
                    @csrf
                    <div class="px-6 py-4 border-b">
                        <h3 class="text-lg font-semibold text-gray-800">Manage Access</h3>
                    </div>

                    <div class="px-6 py-4">
                        <div id="modalUserInfo" class="mb-4 text-gray-700"></div>

                        <div class="border-b">
                            <nav class="flex space-x-6">
                                <button type="button" class="tab-btn active text-blue-600 border-b-2 border-blue-600 px-2 pb-2" data-tab="rolesTab">Roles</button>
                                <button type="button" class="tab-btn text-gray-600 border-b-2 border-transparent hover:text-blue-600 hover:border-blue-600 px-2 pb-2" data-tab="permissionsTab">Permissions</button>
                            </nav>
                        </div>

                        <div class="mt-4">
                            <div class="tab-pane active" id="rolesTab">
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                                    @foreach($roles as $role)
                                        <label class="flex items-center">
                                            <input type="checkbox" name="roles[]" value="{{ $role->name }}" class="mr-2 rounded">
                                            <span>{{ $role->name }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>

                            <div class="tab-pane hidden" id="permissionsTab">
                                @foreach($permissions->groupBy(fn($p) => explode(' ', $p->name)[0]) as $group => $perms)
                                    <div class="mb-4">
                                        <div class="font-semibold text-gray-700 mb-2 capitalize">{{ $group }}</div>
                                        <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                                            @foreach($perms as $permission)
                                                <label class="flex items-center">
                                                    <input type="checkbox" name="permissions[]" value="{{ $permission->name }}" class="mr-2 rounded">
                                                    <span>{{ $permission->name }}</span>
                                                </label>
                                            @endforeach
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end px-6 py-4 border-t">
                        <button type="button" class="mr-3 px-4 py-2 border rounded hover:bg-gray-100" onclick="document.getElementById('manageModal').classList.add('hidden')">Close</button>
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700" id="saveBtn">
                            <span id="saveSpinner" class="hidden animate-spin mr-2 h-4 w-4 border-2 border-white border-t-transparent rounded-full"></span>
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