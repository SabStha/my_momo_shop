@extends('layouts.admin')

@section('title', 'Wallets')

@section('content')
<div class="max-w-7xl mx-auto py-10 px-4">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-semibold text-gray-800">Wallets</h1>
        <a href="{{ route('admin.wallets.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
            <i class="fas fa-plus mr-2"></i>Add New Wallet
        </a>
    </div>

    <div class="bg-white shadow rounded-lg overflow-hidden">
        <div class="p-6">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Owner</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Branch</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Balance</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Last Transaction</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($wallets as $wallet)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $wallet->user->name }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $wallet->branch->name }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                ${{ number_format($wallet->balance, 2) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 text-xs rounded-full 
                                    {{ $wallet->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                    {{ $wallet->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $wallet->last_transaction_at ? $wallet->last_transaction_at->format('M d, Y H:i') : 'No transactions' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <div class="flex items-center space-x-3">
                                    <a href="{{ route('admin.wallets.transactions', $wallet) }}" 
                                       class="text-blue-600 hover:text-blue-900">
                                        <i class="fas fa-history"></i>
                                    </a>
                                    <button onclick="showTopUpModal({{ $wallet->id }})" 
                                            class="text-green-600 hover:text-green-900">
                                        <i class="fas fa-plus-circle"></i>
                                    </button>
                                    <a href="{{ route('admin.wallets.edit', $wallet) }}" 
                                       class="text-yellow-600 hover:text-yellow-900">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('admin.wallets.destroy', $wallet) }}" 
                                          method="POST" 
                                          onsubmit="return confirm('Are you sure you want to delete this wallet?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-900">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Top Up Modal -->
<div id="topUpModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <h3 class="text-lg font-medium text-gray-900">Top Up Wallet</h3>
            <form id="topUpForm" class="mt-4">
                @csrf
                <div class="mb-4">
                    <label for="amount" class="block text-sm font-medium text-gray-700">Amount</label>
                    <input type="number" name="amount" id="amount" step="0.01" min="0"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
                <div class="mb-4">
                    <label for="notes" class="block text-sm font-medium text-gray-700">Notes</label>
                    <textarea name="notes" id="notes" rows="3"
                              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"></textarea>
                </div>
                <div class="flex justify-end space-x-3">
                    <button type="button" onclick="hideTopUpModal()"
                            class="bg-gray-200 text-gray-800 px-4 py-2 rounded hover:bg-gray-300">
                        Cancel
                    </button>
                    <button type="submit"
                            class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                        Top Up
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function showTopUpModal(walletId) {
        const modal = document.getElementById('topUpModal');
        const form = document.getElementById('topUpForm');
        form.action = `/admin/wallets/${walletId}/top-up`;
        modal.classList.remove('hidden');
    }

    function hideTopUpModal() {
        const modal = document.getElementById('topUpModal');
        modal.classList.add('hidden');
    }
</script>
@endpush
@endsection 