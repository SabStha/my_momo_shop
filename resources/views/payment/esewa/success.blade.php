@extends('layouts.app')

@section('title', 'eSewa Payment Success')

@section('content')
<div class="min-h-screen bg-gray-50 flex flex-col justify-center py-12 sm:px-6 lg:px-8">
    <div class="sm:mx-auto sm:w-full sm:max-w-md">
        <div class="text-center">
            <div class="mx-auto h-16 w-16 bg-green-100 rounded-full flex items-center justify-center">
                <svg class="h-8 w-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
            </div>
            <h2 class="mt-6 text-3xl font-extrabold text-gray-900">
                Payment Successful!
            </h2>
            <p class="mt-2 text-sm text-gray-600">
                Your eSewa payment has been processed successfully
            </p>
        </div>
    </div>

    <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
        <div class="bg-white py-8 px-4 shadow sm:rounded-lg sm:px-10">
            <div class="text-center">
                <div class="mb-6">
                    <div class="mx-auto h-20 w-20 bg-green-100 rounded-full flex items-center justify-center mb-4">
                        <span class="text-3xl">💳</span>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">eSewa Payment Completed</h3>
                    <p class="text-sm text-gray-500">
                        Thank you for your payment. Your order has been confirmed.
                    </p>
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
                    <a href="{{ route('home') }}" 
                       class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                        Continue Shopping
                    </a>
                    
                    <a href="{{ route('user.orders') }}" 
                       class="w-full flex justify-center py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                        View My Orders
                    </a>
                </div>

                <div class="mt-6 text-xs text-gray-400">
                    <p>A confirmation email has been sent to your registered email address.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Auto-redirect to home page after 10 seconds
setTimeout(function() {
    window.location.href = '{{ route("home") }}';
}, 10000);
</script>
@endsection
