@extends('layouts.admin')

@section('title', 'Orders')

@section('content')
<div class="container mx-auto p-4">
    <div class="flex justify-between items-center mb-8">
        <h1 class="text-xl font-semibold">Orders</h1>
    </div>

    @if(session('success'))
        <div class="bg-green-100 text-green-800 p-4 rounded mb-6">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="bg-red-100 text-red-800 p-4 rounded mb-6">
            {{ session('error') }}
        </div>
    @endif

    <div class="bg-white shadow-md rounded-lg">
        <div class="p-4">
            <div class="overflow-x-auto">
                <table class="min-w-full bg-white">
                    <thead>
                        <tr>
                            <th class="py-2 px-4 border-b">Order ID</th>
                            <th class="py-2 px-4 border-b">Customer</th>
                            <th class="py-2 px-4 border-b">Date</th>
                            <th class="py-2 px-4 border-b">Total</th>
                            <th class="py-2 px-4 border-b">Status</th>
                            <th class="py-2 px-4 border-b">Payment Status</th>
                            <th class="py-2 px-4 border-b">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($orders as $order)
                            <tr class="hover:bg-gray-100">
                                <td class="py-2 px-4 border-b">#{{ $order->id }}</td>
                                <td class="py-2 px-4 border-b">{{ $order->user->name ?? 'Guest' }}</td>
                                <td class="py-2 px-4 border-b">{{ $order->created_at->format('M d, Y H:i') }}</td>
                                <td class="py-2 px-4 border-b">${{ number_format($order->total_amount, 2) }}</td>
                                <td class="py-2 px-4 border-b">
                                    <span class="px-2 py-1 rounded-full text-white bg-{{ $order->status === 'completed' ? 'green-500' : ($order->status === 'cancelled' ? 'red-500' : 'yellow-500') }}">
                                        {{ ucfirst($order->status) }}
                                    </span>
                                </td>
                                <td class="py-2 px-4 border-b">
                                    <span class="px-2 py-1 rounded-full text-white bg-{{ $order->payment_status === 'paid' ? 'green-500' : ($order->payment_status === 'failed' ? 'red-500' : 'yellow-500') }}">
                                        {{ ucfirst($order->payment_status) }}
                                    </span>
                                </td>
                                <td class="py-2 px-4 border-b">
                                    <div class="flex space-x-2">
                                        <a href="{{ route('admin.orders.show', $order) }}" class="text-blue-600 hover:text-blue-800">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <form action="{{ route('admin.orders.destroy', $order) }}" method="POST" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-800" onclick="return confirm('Are you sure you want to delete this order?')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4">No orders found</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $orders->links() }}
            </div>
        </div>
    </div>
</div>
@endsection 