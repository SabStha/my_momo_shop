@extends('layouts.admin')

@section('title', 'Investor Management')

@section('content')
<div class="px-4 py-6 mx-auto max-w-7xl">
    <!-- Header with Add Button -->
    <div class="flex flex-col sm:flex-row justify-between items-center mb-6 gap-4">
        <h1 class="text-2xl font-bold text-gray-800">Investor Management</h1>
        <a href="{{ route('admin.investors.create') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg shadow hover:bg-blue-700 transition duration-200">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
            </svg>
            Add New Investor
        </a>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
        <div class="bg-white rounded-lg shadow p-6 flex items-center">
            <div class="flex-1">
                <div class="text-xs font-semibold text-blue-600 uppercase tracking-wide mb-1">Total Investors</div>
                <div class="text-3xl font-bold text-gray-900">{{ $totalInvestors }}</div>
            </div>
            <div class="ml-4 text-blue-400">
                <svg class="w-10 h-10" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87m9-5a4 4 0 11-8 0 4 4 0 018 0zm6 6v2a2 2 0 01-2 2H5a2 2 0 01-2-2v-2a6 6 0 0112 0z"/>
                </svg>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow p-6 flex items-center">
            <div class="flex-1">
                <div class="text-xs font-semibold text-green-600 uppercase tracking-wide mb-1">Active Investors</div>
                <div class="text-3xl font-bold text-gray-900">{{ $activeInvestors }}</div>
            </div>
            <div class="ml-4 text-green-400">
                <svg class="w-10 h-10" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                </svg>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow p-6 flex items-center">
            <div class="flex-1">
                <div class="text-xs font-semibold text-cyan-600 uppercase tracking-wide mb-1">Total Investment</div>
                <div class="text-3xl font-bold text-gray-900">Rs {{ number_format($totalInvestment, 2) }}</div>
            </div>
            <div class="ml-4 text-cyan-400">
                <svg class="w-10 h-10" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 1.343-3 3s1.343 3 3 3 3-1.343 3-3-1.343-3-3-3zm0 0V4m0 16v-4"/>
                </svg>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow p-6 flex items-center">
            <div class="flex-1">
                <div class="text-xs font-semibold text-yellow-600 uppercase tracking-wide mb-1">Total Payouts</div>
                <div class="text-3xl font-bold text-gray-900">Rs {{ number_format($totalPayouts, 2) }}</div>
            </div>
            <div class="ml-4 text-yellow-400">
                <svg class="w-10 h-10" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a5 5 0 00-10 0v2a2 2 0 00-2 2v5a2 2 0 002 2h10a2 2 0 002-2v-5a2 2 0 00-2-2z"/>
                </svg>
            </div>
        </div>
    </div>

    <!-- Investors Table -->
    <div class="bg-white rounded-lg shadow">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-900">All Investors</h2>
        </div>
        
        <div class="overflow-x-auto">
            @if($investors->count() > 0)
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Investor</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Investment</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ownership</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($investors as $investor)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-10 w-10">
                                        <div class="h-10 w-10 rounded-full bg-blue-600 flex items-center justify-center">
                                            <span class="text-sm font-medium text-white">{{ strtoupper(substr($investor->name, 0, 1)) }}</span>
                                        </div>
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900">{{ $investor->name }}</div>
                                        <div class="text-sm text-gray-500">{{ $investor->email }}</div>
                                        @if($investor->company_name)
                                            <div class="text-xs text-gray-400">{{ $investor->company_name }}</div>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{
                                    $investor->investment_type === 'individual' ? 'bg-blue-100 text-blue-800' :
                                    ($investor->investment_type === 'corporate' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800')
                                }}">
                                    {{ ucfirst(str_replace('_', ' ', $investor->investment_type)) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                Rs {{ number_format($investor->investments->sum('investment_amount'), 2) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ number_format($investor->investments->sum('ownership_percentage'), 2) }}%
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{
                                    $investor->status === 'active' ? 'bg-green-100 text-green-800' :
                                    ($investor->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-800')
                                }}">
                                    {{ ucfirst($investor->status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex space-x-2">
                                    <a href="{{ route('admin.investors.show', $investor) }}" 
                                       class="text-blue-600 hover:text-blue-900 transition duration-200" 
                                       title="View Details">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0zm6 0c0 5-4.03 9-9 9s-9-4-9-9 4.03-9 9-9 9 4 9 9z"/>
                                        </svg>
                                    </a>
                                    <a href="{{ route('admin.investors.dashboard', $investor) }}" 
                                       class="text-cyan-600 hover:text-cyan-900 transition duration-200" 
                                       title="Dashboard">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 3v18h18M9 17v-6a2 2 0 012-2h2a2 2 0 012 2v6"/>
                                        </svg>
                                    </a>
                                    <a href="{{ route('admin.investors.edit', $investor) }}" 
                                       class="text-yellow-600 hover:text-yellow-900 transition duration-200" 
                                       title="Edit">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536M9 11l6 6M3 21h6l11-11a2.828 2.828 0 00-4-4L5 17v4z"/>
                                        </svg>
                                    </a>
                                    <button type="button" 
                                            onclick="confirmDelete({{ $investor->id }}, '{{ $investor->name }}')" 
                                            class="text-red-600 hover:text-red-900 transition duration-200" 
                                            title="Delete">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <div class="px-6 py-12 text-center">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87m9-5a4 4 0 11-8 0 4 4 0 018 0zm6 6v2a2 2 0 01-2 2H5a2 2 0 01-2-2v-2a6 6 0 0112 0z"/>
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">No investors</h3>
                    <p class="mt-1 text-sm text-gray-500">Get started by creating a new investor.</p>
                    <div class="mt-6">
                        <a href="{{ route('admin.investors.create') }}" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                            </svg>
                            Add New Investor
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Simple Delete Confirmation Modal -->
<div id="deleteModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3 text-center">
            <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100">
                <svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                </svg>
            </div>
            <h3 class="text-lg leading-6 font-medium text-gray-900 mt-4">Delete Investor</h3>
            <div class="mt-2 px-7 py-3">
                <p class="text-sm text-gray-500">
                    Are you sure you want to delete <span id="investorName" class="font-semibold"></span>? This action cannot be undone.
                </p>
            </div>
            <div class="items-center px-4 py-3">
                <form id="deleteForm" method="POST" class="inline">
                    @csrf
                    @method('DELETE')
                    <button type="button" onclick="closeDeleteModal()" class="px-4 py-2 bg-gray-500 text-white text-base font-medium rounded-md w-24 mr-2 hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-300">
                        Cancel
                    </button>
                    <button type="submit" class="px-4 py-2 bg-red-600 text-white text-base font-medium rounded-md w-24 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-300">
                        Delete
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    function confirmDelete(investorId, investorName) {
        const investorNameElement = document.getElementById('investorName');
        const deleteForm = document.getElementById('deleteForm');
        const deleteModal = document.getElementById('deleteModal');
        
        if (investorNameElement && deleteForm && deleteModal) {
            investorNameElement.textContent = investorName;
            deleteForm.action = `/admin/investors/${investorId}`;
            deleteModal.classList.remove('hidden');
        }
    }

    function closeDeleteModal() {
        const deleteModal = document.getElementById('deleteModal');
        if (deleteModal) {
            deleteModal.classList.add('hidden');
        }
    }

    // Make functions globally available
    window.confirmDelete = confirmDelete;
    window.closeDeleteModal = closeDeleteModal;

    // Close modal when clicking outside
    const deleteModal = document.getElementById('deleteModal');
    if (deleteModal) {
        deleteModal.addEventListener('click', function(e) {
            if (e.target === this) {
                closeDeleteModal();
            }
        });
    }

    // Close modal with Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeDeleteModal();
        }
    });
});
</script>
@endpush 