@extends('layouts.admin')

@section('title', 'Daily Stock Check')

@push('styles')
<style>
    /* Smooth transitions for row highlighting */
    .inventory-row {
        transition: background-color 0.3s ease;
    }
    
    /* Enhanced input styling */
    .quantity-input {
        transition: all 0.2s ease;
    }
    
    .quantity-input:focus {
        transform: scale(1.02);
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    }
    
    /* Modal animations */
    .modal-enter {
        opacity: 0;
        transform: scale(0.9);
    }
    
    .modal-enter-active {
        opacity: 1;
        transform: scale(1);
        transition: opacity 0.2s ease, transform 0.2s ease;
    }
    
    .modal-exit {
        opacity: 1;
        transform: scale(1);
    }
    
    .modal-exit-active {
        opacity: 0;
        transform: scale(0.9);
        transition: opacity 0.2s ease, transform 0.2s ease;
    }
    
    /* Button loading animation */
    .btn-loading {
        position: relative;
        overflow: hidden;
    }
    
    .btn-loading::after {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
        animation: loading-shimmer 1.5s infinite;
    }
    
    @keyframes loading-shimmer {
        0% { left: -100%; }
        100% { left: 100%; }
    }
    
    /* Stock difference indicators */
    .stock-decrease {
        border-left: 4px solid #ef4444;
    }
    
    .stock-increase {
        border-left: 4px solid #10b981;
    }
    
    .stock-same {
        border-left: 4px solid #6b7280;
    }
</style>
@endpush

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">
    <!-- Back Button and Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
        <div class="flex items-center space-x-4">
            <a href="{{ route('admin.inventory.index', ['branch' => $branchId]) }}" 
               class="inline-flex items-center px-4 py-2 bg-gray-600 text-white text-sm font-medium rounded-md hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-colors">
                <i class="fas fa-arrow-left mr-2"></i> Back to Inventory
            </a>
            <div class="border-l border-gray-300 h-8"></div>
            <div>
                <h2 class="text-2xl font-semibold text-gray-800">Daily Stock Check</h2>
                <p class="text-sm text-gray-500 mt-1">Branch: {{ \App\Models\Branch::find($branchId)->name ?? 'Unknown' }}</p>
            </div>
        </div>
        <span class="text-sm text-gray-500 mt-2 sm:mt-0">📅 {{ now()->format('F j, Y') }}</span>
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

    <form action="{{ route('admin.inventory.checks.store') }}" method="POST" id="stockCheckForm">
        @csrf
        <input type="hidden" name="branch_id" value="{{ $branchId }}">

        @if($items->count() > 0)
            <div class="overflow-x-auto bg-white shadow-md rounded-lg">
                <table class="min-w-full text-sm text-left">
                    <thead class="bg-gray-50 text-gray-700 uppercase text-xs sticky top-0 z-10">
                        <tr>
                            <th class="px-4 py-4 bg-blue-200 text-gray-900 font-semibold">SN</th>
                            <th class="px-6 py-4 bg-blue-400 text-gray-900 font-semibold"">Item Name</th>
                            <th class="px-6 py-4 bg-blue-400 text-gray-900 font-semibold">Code</th>
                            <th class="px-6 py-4 bg-blue-400 text-gray-900 font-semibold">Current Stock</th>
                            <th class="px-6 py-4 bg-blue-400 text-gray-900 font-semibold">Checked Quantity</th>
                            <th class="px-6 py-4 bg-blue-400 text-gray-900 font-semibold">Notes</th>
                            <th class="px-6 py-4 bg-blue-400 text-gray-900 font-semibold">Last Checked</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($items as $index => $item)
                            <tr class="hover:bg-gray-50 inventory-row stock-same">
                                <td class="px-4 py-4 font-bold text-gray-800 bg-blue-100">{{ $index + 1 }}</td>
                                <td class="px-6 py-4 font-medium text-gray-900">{{ $item->name }}</td>
                                <td class="px-6 py-4 text-gray-800">{{ $item->code }}</td>
                                <td class="px-6 py-4 text-gray-800">{{ $item->current_stock }} {{ $item->unit }}</td>
                                <td class="px-6 py-4">
                                    <input 
                                        type="number"
                                        name="quantities[{{ $item->id }}]"
                                        value="{{ $item->dailyChecks->first()?->closing_stock ?? $item->current_stock }}"
                                        step="0.01"
                                        min="0"
                                        required
                                        class="w-28 px-3 py-2 text-sm border rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500 quantity-input"
                                        data-current-stock="{{ $item->current_stock }}"
                                    >
                                    <input type="hidden" name="item_ids[]" value="{{ $item->id }}">
                                </td>
                                <td class="px-6 py-4">
                                    <input 
                                        type="text" 
                                        name="notes[{{ $item->id }}]" 
                                        value="{{ $item->dailyChecks->first()?->notes }}"
                                        placeholder="Add notes..."
                                        class="w-full px-3 py-2 text-sm border rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    >
                                </td>
                                <td class="px-6 py-4 text-gray-600">
                                    @if($item->dailyChecks->first())
                                        <span class="block text-sm font-medium text-green-700">
                                            {{ $item->dailyChecks->first()->created_at->format('H:i') }}
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
                <button type="submit" class="inline-flex items-center gap-2 px-6 py-3 bg-blue-600 text-white text-sm font-medium rounded-md hover:bg-blue-700 focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    <i class="fas fa-save"></i> Save All Checks
                </button>
            </div>
        @else
            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-6 text-center">
                <div class="text-yellow-600 mb-4">
                    <i class="fas fa-exclamation-triangle text-4xl"></i>
                </div>
                <h3 class="text-lg font-semibold text-yellow-800 mb-2">No Inventory Items Found</h3>
                <p class="text-yellow-700 mb-4">
                    There are no inventory items in this branch. You need to add inventory items before you can perform stock checks.
                </p>
                <div class="space-x-4">
                    <a href="{{ route('admin.inventory.create', ['branch' => $branchId]) }}" 
                       class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-md hover:bg-blue-700">
                        <i class="fas fa-plus mr-2"></i> Add Inventory Item
                    </a>
                    <a href="{{ route('admin.inventory.index', ['branch' => $branchId]) }}" 
                       class="inline-flex items-center px-4 py-2 bg-gray-600 text-white text-sm font-medium rounded-md hover:bg-gray-700">
                        <i class="fas fa-arrow-left mr-2"></i> Back to Inventory
                    </a>
                </div>
            </div>
        @endif
    </form>
</div>

@push('scripts')
<script>
    // Create custom confirmation modal
    function createConfirmationModal() {
        const modal = document.createElement('div');
        modal.id = 'confirmationModal';
        modal.className = 'fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50';
        modal.innerHTML = `
            <div class="bg-white rounded-lg shadow-xl max-w-md w-full mx-4 transform transition-all">
                <div class="flex items-center justify-between p-6 border-b border-gray-200">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                                <i class="fas fa-save text-blue-600 text-lg"></i>
                            </div>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-lg font-semibold text-gray-900">Save Stock Checks</h3>
                            <p class="text-sm text-gray-500">Confirm your action</p>
                        </div>
                    </div>
                </div>
                
                <div class="p-6">
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <div class="w-6 h-6 bg-yellow-100 rounded-full flex items-center justify-center">
                                <i class="fas fa-exclamation-triangle text-yellow-600 text-sm"></i>
                            </div>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-gray-700">
                                Are you sure you want to save all stock checks? This action will update the inventory records for today.
                            </p>
                        </div>
                    </div>
                </div>
                
                <div class="flex items-center justify-end space-x-3 px-6 py-4 bg-gray-50 rounded-b-lg">
                    <button type="button" id="cancelBtn" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                        Cancel
                    </button>
                    <button type="button" id="confirmBtn" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                        <i class="fas fa-save mr-2"></i>Save Checks
                    </button>
                </div>
            </div>
        `;
        
        document.body.appendChild(modal);
        return modal;
    }

    // Show confirmation modal
    function showConfirmationModal() {
        return new Promise((resolve) => {
            const modal = createConfirmationModal();
            
            // Add fade-in animation
            modal.style.opacity = '0';
            setTimeout(() => {
                modal.style.opacity = '1';
            }, 10);
            
            const confirmBtn = modal.querySelector('#confirmBtn');
            const cancelBtn = modal.querySelector('#cancelBtn');
            
            const cleanup = () => {
                modal.style.opacity = '0';
                setTimeout(() => {
                    document.body.removeChild(modal);
                }, 200);
            };
            
            confirmBtn.addEventListener('click', () => {
                cleanup();
                resolve(true);
            });
            
            cancelBtn.addEventListener('click', () => {
                cleanup();
                resolve(false);
            });
            
            // Close on backdrop click
            modal.addEventListener('click', (e) => {
                if (e.target === modal) {
                    cleanup();
                    resolve(false);
                }
            });
            
            // Close on Escape key
            const handleEscape = (e) => {
                if (e.key === 'Escape') {
                    cleanup();
                    resolve(false);
                    document.removeEventListener('keydown', handleEscape);
                }
            };
            document.addEventListener('keydown', handleEscape);
        });
    }

    // Enhanced form submission with better UX
    document.getElementById('stockCheckForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        
        // Show loading state on submit button
        const submitBtn = this.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Saving...';
        submitBtn.disabled = true;
        
        try {
            const confirmed = await showConfirmationModal();
            
            if (confirmed) {
                // Show success feedback
                submitBtn.innerHTML = '<i class="fas fa-check mr-2"></i>Saved!';
                submitBtn.className = submitBtn.className.replace('bg-blue-600 hover:bg-blue-700', 'bg-green-600 hover:bg-green-700');
                
                // Submit the form
                setTimeout(() => {
                    this.submit();
                }, 500);
            } else {
                // Reset button state
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            }
        } catch (error) {
            // Reset button state on error
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
            console.error('Error:', error);
        }
    });

    // Add visual feedback for form interactions
    const quantityInputs = document.querySelectorAll('input[name^="quantities"]');
    quantityInputs.forEach(input => {
        // Store current stock value as data attribute
        const currentStock = input.value;
        input.setAttribute('data-current-stock', currentStock);
        
        input.addEventListener('input', function() {
            const row = this.closest('tr');
            const currentStock = parseFloat(this.getAttribute('data-current-stock') || 0);
            const newValue = parseFloat(this.value) || 0;
            
            // Remove all stock classes first
            row.classList.remove('stock-decrease', 'stock-increase', 'stock-same', 'bg-red-50', 'bg-green-50', 'bg-yellow-50');
            
            // Add visual feedback based on stock difference
            if (newValue < currentStock) {
                row.classList.add('stock-decrease', 'bg-red-50');
                // Add a subtle animation
                row.style.animation = 'pulse 0.5s ease-in-out';
                setTimeout(() => {
                    row.style.animation = '';
                }, 500);
            } else if (newValue > currentStock) {
                row.classList.add('stock-increase', 'bg-green-50');
                // Add a subtle animation
                row.style.animation = 'pulse 0.5s ease-in-out';
                setTimeout(() => {
                    row.style.animation = '';
                }, 500);
            } else {
                row.classList.add('stock-same');
            }
            
            // Add a small indicator next to the input
            const indicator = this.parentNode.querySelector('.stock-indicator') || document.createElement('span');
            indicator.className = 'stock-indicator ml-2 text-xs font-medium';
            
            if (newValue < currentStock) {
                indicator.textContent = `↓ ${(currentStock - newValue).toFixed(2)}`;
                indicator.className += ' text-red-600';
            } else if (newValue > currentStock) {
                indicator.textContent = `↑ ${(newValue - currentStock).toFixed(2)}`;
                indicator.className += ' text-green-600';
            } else {
                indicator.textContent = '✓';
                indicator.className += ' text-gray-500';
            }
            
            if (!this.parentNode.querySelector('.stock-indicator')) {
                this.parentNode.appendChild(indicator);
            }
        });
    });
    
    // Add pulse animation CSS
    const style = document.createElement('style');
    style.textContent = `
        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.02); }
        }
    `;
    document.head.appendChild(style);
</script>
@endpush
@endsection
