@extends('layouts.admin')

@section('title', 'Payment Management')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="bg-white rounded-lg shadow-md p-6">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-semibold text-gray-800">Payment Management</h2>
        </div>

        <!-- Statistics -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-white rounded-lg shadow p-4">
                <div class="text-sm text-gray-500">Total Payments</div>
                <div class="text-2xl font-semibold">{{ $totalPayments }}</div>
            </div>
            <div class="bg-white rounded-lg shadow p-4">
                <div class="text-sm text-gray-500">Today's Revenue</div>
                <div class="text-2xl font-semibold">${{ number_format($todayRevenue, 2) }}</div>
            </div>
            <div class="bg-white rounded-lg shadow p-4">
                <div class="text-sm text-gray-500">Pending Payments</div>
                <div class="text-2xl font-semibold">{{ $pendingPayments }}</div>
            </div>
            <div class="bg-white rounded-lg shadow p-4">
                <div class="text-sm text-gray-500">Failed Payments</div>
                <div class="text-2xl font-semibold">{{ $failedPayments }}</div>
            </div>
        </div>

        <!-- Filters -->
        <div class="mb-6">
            <form action="{{ route('admin.payments.index') }}" method="GET" class="flex flex-wrap gap-4">
                <div class="flex-1">
                    <input type="text" 
                           name="search" 
                           value="{{ request('search') }}"
                           placeholder="Search by ID or Order Number" 
                           class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                </div>
                <div class="w-48">
                    <select name="method" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                        <option value="">All Methods</option>
                        @foreach($paymentMethods as $method)
                            <option value="{{ $method->code }}" {{ request('method') == $method->code ? 'selected' : '' }}>
                                {{ $method->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="w-48">
                    <input type="date" 
                           name="date" 
                           value="{{ request('date') }}"
                           class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                </div>
                <div>
                    <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                        Filter
                    </button>
                </div>
            </form>
        </div>

        <!-- Payments Table -->
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Order</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Method</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created At</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($payments as $payment)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">#{{ $payment->id }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">{{ $payment->order->order_number }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">${{ number_format($payment->amount, 2) }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">{{ $payment->method->name }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                {{ $payment->status === 'completed' ? 'bg-green-100 text-green-800' : 
                                   ($payment->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : 
                                   ($payment->status === 'failed' ? 'bg-red-100 text-red-800' : 'bg-gray-100 text-gray-800')) }}">
                                {{ ucfirst($payment->status) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $payment->created_at->format('M d, Y H:i') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <button onclick="viewPayment({{ $payment->id }})" class="text-indigo-600 hover:text-indigo-900 mr-3">View</button>
                            @if($payment->status === 'pending')
                                <button onclick="cancelPayment({{ $payment->id }})" class="text-red-600 hover:text-red-900">Cancel</button>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $payments->links() }}
        </div>
    </div>

    <!-- Payment Details Modal -->
    <div id="paymentModal" class="fixed inset-0 bg-gray-500 bg-opacity-75 flex items-center justify-center hidden">
        <div class="bg-white rounded-lg shadow-xl p-6 max-w-2xl w-full">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-medium text-gray-900">Payment Details</h3>
                <button onclick="closePaymentModal()" class="text-gray-400 hover:text-gray-500">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div id="paymentDetails" class="space-y-4">
                <!-- Payment details will be loaded here -->
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function viewPayment(paymentId) {
    fetch(`/admin/payments/${paymentId}`)
        .then(response => response.json())
        .then(data => {
            document.getElementById('paymentDetails').innerHTML = `
                <div>
                    <h4 class="text-sm font-medium text-gray-500">Payment Information</h4>
                    <div class="mt-2 grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-sm text-gray-500">Payment ID</p>
                            <p class="text-sm font-medium text-gray-900">#${data.id}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Amount</p>
                            <p class="text-sm font-medium text-gray-900">$${data.amount}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Status</p>
                            <p class="text-sm font-medium text-gray-900">${data.status}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Payment Method</p>
                            <p class="text-sm font-medium text-gray-900">${data.method.name}</p>
                        </div>
                    </div>
                </div>
                <div>
                    <h4 class="text-sm font-medium text-gray-500">Order Information</h4>
                    <div class="mt-2 grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-sm text-gray-500">Order Number</p>
                            <p class="text-sm font-medium text-gray-900">${data.order.order_number}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Order Total</p>
                            <p class="text-sm font-medium text-gray-900">$${data.order.total}</p>
                        </div>
                    </div>
                </div>
                <div>
                    <h4 class="text-sm font-medium text-gray-500">Timeline</h4>
                    <div class="mt-2 space-y-2">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <i class="fas fa-circle text-green-500"></i>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-gray-900">Payment Created</p>
                                <p class="text-sm text-gray-500">${data.created_at}</p>
                            </div>
                        </div>
                        ${data.completed_at ? `
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <i class="fas fa-circle text-green-500"></i>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-gray-900">Payment Completed</p>
                                <p class="text-sm text-gray-500">${data.completed_at}</p>
                            </div>
                        </div>
                        ` : ''}
                        ${data.cancelled_at ? `
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <i class="fas fa-circle text-red-500"></i>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-gray-900">Payment Cancelled</p>
                                <p class="text-sm text-gray-500">${data.cancelled_at}</p>
                            </div>
                        </div>
                        ` : ''}
                    </div>
                </div>
            `;
            document.getElementById('paymentModal').classList.remove('hidden');
        });
}

function closePaymentModal() {
    document.getElementById('paymentModal').classList.add('hidden');
}

function cancelPayment(paymentId) {
    if (confirm('Are you sure you want to cancel this payment?')) {
        fetch(`/admin/payments/${paymentId}/cancel`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.reload();
            } else {
                alert(data.error || 'Failed to cancel payment');
            }
        });
    }
}
</script>
@endpush
@endsection 