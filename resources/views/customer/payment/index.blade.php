@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-3xl mx-auto">
        <div class="bg-white rounded-lg shadow-lg overflow-hidden">
            <div class="p-6">
                <h1 class="text-2xl font-bold text-gray-900 mb-6">Payment Details</h1>
                
                <!-- Order Summary -->
                <div class="mb-8">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Order Summary</h2>
                    <div class="bg-gray-50 rounded-lg p-4">
                        <div class="flex justify-between mb-2">
                            <span class="text-gray-600">Order #</span>
                            <span class="font-medium">{{ $order->order_number }}</span>
                        </div>
                        <div class="flex justify-between mb-2">
                            <span class="text-gray-600">Total Amount</span>
                            <span class="font-medium">Rs {{ number_format($order->total_amount, 2) }}</span>
                        </div>
                    </div>
                </div>

                <!-- Payment Method Selection -->
                <div class="mb-8">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Select Payment Method</h2>
                    <x-payment.method-selector 
                        :methods="$paymentMethods"
                        :selected="$selectedMethod" />
                </div>

                <!-- Payment Amount Input -->
                <div class="mb-8">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Payment Amount</h2>
                    <x-payment.amount-input 
                        :amount="$amount"
                        :total="$order->total_amount"
                        :showChange="true" />
                </div>

                <!-- Payment Actions -->
                <div class="flex justify-end space-x-4">
                    <button type="button" 
                            class="px-6 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50"
                            onclick="window.history.back()">
                        Cancel
                    </button>
                    <button type="button"
                            class="px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700"
                            wire:click="processPayment"
                            wire:loading.attr="disabled">
                        <span wire:loading.remove>Pay Now</span>
                        <span wire:loading>Processing...</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Payment processing status handling
    window.addEventListener('payment-processing', event => {
        // Show loading state
    });

    window.addEventListener('payment-success', event => {
        // Handle successful payment
        window.location.href = event.detail.redirectUrl;
    });

    window.addEventListener('payment-error', event => {
        // Handle payment error
        alert(event.detail.message);
    });
</script>
@endpush
@endsection 