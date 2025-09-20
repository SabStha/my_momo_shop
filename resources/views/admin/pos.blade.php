<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="branch-id" content="{{ $branch->id }}">
    <title>POS System</title>
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- Vite Assets -->
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
    <!-- Custom CSS -->
    <style>
    :root {
        --primary-color: #2563eb;
        --primary-dark: #1d4ed8;
        --secondary-color: #64748b;
        --success-color: #10b981;
        --warning-color: #f59e0b;
        --danger-color: #ef4444;
        --background-color: #f8fafc;
        --card-background: #ffffff;
        --border-color: #e2e8f0;
        --text-primary: #1e293b;
        --text-secondary: #64748b;
        --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
        --shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
        --shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);
        --shadow-xl: 0 20px 25px -5px rgb(0 0 0 / 0.1), 0 8px 10px -6px rgb(0 0 0 / 0.1);
        --border-radius: 0.75rem;
        --border-radius-sm: 0.5rem;
        --border-radius-lg: 1rem;
    }

    html, body {
        overflow: auto;
        height: auto;
        min-height: 100vh;
        margin: 0;
        padding: 0;
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        -webkit-overflow-scrolling: touch;
    }

    .pos-container {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(10px);
        border-radius: var(--border-radius-lg);
        box-shadow: var(--shadow-xl);
        margin: 0.5rem;
        min-height: calc(100vh - 2rem);
        overflow: visible;
        border: 1px solid rgba(255, 255, 255, 0.2);
        display: flex;
        flex-direction: column;
    }

    /* Mobile-first responsive design */
    @media (max-width: 768px) {
        .pos-container {
            margin: 0;
            min-height: 100vh;
            border-radius: 0;
        }
        
        html, body {
            background: #f8fafc;
            overflow: auto;
        }
    }

    .scrollbar-hide::-webkit-scrollbar {
        display: none;
    }
    
    .scrollbar-thin::-webkit-scrollbar {
        width: 4px;
    }

    .scrollbar-thin::-webkit-scrollbar-track {
        background: #f1f5f9;
        border-radius: 2px;
    }

    .scrollbar-thin::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 2px;
    }

    .scrollbar-thin::-webkit-scrollbar-thumb:hover {
        background: #94a3b8;
    }
    
    /* Enhanced Category Buttons */
    .category-btn {
        position: relative;
        overflow: hidden;
        margin-bottom: 0px !important;
        padding-bottom: 0px !important;
        border-radius: var(--border-radius-sm);
        font-weight: 600;
        letter-spacing: 0.025em;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        box-shadow: var(--shadow-sm);
    }

    .category-btn:hover {
        transform: translateY(-2px);
        box-shadow: var(--shadow-lg);
    }

    .category-btn.active {
        background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
        transform: translateY(-1px);
        box-shadow: var(--shadow-md);
    }
    
    .category-btn::after {
        content: '';
        position: absolute;
        top: 50%;
        left: 50%;
        width: 5px;
        height: 5px;
        background: rgba(255, 255, 255, 0.6);
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
            opacity: 0.6;
        }
        100% {
            transform: scale(20, 20);
            opacity: 0;
        }
    }

    /* Order Method Buttons */
    .order-method-btn {
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        border-radius: var(--border-radius-sm);
        font-weight: 500;
        border: 2px solid transparent;
    }

    .order-method-btn:hover {
        transform: translateY(-1px);
        box-shadow: var(--shadow-md);
    }

    .order-method-btn.active {
        background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
        color: white;
        border-color: var(--primary-dark);
        transform: translateY(-1px);
        box-shadow: var(--shadow-md);
    }

    /* Cart Section */
    .cart-section {
        background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
        border-radius: var(--border-radius);
        border: 1px solid var(--border-color);
        box-shadow: var(--shadow-sm);
    }

    #emptyCartIcon {
        opacity: 0.3;
        transition: all 0.3s ease;
        z-index: 1;
        animation: float 3s ease-in-out infinite;
    }

    #emptyCartIcon.hidden {
        opacity: 0;
        pointer-events: none;
        transform: scale(0.8);
    }

    @keyframes float {
        0%, 100% { transform: translateY(0px); }
        50% { transform: translateY(-10px); }
    }

    #cartItems {
        position: relative;
        z-index: 2;
    }

    /* Product Cards */
    .product-card {
        background: var(--card-background);
        border-radius: var(--border-radius);
        border: 1px solid var(--border-color);
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        box-shadow: var(--shadow-sm);
        overflow: hidden;
    }

    .product-card:hover {
        transform: translateY(-4px);
        box-shadow: var(--shadow-lg);
        border-color: var(--primary-color);
    }

    .product-card:active {
        transform: translateY(-2px) scale(0.98);
    }

    /* Enhanced Buttons */
    .btn-primary {
        background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
        color: white;
        border: none;
        border-radius: var(--border-radius-sm);
        font-weight: 600;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        box-shadow: var(--shadow-sm);
    }

    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: var(--shadow-lg);
        background: linear-gradient(135deg, var(--primary-dark), #1e40af);
    }

    .btn-secondary {
        background: var(--secondary-color);
        color: white;
        border: none;
        border-radius: var(--border-radius-sm);
        font-weight: 500;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        box-shadow: var(--shadow-sm);
    }

    .btn-secondary:hover {
        transform: translateY(-1px);
        box-shadow: var(--shadow-md);
        background: #475569;
    }

    .btn-success {
        background: linear-gradient(135deg, var(--success-color), #059669);
        color: white;
        border: none;
        border-radius: var(--border-radius-sm);
        font-weight: 600;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        box-shadow: var(--shadow-sm);
    }

    .btn-success:hover {
        transform: translateY(-2px);
        box-shadow: var(--shadow-lg);
        background: linear-gradient(135deg, #059669, #047857);
    }

    .btn-danger {
        background: linear-gradient(135deg, var(--danger-color), #dc2626);
        color: white;
        border: none;
        border-radius: var(--border-radius-sm);
        font-weight: 600;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        box-shadow: var(--shadow-sm);
    }

    .btn-danger:hover {
        transform: translateY(-2px);
        box-shadow: var(--shadow-lg);
        background: linear-gradient(135deg, #dc2626, #b91c1c);
    }

    /* Active Orders */
    #activeOrders {
        border-top: 1px solid var(--border-color);
        background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
        border-radius: var(--border-radius) var(--border-radius) 0 0;
    }

    #activeOrders .order-item {
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        padding: 0.75rem;
        border-bottom: 1px solid var(--border-color);
        border-radius: var(--border-radius-sm);
        margin: 0.25rem;
        background: var(--card-background);
        box-shadow: var(--shadow-sm);
    }

    #activeOrders .order-item:hover {
        background: linear-gradient(135deg, #f1f5f9, #e2e8f0);
        transform: translateX(4px);
        box-shadow: var(--shadow-md);
    }

    #activeOrders .order-item:last-child {
        border-bottom: none;
    }

    /* Search Bar */
    .search-input {
        background: var(--card-background);
        border: 2px solid var(--border-color);
        border-radius: var(--border-radius-lg);
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        box-shadow: var(--shadow-sm);
    }

    .search-input:focus {
        border-color: var(--primary-color);
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        transform: translateY(-1px);
    }

    /* Loading Animation */
    .loading-spinner {
        animation: spin 1s linear infinite;
    }

    @keyframes spin {
        from { transform: rotate(0deg); }
        to { transform: rotate(360deg); }
    }

    /* Pulse Animation */
    .pulse {
        animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
    }

    @keyframes pulse {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.5; }
    }

    /* Fade In Animation */
    .fade-in {
        animation: fadeIn 0.5s ease-in-out;
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }

    /* Slide In Animation */
    .slide-in {
        animation: slideIn 0.3s ease-out;
    }

    @keyframes slideIn {
        from { transform: translateX(-100%); opacity: 0; }
        to { transform: translateX(0); opacity: 1; }
    }

    /* Glass Effect */
    .glass-effect {
        background: rgba(255, 255, 255, 0.25);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.18);
    }

    /* Mobile-first Responsive Design */
    @media (max-width: 768px) {
        .pos-container {
            margin: 0;
            height: 100vh;
            border-radius: 0;
        }
        
        html, body {
            background: #f8fafc;
        }
        
        /* Header adjustments */
        .header-section {
            padding: 0.5rem;
        }
        
        .header-section h1 {
            font-size: 1rem;
        }
        
        .header-section p {
            font-size: 0.625rem;
        }
        
        .header-section i {
            font-size: 0.875rem !important;
        }
        
        /* Order method buttons */
        .order-method-btn {
            padding: 0.375rem 0.5rem;
            font-size: 0.75rem;
        }
        
        .order-method-btn i {
            font-size: 0.75rem !important;
        }
        
        /* Cart section */
        .cart-section {
            margin: 0.25rem;
            height: 150px;
            flex-shrink: 0;
        }
        
        /* Ensure cart summary is always visible */
        #cartSummary {
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            border-top: 2px solid #3b82f6;
        }
        
        #cartActions {
            background: white;
            border-top: 1px solid #e5e7eb;
        }
        
        .cart-section i {
            font-size: 0.875rem !important;
        }
        
        /* Category buttons */
        .category-btn {
            font-size: 0.625rem;
            padding: 0.375rem 0.5rem;
            white-space: nowrap;
        }
        
        .category-btn i {
            font-size: 0.625rem !important;
        }
        
        /* Search bar */
        .search-input {
            padding: 0.5rem 2.5rem 0.5rem 2.5rem;
            font-size: 0.875rem;
        }
        
        .search-input + div i {
            font-size: 0.75rem !important;
        }
        
        /* Product grid */
        .product-card {
            margin-bottom: 0.25rem;
        }
        
        .product-card:hover {
            transform: translateY(-1px);
        }
        
        .product-card i {
            font-size: 0.625rem !important;
        }
        
        /* Active orders - Make it more visible */
        #activeOrders {
            height: 50px;
            border-top: 2px solid #10b981;
            background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
        }
        
        #activeOrders i {
            font-size: 0.75rem !important;
        }
        
        #activeOrders .bg-gradient-to-r {
            background: linear-gradient(135deg, #dbeafe 0%, #e0e7ff 100%);
        }
        
        /* Touch-friendly buttons */
        button, .btn-primary, .btn-secondary, .btn-success, .btn-danger {
            min-height: 36px;
            min-width: 36px;
            font-size: 0.75rem;
        }
        
        button i, .btn-primary i, .btn-secondary i, .btn-success i, .btn-danger i {
            font-size: 0.625rem !important;
        }
        
        /* Prevent zoom on input focus */
        input, select, textarea {
            font-size: 16px;
        }
    }

    @media (max-width: 480px) {
        /* Extra small screens */
        .category-btn {
            font-size: 0.5rem;
            padding: 0.25rem 0.375rem;
        }
        
        .category-btn i {
            font-size: 0.5rem !important;
        }
        
        .product-card h3 {
            font-size: 0.75rem;
        }
        
        .product-card p {
            font-size: 0.625rem;
        }
        
        .product-card i {
            font-size: 0.5rem !important;
        }
        
        .cart-section {
            height: 130px;
            flex-shrink: 0;
        }
        
        .cart-section i {
            font-size: 0.75rem !important;
        }
        
        #activeOrders {
            height: 130px;
        }
        
        #activeOrders i {
            font-size: 0.625rem !important;
        }
        
        .header-section i {
            font-size: 0.75rem !important;
        }
        
        .order-method-btn i {
            font-size: 0.625rem !important;
        }
        
        button i, .btn-primary i, .btn-secondary i, .btn-success i, .btn-danger i {
            font-size: 0.5rem !important;
        }
    }

    /* Touch interactions */
    .touch-active {
        transform: scale(0.98) !important;
        transition: transform 0.1s ease;
    }

    /* Mobile-specific styles */
    @media (max-width: 768px) {
        /* Disable hover effects on mobile */
        .product-card:hover {
            transform: none !important;
            box-shadow: var(--shadow-sm) !important;
        }
        
        .category-btn:hover {
            transform: none !important;
        }
        
        .order-method-btn:hover {
            transform: none !important;
        }
        
        /* Touch feedback */
        .product-card:active {
            transform: scale(0.98);
            transition: transform 0.1s ease;
        }
        
        .category-btn:active {
            transform: scale(0.95);
            transition: transform 0.1s ease;
        }
        
        .order-method-btn:active {
            transform: scale(0.95);
            transition: transform 0.1s ease;
        }
        
        /* Optimize for touch scrolling */
        .scrollbar-thin {
            -webkit-overflow-scrolling: touch;
            scroll-behavior: smooth;
        }
        
        /* Prevent text selection on UI elements */
        button, .category-btn, .order-method-btn, .product-card {
            -webkit-user-select: none;
            -moz-user-select: none;
            -ms-user-select: none;
            user-select: none;
        }
        
        /* Better tap targets */
        button, .category-btn, .order-method-btn {
            min-height: 44px;
            min-width: 44px;
        }
    }

    /* Dark Mode Support */
    @media (prefers-color-scheme: dark) {
        :root {
            --background-color: #0f172a;
            --card-background: #1e293b;
            --border-color: #334155;
            --text-primary: #f1f5f9;
            --text-secondary: #94a3b8;
        }
    }

    /* Custom SweetAlert2 Styles for Business Closed Popup */
    .swal2-popup-business-closed {
        border-radius: 16px !important;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25) !important;
        border: 1px solid #e5e7eb !important;
    }

    .swal2-title-business-closed {
        font-size: 1.5rem !important;
        font-weight: 600 !important;
        color: #1f2937 !important;
        margin-bottom: 0 !important;
        padding-bottom: 0 !important;
    }

    .swal2-html-container-business-closed {
        padding: 0 !important;
        margin: 0 !important;
    }

    .swal2-confirm-business-closed {
        border-radius: 8px !important;
        font-weight: 500 !important;
        padding: 12px 24px !important;
        font-size: 14px !important;
        transition: all 0.2s ease !important;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1) !important;
    }

    .swal2-confirm-business-closed:hover {
        transform: translateY(-1px) !important;
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1) !important;
    }

    .swal2-confirm-business-closed:active {
        transform: translateY(0) !important;
    }

    /* Animation for the popup */
    .swal2-popup-business-closed {
        animation: businessClosedSlideIn 0.3s ease-out !important;
    }

    @keyframes businessClosedSlideIn {
        from {
            opacity: 0;
            transform: scale(0.9) translateY(-20px);
        }
        to {
            opacity: 1;
            transform: scale(1) translateY(0);
        }
    }

    /* Backdrop blur effect */
    .swal2-backdrop {
        backdrop-filter: blur(4px) !important;
        background-color: rgba(0, 0, 0, 0.4) !important;
    }
    </style>
</head>
<body>
@extends('layouts.pos')

@section('content')
<div class="pos-container">

        <!-- Order Method Selection -->
    <div class="flex-none px-3 py-1 border-b bg-gradient-to-r from-gray-50 to-blue-50">
        <div class="space-y-1">
            <div class="flex space-x-2">
                <label class="flex-1">
                        <input type="radio" name="order_method" value="dine-in" class="hidden" onchange="setOrderMethod('dine-in')">
                    <span id="dineInBtn" class="order-method-btn w-full px-2 py-1.5 text-sm rounded-lg bg-white text-gray-700 border-2 border-gray-200 hover:border-blue-300 cursor-pointer flex items-center justify-center space-x-2">
                        <i class="fas fa-utensils"></i>
                        <span>Dine In</span>
                        </span>
                    </label>
                <label class="flex-1">
                        <input type="radio" name="order_method" value="takeaway" class="hidden" onchange="setOrderMethod('takeaway')">
                    <span id="takeawayBtn" class="order-method-btn w-full px-2 py-1.5 text-sm rounded-lg bg-white text-gray-700 border-2 border-gray-200 hover:border-blue-300 cursor-pointer flex items-center justify-center space-x-2">
                        <i class="fas fa-shopping-bag"></i>
                        <span>Takeaway</span>
                        </span>
                    </label>
            </div>
            <div class="flex items-center justify-center">
                <span class="text-xs text-gray-400 opacity-60">* Required</span>
                </div>
                <!-- Table Selection (initially hidden) -->
            <div id="tableSelection" class="flex items-center justify-center space-x-2 hidden fade-in">
                <label class="text-sm font-medium text-gray-700 flex items-center space-x-1">
                    <i class="fas fa-table"></i>
                    <span>Table:</span>
                </label>
                <select id="tableSelect" class="text-sm border-2 border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200">
                        <option value="">Select Table</option>
                    @if(isset($tables) && $tables->count() > 0)
                        @foreach($tables as $table)
                            <option value="{{ $table->id }}" data-capacity="{{ $table->capacity }}" data-status="{{ $table->status }}">
                                {{ $table->name }} ({{ $table->number }}) - {{ $table->capacity }} seats
                            </option>
                        @endforeach
                    @endif
                    </select>
                </div>
            </div>
        </div>
        <!-- Cart Section -->
    <div id="cartSection" class="cart-section flex flex-col m-2 bg-white rounded-lg shadow-lg border transition-all duration-300 ease-in-out" style="height: 150px; flex-shrink: 0;">
        <!-- Cart Header -->
        <div class="flex-none p-3 border-b border-gray-200 bg-gradient-to-r from-blue-600 to-purple-600 text-white rounded-t-lg">
            <div class="flex items-center justify-between">
                <h3 class="font-bold text-white flex items-center space-x-2">
                    <i class="fas fa-shopping-cart text-lg"></i>
                    <span class="text-base">Cart</span>
                </h3>
                <span id="cartItemCount" class="bg-white bg-opacity-20 text-white text-xs font-medium px-2 py-1 rounded-full">0 items</span>
            </div>
        </div>
        
        <!-- Cart Content Area -->
        <div class="flex-1 relative overflow-hidden transition-all duration-300 ease-in-out" style="height: 40px;">
            <!-- Empty Cart Icon -->
            <div id="emptyCartIcon" class="absolute inset-0 flex flex-col items-center justify-center text-center p-4 z-10">
                <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-3">
                    <i class="fas fa-shopping-cart text-gray-400 text-xl"></i>
                </div>
                <h4 class="text-gray-500 font-medium text-sm mb-1">Your cart is empty</h4>
                <p class="text-gray-400 text-xs">Add items from the menu below</p>
            </div>
            <!-- Cart Items -->
            <div id="cartItems" class="h-full overflow-y-auto scrollbar-thin p-3 space-y-2"></div>
        </div>
        
        <!-- Cart Summary -->
        <div id="cartSummary" class="flex-none border-t bg-gradient-to-r from-gray-50 to-blue-50 transition-all duration-300 ease-in-out" style="height: 60px; opacity: 0.7;">
            <div class="h-full flex flex-col justify-center px-2">
                <!-- Compact view - show only total when empty -->
                <div id="compactSummary" class="flex justify-between items-center text-sm">
                    <span class="text-gray-700 font-medium">Total:</span>
                    <span class="font-bold text-lg text-blue-600" id="cartTotal">Rs 0.00</span>
                </div>
                <!-- Full view - show all details when expanded -->
                <div id="fullSummary" class="space-y-1 text-xs hidden">
                    <div class="flex justify-between items-center">
                        <span class="text-gray-700 font-medium">Subtotal:</span>
                        <span id="cartSubtotal" class="font-bold text-gray-900">Rs 0.00</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-700 font-medium">Tax (10%):</span>
                        <span id="cartTax" class="font-bold text-gray-900">Rs 0.00</span>
                    </div>
                    <div class="flex justify-between items-center font-bold text-base border-t border-gray-300 pt-1">
                        <span class="text-gray-800">Total:</span>
                        <span class="font-bold text-lg text-blue-600" id="cartTotalFull">Rs 0.00</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div id="cartActions" class="flex-none border-t p-2 bg-white rounded-b-lg transition-all duration-300 ease-in-out" style="height: 40px; opacity: 0.7;">
            <div class="flex space-x-2">
                <button id="clearCartBtn" onclick="clearCart()" class="bg-gradient-to-r from-red-500 to-red-600 text-white flex-1 py-2 text-xs font-bold flex items-center justify-center space-x-1 rounded-lg hover:from-red-600 hover:to-red-700 transition-all duration-200 shadow-lg">
                    <i class="fas fa-trash-alt text-xs"></i>
                    <span>Clear</span>
                </button>
                <button id="createOrderBtn" onclick="createOrder()" class="bg-gradient-to-r from-green-500 to-green-600 text-white flex-1 py-2 text-xs font-bold flex items-center justify-center space-x-1 rounded-lg hover:from-green-600 hover:to-green-700 transition-all duration-200 shadow-lg disabled:opacity-50 disabled:cursor-not-allowed">
                    <i class="fas fa-plus-circle text-xs"></i>
                    <span>Order</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Main Menu Categories -->
    <div class="flex-none px-2 py-2 border-b bg-gradient-to-r from-gray-50 to-blue-50">
        <div class="flex space-x-1 overflow-x-auto scrollbar-hide">
            <button onclick="filterProducts('combo')" class="category-btn active whitespace-nowrap text-xs px-3 py-1.5 rounded-lg bg-gradient-to-r from-blue-600 to-purple-600 text-white hover:from-blue-700 hover:to-purple-700 transition-all duration-300 shadow-md flex items-center space-x-1">
                <i class="fas fa-gift text-xs"></i>
                <span>Combos</span>
            </button>
            <button onclick="filterProducts('food')" class="category-btn whitespace-nowrap text-xs px-3 py-1.5 rounded-lg bg-gradient-to-r from-orange-500 to-red-500 text-white hover:from-orange-600 hover:to-red-600 transition-all duration-300 shadow-md flex items-center space-x-1">
                <i class="fas fa-utensils text-xs"></i>
                <span>Food</span>
            </button>
            <button onclick="filterProducts('drinks')" class="category-btn whitespace-nowrap text-xs px-3 py-1.5 rounded-lg bg-gradient-to-r from-cyan-500 to-blue-500 text-white hover:from-cyan-600 hover:to-blue-600 transition-all duration-300 shadow-md flex items-center space-x-1">
                <i class="fas fa-coffee text-xs"></i>
                <span>Drinks</span>
            </button>
            <button onclick="filterProducts('desserts')" class="category-btn whitespace-nowrap text-xs px-3 py-1.5 rounded-lg bg-gradient-to-r from-pink-500 to-purple-500 text-white hover:from-pink-600 hover:to-purple-600 transition-all duration-300 shadow-md flex items-center space-x-1">
                <i class="fas fa-ice-cream text-xs"></i>
                <span>Desserts</span>
            </button>
        </div>
    </div>

    <!-- Sub-category Filter (Dynamic) -->
    <div id="subCategoryFilter" class="flex-none px-2 py-1 border-b bg-gradient-to-r from-blue-50 to-purple-50 hidden fade-in">
        <div class="flex space-x-1 overflow-x-auto scrollbar-hide">
            <!-- Sub-categories will be populated dynamically -->
            </div>
        </div>

        <!-- Search Bar -->
    <div class="flex-none px-3 py-2 border-b bg-white">
        <div class="relative">
                <input type="text" id="productSearch" placeholder="Search products..." 
                class="search-input w-full pl-10 pr-8 py-2 text-sm border-2 border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-300">
            <div class="absolute left-3 top-1/2 -translate-y-1/2">
                <i class="fas fa-search text-gray-400 text-sm"></i>
            </div>
            <div class="absolute right-3 top-1/2 -translate-y-1/2">
                <button onclick="clearSearch()" class="text-gray-400 hover:text-gray-600 transition-colors duration-200">
                    <i class="fas fa-times-circle text-sm"></i>
                </button>
            </div>
        </div>
    </div>

    <!-- Products Grid Section -->
    <div class="flex-1 bg-gradient-to-br from-gray-50 to-blue-50 overflow-auto" style="min-height: 300px;">
        <!-- Products Grid -->
        <div id="productsSection" class="overflow-y-auto scrollbar-thin p-2 min-h-96">
            <div class="grid grid-cols-2 gap-2" id="productsGrid">
                <!-- Product cards will be rendered here -->
            </div>
        </div>
    </div>
        
        <!-- Active Orders Section -->
        <div id="activeOrders" class="flex-none border-t bg-white overflow-hidden shadow-lg rounded-t-lg" style="height: 60px;">
            <div class="flex items-center justify-between px-3 py-2 bg-gradient-to-r from-green-600 to-emerald-600 text-white cursor-pointer" id="activeOrdersHeader">
                <div class="flex items-center gap-2">
                    <div class="w-6 h-6 bg-white bg-opacity-20 rounded flex items-center justify-center">
                        <i class="fas fa-list text-white text-sm"></i>
                    </div>
                    <div>
                        <span class="font-medium text-white text-sm">Active Orders</span>
                        <span id="activeOrdersCount" class="text-green-100 text-xs ml-1 font-medium">(0)</span>
                    </div>
                </div>
                <div class="flex items-center gap-1">
                    <div class="w-1.5 h-1.5 bg-yellow-400 rounded-full animate-pulse"></div>
                    <i class="fas fa-chevron-down text-green-100 text-xs transition-transform duration-200" id="ordersDropdownToggle"></i>
                </div>
            </div>
            <div id="activeOrdersContent" class="overflow-y-auto scrollbar-thin bg-gradient-to-br from-green-50 to-emerald-50" style="height: 0; transition: height 0.3s ease;">
                <!-- Active orders will be loaded here via JavaScript -->
                <div class="p-3 text-center text-gray-500">
                    <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-2">
                        <i class="fas fa-check-circle text-green-400 text-sm"></i>
                    </div>
                    <p class="text-xs font-medium text-gray-700">All caught up!</p>
                    <p class="text-xs text-gray-500">No pending orders</p>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="/js/pos-sounds.js"></script>
<script src="/js/pos.js"></script>
<script>
// Enhanced UI/UX Functions

function clearSearch() {
    const searchInput = document.getElementById('productSearch');
    searchInput.value = '';
    searchInput.focus();
    
    // Trigger search event to show default category
    const event = new Event('input', { bubbles: true });
    searchInput.dispatchEvent(event);
}

function setOrderMethod(method) {
    // Remove active class from all buttons
    document.querySelectorAll('.order-method-btn').forEach(btn => {
        btn.classList.remove('active');
        btn.classList.add('bg-white', 'text-gray-700', 'border-gray-200');
        btn.classList.remove('text-white', 'border-blue-500');
    });
    
    // Add active class to selected button
    const selectedBtn = document.getElementById(method + 'Btn');
    if (selectedBtn) {
        selectedBtn.classList.add('active');
        selectedBtn.classList.remove('bg-white', 'text-gray-700', 'border-gray-200');
        selectedBtn.classList.add('text-white', 'border-blue-500');
    }
    
    // Show/hide table selection for dine-in
    const tableSelection = document.getElementById('tableSelection');
    if (method === 'dine-in') {
        tableSelection.classList.remove('hidden');
        tableSelection.classList.add('fade-in');
    } else {
        tableSelection.classList.add('hidden');
        tableSelection.classList.remove('fade-in');
    }
    
    // Add animation effect
    selectedBtn.style.transform = 'scale(0.95)';
    setTimeout(() => {
        selectedBtn.style.transform = 'scale(1)';
    }, 150);
}

// Add loading states and animations
function showLoadingState(element) {
    element.innerHTML = '<div class="flex items-center justify-center py-4"><div class="loading-spinner w-6 h-6 border-2 border-blue-600 border-t-transparent rounded-full"></div><span class="ml-2 text-gray-600">Loading...</span></div>';
}

function hideLoadingState(element, content) {
    element.innerHTML = content;
}

// Enhanced cart item count update
function updateCartItemCount() {
    const cartItems = document.getElementById('cartItems');
    const cartItemCount = document.getElementById('cartItemCount');
    const itemCount = cartItems.children.length;
    
    cartItemCount.textContent = `${itemCount} item${itemCount !== 1 ? 's' : ''}`;
    
    // Add pulse animation when items are added
    if (itemCount > 0) {
        cartItemCount.classList.add('pulse');
        setTimeout(() => {
            cartItemCount.classList.remove('pulse');
        }, 1000);
    }
}

// Add smooth scroll to top when switching categories
function smoothScrollToTop() {
    const productsSection = document.getElementById('productsSection');
    productsSection.scrollTo({
        top: 0,
        behavior: 'smooth'
    });
}

// Enhanced product card animations
function addProductCardAnimation(card) {
    card.classList.add('fade-in');
    card.style.animationDelay = Math.random() * 0.3 + 's';
}

// Initialize enhanced UI
document.addEventListener('DOMContentLoaded', function() {
    // Add fade-in animation to main container
    const container = document.querySelector('.pos-container');
    if (container) {
        container.classList.add('fade-in');
    }
    
    // Update cart item count on load
    updateCartItemCount();
    
    // Mobile-specific enhancements
    if (window.innerWidth <= 768) {
        // Disable hover effects on mobile
        document.querySelectorAll('.product-card').forEach(card => {
            card.addEventListener('touchstart', function() {
                this.classList.add('touch-active');
            });
            
            card.addEventListener('touchend', function() {
                setTimeout(() => {
                    this.classList.remove('touch-active');
                }, 150);
            });
        });
        
        // Prevent zoom on input focus
        const inputs = document.querySelectorAll('input, select, textarea');
        inputs.forEach(input => {
            input.addEventListener('focus', function() {
                this.style.fontSize = '16px';
            });
        });
    }
    
    // Add keyboard shortcuts (desktop only)
    if (window.innerWidth > 768) {
        document.addEventListener('keydown', function(e) {
            // Ctrl + K to focus search
            if (e.ctrlKey && e.key === 'k') {
                e.preventDefault();
                document.getElementById('productSearch').focus();
            }
            
            // Escape to clear search
            if (e.key === 'Escape') {
                clearSearch();
            }
        });
    }
    
    // Touch-friendly interactions
    document.querySelectorAll('button, .category-btn, .order-method-btn').forEach(btn => {
        btn.addEventListener('touchstart', function() {
            this.style.transform = 'scale(0.95)';
        });
        
        btn.addEventListener('touchend', function() {
            setTimeout(() => {
                this.style.transform = 'scale(1)';
            }, 150);
        });
    });
    
    // Optimize scrolling for mobile
    if ('ontouchstart' in window) {
        document.querySelectorAll('.scrollbar-thin').forEach(element => {
            element.style.webkitOverflowScrolling = 'touch';
        });
    }
});

// Enhanced error handling with user-friendly messages
function showErrorMessage(message, type = 'error') {
    const toast = document.createElement('div');
    toast.className = `fixed top-4 right-4 z-50 px-6 py-3 rounded-lg shadow-lg text-white font-medium transform translate-x-full transition-transform duration-300 ${
        type === 'error' ? 'bg-red-500' : 'bg-blue-500'
    }`;
    toast.textContent = message;
    
    document.body.appendChild(toast);
    
    // Animate in
    setTimeout(() => {
        toast.style.transform = 'translateX(0)';
    }, 100);
    
    // Remove after 3 seconds
    setTimeout(() => {
        toast.style.transform = 'translateX(full)';
        setTimeout(() => {
            document.body.removeChild(toast);
        }, 300);
    }, 3000);
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
</html>