@extends('layouts.admin')

@section('title', 'Session Details')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="mb-6">
        <a href="{{ route('admin.sessions.index') }}" class="text-indigo-600 hover:text-indigo-900">
            <i class="fas fa-arrow-left mr-2"></i> Back to Sessions
        </a>
    </div>

    {{-- Session Information --}}
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <div class="flex justify-between items-start mb-6">
            <div>
                <h2 class="text-2xl font-semibold text-gray-800">Session #{{ $session->id }}</h2>
                <p class="text-sm text-gray-600">
                    {{ $session->opened_at->format('M d, Y H:i') }} - 
                    {{ $session->closed_at ? $session->closed_at->format('M d, Y H:i') : 'Active' }}
                </p>
            </div>
            <span class="px-3 py-1 text-sm font-semibold rounded-full 
                {{ $session->status === 'active' ? 'bg-green-100 text-green-800' : 
                   ($session->status === 'closed' ? 'bg-gray-100 text-gray-800' : 
                   'bg-red-100 text-red-800') }}">
                {{ ucfirst($session->status) }}
            </span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div>
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Session Details</h3>
                <dl class="space-y-3">
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Opened By</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $session->openedBy->name }}</dd>
                    </div>
                    @if($session->closed_by)
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Closed By</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $session->closedBy->name }}</dd>
                    </div>
                    @endif
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Duration</dt>
                        <dd class="mt-1 text-sm text-gray-900">
                            @if($session->closed_at)
                                {{ $session->opened_at->diffInHours($session->closed_at) }} hours
                            @else
                                {{ $session->opened_at->diffForHumans() }}
                            @endif
                        </dd>
                    </div>
                </dl>
            </div>

            <div>
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Cash Movement</h3>
                <dl class="space-y-3">
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Opening Cash</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ number_format($session->opening_cash, 2) }}</dd>
                    </div>
                    @if($session->closed_at)
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Closing Cash</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ number_format($session->closing_cash, 2) }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Cash Difference</dt>
                        <dd class="mt-1 text-sm text-gray-900">
                            {{ number_format($session->closing_cash - $session->opening_cash, 2) }}
                        </dd>
                    </div>
                    @endif
                </dl>
            </div>

            <div>
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Sales Summary</h3>
                <dl class="space-y-3">
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Total Sales</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ number_format($session->total_sales, 2) }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Total Orders</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $session->total_orders }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Voided Orders</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $session->voided_orders }}</dd>
                    </div>
                </dl>
            </div>
        </div>

        @if($session->notes)
        <div class="mt-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-2">Notes</h3>
            <p class="text-sm text-gray-600">{{ $session->notes }}</p>
        </div>
        @endif
    </div>

    {{-- Payment Methods Summary --}}
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Payment Methods Summary</h3>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            @php
                $paymentMethods = $session->orders()
                    ->where('status', 'completed')
                    ->with('payment')
                    ->get()
                    ->pluck('payment.paymentMethod')
                    ->groupBy('id')
                    ->map(function($group) {
                        return [
                            'name' => $group->first()->name,
                            'count' => $group->count(),
                            'total' => $group->sum('amount')
                        ];
                    });
            @endphp

            @forelse($paymentMethods as $method)
            <div class="border rounded-lg p-4">
                <h4 class="font-semibold text-gray-800">{{ $method['name'] }}</h4>
                <div class="mt-2 space-y-1">
                    <p class="text-sm text-gray-600">Orders: {{ $method['count'] }}</p>
                    <p class="text-sm text-gray-600">Total: {{ number_format($method['total'], 2) }}</p>
                </div>
            </div>
            @empty
            <div class="col-span-4 text-center text-gray-500">No payment data available.</div>
            @endforelse
        </div>
    </div>

    {{-- Orders List --}}
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="px-6 py-4 border-b">
            <h3 class="text-lg font-semibold text-gray-800">Orders</h3>
        </div>
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Order ID</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Payment Method</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($session->orders as $order)
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap">#{{ $order->id }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        {{ $order->customer ? $order->customer->name : 'Walk-in Customer' }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">{{ number_format($order->total_amount, 2) }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        {{ $order->payment ? $order->payment->paymentMethod->name : '-' }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                            {{ $order->status === 'completed' ? 'bg-green-100 text-green-800' : 
                               ($order->status === 'voided' ? 'bg-red-100 text-red-800' : 
                               'bg-yellow-100 text-yellow-800') }}">
                            {{ ucfirst($order->status) }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">{{ $order->created_at->format('M d, Y H:i') }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        <a href="{{ route('admin.orders.show', $order) }}" class="text-indigo-600 hover:text-indigo-900">View</a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-6 py-4 text-center text-gray-500">No orders found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection 