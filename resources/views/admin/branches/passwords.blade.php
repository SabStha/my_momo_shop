@extends('layouts.admin')

@section('title', 'Branch Password Management')

@section('content')
<div class="bg-white shadow rounded-lg">
    <div class="p-6">
        <div class="flex justify-between items-center mb-6">
            <h3 class="text-lg font-medium text-gray-900">Branch Password Management</h3>
        </div>

        @if(session('success'))
            <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
                {{ session('success') }}
            </div>
        @endif

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Branch Name
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Password Protection
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($branches as $branch)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                {{ $branch->name }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                @if($branch->requires_password)
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                        Enabled
                                    </span>
                                @else
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                        Disabled
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <button type="button"
                                        onclick="showPasswordModal('{{ $branch->id }}', '{{ $branch->name }}', {{ $branch->requires_password ? 'true' : 'false' }})"
                                        class="text-indigo-600 hover:text-indigo-900">
                                    Set Password
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Password Modal -->
<div id="passwordModal" class="fixed inset-0 bg-gray-500 bg-opacity-75 hidden" role="dialog" aria-modal="true">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full" role="document">
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-medium text-gray-900">Set Branch Password</h3>
                    <button type="button" class="text-gray-400 hover:text-gray-500" onclick="closePasswordModal()">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
            
            <form id="passwordForm" method="POST">
                @csrf
                <div class="px-6 py-4">
                    <div class="mb-4">
                        <label for="branchName" class="block text-sm font-medium text-gray-700">Branch Name</label>
                        <input type="text" id="branchName" class="mt-1 block w-full rounded-md border-gray-300 bg-gray-50 shadow-sm" readonly>
                    </div>
                    
                    <div class="mb-4">
                        <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                        <input type="password" 
                               name="password"
                               id="password"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                               placeholder="Enter branch password">
                    </div>
                    
                    <div class="mb-4">
                        <label class="inline-flex items-center">
                            <input type="checkbox" 
                                   name="requires_password" 
                                   id="requiresPassword"
                                   class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <span class="ml-2 text-sm text-gray-600">Require password for branch access</span>
                        </label>
                    </div>
                </div>
                
                <div class="px-6 py-4 bg-gray-50 rounded-b-lg flex justify-end space-x-3">
                    <button type="button" 
                            class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                            onclick="closePasswordModal()">
                        Cancel
                    </button>
                    <button type="submit" 
                            class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        Save Password
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
function showPasswordModal(branchId, branchName, requiresPassword) {
    const modal = document.getElementById('passwordModal');
    const form = document.getElementById('passwordForm');
    const branchNameInput = document.getElementById('branchName');
    const requiresPasswordCheckbox = document.getElementById('requiresPassword');
    
    branchNameInput.value = branchName;
    requiresPasswordCheckbox.checked = requiresPassword;
    form.action = `/admin/branches/${branchId}/password`;
    
    modal.classList.remove('hidden');
}

function closePasswordModal() {
    document.getElementById('passwordModal').classList.add('hidden');
}
</script>
@endpush
@endsection 