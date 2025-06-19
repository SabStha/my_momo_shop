@php /* Dine-in Orders Grid Partial */ @endphp
<!-- Dine-in Orders Section -->
<div class="bg-white rounded-lg shadow-sm border border-gray-200">
    <div class="px-4 py-3 border-b border-gray-200 bg-gray-50">
        <div class="flex justify-between items-center">
            <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                <i class="fas fa-chair mr-2 text-purple-600"></i>
                Dine-in Orders
                <span class="ml-2 bg-purple-100 text-purple-800 text-xs font-medium px-2 py-1 rounded-full" id="dineinCount">0</span>
            </h3>
            <div class="flex space-x-2">
                <button id="dineinAllBtn" class="px-3 py-1 text-xs font-medium rounded-md bg-blue-100 text-blue-800 hover:bg-blue-200 transition-colors order-filter-btn active" data-section="dinein" data-filter="all">
                    All
                </button>
                <button id="dineinPaidBtn" class="px-3 py-1 text-xs font-medium rounded-md bg-gray-100 text-gray-600 hover:bg-gray-200 transition-colors order-filter-btn" data-section="dinein" data-filter="paid">
                    Paid
                </button>
                <button id="dineinUnpaidBtn" class="px-3 py-1 text-xs font-medium rounded-md bg-gray-100 text-gray-600 hover:bg-gray-200 transition-colors order-filter-btn" data-section="dinein" data-filter="unpaid">
                    Unpaid
                </button>
            </div>
        </div>
    </div>
    <div class="p-4">
        <div class="grid grid-cols-1 gap-3" id="dineInOrdersGrid">
            <!-- Orders will be populated here by JS -->
        </div>
    </div>
</div> 