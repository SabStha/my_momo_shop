@extends('layouts.admin')

@section('title', 'Site Settings')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Site Settings</h1>
        <p class="mt-2 text-gray-600">Manage your website's contact information, social media links, and business details.</p>
    </div>

    @if(session('success'))
        <div class="mb-6 bg-green-50 border border-green-200 rounded-lg p-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
                </div>
            </div>
        </div>
    @endif

    <form action="{{ route('admin.site-settings.update') }}" method="POST">
        @csrf
        @method('PUT')

        <!-- Contact Information -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-4 flex items-center">
                <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                </svg>
                Contact Information
            </h2>
            
            @if(isset($settings['contact']))
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    @foreach($settings['contact'] as $setting)
                        <div>
                            <label for="{{ $setting->key }}" class="block text-sm font-medium text-gray-700 mb-2">
                                {{ $setting->label }}
                                @if($setting->description)
                                    <span class="text-xs text-gray-500 block">{{ $setting->description }}</span>
                                @endif
                            </label>
                            <input 
                                type="{{ $setting->type }}" 
                                name="settings[{{ $setting->key }}]" 
                                id="{{ $setting->key }}"
                                value="{{ old('settings.' . $setting->key, $setting->value) }}"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                placeholder="{{ $setting->label }}"
                            >
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <!-- Business Hours -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-4 flex items-center">
                <svg class="w-5 h-5 mr-2 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Business Hours
            </h2>
            
            @if(isset($settings['business']))
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    @foreach($settings['business'] as $setting)
                        <div>
                            <label for="{{ $setting->key }}" class="block text-sm font-medium text-gray-700 mb-2">
                                {{ $setting->label }}
                                @if($setting->description)
                                    <span class="text-xs text-gray-500 block">{{ $setting->description }}</span>
                                @endif
                            </label>
                            <input 
                                type="text" 
                                name="settings[{{ $setting->key }}]" 
                                id="{{ $setting->key }}"
                                value="{{ old('settings.' . $setting->key, $setting->value) }}"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500"
                                placeholder="{{ $setting->label }}"
                            >
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <!-- Social Media -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-4 flex items-center">
                <svg class="w-5 h-5 mr-2 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 4V2a1 1 0 011-1h8a1 1 0 011 1v2m-9 0h10m-10 0a2 2 0 00-2 2v14a2 2 0 002 2h10a2 2 0 002-2V6a2 2 0 00-2-2"/>
                </svg>
                Social Media Links
            </h2>
            
            @if(isset($settings['social']))
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    @foreach($settings['social'] as $setting)
                        <div>
                            <label for="{{ $setting->key }}" class="block text-sm font-medium text-gray-700 mb-2">
                                {{ $setting->label }}
                                @if($setting->description)
                                    <span class="text-xs text-gray-500 block">{{ $setting->description }}</span>
                                @endif
                            </label>
                            <input 
                                type="url" 
                                name="settings[{{ $setting->key }}]" 
                                id="{{ $setting->key }}"
                                value="{{ old('settings.' . $setting->key, $setting->value) }}"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-purple-500 focus:border-purple-500"
                                placeholder="https://example.com"
                            >
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <!-- General Settings -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-4 flex items-center">
                <svg class="w-5 h-5 mr-2 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                General Settings
            </h2>
            
            @if(isset($settings['general']))
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    @foreach($settings['general'] as $setting)
                        <div>
                            <label for="{{ $setting->key }}" class="block text-sm font-medium text-gray-700 mb-2">
                                {{ $setting->label }}
                                @if($setting->description)
                                    <span class="text-xs text-gray-500 block">{{ $setting->description }}</span>
                                @endif
                            </label>
                            <input 
                                type="text" 
                                name="settings[{{ $setting->key }}]" 
                                id="{{ $setting->key }}"
                                value="{{ old('settings.' . $setting->key, $setting->value) }}"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-gray-500 focus:border-gray-500"
                                placeholder="{{ $setting->label }}"
                            >
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <!-- Tax & Delivery Settings -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-4 flex items-center">
                <svg class="w-5 h-5 mr-2 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/>
                </svg>
                Tax & Delivery Settings
            </h2>
            
            @if(isset($settings['tax_delivery']))
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    @foreach($settings['tax_delivery'] as $setting)
                        <div>
                            <label for="{{ $setting->key }}" class="block text-sm font-medium text-gray-700 mb-2">
                                {{ $setting->label }}
                                @if($setting->description)
                                    <span class="text-xs text-gray-500 block">{{ $setting->description }}</span>
                                @endif
                            </label>
                            
                            @if($setting->type === 'boolean')
                                <select 
                                    name="settings[{{ $setting->key }}]" 
                                    id="{{ $setting->key }}"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-red-500 focus:border-red-500"
                                >
                                    <option value="1" {{ old('settings.' . $setting->key, $setting->value) == '1' ? 'selected' : '' }}>Enabled</option>
                                    <option value="0" {{ old('settings.' . $setting->key, $setting->value) == '0' ? 'selected' : '' }}>Disabled</option>
                                </select>
                            @else
                                <input 
                                    type="{{ $setting->type }}" 
                                    name="settings[{{ $setting->key }}]" 
                                    id="{{ $setting->key }}"
                                    value="{{ old('settings.' . $setting->key, $setting->value) }}"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-red-500 focus:border-red-500"
                                    placeholder="{{ $setting->label }}"
                                    @if($setting->type === 'number') step="0.01" min="0" @endif
                                >
                            @endif
                        </div>
                    @endforeach
                </div>
                
                <!-- Tax & Delivery Preview -->
                <div class="mt-6 p-4 bg-gray-50 rounded-lg border border-gray-200">
                    <h3 class="text-sm font-semibold text-gray-800 mb-3">💡 Settings Preview</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                        <div>
                            <span class="text-gray-600">Tax Rate:</span>
                            <span class="font-medium text-gray-900 ml-2">
                                @php
                                    $taxRate = $settings['tax_delivery']->where('key', 'tax_rate')->first()->value ?? '13';
                                    $taxEnabled = $settings['tax_delivery']->where('key', 'tax_enabled')->first()->value ?? '1';
                                @endphp
                                {{ $taxEnabled == '1' ? $taxRate . '%' : 'Disabled' }}
                            </span>
                        </div>
                        <div>
                            <span class="text-gray-600">Delivery Fee:</span>
                            <span class="font-medium text-gray-900 ml-2">
                                @php
                                    $deliveryEnabled = $settings['tax_delivery']->where('key', 'delivery_fee_enabled')->first()->value ?? '1';
                                    $baseFee = $settings['tax_delivery']->where('key', 'delivery_fee_base')->first()->value ?? '0';
                                    $perKm = $settings['tax_delivery']->where('key', 'delivery_fee_per_km')->first()->value ?? '0';
                                @endphp
                                @if($deliveryEnabled == '1')
                                    Rs.{{ $baseFee }} + Rs.{{ $perKm }}/km
                                @else
                                    Disabled
                                @endif
                            </span>
                        </div>
                        <div>
                            <span class="text-gray-600">Free Delivery:</span>
                            <span class="font-medium text-gray-900 ml-2">
                                @php
                                    $freeThreshold = $settings['tax_delivery']->where('key', 'free_delivery_threshold')->first()->value ?? '500';
                                @endphp
                                Orders above Rs.{{ $freeThreshold }}
                            </span>
                        </div>
                        <div>
                            <span class="text-gray-600">Delivery Radius:</span>
                            <span class="font-medium text-gray-900 ml-2">
                                @php
                                    $radius = $settings['tax_delivery']->where('key', 'delivery_radius_km')->first()->value ?? '10';
                                @endphp
                                {{ $radius }} km
                            </span>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <!-- Submit Button -->
        <div class="flex justify-end">
            <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors duration-200">
                Save Changes
            </button>
        </div>
    </form>
</div>
@endsection 