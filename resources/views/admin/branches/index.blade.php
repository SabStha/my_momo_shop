@extends('layouts.admin')

@section('title', 'Branches')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Branches</h1>
        <button onclick="showCreateModal()" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg flex items-center">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
            Add Branch
        </button>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach($branches as $branch)
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <div class="p-6">
                <div class="flex justify-between items-start mb-4">
                    <div>
                        <h2 class="text-xl font-semibold text-gray-800">{{ $branch->name }}</h2>
                        <p class="text-sm text-gray-600">Code: {{ $branch->code }}</p>
                    </div>
                    <div class="flex items-center">
                        @if($branch->is_main)
                        <span class="bg-purple-100 text-purple-800 text-xs px-2 py-1 rounded-full">Main Branch</span>
                        @endif
                        <span class="ml-2 {{ $branch->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }} text-xs px-2 py-1 rounded-full">
                            {{ $branch->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div class="text-center p-3 bg-gray-50 rounded-lg">
                        <p class="text-sm text-gray-600">Products</p>
                        <p class="text-lg font-semibold text-gray-800">{{ $branch->products_count }}</p>
                    </div>
                    <div class="text-center p-3 bg-gray-50 rounded-lg">
                        <p class="text-sm text-gray-600">Orders</p>
                        <p class="text-lg font-semibold text-gray-800">{{ $branch->orders_count }}</p>
                    </div>
                    <div class="text-center p-3 bg-gray-50 rounded-lg">
                        <p class="text-sm text-gray-600">Employees</p>
                        <p class="text-lg font-semibold text-gray-800">{{ $branch->employees_count }}</p>
                    </div>
                    <div class="text-center p-3 bg-gray-50 rounded-lg">
                        <p class="text-sm text-gray-600">Tables</p>
                        <p class="text-lg font-semibold text-gray-800">{{ $branch->tables_count }}</p>
                    </div>
                </div>

                <div class="flex justify-between items-center pt-4 border-t">
                    <a href="{{ route('admin.branches.show', $branch) }}" class="text-blue-500 hover:text-blue-600">
                        View Details
                    </a>
                    <div class="flex space-x-2">
                        <button onclick="showEditModal({{ $branch->id }})" class="text-gray-600 hover:text-gray-800">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                            </svg>
                        </button>
                        @unless($branch->is_main)
                        <button onclick="confirmDelete({{ $branch->id }})" class="text-red-600 hover:text-red-800">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                            </svg>
                        </button>
                        @endunless
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>

<!-- Create Branch Modal -->
<div id="createModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Create New Branch</h3>
            <form action="{{ route('admin.branches.store') }}" method="POST">
                @csrf
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="name">Name</label>
                    <input type="text" name="name" id="name" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                </div>
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="code">Code</label>
                    <input type="text" name="code" id="code" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                </div>
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="address">Address</label>
                    <textarea name="address" id="address" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required></textarea>
                </div>
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="contact_person">Contact Person</label>
                    <input type="text" name="contact_person" id="contact_person" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                </div>
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="email">Email</label>
                    <input type="email" name="email" id="email" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                </div>
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="phone">Phone</label>
                    <input type="text" name="phone" id="phone" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                </div>
                <div class="mb-4">
                    <label class="flex items-center">
                        <input type="checkbox" name="is_active" class="form-checkbox" checked>
                        <span class="ml-2 text-gray-700">Active</span>
                    </label>
                </div>
                <div class="mb-4">
                    <label class="flex items-center">
                        <input type="checkbox" name="is_main" class="form-checkbox">
                        <span class="ml-2 text-gray-700">Main Branch</span>
                    </label>
                </div>
                <div class="flex justify-end space-x-2">
                    <button type="button" onclick="hideCreateModal()" class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded-lg">Cancel</button>
                    <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg">Create</button>
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
            <form id="editForm" method="POST">
                @csrf
                @method('PUT')
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="edit_name">Name</label>
                    <input type="text" name="name" id="edit_name" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                </div>
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="edit_code">Code</label>
                    <input type="text" name="code" id="edit_code" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                </div>
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="edit_address">Address</label>
                    <textarea name="address" id="edit_address" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required></textarea>
                </div>
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="edit_contact_person">Contact Person</label>
                    <input type="text" name="contact_person" id="edit_contact_person" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                </div>
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="edit_email">Email</label>
                    <input type="email" name="email" id="edit_email" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                </div>
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="edit_phone">Phone</label>
                    <input type="text" name="phone" id="edit_phone" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                </div>
                <div class="mb-4">
                    <label class="flex items-center">
                        <input type="checkbox" name="is_active" id="edit_is_active" class="form-checkbox">
                        <span class="ml-2 text-gray-700">Active</span>
                    </label>
                </div>
                <div class="mb-4">
                    <label class="flex items-center">
                        <input type="checkbox" name="is_main" id="edit_is_main" class="form-checkbox">
                        <span class="ml-2 text-gray-700">Main Branch</span>
                    </label>
                </div>
                <div class="flex justify-end space-x-2">
                    <button type="button" onclick="hideEditModal()" class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded-lg">Cancel</button>
                    <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div id="deleteModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Confirm Delete</h3>
            <p class="text-gray-600 mb-4">Are you sure you want to delete this branch? This action cannot be undone.</p>
            <form id="deleteForm" method="POST">
                @csrf
                @method('DELETE')
                <div class="flex justify-end space-x-2">
                    <button type="button" onclick="hideDeleteModal()" class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded-lg">Cancel</button>
                    <button type="submit" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg">Delete</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function showCreateModal() {
        document.getElementById('createModal').classList.remove('hidden');
    }

    function hideCreateModal() {
        document.getElementById('createModal').classList.add('hidden');
    }

    function showEditModal(branchId) {
        const branch = @json($branches);
        const selectedBranch = branch.find(b => b.id === branchId);
        
        if (selectedBranch) {
            document.getElementById('editForm').action = `/admin/branches/${branchId}`;
            document.getElementById('edit_name').value = selectedBranch.name;
            document.getElementById('edit_code').value = selectedBranch.code;
            document.getElementById('edit_address').value = selectedBranch.address;
            document.getElementById('edit_contact_person').value = selectedBranch.contact_person;
            document.getElementById('edit_email').value = selectedBranch.email;
            document.getElementById('edit_phone').value = selectedBranch.phone;
            document.getElementById('edit_is_active').checked = selectedBranch.is_active;
            document.getElementById('edit_is_main').checked = selectedBranch.is_main;
            
            document.getElementById('editModal').classList.remove('hidden');
        }
    }

    function hideEditModal() {
        document.getElementById('editModal').classList.add('hidden');
    }

    function confirmDelete(branchId) {
        document.getElementById('deleteForm').action = `/admin/branches/${branchId}`;
        document.getElementById('deleteModal').classList.remove('hidden');
    }

    function hideDeleteModal() {
        document.getElementById('deleteModal').classList.add('hidden');
    }
</script>
@endpush
@endsection 