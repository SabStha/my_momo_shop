@extends('layouts.app')

@section('title', 'eSewa Payment Failed')

@section('content')
<div class="min-h-screen bg-gray-50 flex flex-col justify-center py-12 sm:px-6 lg:px-8">
    <div class="sm:mx-auto sm:w-full sm:max-w-md">
        <div class="text-center">
            <div class="mx-auto h-16 w-16 bg-red-100 rounded-full flex items-center justify-center">
                <svg class="h-8 w-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </div>
            <h2 class="mt-6 text-3xl font-extrabold text-gray-900">
                Payment Failed
            </h2>
            <p class="mt-2 text-sm text-gray-600">
                Your eSewa payment could not be completed
            </p>
        </div>
    </div>

    <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
        <div class="bg-white py-8 px-4 shadow sm:rounded-lg sm:px-10">
            <div class="text-center">
                <div class="mb-6">
                    <div class="mx-auto h-20 w-20 bg-red-100 rounded-full flex items-center justify-center mb-4">
                        <span class="text-3xl">💳</span>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">eSewa Payment Unsuccessful</h3>
                    <p class="text-sm text-gray-500">
                        We're sorry, but your payment could not be processed. This could be due to:
                    </p>
                </div>

                <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6 text-left">
                    <ul class="text-sm text-red-800 space-y-2">
                        <li class="flex items-start">
                            <span class="text-red-500 mr-2">•</span>
                            Insufficient balance in your eSewa account
                        </li>
                        <li class="flex items-start">
                            <span class="text-red-500 mr-2">•</span>
                            Network connectivity issues
                        </li>
                        <li class="flex items-start">
                            <span class="text-red-500 mr-2">•</span>
                            Payment was cancelled by you
                        </li>
                        <li class="flex items-start">
                            <span class="text-red-500 mr-2">•</span>
                            Technical issues with eSewa service
                        </li>
                    </ul>
                </div>

                @if(isset($_GET['pid']) && isset($_GET['rid']))
                <div class="bg-gray-50 rounded-lg p-4 mb-6">
                    <div class="grid grid-cols-2 gap-4 text-sm">
                        <div>
                            <span class="text-gray-500">Payment ID:</span>
                            <p class="font-medium text-gray-900">{{ $_GET['pid'] }}</p>
                        </div>
                        <div>
                            <span class="text-gray-500">Transaction ID:</span>
                            <p class="font-medium text-gray-900">{{ $_GET['rid'] }}</p>
                        </div>
                    </div>
                </div>
                @endif

                <div class="space-y-4">
                    <a href="{{ route('checkout') }}" 
                       class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                        Try Again
                    </a>
                    
                    <a href="{{ route('cart') }}" 
                       class="w-full flex justify-center py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                        Back to Cart
                    </a>
                </div>

                <div class="mt-6 text-xs text-gray-400">
                    <p>If you continue to experience issues, please contact our support team.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Auto-redirect to checkout page after 15 seconds
setTimeout(function() {
    window.location.href = '{{ route("checkout") }}';
}, 15000);
</script>
@endsection
