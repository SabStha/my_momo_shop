@extends('layouts.admin')

@section('title', 'Monthly Stock Check')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
        <h2 class="text-2xl font-semibold text-gray-800">Monthly Stock Check</h2>
        <div class="flex items-center space-x-3 mt-4 sm:mt-0">
            <a href="{{ route('admin.inventory.audit-reports.index', ['type' => 'monthly', 'branch' => $branch ? $branch->id : null]) }}" 
               class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-md hover:bg-blue-700 focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200">
                <i class="fas fa-chart-bar"></i> View Reports
            </a>
            <a href="{{ route('admin.inventory.audit-reports.sessions', ['type' => 'monthly', 'branch' => $branch ? $branch->id : null]) }}" 
               class="inline-flex items-center gap-2 px-4 py-2 bg-purple-600 text-white text-sm font-medium rounded-md hover:bg-purple-700 focus:ring-2 focus:ring-offset-2 focus:ring-purple-500 transition-all duration-200">
                <i class="fas fa-clock"></i> Audit Sessions
            </a>
            <span class="text-sm text-gray-500">📅 Month of {{ now()->format('F Y') }}</span>
        </div>
    </div>

    @if(session('success'))
        <div class="bg-green-100 border border-green-300 text-green-800 px-4 py-3 rounded-md">
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="bg-red-100 border border-red-300 text-red-800 px-4 py-3 rounded-md">
            {{ session('error') }}
        </div>
    @endif

    @if($items->count() === 0)
        <div class="bg-white shadow-md rounded-lg p-8 text-center">
            <div class="mb-6">
                <div class="w-16 h-16 bg-purple-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-box-open text-purple-600 text-2xl"></i>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">No Inventory Items Found</h3>
                <p class="text-gray-600 mb-6">
                    @if($branch)
                        There are no inventory items in <strong>{{ $branch->name }}</strong> to perform monthly stock checks on.
                    @else
                        There are no inventory items to perform monthly stock checks on.
                    @endif
                </p>
            </div>
            
            <div class="flex flex-col sm:flex-row gap-3 justify-center">
                <a href="{{ route('admin.inventory.create', ['branch' => $branch ? $branch->id : null]) }}" 
                   class="inline-flex items-center gap-2 px-4 py-2 bg-purple-600 text-white text-sm font-medium rounded-md hover:bg-purple-700 focus:ring-2 focus:ring-offset-2 focus:ring-purple-500 transition-all duration-200">
                    <i class="fas fa-plus"></i>
                    Add Inventory Items
                </a>
                <a href="{{ route('admin.inventory.index') }}" 
                   class="inline-flex items-center gap-2 px-4 py-2 bg-gray-600 text-white text-sm font-medium rounded-md hover:bg-gray-700 focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-all duration-200">
                    <i class="fas fa-arrow-left"></i>
                    Back to Inventory
                </a>
            </div>
        </div>
    @else
        <form action="{{ route('admin.inventory.monthly-checks.store') }}" method="POST" id="monthlyStockCheckForm">
            @csrf
            <input type="hidden" name="branch_id" value="{{ $branch ? $branch->id : '' }}">

            <div class="overflow-x-auto bg-white shadow-md rounded-lg">
                <table class="min-w-full text-sm text-left">
                    <thead class="bg-gray-50 text-gray-700 uppercase text-xs sticky top-0 z-10">
                        <tr>
                            <th class="px-4 py-4 bg-purple-200 text-gray-900 font-semibold">SN</th>
                            <th class="px-6 py-4 bg-purple-400 text-gray-900 font-semibold">Item Name</th>
                            <th class="px-6 py-4 bg-purple-400 text-gray-900 font-semibold">SKU</th>
                            <th class="px-6 py-4 bg-purple-400 text-gray-900 font-semibold">Current Stock</th>
                            <th class="px-6 py-4 bg-purple-400 text-gray-900 font-semibold">Checked Quantity</th>
                            <th class="px-6 py-4 bg-purple-400 text-gray-900 font-semibold">Notes</th>
                            <th class="px-6 py-4 bg-purple-400 text-gray-900 font-semibold">Last Checked</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($items as $index => $item)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-4 font-bold text-gray-800 bg-purple-100">{{ $index + 1 }}</td>
                                <td class="px-6 py-4 font-medium text-gray-900">{{ $item->name }}</td>
                                <td class="px-6 py-4 text-gray-800">{{ $item->code }}</td>
                                <td class="px-6 py-4 text-gray-800">{{ $item->current_stock }} {{ $item->unit }}</td>
                                <td class="px-6 py-4">
                                    <input 
                                        type="number"
                                        name="quantities[{{ $item->id }}]"
                                        value="{{ $item->monthlyChecks->first()?->quantity_checked ?? $item->current_stock }}"
                                        step="0.01"
                                        min="0"
                                        required
                                        class="w-28 px-3 py-2 text-sm border rounded-md focus:ring-2 focus:ring-purple-500 focus:border-purple-500 stock-input"
                                        data-item-id="{{ $item->id }}"
                                        data-current-stock="{{ $item->current_stock }}"
                                    >
                                    <input type="hidden" name="item_ids[]" value="{{ $item->id }}">
                                </td>
                                <td class="px-6 py-4">
                                    <input 
                                        type="text" 
                                        name="notes[{{ $item->id }}]" 
                                        value="{{ $item->monthlyChecks->first()?->notes }}"
                                        placeholder="Add notes..."
                                        class="w-full px-3 py-2 text-sm border rounded-md focus:ring-2 focus:ring-purple-500 focus:border-purple-500"
                                    >
                                </td>
                                <td class="px-6 py-4 text-gray-600">
                                    @if($item->monthlyChecks->first())
                                        <span class="block text-sm font-medium text-green-700">
                                            {{ $item->monthlyChecks->first()->created_at->format('M j, H:i') }}
                                        </span>
                                    @else
                                        <span class="text-gray-400 italic">Not checked</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="flex justify-end mt-6">
                <button type="button" id="saveMonthlyChecksBtn" class="inline-flex items-center gap-2 px-6 py-3 bg-purple-600 text-white text-sm font-medium rounded-md hover:bg-purple-700 focus:ring-2 focus:ring-offset-2 focus:ring-purple-500 transition-all duration-200">
                    <i class="fas fa-save"></i> Save All Monthly Checks
                </button>
            </div>
        </form>
    @endif
</div>

<!-- Enhanced Confirmation Modal -->
<div id="monthlyConfirmationModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full transform transition-all duration-300 scale-95 opacity-0" id="monthlyModalContent">
            <!-- Header -->
            <div class="flex items-center justify-between p-6 border-b border-gray-200">
                <div class="flex items-center space-x-3">
                    <div class="flex-shrink-0">
                        <div class="w-10 h-10 bg-purple-100 rounded-full flex items-center justify-center">
                            <i class="fas fa-calendar-alt text-purple-600 text-lg"></i>
                        </div>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">Confirm Monthly Stock Check</h3>
                        <p class="text-sm text-gray-500">Review before saving</p>
                    </div>
                </div>
                <button type="button" class="text-gray-400 hover:text-gray-600 transition-colors duration-200" onclick="closeMonthlyModal()">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>

            <!-- Content -->
            <div class="p-6">
                <div class="mb-4">
                    <p class="text-gray-700 mb-3">Are you sure you want to save all monthly stock checks?</p>
                    <div class="bg-purple-50 border border-purple-200 rounded-md p-4">
                        <div class="flex items-start space-x-3">
                            <i class="fas fa-info-circle text-purple-600 mt-0.5"></i>
                            <div class="text-sm text-purple-800">
                                <p class="font-medium mb-1">This action will:</p>
                                <ul class="list-disc list-inside space-y-1 text-xs">
                                    <li>Record the physical quantities you've counted</li>
                                    <li>Save any notes you've added about discrepancies</li>
                                    <li>Create a permanent audit trail of this monthly check</li>
                                    <li>Store the check date and time for historical tracking</li>
                                    <li>Allow comparison with previous monthly checks</li>
                                </ul>
                                <p class="text-xs text-purple-700 mt-2 italic">
                                    Note: This creates a record of your physical count but does not automatically update inventory levels. 
                                    Use this data to identify discrepancies and adjust stock levels manually if needed.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Stock Difference Summary -->
                <div id="monthlyStockSummary" class="mb-4 bg-gray-50 rounded-md p-3">
                    <h4 class="text-sm font-medium text-gray-700 mb-2">Monthly Stock Check Summary:</h4>
                    <div class="text-xs text-gray-600 space-y-1">
                        <div>Items to check: <span id="monthlyTotalItems" class="font-medium">0</span></div>
                        <div>Items with differences: <span id="monthlyDiffItems" class="font-medium text-orange-600">0</span></div>
                        <div>Total difference: <span id="monthlyTotalDiff" class="font-medium">0</span></div>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="flex items-center justify-end space-x-3 p-6 border-t border-gray-200 bg-gray-50 rounded-b-lg">
                <button type="button" onclick="closeMonthlyModal()" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-all duration-200">
                    Cancel
                </button>
                <button type="button" id="monthlyConfirmSaveBtn" onclick="submitMonthlyForm()" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-purple-600 border border-transparent rounded-md hover:bg-purple-700 focus:ring-2 focus:ring-offset-2 focus:ring-purple-500 transition-all duration-200">
                    <i class="fas fa-save"></i>
                    <span>Save Monthly Checks</span>
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Enhanced confirmation modal for monthly checks
    function showMonthlyModal() {
        const modal = document.getElementById('monthlyConfirmationModal');
        const content = document.getElementById('monthlyModalContent');
        
        modal.classList.remove('hidden');
        
        // Animate in
        setTimeout(() => {
            content.classList.remove('scale-95', 'opacity-0');
            content.classList.add('scale-100', 'opacity-100');
        }, 10);
        
        // Calculate stock differences
        calculateMonthlyStockDifferences();
    }

    function closeMonthlyModal() {
        const modal = document.getElementById('monthlyConfirmationModal');
        const content = document.getElementById('monthlyModalContent');
        
        // Animate out
        content.classList.remove('scale-100', 'opacity-100');
        content.classList.add('scale-95', 'opacity-0');
        
        setTimeout(() => {
            modal.classList.add('hidden');
        }, 300);
    }

    function calculateMonthlyStockDifferences() {
        const inputs = document.querySelectorAll('.stock-input');
        let totalItems = inputs.length;
        let diffItems = 0;
        let totalDiff = 0;

        inputs.forEach(input => {
            const currentStock = parseFloat(input.dataset.currentStock) || 0;
            const checkedQuantity = parseFloat(input.value) || 0;
            const difference = Math.abs(checkedQuantity - currentStock);
            
            if (difference > 0) {
                diffItems++;
                totalDiff += difference;
            }
        });

        document.getElementById('monthlyTotalItems').textContent = totalItems;
        document.getElementById('monthlyDiffItems').textContent = diffItems;
        document.getElementById('monthlyTotalDiff').textContent = totalDiff.toFixed(2);
    }

    function submitMonthlyForm() {
        const btn = document.getElementById('monthlyConfirmSaveBtn');
        const originalText = btn.innerHTML;
        
        // Show loading state
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
        btn.disabled = true;
        
        // Submit the form
        document.getElementById('monthlyStockCheckForm').submit();
    }

    // Event listeners
    document.getElementById('saveMonthlyChecksBtn')?.addEventListener('click', function(e) {
        e.preventDefault();
        showMonthlyModal();
    });

    // Close modal on backdrop click
    document.getElementById('monthlyConfirmationModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeMonthlyModal();
        }
    });

    // Close modal on Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeMonthlyModal();
        }
    });

    // Real-time stock difference calculation
    document.querySelectorAll('.stock-input').forEach(input => {
        input.addEventListener('input', function() {
            const currentStock = parseFloat(this.dataset.currentStock) || 0;
            const checkedQuantity = parseFloat(this.value) || 0;
            const difference = checkedQuantity - currentStock;
            
            // Visual feedback for differences
            if (difference !== 0) {
                this.style.borderColor = difference > 0 ? '#10b981' : '#ef4444';
                this.style.backgroundColor = difference > 0 ? '#f0fdf4' : '#fef2f2';
            } else {
                this.style.borderColor = '';
                this.style.backgroundColor = '';
            }
        });
    });
</script>
@endpush
@endsection
