@php /* Online Orders Grid Partial */ @endphp
<!-- Online Orders Section -->
<div class="bg-white rounded-lg shadow-sm border border-gray-200">
    <div class="px-3 py-2 border-b border-gray-200 bg-gray-50 cursor-pointer" onclick="toggleOnlineSection()">
        <div class="flex justify-between items-center">
            <h3 class="text-sm font-semibold text-gray-900 flex items-center">
                <i class="fas fa-globe mr-1 text-green-600"></i>
                Online Orders
                <span class="ml-1 bg-green-100 text-green-800 text-xs font-medium px-1.5 py-0.5 rounded-full" id="onlineCount">0</span>
            </h3>
            <div class="flex items-center space-x-1">
                <div class="flex space-x-1">
                    <button id="onlineAllBtn" class="px-2 py-0.5 text-xs font-medium rounded-md bg-blue-100 text-blue-700 hover:bg-blue-200 transition-colors order-filter-btn active" data-section="online" data-filter="all">
                        All
                    </button>
                    <button id="onlinePaidBtn" class="px-2 py-0.5 text-xs font-medium rounded-md bg-green-100 text-green-700 hover:bg-green-200 transition-colors order-filter-btn" data-section="online" data-filter="paid">
                        Paid
                    </button>
                    <button id="onlineUnpaidBtn" class="px-2 py-0.5 text-xs font-medium rounded-md bg-red-100 text-red-700 hover:bg-red-200 transition-colors order-filter-btn" data-section="online" data-filter="unpaid">
                        Unpaid
                    </button>
                </div>
                <i class="fas fa-chevron-down ml-2 text-gray-500" id="onlineSectionIcon"></i>
            </div>
        </div>
    </div>
    <div class="p-2 hidden" id="onlineSectionContent">
        <div class="grid grid-cols-1 gap-2" id="onlineOrdersGrid">
            <!-- Orders will be populated here by JS -->
        </div>
    </div>
</div> 