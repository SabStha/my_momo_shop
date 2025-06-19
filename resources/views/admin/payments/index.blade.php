@extends('layouts.payment')

@section('content')
    <div id="paymentApp" data-branch-id="{{ $branch->id ?? 1 }}">
        @include('admin.payments.partials.header')
        @include('admin.payments.partials.status-bar')
        <div id="mainPanels" class="relative">
                <div class="flex h-full relative">
                    <!-- Orders Grid - 30% width -->
                    <div class="w-1/3 flex flex-col overflow-hidden border-r border-gray-200">
                    @include('admin.payments.partials.orders.takeaway')
                    @include('admin.payments.partials.orders.dinein')
                    @include('admin.payments.partials.orders.online')
                                </div>
                <!-- Payment Panel - 70% width, increased height -->
                <div class="w-2/3 bg-white shadow-lg border-l border-gray-200 flex flex-col h-[90vh]">
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
    @vite(['resources/js/payment-manager.js'])
@endpush 