@extends('layouts.admin')

@section('title', 'Weekly Stock Check - Advanced Audit')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">
    <!-- Header with Audit Session Info -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-2xl font-semibold text-gray-800">Weekly Stock Check - Advanced Audit</h2>
            <p class="text-sm text-gray-500 mt-1">📅 Week of {{ now()->startOfWeek()->format('F j, Y') }}</p>
            @if($branch)
                <p class="text-sm text-gray-600 mt-1">📍 Branch: {{ $branch->name }}</p>
            @endif
        </div>
        <div class="flex items-center space-x-3 mt-4 sm:mt-0">
            <a href="{{ route('admin.inventory.audit-reports.index', ['type' => 'weekly', 'branch' => $branch ? $branch->id : null]) }}"
               class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-md hover:bg-blue-700 focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200">
                <i class="fas fa-chart-bar"></i> View Reports
            </a>
            <a href="{{ route('admin.inventory.audit-reports.sessions', ['type' => 'weekly', 'branch' => $branch ? $branch->id : null]) }}"
               class="inline-flex items-center gap-2 px-4 py-2 bg-purple-600 text-white text-sm font-medium rounded-md hover:bg-purple-700 focus:ring-2 focus:ring-offset-2 focus:ring-purple-500 transition-all duration-200">
                <i class="fas fa-clock"></i> Audit Sessions
            </a>
            <button type="button" id="startAuditBtn" class="inline-flex items-center gap-2 px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-md hover:bg-green-700 focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-all duration-200">
                <i class="fas fa-play"></i> Start Audit Session
            </button>
            <button type="button" id="exportAuditBtn" class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-md hover:bg-blue-700 focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200">
                <i class="fas fa-download"></i> Export Report
            </button>
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
                <div class="w-16 h-16 bg-yellow-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-box-open text-yellow-600 text-2xl"></i>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">No Inventory Items Found</h3>
                <p class="text-gray-600 mb-6">
                    @if($branch)
                        There are no inventory items in <strong>{{ $branch->name }}</strong> to perform weekly stock checks on.
                    @else
                        There are no inventory items to perform weekly stock checks on.
                    @endif
                </p>
            </div>
            
            <div class="flex flex-col sm:flex-row gap-3 justify-center">
                <a href="{{ route('admin.inventory.create', ['branch' => $branch ? $branch->id : null]) }}" 
                   class="inline-flex items-center gap-2 px-4 py-2 bg-yellow-600 text-white text-sm font-medium rounded-md hover:bg-yellow-700 focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500 transition-all duration-200">
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
        <!-- Search and Filter Controls -->
        <div class="bg-white shadow-md rounded-lg p-6">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Search Items</label>
                    <input type="text" id="searchInput" placeholder="Search by name or SKU..." 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Category Filter</label>
                    <select id="categoryFilter" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500">
                        <option value="">All Categories</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Status Filter</label>
                    <select id="statusFilter" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500">
                        <option value="">All Items</option>
                        <option value="discrepancy">With Discrepancies</option>
                        <option value="damaged">Damaged Items</option>
                        <option value="missing">Missing Items</option>
                        <option value="match">Matching Items</option>
                    </select>
                </div>
                <div class="flex items-end">
                    <button type="button" id="clearFiltersBtn" class="w-full px-4 py-2 bg-gray-500 text-white text-sm font-medium rounded-md hover:bg-gray-600 focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-all duration-200">
                        Clear Filters
                    </button>
                </div>
            </div>
        </div>

        <!-- Audit Summary Stats -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="bg-white shadow-md rounded-lg p-4">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                            <i class="fas fa-boxes text-blue-600"></i>
                        </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Total Items</p>
                        <p class="text-lg font-semibold text-gray-900" id="totalItemsCount">{{ $items->count() }}</p>
                    </div>
                </div>
            </div>
            <div class="bg-white shadow-md rounded-lg p-4">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-red-100 rounded-full flex items-center justify-center">
                            <i class="fas fa-exclamation-triangle text-red-600"></i>
                        </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Discrepancies</p>
                        <p class="text-lg font-semibold text-gray-900" id="discrepanciesCount">0</p>
                    </div>
                </div>
            </div>
            <div class="bg-white shadow-md rounded-lg p-4">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-orange-100 rounded-full flex items-center justify-center">
                            <i class="fas fa-times-circle text-orange-600"></i>
                        </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Damaged/Missing</p>
                        <p class="text-lg font-semibold text-gray-900" id="damagedMissingCount">0</p>
                    </div>
                </div>
            </div>
            <div class="bg-white shadow-md rounded-lg p-4">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                            <i class="fas fa-check-circle text-green-600"></i>
                        </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Completed</p>
                        <p class="text-lg font-semibold text-gray-900" id="completedCount">0%</p>
                    </div>
                </div>
            </div>
        </div>

        <form action="{{ route('admin.inventory.weekly-checks.store') }}" method="POST" id="weeklyStockCheckForm" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="branch_id" value="{{ $branch ? $branch->id : '' }}">
            <input type="hidden" name="audit_session_id" id="auditSessionId" value="{{ Str::uuid() }}">

            <!-- Grouped by Category -->
            @foreach($items->groupBy('category.name') as $categoryName => $categoryItems)
                <div class="bg-white shadow-md rounded-lg overflow-hidden audit-category" data-category="{{ $categoryItems->first()->category_id }}">
                    <div class="bg-yellow-50 px-6 py-4 border-b border-yellow-200">
                        <h3 class="text-lg font-semibold text-yellow-800">
                            <i class="fas fa-folder mr-2"></i>{{ $categoryName ?? 'Uncategorized' }}
                            <span class="text-sm font-normal text-yellow-600 ml-2">({{ $categoryItems->count() }} items)</span>
                        </h3>
                    </div>
                    
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Item</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">System Stock</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actual Count</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Discrepancy</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Notes</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($categoryItems as $item)
                                    <tr class="audit-item-row hover:bg-gray-50" data-item-id="{{ $item->id }}" data-category="{{ $item->category_id }}">
                                        <td class="px-4 py-4">
                                            <div class="flex items-center">
                                                <div class="flex-shrink-0 h-10 w-10">
                                                    @if($item->image_path)
                                                        <img class="h-10 w-10 rounded-full object-cover" src="{{ asset('storage/' . $item->image_path) }}" alt="{{ $item->name }}">
                                                    @else
                                                        <div class="h-10 w-10 rounded-full bg-gray-200 flex items-center justify-center">
                                                            <i class="fas fa-box text-gray-400"></i>
                                                        </div>
                                                    @endif
                                                </div>
                                                <div class="ml-4">
                                                    <div class="text-sm font-medium text-gray-900">{{ $item->name }}</div>
                                                    <div class="text-sm text-gray-500">{{ $item->code }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-4 py-4">
                                            <div class="text-sm text-gray-900">{{ $item->current_stock }} {{ $item->unit }}</div>
                                            <div class="text-xs text-gray-500">Rs. {{ number_format($item->current_stock * $item->unit_price, 2) }}</div>
                                        </td>
                                        <td class="px-4 py-4">
                                            <input type="number" 
                                                   name="quantities[{{ $item->id }}]"
                                                   value="{{ $item->weeklyChecks->first()?->quantity_checked ?? $item->current_stock }}"
                                                   step="0.01"
                                                   min="0"
                                                   required
                                                   class="w-20 px-2 py-1 text-sm border rounded-md focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500 actual-count-input"
                                                   data-item-id="{{ $item->id }}"
                                                   data-system-stock="{{ $item->current_stock }}"
                                                   data-unit-price="{{ $item->unit_price }}"
                                                   data-unit="{{ $item->unit }}">
                                            <input type="hidden" name="item_ids[]" value="{{ $item->id }}">
                                            <input type="hidden" name="system_stocks[{{ $item->id }}]" value="{{ $item->current_stock }}">
                                        </td>
                                        <td class="px-4 py-4">
                                            <div class="discrepancy-display" id="discrepancy-{{ $item->id }}">
                                                <span class="text-sm text-gray-500">-</span>
                                            </div>
                                        </td>
                                        <td class="px-4 py-4">
                                            <div class="flex items-center space-x-2">
                                                <label class="flex items-center">
                                                    <input type="checkbox" 
                                                           name="is_damaged[{{ $item->id }}]" 
                                                           value="1"
                                                           class="rounded border-gray-300 text-yellow-600 focus:ring-yellow-500"
                                                           {{ $item->weeklyChecks->first()?->is_damaged ? 'checked' : '' }}>
                                                    <span class="ml-2 text-xs text-red-600">Damaged</span>
                                                </label>
                                                <label class="flex items-center">
                                                    <input type="checkbox" 
                                                           name="is_missing[{{ $item->id }}]" 
                                                           value="1"
                                                           class="rounded border-gray-300 text-yellow-600 focus:ring-yellow-500"
                                                           {{ $item->weeklyChecks->first()?->is_missing ? 'checked' : '' }}>
                                                    <span class="ml-2 text-xs text-orange-600">Missing</span>
                                                </label>
                                            </div>
                                        </td>
                                        <td class="px-4 py-4">
                                            <textarea name="audit_notes[{{ $item->id }}]" 
                                                      placeholder="Add audit notes..."
                                                      rows="2"
                                                      class="w-full px-2 py-1 text-sm border rounded-md focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500">{{ $item->weeklyChecks->first()?->audit_notes }}</textarea>
                                        </td>
                                        <td class="px-4 py-4">
                                            <div class="flex items-center space-x-2">
                                                <label class="cursor-pointer">
                                                    <input type="file" 
                                                           name="images[{{ $item->id }}]" 
                                                           accept="image/*"
                                                           class="hidden image-upload"
                                                           data-item-id="{{ $item->id }}">
                                                    <i class="fas fa-camera text-blue-600 hover:text-blue-800 cursor-pointer"></i>
                                                </label>
                                                <div class="image-preview" id="image-preview-{{ $item->id }}"></div>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endforeach

            <div class="flex justify-between items-center mt-6">
                <div class="text-sm text-gray-600">
                    <span id="auditProgress">0 of {{ $items->count() }} items completed</span>
                </div>
                <div class="flex space-x-3">
                    <button type="button" id="saveWeeklyChecksBtn" class="inline-flex items-center gap-2 px-6 py-3 bg-yellow-600 text-white text-sm font-medium rounded-md hover:bg-yellow-700 focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500 transition-all duration-200">
                        <i class="fas fa-save"></i> Save All Weekly Checks
                    </button>
                </div>
            </div>
        </form>
    @endif
</div>

<!-- Enhanced Confirmation Modal -->
<div id="weeklyConfirmationModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full transform transition-all duration-300 scale-95 opacity-0" id="weeklyModalContent">
            <!-- Header -->
            <div class="flex items-center justify-between p-6 border-b border-gray-200">
                <div class="flex items-center space-x-3">
                    <div class="flex-shrink-0">
                        <div class="w-10 h-10 bg-yellow-100 rounded-full flex items-center justify-center">
                            <i class="fas fa-exclamation-triangle text-yellow-600 text-lg"></i>
                        </div>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">Confirm Weekly Stock Check</h3>
                        <p class="text-sm text-gray-500">Review before saving</p>
                    </div>
                </div>
                <button type="button" class="text-gray-400 hover:text-gray-600 transition-colors duration-200" onclick="closeWeeklyModal()">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>

            <!-- Content -->
            <div class="p-6">
                <div class="mb-4">
                    <p class="text-gray-700 mb-3">Are you sure you want to save all weekly stock checks?</p>
                    <div class="bg-yellow-50 border border-yellow-200 rounded-md p-4">
                        <div class="flex items-start space-x-3">
                            <i class="fas fa-info-circle text-yellow-600 mt-0.5"></i>
                            <div class="text-sm text-yellow-800">
                                <p class="font-medium mb-1">This action will:</p>
                                <ul class="list-disc list-inside space-y-1 text-xs">
                                    <li>Record the physical quantities you've counted</li>
                                    <li>Save any notes you've added about discrepancies</li>
                                    <li>Create a permanent audit trail of this weekly check</li>
                                    <li>Store the check date and time for historical tracking</li>
                                    <li>Allow comparison with previous weekly checks</li>
                                </ul>
                                <p class="text-xs text-yellow-700 mt-2 italic">
                                    Note: This creates a record of your physical count but does not automatically update inventory levels. 
                                    Use this data to identify discrepancies and adjust stock levels manually if needed.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Stock Difference Summary -->
                <div id="weeklyStockSummary" class="mb-4 bg-gray-50 rounded-md p-3">
                    <h4 class="text-sm font-medium text-gray-700 mb-2">Weekly Stock Check Summary:</h4>
                    <div class="text-xs text-gray-600 space-y-1">
                        <div>Items to check: <span id="weeklyTotalItems" class="font-medium">0</span></div>
                        <div>Items with discrepancies: <span id="weeklyDiffItems" class="font-medium text-orange-600">0</span></div>
                        <div>Total discrepancy value: <span id="weeklyTotalDiffValue" class="font-medium">Rs. 0.00</span></div>
                        <div>Damaged/Missing items: <span id="weeklyDamagedMissing" class="font-medium text-red-600">0</span></div>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="flex items-center justify-end space-x-3 p-6 border-t border-gray-200 bg-gray-50 rounded-b-lg">
                <button type="button" onclick="closeWeeklyModal()" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-all duration-200">
                    Cancel
                </button>
                <button type="button" id="weeklyConfirmSaveBtn" onclick="submitWeeklyForm()" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-yellow-600 border border-transparent rounded-md hover:bg-yellow-700 focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500 transition-all duration-200">
                    <i class="fas fa-save"></i>
                    <span>Save Weekly Checks</span>
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Advanced Audit System
    let auditSessionId = '{{ Str::uuid() }}';
    let auditStarted = false;

    // Initialize audit session
    function startAuditSession() {
        auditSessionId = '{{ Str::uuid() }}';
        document.getElementById('auditSessionId').value = auditSessionId;
        auditStarted = true;
        
        // Update UI
        document.getElementById('startAuditBtn').innerHTML = '<i class="fas fa-pause"></i> Pause Audit';
        document.getElementById('startAuditBtn').classList.remove('bg-green-600', 'hover:bg-green-700');
        document.getElementById('startAuditBtn').classList.add('bg-orange-600', 'hover:bg-orange-700');
        
        // Start tracking
        updateAuditProgress();
    }

    // Search and filter functionality
    document.getElementById('searchInput').addEventListener('input', function() {
        filterItems();
    });

    document.getElementById('categoryFilter').addEventListener('change', function() {
        filterItems();
    });

    document.getElementById('statusFilter').addEventListener('change', function() {
        filterItems();
    });

    document.getElementById('clearFiltersBtn').addEventListener('click', function() {
        document.getElementById('searchInput').value = '';
        document.getElementById('categoryFilter').value = '';
        document.getElementById('statusFilter').value = '';
        filterItems();
    });

    function filterItems() {
        const searchTerm = document.getElementById('searchInput').value.toLowerCase();
        const categoryFilter = document.getElementById('categoryFilter').value;
        const statusFilter = document.getElementById('statusFilter').value;

        document.querySelectorAll('.audit-item-row').forEach(row => {
            const itemName = row.querySelector('td:first-child').textContent.toLowerCase();
            const itemCode = row.querySelector('td:first-child .text-gray-500').textContent.toLowerCase();
            const category = row.dataset.category;
            const hasDiscrepancy = row.classList.contains('discrepancy-row');
            const isDamaged = row.querySelector('input[name*="is_damaged"]').checked;
            const isMissing = row.querySelector('input[name*="is_missing"]').checked;

            let showRow = true;

            // Search filter
            if (searchTerm && !itemName.includes(searchTerm) && !itemCode.includes(searchTerm)) {
                showRow = false;
            }

            // Category filter
            if (categoryFilter && category !== categoryFilter) {
                showRow = false;
            }

            // Status filter
            if (statusFilter) {
                switch(statusFilter) {
                    case 'discrepancy':
                        if (!hasDiscrepancy) showRow = false;
                        break;
                    case 'damaged':
                        if (!isDamaged) showRow = false;
                        break;
                    case 'missing':
                        if (!isMissing) showRow = false;
                        break;
                    case 'match':
                        if (hasDiscrepancy || isDamaged || isMissing) showRow = false;
                        break;
                }
            }

            row.style.display = showRow ? '' : 'none';
        });

        // Update category visibility
        document.querySelectorAll('.audit-category').forEach(category => {
            const visibleItems = category.querySelectorAll('.audit-item-row:not([style*="display: none"])');
            category.style.display = visibleItems.length > 0 ? '' : 'none';
        });
    }

    // Real-time discrepancy calculation
    document.querySelectorAll('.actual-count-input').forEach(input => {
        input.addEventListener('input', function() {
            calculateDiscrepancy(this);
            updateAuditProgress();
            updateSummaryStats();
        });
    });

    function calculateDiscrepancy(input) {
        const itemId = input.dataset.itemId;
        const systemStock = parseFloat(input.dataset.systemStock) || 0;
        const actualCount = parseFloat(input.value) || 0;
        const unitPrice = parseFloat(input.dataset.unitPrice) || 0;
        const unit = input.dataset.unit;

        const discrepancy = actualCount - systemStock;
        const discrepancyValue = discrepancy * unitPrice;

        const displayElement = document.getElementById(`discrepancy-${itemId}`);
        const row = input.closest('tr');

        if (discrepancy !== 0) {
            const sign = discrepancy > 0 ? '+' : '';
            const color = discrepancy > 0 ? 'text-green-600' : 'text-red-600';
            const bgColor = discrepancy > 0 ? 'bg-green-50' : 'bg-red-50';
            
            displayElement.innerHTML = `
                <div class="text-sm ${color} font-medium">
                    ${sign}${discrepancy} ${unit}
                </div>
                <div class="text-xs ${color}">
                    ${sign}Rs. ${Math.abs(discrepancyValue).toFixed(2)}
                </div>
            `;
            
            row.classList.add('discrepancy-row', bgColor);
        } else {
            displayElement.innerHTML = '<span class="text-sm text-gray-500">Match</span>';
            row.classList.remove('discrepancy-row', 'bg-green-50', 'bg-red-50');
        }
    }

    // Image upload handling
    document.querySelectorAll('.image-upload').forEach(input => {
        input.addEventListener('change', function() {
            const itemId = this.dataset.itemId;
            const file = this.files[0];
            const previewElement = document.getElementById(`image-preview-${itemId}`);

            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    previewElement.innerHTML = `
                        <img src="${e.target.result}" alt="Preview" class="w-8 h-8 rounded object-cover">
                    `;
                };
                reader.readAsDataURL(file);
            }
        });
    });

    // Update audit progress
    function updateAuditProgress() {
        const totalItems = document.querySelectorAll('.actual-count-input').length;
        const completedItems = document.querySelectorAll('.actual-count-input[value]:not([value=""])').length;
        const percentage = totalItems > 0 ? Math.round((completedItems / totalItems) * 100) : 0;

        document.getElementById('auditProgress').textContent = `${completedItems} of ${totalItems} items completed`;
        document.getElementById('completedCount').textContent = `${percentage}%`;
    }

    // Update summary statistics
    function updateSummaryStats() {
        const totalItems = document.querySelectorAll('.actual-count-input').length;
        const discrepancyItems = document.querySelectorAll('.discrepancy-row').length;
        const damagedItems = document.querySelectorAll('input[name*="is_damaged"]:checked').length;
        const missingItems = document.querySelectorAll('input[name*="is_missing"]:checked').length;

        document.getElementById('totalItemsCount').textContent = totalItems;
        document.getElementById('discrepanciesCount').textContent = discrepancyItems;
        document.getElementById('damagedMissingCount').textContent = damagedItems + missingItems;
    }

    // Enhanced confirmation modal for weekly checks
    function showWeeklyModal() {
        const modal = document.getElementById('weeklyConfirmationModal');
        const content = document.getElementById('weeklyModalContent');
        
        modal.classList.remove('hidden');
        
        // Animate in
        setTimeout(() => {
            content.classList.remove('scale-95', 'opacity-0');
            content.classList.add('scale-100', 'opacity-100');
        }, 10);
        
        // Calculate summary statistics
        calculateWeeklySummary();
    }

    function closeWeeklyModal() {
        const modal = document.getElementById('weeklyConfirmationModal');
        const content = document.getElementById('weeklyModalContent');
        
        // Animate out
        content.classList.remove('scale-100', 'opacity-100');
        content.classList.add('scale-95', 'opacity-0');
        
        setTimeout(() => {
            modal.classList.add('hidden');
        }, 300);
    }

    function calculateWeeklySummary() {
        const inputs = document.querySelectorAll('.actual-count-input');
        let totalItems = inputs.length;
        let diffItems = 0;
        let totalDiffValue = 0;
        let damagedMissing = 0;

        inputs.forEach(input => {
            const systemStock = parseFloat(input.dataset.systemStock) || 0;
            const actualCount = parseFloat(input.value) || 0;
            const unitPrice = parseFloat(input.dataset.unitPrice) || 0;
            const difference = Math.abs(actualCount - systemStock);
            
            if (difference > 0) {
                diffItems++;
                totalDiffValue += difference * unitPrice;
            }
        });

        // Count damaged/missing items
        damagedMissing = document.querySelectorAll('input[name*="is_damaged"]:checked, input[name*="is_missing"]:checked').length;

        document.getElementById('weeklyTotalItems').textContent = totalItems;
        document.getElementById('weeklyDiffItems').textContent = diffItems;
        document.getElementById('weeklyTotalDiffValue').textContent = `Rs. ${totalDiffValue.toFixed(2)}`;
        document.getElementById('weeklyDamagedMissing').textContent = damagedMissing;
    }

    function submitWeeklyForm() {
        const btn = document.getElementById('weeklyConfirmSaveBtn');
        
        // Show loading state
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
        btn.disabled = true;
        
        // Submit the form
        document.getElementById('weeklyStockCheckForm').submit();
    }

    // Event listeners
    document.getElementById('startAuditBtn')?.addEventListener('click', function(e) {
        e.preventDefault();
        if (!auditStarted) {
            startAuditSession();
        }
    });

    document.getElementById('saveWeeklyChecksBtn')?.addEventListener('click', function(e) {
        e.preventDefault();
        showWeeklyModal();
    });

    // Close modal on backdrop click
    document.getElementById('weeklyConfirmationModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeWeeklyModal();
        }
    });

    // Close modal on Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeWeeklyModal();
        }
    });

    // Initialize on page load
    document.addEventListener('DOMContentLoaded', function() {
        updateAuditProgress();
        updateSummaryStats();
        
        // Calculate initial discrepancies
        document.querySelectorAll('.actual-count-input').forEach(input => {
            calculateDiscrepancy(input);
        });
    });
</script>
@endpush
@endsection
