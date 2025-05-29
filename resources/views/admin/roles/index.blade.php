@extends('layouts.app')

@section('content')
<div class="container py-4">
    <h2 class="mb-4">Role & Permission Management</h2>
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    <div class="mb-3">
        <input type="text" id="userSearch" class="form-control" placeholder="Search users by name or email...">
    </div>
    <div class="list-group" id="userList">
        @foreach($users as $user)
            <div class="list-group-item d-flex justify-content-between align-items-center flex-wrap user-row" data-name="{{ strtolower($user->name) }}" data-email="{{ strtolower($user->email) }}">
                <div>
                    <span class="fw-bold">{{ $user->name }}</span>
                    <span class="text-muted small">({{ $user->email }})</span>
                    @foreach($user->roles as $role)
                        <span class="badge bg-primary ms-1">{{ $role->name }}</span>
                    @endforeach
                </div>
                <button class="btn btn-outline-secondary btn-sm manage-access-btn" data-bs-toggle="modal" data-bs-target="#manageModal" data-user='@json($user)'>Manage Access</button>
            </div>
        @endforeach
    </div>

    <!-- Modal -->
    <div class="modal fade" id="manageModal" tabindex="-1" aria-labelledby="manageModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form id="accessForm" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="manageModalLabel">Manage Access</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div id="modalUserInfo" class="mb-3"></div>
                        <ul class="nav nav-tabs mb-3" id="accessTab" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="roles-tab" data-bs-toggle="tab" data-bs-target="#rolesTab" type="button" role="tab">Roles</button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="permissions-tab" data-bs-toggle="tab" data-bs-target="#permissionsTab" type="button" role="tab">Permissions</button>
                            </li>
                        </ul>
                        <div class="tab-content" id="accessTabContent">
                            <div class="tab-pane fade show active" id="rolesTab" role="tabpanel">
                                <div class="row">
                                    @foreach($roles as $role)
                                        <div class="col-6 col-md-4 mb-2">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="roles[]" value="{{ $role->name }}" id="modal-role-{{ $role->id }}">
                                                <label class="form-check-label" for="modal-role-{{ $role->id }}">{{ $role->name }}</label>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                            <div class="tab-pane fade" id="permissionsTab" role="tabpanel">
                                @php
                                    $grouped = $permissions->groupBy(fn($p) => explode(' ', $p->name)[0]);
                                @endphp
                                @foreach($grouped as $group => $perms)
                                    <div class="mb-2">
                                        <div class="fw-bold text-capitalize mb-1">{{ $group }}</div>
                                        <div class="row">
                                            @foreach($perms as $permission)
                                                <div class="col-6 col-md-4 mb-1">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" name="permissions[]" value="{{ $permission->name }}" id="modal-perm-{{ $permission->id }}">
                                                        <label class="form-check-label" for="modal-perm-{{ $permission->id }}">{{ $permission->name }}</label>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary" id="saveBtn">
                            <span id="saveSpinner" class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                            Save
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .user-row { cursor: pointer; }
    .badge { font-size: 0.85em; }
    @media (max-width: 600px) {
        .modal-lg { max-width: 98vw; }
    }
</style>
@endpush

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

    // Modal open handler
    document.querySelectorAll('.manage-access-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const user = JSON.parse(this.getAttribute('data-user'));
            selectedUser = user;
            document.getElementById('modalUserInfo').innerHTML = `<b>${user.name}</b> <span class='text-muted small'>(${user.email})</span>`;
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
        });
    });

    // Show spinner on save
    document.getElementById('accessForm').addEventListener('submit', function() {
        document.getElementById('saveSpinner').classList.remove('d-none');
        document.getElementById('saveBtn').setAttribute('disabled', 'disabled');
    });
</script>
@endpush 