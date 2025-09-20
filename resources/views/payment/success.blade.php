@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50 flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
        <div class="text-center">
            <!-- Success Icon -->
            <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-green-100">
                <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
            </div>
            
            <!-- Success Message -->
            <h2 class="mt-6 text-3xl font-extrabold text-gray-900">
                Payment Successful! 🎉
            </h2>
            <p class="mt-2 text-sm text-gray-600">
                Thank you for your order. We've received your payment and will start preparing your delicious momo right away!
            </p>
        </div>

        <!-- Order Details -->
        <div class="bg-white shadow rounded-lg p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Order Details</h3>
            <div class="space-y-3">
                <div class="flex justify-between">
                    <span class="text-gray-600">Order Number:</span>
                    <span class="font-medium text-gray-900" id="order-number">#{{ time() }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Payment Amount:</span>
                    <span class="font-medium text-gray-900" id="payment-amount">Rs.0.00</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Payment Method:</span>
                    <span class="font-medium text-gray-900" id="payment-method">-</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Estimated Delivery:</span>
                    <span class="font-medium text-gray-900">30-45 minutes</span>
                </div>
            </div>
        </div>

        <!-- Next Steps -->
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
            <h4 class="text-sm font-medium text-blue-900 mb-2">What happens next?</h4>
            <ul class="text-sm text-blue-800 space-y-1">
                <li>• We'll send you an SMS confirmation</li>
                <li>• Our kitchen will start preparing your order</li>
                <li>• You'll get updates on your order status</li>
                <li>• Our delivery partner will contact you</li>
            </ul>
        </div>

        <!-- Action Buttons -->
        <div class="space-y-3">
            <a href="{{ route('home') }}" 
               class="w-full flex justify-center py-3 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-[#6E0D25] hover:bg-[#8B0D2F] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#6E0D25] transition-colors">
                Continue Shopping
            </a>
            <button onclick="viewOrderStatus()" 
                    class="w-full flex justify-center py-3 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#6E0D25] transition-colors">
                Track My Order
            </button>
        </div>

        <!-- Contact Info -->
        <div class="text-center text-xs text-gray-500">
            <p>Questions about your order?</p>
            <p>Call us at <span class="font-medium">+977-1-4XXXXXX</span> or email <span class="font-medium">support@amakomomo.com</span></p>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Get order details from sessionStorage or localStorage
    const orderData = sessionStorage.getItem('orderData') || localStorage.getItem('orderData');
    if (orderData) {
        try {
            const order = JSON.parse(orderData);
            document.getElementById('order-number').textContent = order.orderNumber || '#{{ time() }}';
            document.getElementById('payment-amount').textContent = `Rs.${(parseFloat(order.amount || order.grand_total || order.total || '0.00')).toFixed(2)}`;
            document.getElementById('payment-method').textContent = order.paymentMethod || 'Online Payment';
        } catch (error) {
            console.error('Error parsing order data:', error);
        }
    }
    
    // Clear cart and order data
    localStorage.removeItem('momo_cart');
    localStorage.removeItem('applied_offer');
    localStorage.removeItem('checkout_data');
    sessionStorage.removeItem('orderData');
    
    if (typeof window.cartManager !== 'undefined') {
        window.cartManager.clearCart();
    }
});

function viewOrderStatus() {
    // This would typically redirect to an order tracking page
    alert('Order tracking feature coming soon!');
}
</script>
@endsection 