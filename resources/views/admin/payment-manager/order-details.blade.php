@extends('layouts.admin')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="bg-white rounded-lg shadow-lg p-6">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold">Order #{{ $order->id }}</h1>
            <div class="flex space-x-2">
                <a href="{{ route('admin.payment-manager.index') }}" class="btn btn-secondary">
                    Back to Payment Manager
                </a>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <div class="bg-gray-50 p-4 rounded">
                <h2 class="text-lg font-semibold mb-4">Order Information</h2>
                <div class="space-y-2">
                    <p><strong>Order Type:</strong> {{ ucfirst($order->order_type) }}</p>
                    <p><strong>Status:</strong> {{ ucfirst($order->status) }}</p>
                    <p><strong>Total Amount:</strong> ${{ number_format($order->total, 2) }}</p>
                    <p><strong>Created At:</strong> {{ $order->created_at->format('M d, Y H:i') }}</p>
                    @if($order->table)
                        <p><strong>Table:</strong> {{ $order->table->number }}</p>
                    @endif
                </div>
            </div>

            <div class="bg-gray-50 p-4 rounded">
                <h2 class="text-lg font-semibold mb-4">Customer Information</h2>
                <div class="space-y-2">
                    @if($order->user)
                        <p><strong>Name:</strong> {{ $order->user->name }}</p>
                        <p><strong>Email:</strong> {{ $order->user->email }}</p>
                    @else
                        <p>Walk-in customer</p>
                    @endif
                </div>
            </div>
        </div>

        <div class="mb-6">
            <h2 class="text-lg font-semibold mb-4">Order Items</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full bg-white">
                    <thead>
                        <tr>
                            <th class="px-4 py-2 text-left">Item</th>
                            <th class="px-4 py-2 text-right">Quantity</th>
                            <th class="px-4 py-2 text-right">Price</th>
                            <th class="px-4 py-2 text-right">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($order->items as $item)
                            <tr>
                                <td class="px-4 py-2">{{ $item->product->name }}</td>
                                <td class="px-4 py-2 text-right">{{ $item->quantity }}</td>
                                <td class="px-4 py-2 text-right">${{ number_format($item->price, 2) }}</td>
                                <td class="px-4 py-2 text-right">${{ number_format($item->quantity * $item->price, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="3" class="px-4 py-2 text-right font-bold">Subtotal:</td>
                            <td class="px-4 py-2 text-right font-bold">${{ number_format($order->subtotal, 2) }}</td>
                        </tr>
                        <tr>
                            <td colspan="3" class="px-4 py-2 text-right font-bold">Tax:</td>
                            <td class="px-4 py-2 text-right font-bold">${{ number_format($order->tax, 2) }}</td>
                        </tr>
                        <tr>
                            <td colspan="3" class="px-4 py-2 text-right font-bold">Discount:</td>
                            <td class="px-4 py-2 text-right font-bold">${{ number_format($order->discount, 2) }}</td>
                        </tr>
                        <tr>
                            <td colspan="3" class="px-4 py-2 text-right font-bold">Total:</td>
                            <td class="px-4 py-2 text-right font-bold">${{ number_format($order->total, 2) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        @if($order->status !== 'completed')
            <div class="bg-gray-50 p-4 rounded">
                <h2 class="text-lg font-semibold mb-4">Process Payment</h2>
                <form id="paymentForm" class="space-y-4">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Amount</label>
                            <input type="number" name="amount" step="0.01" min="0" max="{{ $order->total }}" 
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                   value="{{ $order->total }}" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Payment Method</label>
                            <select name="payment_method" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" required>
                                <option value="cash">Cash</option>
                                <option value="card">Card</option>
                                <option value="wallet">Wallet</option>
                            </select>
                        </div>
                        <div id="referenceNumberField" class="hidden">
                            <label class="block text-sm font-medium text-gray-700">Reference Number</label>
                            <input type="text" name="reference_number" 
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                    </div>
                    <div class="flex justify-end">
                        <button type="submit" class="btn btn-primary">
                            Process Payment
                        </button>
                    </div>
                </form>
            </div>
        @endif

        @if($order->payments->count() > 0)
            <div class="mt-6">
                <h2 class="text-lg font-semibold mb-4">Payment History</h2>
                <div class="overflow-x-auto">
                    <table class="min-w-full bg-white">
                        <thead>
                            <tr>
                                <th class="px-4 py-2 text-left">Date</th>
                                <th class="px-4 py-2 text-left">Method</th>
                                <th class="px-4 py-2 text-right">Amount</th>
                                <th class="px-4 py-2 text-left">Reference</th>
                                <th class="px-4 py-2 text-left">Processed By</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($order->payments as $payment)
                                <tr>
                                    <td class="px-4 py-2">{{ $payment->created_at->format('M d, Y H:i') }}</td>
                                    <td class="px-4 py-2">{{ ucfirst($payment->payment_method) }}</td>
                                    <td class="px-4 py-2 text-right">${{ number_format($payment->amount, 2) }}</td>
                                    <td class="px-4 py-2">{{ $payment->reference_number ?? '-' }}</td>
                                    <td class="px-4 py-2">{{ $payment->processedBy->name ?? '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const paymentForm = document.getElementById('paymentForm');
    const paymentMethodSelect = document.querySelector('select[name="payment_method"]');
    const referenceNumberField = document.getElementById('referenceNumberField');
    const referenceNumberInput = document.querySelector('input[name="reference_number"]');

    paymentMethodSelect.addEventListener('change', function() {
        if (this.value === 'card') {
            referenceNumberField.classList.remove('hidden');
            referenceNumberInput.required = true;
        } else {
            referenceNumberField.classList.add('hidden');
            referenceNumberInput.required = false;
        }
    });

    paymentForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        
        fetch('{{ route("admin.payment-manager.order.process-payment", $order->id) }}', {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Payment processed successfully');
                window.location.reload();
            } else {
                alert('Failed to process payment: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while processing the payment');
        });
    });
});
</script>
@endpush
@endsection 