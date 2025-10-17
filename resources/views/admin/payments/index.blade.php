@extends('layouts.payment')
{{-- Cache bust: {{ microtime(true) }} --}}

@section('content')
    <div id="paymentApp" data-branch-id="{{ $branch->id ?? 1 }}">
        @include('admin.payments.partials.header')
        @include('admin.payments.partials.status-bar')
        
        <!-- Drawer Status Banner (hidden by default) -->
        <div id="drawerStatusBanner" class="hidden bg-red-50 border-b border-red-200 px-4 py-3">
            <div class="flex items-center justify-center">
                <div class="flex items-center">
                    <i class="fas fa-store-slash text-red-600 mr-2"></i>
                    <span class="text-red-800 font-medium">Cash Drawer Closed</span>
                    <span class="text-red-600 ml-2">- Order history is hidden</span>
                </div>
            </div>
        </div>
        
        <div id="mainPanels" class="relative">
                <div class="flex h-full relative">
                    <!-- Orders Grid - 30% width -->
                    <div class="w-1/3 flex flex-col overflow-hidden border-r border-gray-200">
                        <!-- Authentication Loading State -->
                        <div id="ordersLoadingState" class="flex-1 flex items-center justify-center bg-gray-50">
                            <div class="text-center">
                                <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mx-auto mb-4"></div>
                                <p class="text-gray-600 text-sm">Authenticating payment access...</p>
                                <p class="text-gray-400 text-xs mt-1">Orders will load after authentication</p>
                            </div>
                        </div>
                        
                        <!-- Orders Sections (hidden initially) -->
                        <div id="ordersSections" class="hidden">
                            @include('admin.payments.partials.orders.dinein')
                            @include('admin.payments.partials.orders.takeaway')
                            @include('admin.payments.partials.orders.online')
                        </div>
                    </div>
                <!-- Payment Panel - 70% width, increased height -->
                @include('admin.payments.partials.payment-panel')
                </div>
            </div>
        @include('partials.payment-modals')
        @include('admin.payments.partials.modals.cash-drawer')
        @include('admin.payments.partials.modals.settlement')
        @include('admin.payments.partials.modals.alert-settings')
        @include('admin.payments.partials.modals.cash-adjustment')
        @include('admin.payments.partials.modals.physical-drawer-denominations')
        @include('admin.payments.partials.modals.mark-ready')
        <div id="toastContainer" class="fixed top-6 right-6 z-50 space-y-2"></div>
        <!-- Cash Drawer Actions Dropdown (fixed bottom left) -->
        <div class="fixed bottom-6 left-6 z-50">
            <div class="relative group">
                <button class="px-4 py-2 bg-yellow-500 text-white rounded-md shadow-lg hover:bg-yellow-600 focus:outline-none focus:ring-2 focus:ring-yellow-400 flex items-center">
                    <i class="fas fa-cash-register mr-2"></i> Cash Drawer <i class="fas fa-chevron-up ml-2"></i>
                                                    </button>
                <div class="absolute left-0 bottom-full mb-2 w-56 bg-white rounded-md shadow-lg border border-gray-200 opacity-0 group-hover:opacity-100 group-focus-within:opacity-100 pointer-events-none group-hover:pointer-events-auto group-focus-within:pointer-events-auto transition-opacity duration-200">
                    <button onclick="openPhysicalCashDrawer()" class="w-full text-left px-4 py-2 hover:bg-yellow-100 text-gray-800 flex items-center">
                        <i class="fas fa-door-open mr-2"></i> Open Physical Cash Drawer
                                                </button>
                    <!-- Add more actions here if needed -->
                                            </div>
                                        </div>
                                    </div>
                                </div>
@endsection

@push('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <script>
        // Order section toggle functions
        function toggleDineInSection() {
            const content = document.getElementById('dineInSectionContent');
            const icon = document.getElementById('dineInSectionIcon');
            
            if (content.classList.contains('hidden')) {
                content.classList.remove('hidden');
                icon.classList.remove('fa-chevron-down');
                icon.classList.add('fa-chevron-up');
            } else {
                content.classList.add('hidden');
                icon.classList.remove('fa-chevron-up');
                icon.classList.add('fa-chevron-down');
            }
        }

        function toggleTakeawaySection() {
            const content = document.getElementById('takeawaySectionContent');
            const icon = document.getElementById('takeawaySectionIcon');
            
            if (content.classList.contains('hidden')) {
                content.classList.remove('hidden');
                icon.classList.remove('fa-chevron-down');
                icon.classList.add('fa-chevron-up');
            } else {
                content.classList.add('hidden');
                icon.classList.remove('fa-chevron-up');
                icon.classList.add('fa-chevron-down');
            }
        }

        function toggleOnlineSection() {
            const content = document.getElementById('onlineSectionContent');
            const icon = document.getElementById('onlineSectionIcon');
            
            if (content.classList.contains('hidden')) {
                content.classList.remove('hidden');
                icon.classList.remove('fa-chevron-down');
                icon.classList.add('fa-chevron-up');
            } else {
                content.classList.add('hidden');
                icon.classList.remove('fa-chevron-up');
                icon.classList.add('fa-chevron-down');
            }
        }

        // Note: SoundManager class is now in payment-manager.js (removed duplicate to fix error)
        // Sound control functions are also in payment-manager.js and available globally

        // Modal close functions
        function closeErrorModal() {
            const modal = document.getElementById('errorModal');
            if (modal) {
                modal.classList.add('hidden');
            }
        }

        function closeSuccessModal() {
            const modal = document.getElementById('successModal');
            if (modal) {
                modal.classList.add('hidden');
            }
        }

        // Wire up modal close buttons when DOM is loaded
        document.addEventListener('DOMContentLoaded', function() {
            const errorModalClose = document.getElementById('errorModalClose');
            if (errorModalClose) {
                errorModalClose.addEventListener('click', closeErrorModal);
            }
            
            const successModalClose = document.getElementById('successModalClose');
            if (successModalClose) {
                successModalClose.addEventListener('click', closeSuccessModal);
            }

            // Close error modal when clicking outside
            const errorModal = document.getElementById('errorModal');
            if (errorModal) {
                errorModal.addEventListener('click', function(e) {
                    if (e.target === errorModal) {
                        closeErrorModal();
                    }
                });
            }

            // Close modals with Escape key
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    closeErrorModal();
                    closeSuccessModal();
                }
            });
        });
    </script>
@endpush 