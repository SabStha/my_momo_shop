@extends('layouts.admin')

@section('title', 'Cash Management')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-semibold text-gray-800">Cash Management</h2>
        <div class="text-right">
            <p class="text-sm text-gray-600">Total Cash Available</p>
            <p class="text-2xl font-bold text-green-600">{{ number_format($totalCash, 2) }}</p>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="px-6 py-4 border-b">
            <h3 class="text-lg font-semibold text-gray-800">Cash Denominations</h3>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($denominations as $denomination)
                <div class="border rounded-lg p-4 {{ $denomination->is_active ? 'bg-white' : 'bg-gray-50' }}">
                    <div class="flex justify-between items-start mb-4">
                        <div>
                            <h4 class="text-lg font-semibold">{{ $denomination->name }}</h4>
                            <p class="text-sm text-gray-600">Value: {{ number_format($denomination->value, 2) }}</p>
                        </div>
                        <span class="px-2 py-1 text-xs rounded-full {{ $denomination->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                            {{ $denomination->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </div>
                    
                    <div class="mb-4">
                        <p class="text-sm text-gray-600">Current Quantity</p>
                        <p class="text-xl font-semibold">{{ $denomination->quantity }}</p>
                        <p class="text-sm text-gray-600">Total Value: {{ number_format($denomination->total_value, 2) }}</p>
                    </div>

                    <div class="flex justify-between items-center">
                        <button onclick="openUpdateModal({{ $denomination->id }}, '{{ $denomination->name }}', {{ $denomination->quantity }})"
                                class="text-indigo-600 hover:text-indigo-900 text-sm font-medium">
                            Update Quantity
                        </button>
                        <a href="{{ route('admin.cash-denominations.history', $denomination) }}"
                           class="text-gray-600 hover:text-gray-900 text-sm font-medium">
                            View History
                        </a>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

{{-- Update Modal --}}
<div id="updateModal" class="fixed inset-0 bg-gray-500 bg-opacity-75 flex items-center justify-center hidden">
    <div class="bg-white rounded-lg shadow-xl p-6 max-w-md w-full">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-medium text-gray-900">Update Cash Quantity</h3>
            <button onclick="closeUpdateModal()" class="text-gray-400 hover:text-gray-500">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form id="updateForm" method="POST">
            @csrf
            @method('PUT')
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Denomination</label>
                    <p id="denominationName" class="mt-1 text-lg font-semibold"></p>
                </div>
                <div>
                    <label for="quantity" class="block text-sm font-medium text-gray-700">New Quantity</label>
                    <input type="number" 
                           name="quantity" 
                           id="quantity" 
                           required 
                           min="0"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                </div>
                <div>
                    <label for="reason" class="block text-sm font-medium text-gray-700">Reason for Change</label>
                    <textarea name="reason" 
                              id="reason" 
                              rows="3" 
                              required
                              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"></textarea>
                </div>
            </div>
            <div class="mt-6 flex justify-end space-x-3">
                <button type="button" 
                        onclick="closeUpdateModal()" 
                        class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Cancel
                </button>
                <button type="submit" 
                        class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Update
                </button>
            </div>
        </form>
    </div>
</div>

@endsection

@push('scripts')
<script>
    function openUpdateModal(id, name, currentQuantity) {
        document.getElementById('updateModal').classList.remove('hidden');
        document.getElementById('denominationName').textContent = name;
        document.getElementById('quantity').value = currentQuantity;
        document.getElementById('updateForm').action = `/admin/cash-denominations/${id}`;
    }

    function closeUpdateModal() {
        document.getElementById('updateModal').classList.add('hidden');
    }

    // Update total cash every 30 seconds
    setInterval(async () => {
        try {
            const response = await fetch('{{ route("admin.cash-denominations.total") }}');
            const data = await response.json();
            document.querySelector('.text-2xl.font-bold.text-green-600').textContent = 
                new Intl.NumberFormat('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
                    .format(data.total_cash);
        } catch (error) {
            console.error('Error updating total cash:', error);
        }
    }, 30000);
</script>
@endpush 