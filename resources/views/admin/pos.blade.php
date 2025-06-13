<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="branch-id" content="{{ $branch->id }}">
    <title>POS System</title>
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- Vite Assets -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <!-- Custom CSS -->
    <style>
    html, body {
        overflow: hidden;
        height: 100%;
        margin: 0;
        padding: 0;
    }

    .scrollbar-hide::-webkit-scrollbar {
        display: none;
    }
    
    .category-btn {
        position: relative;
        overflow: hidden;
        margin-bottom: 0px !important;
        padding-bottom: 0px !important;
    }
    
    .category-btn::after {
        content: '';
        position: absolute;
        top: 50%;
        left: 50%;
        width: 5px;
        height: 5px;
        background:rgba(38, 65, 140, 0.5);
        opacity: 0;
        border-radius: 100%;
        transform: scale(1, 1) translate(-50%);
        transform-origin: 50% 50%;
    }
    
    .category-btn:active::after {
        animation: ripple 0.6s ease-out;
    }
    
    @keyframes ripple {
        0% {
            transform: scale(0, 0);
            opacity: 0.5;
        }
        100% {
            transform: scale(20, 20);
            opacity: 0;
        }
    }

    /* Ensure proper section separation */
    #productsSection {
        padding-top: 1rem;
        margin-top: 0.25rem;
    }

    /* Empty cart icon styles */
    #emptyCartIcon {
        opacity: 0.5;
        transition: opacity 0.3s ease;
        z-index: 1;
    }

    #emptyCartIcon.hidden {
        opacity: 0;
        pointer-events: none;
    }

    #cartItems {
        position: relative;
        z-index: 2;
    }

    /* Active Orders styles */
    #activeOrders {
        border-top: 1px solid #e5e7eb;
        background-color: #f9fafb;
    }

    #activeOrders .order-item {
        transition: all 0.2s ease;
        padding: 0.5rem;
        border-bottom: 1px solid #e5e7eb;
    }

    #activeOrders .order-item:hover {
        background-color: #f3f4f6;
    }

    #activeOrders .order-item:last-child {
        border-bottom: none;
    }
    </style>
</head>
<body>
@extends('layouts.pos')

@section('content')
<div class="h-screen flex flex-col overflow-hidden">
    <!-- Upper Section -->
    <div class="flex-none bg-white" style="height: 45vh;">
        <!-- Order Method Selection -->
        <div class="px-3 py-2 border-b bg-white relative z-50">
            <div class="flex items-center justify-between">
                <div class="flex space-x-1">
                    <label class="inline-flex items-center">
                        <input type="radio" name="order_method" value="dine-in" class="hidden" onchange="setOrderMethod('dine-in')">
                        <span id="dineInBtn" class="order-method-btn px-2 py-0.5 text-xs rounded-full bg-gray-200 text-gray-700 hover:bg-gray-300 cursor-pointer">
                            Dine In
                        </span>
                    </label>
                    <label class="inline-flex items-center">
                        <input type="radio" name="order_method" value="takeaway" class="hidden" onchange="setOrderMethod('takeaway')">
                        <span id="takeawayBtn" class="order-method-btn px-2 py-0.5 text-xs rounded-full bg-gray-200 text-gray-700 hover:bg-gray-300 cursor-pointer">
                            Takeaway
                        </span>
                    </label>
                    <span class="text-xs text-red-500 ml-2">* Required</span>
                </div>
                <!-- Table Selection (initially hidden) -->
                <div id="tableSelection" class="flex items-center space-x-1 hidden">
                    <label class="text-xs font-medium text-gray-700">Table:</label>
                    <select id="tableSelect" class="text-xs border rounded px-1 py-0.5 focus:outline-none focus:ring-1 focus:ring-blue-500">
                        <option value="">Select Table</option>
                    </select>
                </div>
            </div>
        </div>
        <!-- Cart Section -->
        <div id="cartSection" class="flex flex-col" style="height: calc(45vh - 120px);">
            <!-- Cart Content Area -->
            <div class="flex-1 relative">
                <!-- Empty Cart Icon -->
                <div id="emptyCartIcon" class="absolute inset-0 flex items-center justify-center">
                    <i class="fas fa-shopping-cart text-gray-300 text-5xl"></i>
                </div>
                <!-- Cart Items -->
                <div id="cartItems" class="h-full overflow-y-auto" style="max-height: calc(45vh - 180px);"></div>
            </div>
            <!-- Total Section -->
            <div class="flex-none border-t">
                <div class="flex justify-between items-center px-2 py-1">
                    <span class="font-medium text-sm">Total:</span>
                    <span class="font-semibold text-base" id="cartTotal">$0.00</span>
                </div>
            </div>
            <!-- Action Buttons -->
            <div class="border-t p-1.5 flex-none">
                <div class="space-y-0.5">
                    <div class="flex space-x-1">
                        <button id="clearCartBtn" onclick="clearCart()" class="flex-1 bg-gray-200 text-gray-800 py-1 text-xs rounded hover:bg-gray-300">Clear</button>
                        <button id="createOrderBtn" onclick="createOrder()" class="flex-1 bg-blue-500 text-white py-1 text-xs rounded hover:bg-blue-600 disabled:opacity-50 disabled:cursor-not-allowed">Create Order</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Category Filter -->
        <div class="px-2 py-1 border-b bg-white">
            <div class="flex items-center space-x-1 overflow-x-auto scrollbar-hide">
                <button onclick="filterProducts('all')" class="category-btn active whitespace-nowrap text-xs px-2 py-0.5 rounded-full bg-blue-600 text-white hover:bg-blue-700 transition-all duration-200 shadow-sm hover:shadow-md hover:scale-105 active:scale-95">
                    All Items
                </button>
                @foreach($tags as $tag)
                <button onclick="filterProducts('{{ $tag }}')" class="category-btn whitespace-nowrap text-xs px-2 py-0.5 rounded-full bg-blue-600 text-white hover:bg-blue-700 transition-all duration-200 shadow-sm hover:shadow-md hover:scale-105 active:scale-95">
                    {{ $tag }}
                </button>
                @endforeach
            </div>
        </div>

        <!-- Search Bar -->
        <div class="px-2 py-1 border-b bg-white">
            <div class="relative max-w-sm mx-auto">
                <input type="text" id="productSearch" placeholder="Search products..." 
                    class="w-full pl-6 pr-2 py-0.5 text-xs border rounded-full focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-transparent transition-all duration-200">
                <i class="fas fa-search absolute left-2 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
            </div>
        </div>
    </div>

    <!-- Lower Section -->
    <div class="flex-1 bg-gray-100" style="height: 55vh;">
        <!-- Products Grid -->
        <div id="productsSection" class="divide-y divide-gray-200 overflow-y-auto" style="height: calc(55vh - 100px);">
            <!-- Products will be rendered via JS -->
        </div>
        
        <!-- Active Orders Section -->
        <div id="activeOrders" class="border-t bg-white overflow-y-auto" style="height: 100px;">
            <div class="flex items-center justify-between px-2 py-1 border-b bg-gray-50 cursor-pointer" onclick="toggleActiveOrders()">
                <div class="flex items-center gap-2">
                    <span class="font-medium text-sm text-gray-700">Active Orders</span>
                    <span id="activeOrdersCount" class="text-xs text-gray-500">(0)</span>
                </div>
                <button class="text-gray-500 hover:text-gray-700">
                    <i class="fas fa-chevron-down" id="activeOrdersToggle"></i>
                </button>
            </div>
            <div id="activeOrdersContent" class="overflow-y-auto" style="height: calc(100px - 32px);">
                <!-- Active orders will be loaded here via JavaScript -->
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="/js/pos.js"></script>
<script>
function toggleActiveOrders() {
    const content = document.getElementById('activeOrdersContent');
    const toggle = document.getElementById('activeOrdersToggle');
    const orders = document.getElementById('activeOrders');
    
    if (content.style.display === 'none') {
        content.style.display = 'block';
        orders.style.height = '100px';
        toggle.className = 'fas fa-chevron-down';
    } else {
        content.style.display = 'none';
        orders.style.height = '32px';
        toggle.className = 'fas fa-chevron-up';
    }
}
</script>
@endpush

<!-- Edit Order Modal -->
<div id="editOrderModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <h3 class="text-lg font-medium leading-6 text-gray-900 mb-4">Edit Order #<span id="editOrderId"></span></h3>
            <div id="editOrderItems" class="space-y-2 max-h-60 overflow-y-auto">
                <!-- Order items will be rendered here -->
            </div>
            <div class="mt-4 flex justify-end space-x-2">
                <button onclick="closeEditModal()" class="px-4 py-2 bg-gray-200 text-gray-800 text-sm rounded hover:bg-gray-300">
                    Cancel
                </button>
                <button onclick="saveOrderChanges()" class="px-4 py-2 bg-blue-500 text-white text-sm rounded hover:bg-blue-600">
                    Save Changes
                </button>
            </div>
        </div>
    </div>
</div>
@endsection
</body>
</html>