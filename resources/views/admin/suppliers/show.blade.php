@extends('layouts.admin')

@section('title', 'Supplier Details')

@section('content')
<div class="space-y-6">
    <div class="flex justify-between items-center">
        <h2 class="text-2xl font-semibold">Supplier Details</h2>
        <div class="space-x-4">
            <a href="{{ route('admin.suppliers.edit', $supplier) }}" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                <i class="fas fa-edit mr-2"></i> Edit Supplier
            </a>
            <a href="{{ route('admin.suppliers.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2">
                <i class="fas fa-arrow-left mr-2"></i> Back to Suppliers
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="bg-white shadow rounded-lg overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h5 class="text-lg font-medium text-gray-900">Supplier Information</h5>
            </div>
            <div class="p-6">
                <dl class="divide-y divide-gray-200">
                    <div class="py-3 flex justify-between">
                        <dt class="text-sm font-medium text-gray-500 w-48">Name</dt>
                        <dd class="text-sm text-gray-900">{{ $supplier->name }}</dd>
                    </div>
                    <div class="py-3 flex justify-between">
                        <dt class="text-sm font-medium text-gray-500 w-48">Contact Person</dt>
                        <dd class="text-sm text-gray-900">{{ $supplier->contact ?? 'N/A' }}</dd>
                    </div>
                    <div class="py-3 flex justify-between">
                        <dt class="text-sm font-medium text-gray-500 w-48">Email</dt>
                        <dd class="text-sm text-gray-900">{{ $supplier->email ?? 'N/A' }}</dd>
                    </div>
                    <div class="py-3 flex justify-between">
                        <dt class="text-sm font-medium text-gray-500 w-48">Phone</dt>
                        <dd class="text-sm text-gray-900">{{ $supplier->phone ?? 'N/A' }}</dd>
                    </div>
                    <div class="py-3 flex justify-between">
                        <dt class="text-sm font-medium text-gray-500 w-48">Address</dt>
                        <dd class="text-sm text-gray-900">{{ $supplier->address ?? 'N/A' }}</dd>
                    </div>
                    <div class="py-3 flex justify-between">
                        <dt class="text-sm font-medium text-gray-500 w-48">Created At</dt>
                        <dd class="text-sm text-gray-900">{{ $supplier->created_at->format('M d, Y H:i') }}</dd>
                    </div>
                    <div class="py-3 flex justify-between">
                        <dt class="text-sm font-medium text-gray-500 w-48">Last Updated</dt>
                        <dd class="text-sm text-gray-900">{{ $supplier->updated_at->format('M d, Y H:i') }}</dd>
                    </div>
                </dl>
            </div>
        </div>

        <div class="bg-white shadow rounded-lg overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h5 class="text-lg font-medium text-gray-900">Supplied Items</h5>
            </div>
            <div class="p-6">
                @if($supplier->items->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">SKU</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($supplier->items as $item)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            <a href="{{ route('admin.inventory.show', $item) }}" class="text-blue-600 hover:text-blue-900">
                                                {{ $item->name }}
                                            </a>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $item->sku }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $item->quantity }} {{ $item->unit }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                                {{ $item->status === 'active' ? 'bg-green-100 text-green-800' : 
                                                   ($item->status === 'inactive' ? 'bg-yellow-100 text-yellow-800' : 
                                                   'bg-red-100 text-red-800') }}">
                                                {{ ucfirst($item->status) }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-sm text-gray-500">No items supplied by this supplier.</p>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection 
