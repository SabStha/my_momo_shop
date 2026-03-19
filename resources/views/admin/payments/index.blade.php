@extends('layouts.payment')
{{-- Cache bust: {{ microtime(true) }} --}}

@section('content')
    <div id="paymentApp" data-branch-id="{{ $branch->id ?? 1 }}"
         style="height:100vh;display:flex;flex-direction:column;overflow:hidden;">
        @include('admin.payments.partials.header')

        <!-- Drawer closed banner (JS toggles visibility) -->
        <div id="drawerStatusBanner" class="hidden bg-red-50 border-b border-red-200 px-4 py-3" style="flex-shrink:0;">
            <div class="flex items-center justify-center">
                <div class="flex items-center">
                    <i class="fas fa-store-slash text-red-600 mr-2"></i>
                    <span class="text-red-800 font-medium">Cash Drawer Closed</span>
                    <span class="text-red-600 ml-2">- Order history is hidden</span>
                </div>
            </div>
        </div>

        <style>
            /* Responsive two-column layout — avoids Tailwind JIT arbitrary-value compilation */
            #ordersListPanel    { width: 100%; }
            #paymentDetailsPanel { display: none; }
            @@media (min-width: 768px) {
                #ordersListPanel     { width: 30%; flex-shrink: 0; }
                #paymentDetailsPanel { display: flex; flex: 1; }
            }
        </style>

        <div id="mainPanels" style="flex:1;overflow:hidden;min-height:0;">
            <div style="display:flex;height:100%;">
                <!-- Orders List -->
                <div id="ordersListPanel" class="flex flex-col border-r border-gray-200"
                     style="height:100%;overflow-y:auto;">
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

                <!-- Payment Panel (70% desktop, full mobile — shown via CSS/JS) -->
                <div id="paymentDetailsPanel" class="flex-col"
                     style="height:100%;overflow:hidden;">
                    @include('admin.payments.partials.payment-panel')
                </div>
            </div>
        </div>
        @include('partials.payment-modals')
        @include('admin.payments.partials.modals.cash-drawer')
        @include('admin.payments.partials.modals.settlement')
        @include('admin.payments.partials.modals.alert-settings')
        @include('admin.payments.partials.modals.cash-adjustment')
        @include('admin.payments.partials.modals.physical-drawer-denominations')
        @include('admin.payments.partials.modals.mark-ready')
        @include('admin.payments.partials.modals.mark-preparing')
        <div id="toastContainer" class="fixed top-6 right-6 z-50 space-y-2"></div>
@endsection

@push('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <script>
        // Order section toggle functions
        function toggleDineInSection() {
            const content = document.getElementById('dineInSectionContent');
            const icon = document.getElementById('dineInSectionIcon');
            if (!content || !icon) return;
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
            if (!content || !icon) return;
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
            if (!content || !icon) return;
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