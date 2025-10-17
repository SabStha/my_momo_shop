@extends('layouts.admin')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold text-gray-900">Site Settings</h1>
        </div>

        <form action="{{ route('admin.settings') }}" method="POST" class="bg-white shadow-md rounded-lg p-6">
            @csrf
            @method('PUT')
            
            <div class="space-y-8">
                <!-- Bulk Order Settings Section -->
                <div class="border-b border-gray-200 pb-6">
                    <h2 class="text-xl font-semibold text-gray-900 mb-4">📦 Bulk Order Settings</h2>
                    
                    <div class="space-y-4">
                        <!-- Bulk Discount Percentage -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Bulk Discount Percentage</label>
                            <div class="relative max-w-xs">
                                <input type="number" 
                                       name="bulk_discount_percentage" 
                                       value="{{ old('bulk_discount_percentage', $bulkDiscountPercentage) }}" 
                                       min="0" 
                                       max="100" 
                                       step="0.1"
                                       required
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                    <span class="text-gray-500 sm:text-sm">%</span>
                                </div>
                            </div>
                            <p class="text-sm text-gray-600 mt-1">
                                This percentage will be automatically deducted from regular prices to calculate bulk prices. 
                                For example, if set to 15%, a Rs. 100 item will have a bulk price of Rs. 85.
                            </p>
                            @error('bulk_discount_percentage')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Current Setting Display -->
                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                            <h3 class="text-sm font-medium text-blue-900 mb-2">Current Setting</h3>
                            <p class="text-sm text-blue-700">
                                Bulk orders currently receive a <strong>{{ $bulkDiscountPercentage }}%</strong> discount on all items.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Future Settings Sections -->
                <div class="border-b border-gray-200 pb-6">
                    <h2 class="text-xl font-semibold text-gray-900 mb-4">🔧 General Settings</h2>
                    <p class="text-gray-600">More settings will be added here in the future.</p>
                </div>

                <div class="border-b border-gray-200 pb-6">
                    <h2 class="text-xl font-semibold text-gray-900 mb-4">💳 Payment Settings</h2>
                    <p class="text-gray-600">Payment-related settings will be added here in the future.</p>
                </div>

                <div>
                    <h2 class="text-xl font-semibold text-gray-900 mb-4">📧 Notification Settings</h2>
                    <p class="text-gray-600">Notification settings will be added here in the future.</p>
                </div>
            </div>

            <div class="flex justify-end space-x-4 mt-8">
                <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition">
                    Save Settings
                </button>
            </div>
        </form>
    </div>
</div>
@endsection






