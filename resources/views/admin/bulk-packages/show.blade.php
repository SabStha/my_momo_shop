@extends('layouts.admin')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold text-gray-900">Bulk Package Details</h1>
            <div class="flex space-x-3">
                <a href="{{ route('admin.bulk-packages.edit', $bulkPackage) }}" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition">
                    Edit Package
                </a>
                <a href="{{ route('admin.bulk-packages.index') }}" class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 transition">
                    Back to Packages
                </a>
            </div>
        </div>

        <div class="bg-white shadow-md rounded-lg overflow-hidden">
            <!-- Package Header -->
            <div class="bg-gradient-to-r from-blue-50 to-indigo-50 px-6 py-8">
                <div class="flex items-center space-x-4">
                    <div class="text-6xl">{{ $bulkPackage->emoji }}</div>
                    <div>
                        <h2 class="text-2xl font-bold text-gray-900">{{ $bulkPackage->name }}</h2>
                        <p class="text-gray-600 mt-1">{{ $bulkPackage->description }}</p>
                        <div class="flex items-center space-x-4 mt-3">
                            <span class="inline-flex px-3 py-1 text-sm font-semibold rounded-full 
                                {{ $bulkPackage->type === 'cooked' ? 'bg-red-100 text-red-800' : 'bg-blue-100 text-blue-800' }}">
                                {{ ucfirst($bulkPackage->type) }}
                            </span>
                            <span class="inline-flex px-3 py-1 text-sm font-semibold rounded-full 
                                {{ $bulkPackage->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                {{ $bulkPackage->is_active ? 'Active' : 'Inactive' }}
                            </span>
                            <span class="text-sm text-gray-500">Sort Order: {{ $bulkPackage->sort_order }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Package Details -->
            <div class="px-6 py-6">
                <div class="grid md:grid-cols-2 gap-8">
                    <!-- Package Information -->
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Package Information</h3>
                        <dl class="space-y-3">
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Package Key</dt>
                                <dd class="text-sm text-gray-900 font-mono bg-gray-100 px-2 py-1 rounded">{{ $bulkPackage->package_key }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Total Price</dt>
                                <dd class="text-2xl font-bold text-green-600">Rs. {{ number_format($bulkPackage->total_price, 2) }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Created</dt>
                                <dd class="text-sm text-gray-900">{{ $bulkPackage->created_at->format('M d, Y \a\t g:i A') }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Last Updated</dt>
                                <dd class="text-sm text-gray-900">{{ $bulkPackage->updated_at->format('M d, Y \a\t g:i A') }}</dd>
                            </div>
                        </dl>
                    </div>

                    <!-- Package Items -->
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Package Items</h3>
                        <div class="space-y-3">
                            @if($bulkPackage->items && count($bulkPackage->items) > 0)
                                @foreach($bulkPackage->items as $index => $item)
                                <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                                    <div>
                                        <div class="font-medium text-gray-900">{{ $item['name'] ?? 'Unnamed Item' }}</div>
                                        <div class="text-sm text-gray-500">Item #{{ $index + 1 }}</div>
                                    </div>
                                    <div class="text-right">
                                        <div class="font-semibold text-gray-900">Rs. {{ number_format($item['price'] ?? 0, 2) }}</div>
                                    </div>
                                </div>
                                @endforeach
                                
                                <!-- Total -->
                                <div class="flex justify-between items-center p-4 bg-blue-50 rounded-lg border-2 border-blue-200">
                                    <div class="font-bold text-gray-900">Total Package Price</div>
                                    <div class="text-xl font-bold text-blue-600">Rs. {{ number_format($bulkPackage->total_price, 2) }}</div>
                                </div>
                            @else
                                <div class="text-center py-8 text-gray-500">
                                    <div class="text-4xl mb-2">📦</div>
                                    <p>No items configured for this package</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="bg-gray-50 px-6 py-4 border-t">
                <div class="flex justify-between items-center">
                    <div class="flex space-x-3">
                        <form action="{{ route('admin.bulk-packages.toggle-status', $bulkPackage) }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="bg-yellow-600 text-white px-4 py-2 rounded-lg hover:bg-yellow-700 transition">
                                {{ $bulkPackage->is_active ? 'Deactivate Package' : 'Activate Package' }}
                            </button>
                        </form>
                    </div>
                    <div class="flex space-x-3">
                        <a href="{{ route('admin.bulk-packages.edit', $bulkPackage) }}" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition">
                            Edit Package
                        </a>
                        <form action="{{ route('admin.bulk-packages.destroy', $bulkPackage) }}" method="POST" class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 transition" 
                                    onclick="return confirm('Are you sure you want to delete this package? This action cannot be undone.')">
                                Delete Package
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

