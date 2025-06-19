@php /* Takeaway Orders Grid Partial */ @endphp
<!-- Takeaway Orders Section -->
<div class="bg-white rounded-lg shadow-sm border border-gray-200">
    <div class="px-4 py-3 border-b border-gray-200 bg-gray-50">
        <div class="flex justify-between items-center">
            <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                <i class="fas fa-utensils mr-2 text-orange-600"></i>
                Takeaway Orders
                <span class="ml-2 bg-orange-100 text-orange-800 text-xs font-medium px-2 py-1 rounded-full" id="takeawayCount">0</span>
            </h3>
            <div class="flex space-x-2">
                <button id="takeawayAllBtn" class="px-3 py-1 text-xs font-medium rounded-md bg-blue-100 text-blue-800 hover:bg-blue-200 transition-colors order-filter-btn active" data-section="takeaway" data-filter="all">
                    All
                </button>
                <button id="takeawayPaidBtn" class="px-3 py-1 text-xs font-medium rounded-md bg-gray-100 text-gray-600 hover:bg-gray-200 transition-colors order-filter-btn" data-section="takeaway" data-filter="paid">
                    Paid
                </button>
                <button id="takeawayUnpaidBtn" class="px-3 py-1 text-xs font-medium rounded-md bg-gray-100 text-gray-600 hover:bg-gray-200 transition-colors order-filter-btn" data-section="takeaway" data-filter="unpaid">
                    Unpaid
                </button>
            </div>
        </div>
    </div>
    <div class="p-4">
        <div class="grid grid-cols-1 gap-3" id="takeawayOrdersGrid">
            <!-- Orders will be populated here by JS -->
        </div>
    </div>
</div> 