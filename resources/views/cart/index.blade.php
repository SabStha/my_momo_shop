@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Page Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">Shopping Cart</h1>
            <p class="text-gray-600 mt-2">Review your items and proceed to checkout</p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Cart Items -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                    <div class="p-6 border-b border-gray-200">
                        <h2 class="text-lg font-semibold text-gray-900">Cart Items</h2>
                    </div>
                    
                    <div id="cart-items-container">
                        <!-- Cart items will be loaded here via JavaScript -->
                        <div class="p-8 text-center">
                            <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 13m0 0l-2.5 5M7 13l2.5 5m6-5v6a2 2 0 01-2 2H9a2 2 0 01-2-2v-6m8 0V9a2 2 0 00-2-2H9a2 2 0 00-2 2v4.01"></path>
                                </svg>
                            </div>
                            <h3 class="text-lg font-medium text-gray-900 mb-2">Your cart is empty</h3>
                            <p class="text-gray-600 mb-6">Looks like you haven't added any items to your cart yet.</p>
                            <a href="{{ route('home') }}" class="inline-flex items-center px-4 py-2 bg-[#6E0D25] text-white rounded-lg hover:bg-[#8B0D2F] transition-colors">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                </svg>
                                Start Shopping
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Suggested Products -->
                @if(isset($suggestedProducts) && $suggestedProducts->count() > 0)
                <div class="mt-8 bg-white rounded-lg shadow-sm border border-gray-200">
                    <div class="p-6 border-b border-gray-200">
                        <h2 class="text-lg font-semibold text-gray-900">You might also like</h2>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                            @foreach($suggestedProducts as $product)
                            <div class="group">
                                <div class="aspect-w-1 aspect-h-1 w-full overflow-hidden rounded-lg bg-gray-200 mb-3">
                                    @if($product->image)
                                        <img src="{{ asset('storage/' . $product->image) }}" 
                                             alt="{{ $product->name }}" 
                                             class="h-full w-full object-cover object-center group-hover:opacity-75">
                                    @else
                                        <div class="flex items-center justify-center h-full">
                                            <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                            </svg>
                                        </div>
                                    @endif
                                </div>
                                <h3 class="text-sm font-medium text-gray-900 truncate">{{ $product->name }}</h3>
                                <p class="text-sm font-bold text-[#6E0D25]">${{ number_format($product->price, 2) }}</p>
                                <button onclick="addToCart('{{ $product->id }}', '{{ $product->name }}', {{ $product->price }}, '{{ $product->image ? asset('storage/' . $product->image) : '' }}')" 
                                        class="mt-2 w-full bg-[#6E0D25] text-white py-2 px-3 rounded-lg text-sm font-medium hover:bg-[#8B0D2F] transition-colors">
                                    Add to Cart
                                </button>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                @endif
            </div>

            <!-- Cart Summary -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 sticky top-8">
                    <div class="p-6 border-b border-gray-200">
                        <h2 class="text-lg font-semibold text-gray-900">Order Summary</h2>
                    </div>
                    
                    <div class="p-6">
                        <!-- Cart Items Summary -->
                        <div id="cart-summary-items" class="space-y-4 mb-6">
                            <!-- Items will be populated by JavaScript -->
                        </div>

                        <!-- Subtotal -->
                        <div class="border-t border-gray-200 pt-4 space-y-3">
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">Subtotal</span>
                                <span class="font-medium text-gray-900" id="cart-subtotal">$0.00</span>
                            </div>
                            
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">Delivery Fee</span>
                                <span class="font-medium text-gray-900" id="cart-delivery">$5.00</span>
                            </div>
                            
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">Tax (13%)</span>
                                <span class="font-medium text-gray-900" id="cart-tax">$0.00</span>
                            </div>
                            
                            <div class="border-t border-gray-200 pt-3">
                                <div class="flex justify-between text-lg font-bold">
                                    <span class="text-gray-900">Total</span>
                                    <span class="text-[#6E0D25]" id="cart-total">$0.00</span>
                                </div>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="mt-6 space-y-3">
                            <button onclick="proceedToCheckout()" 
                                    id="checkout-btn"
                                    disabled
                                    class="w-full bg-[#6E0D25] text-white py-3 px-4 rounded-lg font-semibold hover:bg-[#8B0D2F] transition-colors disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                Proceed to Checkout
                            </button>
                            
                            <button onclick="clearCart()" 
                                    id="clear-cart-btn"
                                    class="w-full bg-gray-100 text-gray-700 py-3 px-4 rounded-lg font-semibold hover:bg-gray-200 transition-colors">
                                Clear Cart
                            </button>
                            
                            <a href="{{ route('home') }}" 
                               class="block w-full text-center bg-white border border-gray-300 text-gray-700 py-3 px-4 rounded-lg font-semibold hover:bg-gray-50 transition-colors">
                                Continue Shopping
                            </a>
                        </div>

                        <!-- Delivery Info -->
                        <div class="mt-6 p-4 bg-green-50 rounded-lg border border-green-200">
                            <div class="flex items-start gap-3">
                                <svg class="w-5 h-5 text-green-600 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                <div>
                                    <h4 class="text-sm font-semibold text-green-900">Free Delivery</h4>
                                    <p class="text-sm text-green-700 mt-1">Orders over $25 qualify for free delivery</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Cart Modal -->
@include('components.cart-modal')

@endsection

@push('scripts')
<script src="{{ asset('js/cart.js') }}"></script>
<script>
// Cart page specific functionality
document.addEventListener('DOMContentLoaded', function() {
    updateCartPage();
});

function updateCartPage() {
    if (!cartManager) return;
    
    const cartItems = cartManager.getCartItems();
    const container = document.getElementById('cart-items-container');
    const summaryContainer = document.getElementById('cart-summary-items');
    const checkoutBtn = document.getElementById('checkout-btn');
    const clearCartBtn = document.getElementById('clear-cart-btn');
    
    if (cartItems.length === 0) {
        // Show empty cart
        container.innerHTML = `
            <div class="p-8 text-center">
                <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 13m0 0l-2.5 5M7 13l2.5 5m6-5v6a2 2 0 01-2 2H9a2 2 0 01-2-2v-6m8 0V9a2 2 0 00-2-2H9a2 2 0 00-2 2v4.01"></path>
                    </svg>
                </div>
                <h3 class="text-lg font-medium text-gray-900 mb-2">Your cart is empty</h3>
                <p class="text-gray-600 mb-6">Looks like you haven't added any items to your cart yet.</p>
                <a href="{{ route('home') }}" class="inline-flex items-center px-4 py-2 bg-[#6E0D25] text-white rounded-lg hover:bg-[#8B0D2F] transition-colors">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    Start Shopping
                </a>
            </div>
        `;
        
        summaryContainer.innerHTML = '';
        checkoutBtn.disabled = true;
        clearCartBtn.style.display = 'none';
        
        // Update totals
        document.getElementById('cart-subtotal').textContent = '$0.00';
        document.getElementById('cart-tax').textContent = '$0.00';
        document.getElementById('cart-total').textContent = '$0.00';
        
        return;
    }
    
    // Show cart items
    let cartItemsHtml = '';
    let summaryItemsHtml = '';
    let subtotal = 0;
    
    cartItems.forEach(item => {
        const itemTotal = item.price * item.quantity;
        subtotal += itemTotal;
        
        cartItemsHtml += `
            <div class="p-6 border-b border-gray-200 last:border-b-0">
                <div class="flex items-center gap-4">
                    <div class="flex-shrink-0">
                        ${item.image ? 
                            `<img src="${item.image}" alt="${item.name}" class="w-16 h-16 object-cover rounded-lg">` :
                            `<div class="w-16 h-16 bg-gray-200 rounded-lg flex items-center justify-center">
                                <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 002 2v12a2 2 0 002 2z"></path>
                                </svg>
                            </div>`
                        }
                    </div>
                    <div class="flex-1 min-w-0">
                        <h3 class="text-sm font-medium text-gray-900 truncate">${item.name}</h3>
                        <p class="text-sm text-gray-500">$${item.price.toFixed(2)} each</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <button onclick="updateQuantity('${item.id}', ${item.quantity - 1})" 
                                class="w-8 h-8 bg-gray-100 rounded-full flex items-center justify-center hover:bg-gray-200 transition-colors">
                            <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path>
                            </svg>
                        </button>
                        <span class="w-12 text-center text-sm font-medium text-gray-900">${item.quantity}</span>
                        <button onclick="updateQuantity('${item.id}', ${item.quantity + 1})" 
                                class="w-8 h-8 bg-gray-100 rounded-full flex items-center justify-center hover:bg-gray-200 transition-colors">
                            <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                        </button>
                    </div>
                    <div class="text-right">
                        <p class="text-sm font-bold text-[#6E0D25]">$${itemTotal.toFixed(2)}</p>
                        <button onclick="removeFromCart('${item.id}')" 
                                class="text-xs text-red-600 hover:text-red-800 transition-colors mt-1">
                            Remove
                        </button>
                    </div>
                </div>
            </div>
        `;
        
        summaryItemsHtml += `
            <div class="flex justify-between text-sm">
                <span class="text-gray-600">${item.name} × ${item.quantity}</span>
                <span class="font-medium text-gray-900">$${itemTotal.toFixed(2)}</span>
            </div>
        `;
    });
    
    container.innerHTML = cartItemsHtml;
    summaryContainer.innerHTML = summaryItemsHtml;
    
    // Calculate totals
    const deliveryFee = subtotal >= 25 ? 0 : 5;
    const tax = subtotal * 0.13;
    const total = subtotal + deliveryFee + tax;
    
    // Update totals
    document.getElementById('cart-subtotal').textContent = `$${subtotal.toFixed(2)}`;
    document.getElementById('cart-tax').textContent = `$${tax.toFixed(2)}`;
    document.getElementById('cart-total').textContent = `$${total.toFixed(2)}`;
    document.getElementById('cart-delivery').textContent = deliveryFee === 0 ? 'Free' : `$${deliveryFee.toFixed(2)}`;
    
    // Enable/disable buttons
    checkoutBtn.disabled = false;
    clearCartBtn.style.display = 'block';
}

function proceedToCheckout() {
    window.location.href = '{{ route("checkout") }}';
}
</script>
@endpush 