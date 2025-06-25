@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Page Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">Checkout</h1>
            <p class="text-gray-600 mt-2">Complete your order</p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Order Summary -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                    <div class="p-6 border-b border-gray-200">
                        <h2 class="text-lg font-semibold text-gray-900">Order Summary</h2>
                    </div>
                    
                    <div class="p-6">
                        <div id="checkout-items" class="space-y-4 mb-6">
                            <!-- Items will be populated by JavaScript -->
                        </div>

                        <!-- Order Totals -->
                        <div class="border-t border-gray-200 pt-4 space-y-3">
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">Subtotal</span>
                                <span class="font-medium text-gray-900" id="checkout-subtotal">$0.00</span>
                            </div>
                            
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">Delivery Fee</span>
                                <span class="font-medium text-gray-900" id="checkout-delivery">$5.00</span>
                            </div>
                            
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">Tax (13%)</span>
                                <span class="font-medium text-gray-900" id="checkout-tax">$0.00</span>
                            </div>
                            
                            <div class="border-t border-gray-200 pt-3">
                                <div class="flex justify-between text-lg font-bold">
                                    <span class="text-gray-900">Total</span>
                                    <span class="text-[#6E0D25]" id="checkout-total">$0.00</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Checkout Form -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                    <div class="p-6 border-b border-gray-200">
                        <h2 class="text-lg font-semibold text-gray-900">Delivery Information</h2>
                    </div>
                    
                    <form id="checkout-form" class="p-6 space-y-6">
                        <!-- Name -->
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Full Name</label>
                            <input type="text" id="name" name="name" required
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#6E0D25] focus:border-[#6E0D25] transition-colors">
                        </div>

                        <!-- Email -->
                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700 mb-2">Email Address</label>
                            <input type="email" id="email" name="email" required
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#6E0D25] focus:border-[#6E0D25] transition-colors">
                        </div>

                        <!-- Phone -->
                        <div>
                            <label for="phone" class="block text-sm font-medium text-gray-700 mb-2">Phone Number</label>
                            <input type="tel" id="phone" name="phone" required
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#6E0D25] focus:border-[#6E0D25] transition-colors">
                        </div>

                        <!-- Address -->
                        <div>
                            <label for="address" class="block text-sm font-medium text-gray-700 mb-2">Delivery Address</label>
                            <textarea id="address" name="address" rows="3" required
                                      class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#6E0D25] focus:border-[#6E0D25] transition-colors"></textarea>
                        </div>

                        <!-- Payment Method -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-3">Payment Method</label>
                            <div class="space-y-3">
                                <label class="flex items-center">
                                    <input type="radio" name="payment_method" value="cash" checked
                                           class="w-4 h-4 text-[#6E0D25] border-gray-300 focus:ring-[#6E0D25]">
                                    <span class="ml-3 text-sm text-gray-700">Cash on Delivery</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="radio" name="payment_method" value="card"
                                           class="w-4 h-4 text-[#6E0D25] border-gray-300 focus:ring-[#6E0D25]">
                                    <span class="ml-3 text-sm text-gray-700">Credit/Debit Card</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="radio" name="payment_method" value="online"
                                           class="w-4 h-4 text-[#6E0D25] border-gray-300 focus:ring-[#6E0D25]">
                                    <span class="ml-3 text-sm text-gray-700">Online Payment</span>
                                </label>
                            </div>
                        </div>

                        <!-- Special Instructions -->
                        <div>
                            <label for="instructions" class="block text-sm font-medium text-gray-700 mb-2">Special Instructions (Optional)</label>
                            <textarea id="instructions" name="instructions" rows="2"
                                      class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#6E0D25] focus:border-[#6E0D25] transition-colors"
                                      placeholder="Any special delivery instructions..."></textarea>
                        </div>

                        <!-- Submit Button -->
                        <button type="submit" 
                                id="place-order-btn"
                                class="w-full bg-[#6E0D25] text-white py-3 px-4 rounded-lg font-semibold hover:bg-[#8B0D2F] transition-colors duration-300 flex items-center justify-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            Place Order
                        </button>

                        <!-- Back to Cart -->
                        <a href="{{ route('cart') }}" 
                           class="block w-full text-center bg-gray-100 text-gray-700 py-3 px-4 rounded-lg font-semibold hover:bg-gray-200 transition-colors duration-300">
                            Back to Cart
                        </a>
                    </form>
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
// Checkout page functionality
document.addEventListener('DOMContentLoaded', function() {
    updateCheckoutPage();
    
    // Handle form submission
    document.getElementById('checkout-form').addEventListener('submit', function(e) {
        e.preventDefault();
        placeOrder();
    });
});

function updateCheckoutPage() {
    if (!cartManager) return;
    
    const cartItems = cartManager.getCartItems();
    const itemsContainer = document.getElementById('checkout-items');
    
    if (cartItems.length === 0) {
        // Redirect to cart if empty
        window.location.href = '{{ route("cart") }}';
        return;
    }
    
    // Display items
    let itemsHtml = '';
    let subtotal = 0;
    
    cartItems.forEach(item => {
        const itemTotal = item.price * item.quantity;
        subtotal += itemTotal;
        
        itemsHtml += `
            <div class="flex items-center gap-4 p-4 bg-gray-50 rounded-lg">
                <div class="flex-shrink-0">
                    ${item.image ? 
                        `<img src="${item.image}" alt="${item.name}" class="w-12 h-12 object-cover rounded-lg">` :
                        `<div class="w-12 h-12 bg-gray-200 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 002 2v12a2 2 0 002 2z"></path>
                            </svg>
                        </div>`
                    }
                </div>
                <div class="flex-1 min-w-0">
                    <h3 class="text-sm font-medium text-gray-900 truncate">${item.name}</h3>
                    <p class="text-sm text-gray-500">Quantity: ${item.quantity}</p>
                </div>
                <div class="text-right">
                    <p class="text-sm font-bold text-[#6E0D25]">$${itemTotal.toFixed(2)}</p>
                </div>
            </div>
        `;
    });
    
    itemsContainer.innerHTML = itemsHtml;
    
    // Calculate totals
    const deliveryFee = subtotal >= 25 ? 0 : 5;
    const tax = subtotal * 0.13;
    const total = subtotal + deliveryFee + tax;
    
    // Update totals
    document.getElementById('checkout-subtotal').textContent = `$${subtotal.toFixed(2)}`;
    document.getElementById('checkout-tax').textContent = `$${tax.toFixed(2)}`;
    document.getElementById('checkout-total').textContent = `$${total.toFixed(2)}`;
    document.getElementById('checkout-delivery').textContent = deliveryFee === 0 ? 'Free' : `$${deliveryFee.toFixed(2)}`;
}

function placeOrder() {
    const form = document.getElementById('checkout-form');
    const submitBtn = document.getElementById('place-order-btn');
    
    // Get form data
    const formData = new FormData(form);
    const orderData = {
        name: formData.get('name'),
        email: formData.get('email'),
        phone: formData.get('phone'),
        address: formData.get('address'),
        payment_method: formData.get('payment_method'),
        instructions: formData.get('instructions'),
        items: cartManager.getCartItems(),
        total: parseFloat(document.getElementById('checkout-total').textContent.replace('$', ''))
    };
    
    // Show loading state
    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML = `
        <div class="flex items-center justify-center gap-2">
            <div class="w-4 h-4 border-2 border-current border-t-transparent rounded-full animate-spin"></div>
            <span>Processing...</span>
        </div>
    `;
    submitBtn.disabled = true;
    
    // Simulate order processing
    setTimeout(() => {
        // Clear cart
        cartManager.clearCart();
        
        // Show success message
        alert('Order placed successfully! We\'ll contact you soon.');
        
        // Redirect to home
        window.location.href = '{{ route("home") }}';
    }, 2000);
}
</script>
@endpush 