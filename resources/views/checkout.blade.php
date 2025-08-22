@extends('layouts.app')

@section('content')
<script>
    // Tax and delivery settings from server
    window.taxDeliverySettings = @json(\App\Services\TaxDeliveryService::getAllSettings());
</script>

<div class="min-h-screen bg-[#F4E9E1] py-4">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Progress Indicator -->
        <div class="mb-8">
            <div class="flex items-center justify-center space-x-4">
                <div class="flex items-center">
                    <div class="w-8 h-8 bg-[#6E0D25] text-white rounded-full flex items-center justify-center text-sm font-semibold">1</div>
                    <span class="ml-2 text-sm font-medium text-[#6E0D25]">Cart</span>
                </div>
                <div class="w-12 h-0.5 bg-[#6E0D25]"></div>
                <div class="flex items-center">
                    <div class="w-8 h-8 bg-[#6E0D25] text-white rounded-full flex items-center justify-center text-sm font-semibold">2</div>
                    <span class="ml-2 text-sm font-medium text-[#6E0D25]">Delivery Info</span>
                </div>
                <div class="w-12 h-0.5 bg-gray-300"></div>
                <div class="flex items-center">
                    <div class="w-8 h-8 bg-gray-300 text-gray-500 rounded-full flex items-center justify-center text-sm font-semibold">3</div>
                    <span class="ml-2 text-sm font-medium text-gray-500">Payment</span>
                </div>
            </div>
        </div>

        <!-- Page Header -->
        <div class="mb-6 text-center">
            <h1 class="text-xl font-bold text-gray-900">Complete Your Order</h1>
            <p class="text-sm text-gray-600 mt-1">Step 2: Enter your delivery information</p>
        </div>

        <div class="grid grid-cols-1 gap-8">
            <!-- Checkout Form -->
            <div class="w-full">
                <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                    <div class="p-4 border-b border-gray-200 bg-gray-50">
                        <h2 class="text-base font-semibold text-gray-900 flex items-center">
                            <svg class="w-4 h-4 mr-2 text-[#6E0D25]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                            Delivery Information
                        </h2>
                        <p class="text-xs text-gray-600 mt-1">Please provide your details for delivery</p>
                    </div>
                    
                    <form id="checkout-form" class="p-6 space-y-6" @if($userData) data-user-logged-in="true" @endif>
                        <!-- Personal Information Section -->
                        <div class="space-y-4">
                            <h3 class="text-md font-semibold text-gray-800 border-b border-gray-200 pb-2">Personal Information</h3>
                            
                            <!-- Name -->
                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                                    Full Name <span class="text-red-500">*</span>
                                    @if($userData && $userData['name'])
                                        <span class="text-xs text-green-600 ml-1">✓ Auto-filled</span>
                                    @endif
                                </label>
                                <input type="text" id="name" name="name" required
                                       value="{{ $userData['name'] ?? '' }}"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#6E0D25] focus:border-[#6E0D25] transition-colors {{ $userData && $userData['name'] ? 'bg-green-50 border-green-200' : '' }}"
                                       oninput="console.log('Name field input:', this.value)"
                                       placeholder="Enter your full name...">
                            </div>

                            <!-- Email -->
                            <div>
                                <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                                    Email Address <span class="text-red-500">*</span>
                                    @if($userData && $userData['email'])
                                        <span class="text-xs text-green-600 ml-1">✓ Auto-filled</span>
                                    @endif
                                </label>
                                <input type="email" id="email" name="email" required
                                       value="{{ $userData['email'] ?? '' }}"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#6E0D25] focus:border-[#6E0D25] transition-colors {{ $userData && $userData['email'] ? 'bg-green-50 border-green-200' : '' }}"
                                       placeholder="Enter your email address...">
                            </div>

                            <!-- Phone Number -->
                            <div>
                                <label for="phone" class="block text-sm font-medium text-gray-700 mb-2">
                                    Phone Number <span class="text-red-500">*</span>
                                    @if($userData && $userData['phone'])
                                        <span class="text-xs text-green-600 ml-1">✓ Auto-filled</span>
                                    @endif
                                </label>
                                <input type="tel" id="phone" name="phone" required
                                       value="{{ $userData['phone'] ?? '' }}"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#6E0D25] focus:border-[#6E0D25] transition-colors {{ $userData && $userData['phone'] ? 'bg-green-50 border-green-200' : '' }}"
                                       placeholder="Enter your phone number...">
                            </div>
                        </div>

                        <!-- Location Section -->
                        <div class="space-y-4">
                            <h3 class="text-md font-semibold text-gray-800 border-b border-gray-200 pb-2">Location & Branch Selection</h3>
                            
                            <!-- GPS Location Button -->
                            <div class="space-y-3">
                                <button type="button" id="use-gps-btn" 
                                        class="w-full bg-[#6E0D25] text-white py-3 px-4 sm:py-4 sm:px-6 rounded-lg font-semibold hover:bg-[#8B0D2F] transition-colors duration-300 flex items-center justify-center gap-2 sm:gap-3 shadow-lg">
                                    <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    </svg>
                                    <span class="text-base sm:text-lg">📍 Use My Current Location</span>
                                </button>
                                <p class="text-xs sm:text-sm text-gray-600 text-center">Tap to detect your location automatically for faster delivery</p>
                            </div>

                            <!-- Manual Entry Option -->
                            <div class="text-center">
                                <span class="text-sm text-gray-500">or</span>
                                <button type="button" id="manual-entry-btn" 
                                        class="ml-2 text-sm text-[#6E0D25] hover:text-[#8B0D2F] underline transition-colors">
                                    Enter address manually
                                </button>
                            </div>

                            <!-- GPS Help Information -->
                            <div class="border border-gray-200 rounded-lg overflow-hidden">
                                <button type="button" id="gps-help-toggle" 
                                        class="w-full bg-gray-50 hover:bg-gray-100 px-4 py-3 flex items-center justify-between transition-colors">
                                    <div class="flex items-center space-x-2">
                                        <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        <span class="text-sm font-medium text-gray-700">💡 Why use GPS?</span>
                                    </div>
                                    <svg class="w-5 h-5 text-gray-600 transform transition-transform" id="gps-help-arrow" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                    </svg>
                                </button>
                                <div id="gps-help-content" class="hidden bg-blue-50 border-t border-blue-200">
                                    <div class="p-4">
                                        <ul class="space-y-2 text-sm text-blue-700">
                                            <li class="flex items-start space-x-2">
                                                <span class="text-blue-500 mt-0.5">•</span>
                                                <span>Automatically finds the nearest branch for fastest delivery</span>
                                            </li>
                                            <li class="flex items-start space-x-2">
                                                <span class="text-blue-500 mt-0.5">•</span>
                                                <span>Calculates accurate delivery fees based on distance</span>
                                            </li>
                                            <li class="flex items-start space-x-2">
                                                <span class="text-blue-500 mt-0.5">•</span>
                                                <span>Shows estimated delivery times</span>
                                            </li>
                                            <li class="flex items-start space-x-2">
                                                <span class="text-blue-500 mt-0.5">•</span>
                                                <span>No need to manually enter your address</span>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>

                            <!-- GPS Location Status -->
                            <div id="gps-status" class="hidden">
                                <div class="flex items-center p-3 bg-blue-50 border border-blue-200 rounded-lg">
                                    <div class="flex-shrink-0">
                                        <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                    </div>
                                    <div class="ml-3">
                                        <p class="text-sm font-medium text-blue-800" id="gps-status-text">Getting your location...</p>
                                        <p class="text-xs text-blue-600" id="gps-coordinates"></p>
                                    </div>
                                </div>
                            </div>

                            <!-- GPS Error Status -->
                            <div id="gps-error" class="hidden">
                                <div class="flex items-center p-3 bg-red-50 border border-red-200 rounded-lg">
                                    <div class="flex-shrink-0">
                                        <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                    </div>
                                    <div class="ml-3">
                                        <p class="text-sm font-medium text-red-800" id="gps-error-text">Unable to get your location</p>
                                        <p class="text-xs text-red-600">Please use manual entry or check your location permissions.</p>
                                    </div>
                                </div>
                            </div>

                            <!-- GPS Success Status -->
                            <div id="gps-success" class="hidden">
                                <div class="flex items-center p-3 bg-green-50 border border-green-200 rounded-lg">
                                    <div class="flex-shrink-0">
                                        <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                    </div>
                                    <div class="ml-3">
                                        <p class="text-sm font-medium text-green-800">Location detected successfully!</p>
                                        <p class="text-xs text-green-600" id="gps-address"></p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Address Section (Hidden by default, shown when manual entry is clicked) -->
                        <div id="address-section" class="space-y-4 hidden">
                            <h3 class="text-md font-semibold text-gray-800 border-b border-gray-200 pb-2">Delivery Address</h3>
                            
                            <!-- City / Municipality (Required) -->
                            <div>
                                <label for="city" class="block text-sm font-medium text-gray-700 mb-2">
                                    City / Municipality <span class="text-red-500">*</span>
                                    @if($userData && $userData['city'])
                                        <span class="text-xs text-green-600 ml-1">✓ Auto-filled</span>
                                    @endif
                                </label>
                                <input type="text" id="city" name="city" required
                                       value="{{ $userData['city'] ?? '' }}"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#6E0D25] focus:border-[#6E0D25] transition-colors {{ $userData && $userData['city'] ? 'bg-green-50 border-green-200' : '' }}"
                                       placeholder="Enter your city or municipality...">
                            </div>

                            <!-- Ward Number (Required) -->
                            <div>
                                <label for="ward_number" class="block text-sm font-medium text-gray-700 mb-2">
                                    Ward Number <span class="text-red-500">*</span>
                                    @if($userData && $userData['ward_number'])
                                        <span class="text-xs text-green-600 ml-1">✓ Auto-filled</span>
                                    @endif
                                </label>
                                <input type="text" id="ward_number" name="ward_number" required
                                       value="{{ $userData['ward_number'] ?? '' }}"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#6E0D25] focus:border-[#6E0D25] transition-colors {{ $userData && $userData['ward_number'] ? 'bg-green-50 border-green-200' : '' }}"
                                       placeholder="Enter your ward number...">
                            </div>

                            <!-- Area / Locality / Tole / Nearby Landmark (Required) -->
                            <div>
                                <label for="area_locality" class="block text-sm font-medium text-gray-700 mb-2">
                                    Area / Locality / Tole / Nearby Landmark <span class="text-red-500">*</span>
                                    @if($userData && $userData['area_locality'])
                                        <span class="text-xs text-green-600 ml-1">✓ Auto-filled</span>
                                    @endif
                                </label>
                                <input type="text" id="area_locality" name="area_locality" required
                                       value="{{ $userData['area_locality'] ?? '' }}"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#6E0D25] focus:border-[#6E0D25] transition-colors {{ $userData && $userData['area_locality'] ? 'bg-green-50 border-green-200' : '' }}"
                                       placeholder="Enter your area, locality, tole, or nearby landmark...">
                            </div>

                            <!-- House / Apartment / Building Name (Optional) -->
                            <div>
                                <label for="building_name" class="block text-sm font-medium text-gray-700 mb-2">
                                    House / Apartment / Building Name 
                                    <span class="text-xs text-gray-500 ml-1">(Optional)</span>
                                    @if($userData && $userData['building_name'])
                                        <span class="text-xs text-green-600 ml-1">✓ Auto-filled</span>
                                    @endif
                                </label>
                                <input type="text" id="building_name" name="building_name"
                                       value="{{ $userData['building_name'] ?? '' }}"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#6E0D25] focus:border-[#6E0D25] transition-colors {{ $userData && $userData['building_name'] ? 'bg-green-50 border-green-200' : '' }}"
                                       placeholder="Enter house, apartment, or building name...">
                            </div>

                            <!-- Detailed Directions / Instructions (Optional) -->
                            <div>
                                <label for="detailed_directions" class="block text-sm font-medium text-gray-700 mb-2">
                                    Additional Instructions 
                                    <span class="text-xs text-gray-500 ml-1">(Optional)</span>
                                    @if($userData && $userData['detailed_directions'])
                                        <span class="text-xs text-green-600 ml-1">✓ Auto-filled</span>
                                    @endif
                                </label>
                                <textarea id="detailed_directions" name="detailed_directions" rows="3"
                                          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#6E0D25] focus:border-[#6E0D25] transition-colors {{ $userData && $userData['detailed_directions'] ? 'bg-green-50 border-green-200' : '' }}"
                                          placeholder="Any additional directions or instructions for delivery...">{{ $userData['detailed_directions'] ?? '' }}</textarea>
                            </div>
                        </div>

                        <!-- Branch Selection Section -->
                        <div class="space-y-4">
                            <div class="space-y-3">
                                <label class="block text-sm font-medium text-gray-700">Select Delivery Branch</label>
                                
                                <!-- Branch Selection Buttons -->
                                <div class="space-y-2">
                                    <button type="button" id="find-nearest-branch-btn" 
                                            class="w-full bg-[#6E0D25] text-white py-3 px-4 rounded-lg font-semibold hover:bg-[#8B0D2F] transition-colors duration-300 flex items-center justify-center gap-2 shadow-lg">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        </svg>
                                        <span>Find Nearest Branch</span>
                                    </button>
                                    
                                    <button type="button" id="show-all-branches-btn" 
                                            class="w-full bg-blue-600 text-white py-3 px-4 rounded-lg font-semibold hover:bg-blue-700 transition-colors duration-300 flex items-center justify-center gap-2 shadow-lg">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                        </svg>
                                        <span>View All Branches</span>
                                    </button>
                                </div>
                            </div>

                            <!-- Branch Selection Help -->
                            <div class="border border-gray-200 rounded-lg overflow-hidden">
                                <button type="button" id="branch-help-toggle" 
                                        class="w-full bg-gray-50 hover:bg-gray-100 px-4 py-3 flex items-center justify-between transition-colors">
                                    <div class="flex items-center space-x-2">
                                        <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        <span class="text-sm font-medium text-gray-700">🏪 Branch Selection Help</span>
                                    </div>
                                    <svg class="w-5 h-5 text-gray-600 transform transition-transform" id="branch-help-arrow" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                    </svg>
                                </button>
                                <div id="branch-help-content" class="hidden bg-green-50 border-t border-green-200">
                                    <div class="p-4">
                                        <ul class="space-y-2 text-sm text-green-700">
                                            <li class="flex items-start space-x-2">
                                                <span class="text-green-500 mt-0.5">•</span>
                                                <span><strong>Find Nearest:</strong> Requires GPS location to find the closest branch for fastest delivery</span>
                                            </li>
                                            <li class="flex items-start space-x-2">
                                                <span class="text-green-500 mt-0.5">•</span>
                                                <span><strong>All Branches:</strong> View all available branches (no GPS required) - shows distance if location available</span>
                                            </li>
                                            <li class="flex items-start space-x-2">
                                                <span class="text-green-500 mt-0.5">•</span>
                                                <span><strong>Faster Delivery:</strong> Closer branches = faster delivery and lower delivery fees</span>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>

                            <!-- Branch Loading Status -->
                            <div id="branch-loading" class="hidden">
                                <div class="flex items-center p-3 bg-blue-50 border border-blue-200 rounded-lg">
                                    <div class="flex-shrink-0">
                                        <svg class="animate-spin h-5 w-5 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                    </div>
                                    <div class="ml-3">
                                        <p class="text-sm font-medium text-blue-800">Finding nearest branches...</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Branch Selection Results -->
                            <div id="branch-selection-results" class="hidden space-y-3">
                                <!-- Nearest Branch Suggestion -->
                                <div id="nearest-branch-suggestion" class="hidden">
                                    <h4 class="text-sm font-semibold text-green-700 mb-2">🚀 Recommended Branch</h4>
                                    <div id="nearest-branch-card" class="bg-green-50 border border-green-200 rounded-lg p-4">
                                        <!-- Nearest branch content will be populated here -->
                                    </div>
                                </div>

                                <!-- All Available Branches -->
                                <div id="all-branches-list" class="hidden">
                                    <h4 class="text-sm font-semibold text-gray-700 mb-2">All Available Branches</h4>
                                    <div id="branches-list" class="space-y-2">
                                        <!-- Branch cards will be populated here -->
                                    </div>
                                </div>
                            </div>

                            <!-- Branch Selection Error -->
                            <div id="branch-error" class="hidden">
                                <div class="flex items-center p-3 bg-red-50 border border-red-200 rounded-lg">
                                    <div class="flex-shrink-0">
                                        <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                    </div>
                                    <div class="ml-3">
                                        <p class="text-sm font-medium text-red-800" id="branch-error-text">Unable to find branches</p>
                                        <p class="text-xs text-red-600">Please try again or contact support.</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Hidden input for selected branch -->
                            <input type="hidden" id="selected-branch-id" name="branch_id" required>
                            <div id="branch-validation-error" class="hidden">
                                <p class="text-sm text-red-600 mt-1">Please select a branch to continue.</p>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="space-y-3 pt-4 border-t border-gray-200">
                            <!-- Move to Payment Button -->
                            <button type="button" 
                                    id="move-to-payment-btn"
                                    class="w-full bg-[#6E0D25] text-white py-3 px-4 rounded-lg font-semibold hover:bg-[#8B0D2F] transition-colors duration-300 flex items-center justify-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                                </svg>
                                Continue to Payment
                            </button>

                            <!-- Back to Cart -->
                            <a href="{{ route('cart') }}" 
                               class="block w-full text-center bg-gray-100 text-gray-700 py-3 px-4 rounded-lg font-semibold hover:bg-gray-200 transition-colors duration-300">
                                ← Back to Cart
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Order Summary -->
            <div class="w-full">
                <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                    <div class="p-4 border-b border-gray-200 bg-gray-50">
                        <h2 class="text-base font-semibold text-gray-900 flex items-center">
                            <svg class="w-4 h-4 mr-2 text-[#6E0D25]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                            </svg>
                            Order Summary
                        </h2>
                    </div>
                    
                    <div class="p-4">
                        <div id="checkout-items" class="space-y-2 mb-4">
                            <!-- Items will be populated by JavaScript -->
                        </div>

                        <!-- Order Totals -->
                        <div class="border-t border-gray-200 pt-3 space-y-2">
                            <div class="flex justify-between text-xs">
                                <span class="text-gray-600">Subtotal</span>
                                <span class="font-medium text-gray-900" id="checkout-subtotal">Rs.0.00</span>
                            </div>
                            
                            <!-- Applied Offer Section -->
                            <div id="checkout-offer-section" style="display: none;">
                                <div class="flex justify-between items-center text-xs bg-green-50 border border-green-200 rounded px-2 py-1 mb-1">
                                    <div>
                                        <span class="font-semibold text-green-700">Offer:</span>
                                        <span class="font-mono text-green-800" id="checkout-offer-code"></span>
                                        <span class="text-xs text-green-600 ml-1" id="checkout-offer-discount"></span>
                                    </div>
                                </div>
                                <div class="flex justify-between text-xs">
                                    <span class="text-green-700">Discount</span>
                                    <span class="font-medium text-green-700" id="checkout-discount-amount">-Rs.0.00</span>
                                </div>
                            </div>
                            
                            <div class="flex justify-between text-xs">
                                <span class="text-gray-600">Delivery Fee</span>
                                <span class="font-medium text-gray-900" id="checkout-delivery">{{ formatPrice(getDeliveryFee()) }}</span>
                            </div>
                            
                            <div class="flex justify-between text-xs">
                                <span class="text-gray-600">Tax ({{ getTaxRate() }}%)</span>
                                <span class="font-medium text-gray-900" id="checkout-tax">Rs.0.00</span>
                            </div>
                            
                            <div class="border-t border-gray-200 pt-2">
                                <div class="flex justify-between text-sm font-bold">
                                    <span class="text-gray-900">Total</span>
                                    <span class="text-[#6E0D25]" id="checkout-total">Rs.0.00</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Cart Modal -->
@include('components.cart-modal')

@endsection

@push('scripts')
<script src="{{ asset('js/cart.js') }}"></script>
<script>
console.log('Checkout script starting...');

// Global variables
let currentLocation = null;

// Enhanced localStorage functions for user data
const UserDataManager = {
    // Save user data to localStorage
    saveUserData: function(data) {
        try {
            const userData = {
                name: data.name || '',
                email: data.email || '',
                phone: data.phone || '',
                city: data.city || '',
                ward_number: data.ward_number || '',
                area_locality: data.area_locality || '',
                building_name: data.building_name || '',
                detailed_directions: data.detailed_directions || '',
                preferred_branch_id: data.preferred_branch_id || '',
                lastUpdated: new Date().toISOString()
            };
            localStorage.setItem('momo_user_data', JSON.stringify(userData));
            console.log('User data saved to localStorage:', userData);
        } catch (error) {
            console.error('Error saving user data:', error);
        }
    },

    // Load user data from localStorage
    loadUserData: function() {
        try {
            const savedData = localStorage.getItem('momo_user_data');
            if (savedData) {
                const userData = JSON.parse(savedData);
                console.log('User data loaded from localStorage:', userData);
                return userData;
            }
        } catch (error) {
            console.error('Error loading user data:', error);
        }
        return null;
    },

    // Auto-fill form with saved data
    autoFillForm: function() {
        console.log('autoFillForm called');
        console.log('Server user data:', @json($userData ?? null));
        
        const userData = this.loadUserData();
        console.log('LocalStorage user data:', userData);
        
        // Get server-side data
        const serverData = @json($userData ?? null);
        
        // Merge server data with localStorage data (localStorage takes precedence for non-empty values)
        const mergedData = {
            name: userData?.name || serverData?.name || '',
            email: userData?.email || serverData?.email || '',
            phone: userData?.phone || serverData?.phone || '',
            city: userData?.city || serverData?.city || '',
            ward_number: userData?.ward_number || serverData?.ward_number || '',
            area_locality: userData?.area_locality || serverData?.area_locality || '',
            building_name: userData?.building_name || serverData?.building_name || '',
            detailed_directions: userData?.detailed_directions || serverData?.detailed_directions || '',
            preferred_branch_id: userData?.preferred_branch_id || serverData?.preferred_branch_id || ''
        };
        
        console.log('Merged data for form filling:', mergedData);
        
        if (mergedData.name || mergedData.email || mergedData.phone || mergedData.city || mergedData.ward_number || mergedData.area_locality) {
            console.log('Filling form with merged data');
            
            // Fill all form fields with merged data
            if (document.getElementById('name')) {
                document.getElementById('name').value = mergedData.name;
                console.log('Filled name:', mergedData.name);
            }
            if (document.getElementById('email')) {
                document.getElementById('email').value = mergedData.email;
                console.log('Filled email:', mergedData.email);
            }
            if (document.getElementById('phone')) {
                document.getElementById('phone').value = mergedData.phone;
                console.log('Filled phone:', mergedData.phone);
            }
            if (document.getElementById('city')) {
                document.getElementById('city').value = mergedData.city;
                console.log('Filled city:', mergedData.city);
            }
            if (document.getElementById('ward_number')) {
                document.getElementById('ward_number').value = mergedData.ward_number;
                console.log('Filled ward_number:', mergedData.ward_number);
            }
            if (document.getElementById('area_locality')) {
                document.getElementById('area_locality').value = mergedData.area_locality;
                console.log('Filled area_locality:', mergedData.area_locality);
            }
            if (document.getElementById('building_name')) {
                document.getElementById('building_name').value = mergedData.building_name;
                console.log('Filled building_name:', mergedData.building_name);
            }
            if (document.getElementById('detailed_directions')) {
                document.getElementById('detailed_directions').value = mergedData.detailed_directions;
                console.log('Filled detailed_directions:', mergedData.detailed_directions);
            }
            
            // Auto-fill branch selection if available
            if (mergedData.preferred_branch_id && document.getElementById('selected-branch-id')) {
                document.getElementById('selected-branch-id').value = mergedData.preferred_branch_id;
                console.log('Filled preferred_branch_id:', mergedData.preferred_branch_id);
                // Trigger branch selection display if branch data is available
                if (window.selectedBranch) {
                    displaySelectedBranch(window.selectedBranch);
                }
            }
            
            console.log('Form auto-filled with merged user data');
        } else {
            console.log('No user data found in localStorage or server');
        }
    },

    // Save current form data
    saveCurrentFormData: function() {
        const formData = {
            name: document.getElementById('name').value.trim(),
            email: document.getElementById('email').value.trim(),
            phone: document.getElementById('phone').value.trim(),
            city: document.getElementById('city').value.trim(),
            ward_number: document.getElementById('ward_number').value.trim(),
            area_locality: document.getElementById('area_locality').value.trim(),
            building_name: document.getElementById('building_name').value.trim(),
            detailed_directions: document.getElementById('detailed_directions').value.trim(),
            preferred_branch_id: document.getElementById('selected-branch-id').value.trim()
        };
        this.saveUserData(formData);
        console.log('Current form data saved to localStorage:', formData);
    }
};

// GPS Location Functions
function showGPSStatus(message, coordinates = '') {
    document.getElementById('gps-status').classList.remove('hidden');
    document.getElementById('gps-error').classList.add('hidden');
    document.getElementById('gps-success').classList.add('hidden');
    document.getElementById('gps-status-text').textContent = message;
    document.getElementById('gps-coordinates').textContent = coordinates;
}

// Tax and delivery settings
window.taxDeliverySettings = @json(\App\Services\TaxDeliveryService::getAllSettings());

// Function to check and request location permission
async function checkLocationPermission() {
    if (!navigator.permissions || !navigator.permissions.query) {
        return 'unknown';
    }
    
    try {
        const result = await navigator.permissions.query({ name: 'geolocation' });
        return result.state;
    } catch (error) {
        console.error('Error checking permission:', error);
        return 'unknown';
    }
}

// Function to update GPS button appearance based on permission status
function updateGPSButtonStatus(permissionStatus) {
    const gpsBtn = document.getElementById('use-gps-btn');
    const manualBtn = document.getElementById('manual-entry-btn');
    
    switch (permissionStatus) {
        case 'denied':
            gpsBtn.classList.add('opacity-50', 'cursor-not-allowed');
            gpsBtn.title = 'Location access blocked. Use Manual Entry instead.';
            manualBtn.classList.remove('opacity-50');
            break;
        case 'granted':
            gpsBtn.classList.remove('opacity-50', 'cursor-not-allowed');
            gpsBtn.title = 'Click to get your current location';
            break;
        case 'prompt':
            gpsBtn.classList.remove('opacity-50', 'cursor-not-allowed');
            gpsBtn.title = 'Click to request location access';
            break;
        default:
            gpsBtn.classList.remove('opacity-50', 'cursor-not-allowed');
            gpsBtn.title = 'Click to get your current location';
            break;
    }
}

// Function to retry GPS after permission fix
async function retryGPS() {
    console.log('Retrying GPS...');
    
    // Hide any existing error messages
    hideAllGPSStatus();
    
    // Check current permission status
    const permissionStatus = await checkLocationPermission();
    
    if (permissionStatus === 'denied') {
        showGPSError('Location access is still blocked');
        const errorDiv = document.getElementById('gps-error');
        const helpText = errorDiv.querySelector('p:last-child');
        helpText.innerHTML = `
            <div class="space-y-2">
                <p class="text-xs text-red-600">Please enable location access in your browser settings:</p>
                <div class="text-xs text-red-600 space-y-1">
                    <p><strong>Desktop:</strong> Click the lock icon in address bar → Allow location</p>
                    <p><strong>Mobile:</strong> Settings → Privacy → Location Services → Enable for browser</p>
                    <p><strong>Alternative:</strong> Use Manual Entry to continue with your order</p>
                </div>
                <div class="flex gap-2 mt-2">
                    <button type="button" onclick="retryGPS()" class="px-3 py-1 text-xs bg-blue-100 text-blue-700 rounded border border-blue-300 hover:bg-blue-200 transition-colors">
                        Try Again
                    </button>
                    <button type="button" onclick="enableManualEntry()" class="px-3 py-1 text-xs bg-red-100 text-red-700 rounded border border-red-300 hover:bg-red-200 transition-colors">
                        Use Manual Entry
                    </button>
                </div>
            </div>
        `;
        return;
    }
    
    // If permission is now granted or prompt, try GPS again
    if (permissionStatus === 'granted' || permissionStatus === 'prompt') {
        await useGPSLocation();
    }
}

// Global GPS functions for inline onclick handlers
function enableManualEntry() {
    // Update button states
    document.getElementById('use-gps-btn').disabled = false;
    document.getElementById('manual-entry-btn').disabled = true;
    
    // Show the address section
    document.getElementById('address-section').classList.remove('hidden');
    
    // Hide all GPS status messages
    hideAllGPSStatus();
    
    // Clear any GPS-derived data
    currentLocation = null;
    
    // Clear form fields if they were filled by GPS
    if (document.getElementById('city').value.includes('GPS:') || 
        document.getElementById('city').value === 'GPS Location') {
        document.getElementById('city').value = '';
    }
    if (document.getElementById('area_locality').value.includes('GPS:')) {
        document.getElementById('area_locality').value = '';
    }
}

// Demo GPS function for testing
async function useDemoGPS() {
    try {
        // Update button states
        document.getElementById('use-gps-btn').disabled = false;
        document.getElementById('demo-gps-btn').disabled = true;
        document.getElementById('manual-entry-btn').disabled = false;
        
        showGPSStatus('Setting demo location...');

        // Use a demo location in Kathmandu (Thamel area)
        const demoCoords = { lat: 27.7172, lng: 85.3240 };
        currentLocation = demoCoords;
        
        // Show success with coordinates
        showGPSSuccess(demoCoords);

        // Fill in demo address
        document.getElementById('city').value = 'Kathmandu';
        document.getElementById('area_locality').value = 'Thamel';
        
        // Update success message with demo address
        document.getElementById('gps-address').textContent = 'Demo Location: Thamel, Kathmandu, Nepal';

        // Automatically find nearest branches after demo location is set
        setTimeout(() => {
            findNearestBranches();
        }, 1000);

    } catch (error) {
        console.error('Demo GPS Error:', error);
        showGPSError('Failed to set demo location');
    }
}

// Function to refresh profile data
window.refreshProfileData = function() {
    console.log('Refreshing profile data...');
    
    // Show loading state
    const refreshBtn = event.target;
    const originalText = refreshBtn.textContent;
    refreshBtn.textContent = 'Loading...';
    refreshBtn.disabled = true;
    
    // Fetch fresh user data using web route
    fetch('/user/profile', {
        method: 'GET',
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Update form fields with fresh data
            document.getElementById('name').value = data.user.name || '';
            document.getElementById('email').value = data.user.email || '';
            document.getElementById('phone').value = data.user.phone || '';
            document.getElementById('city').value = data.user.city || '';
            document.getElementById('ward_number').value = data.user.ward_number || '';
            document.getElementById('area_locality').value = data.user.area_locality || '';
            document.getElementById('building_name').value = data.user.building_name || '';
            document.getElementById('detailed_directions').value = data.user.detailed_directions || '';
            
            // Show success notification
            if (typeof showSuccessNotification === 'function') {
                showSuccessNotification('Profile data refreshed successfully!', null, null);
            } else {
                alert('Profile data refreshed successfully!');
            }
            
            console.log('Profile data refreshed:', data.user);
        } else {
            throw new Error(data.message || 'Failed to refresh profile data');
        }
    })
    .catch(error => {
        console.error('Error refreshing profile data:', error);
        alert('Failed to refresh profile data. Please try again.');
    })
    .finally(() => {
        // Reset button
        refreshBtn.textContent = originalText;
        refreshBtn.disabled = false;
    });
};

// Function to save form data to user profile (optional enhancement)
window.saveFormDataToProfile = function() {
    const formData = {
        name: document.getElementById('name').value,
        email: document.getElementById('email').value,
        phone: document.getElementById('phone').value,
        city: document.getElementById('city').value,
        ward_number: document.getElementById('ward_number').value,
        area_locality: document.getElementById('area_locality').value,
        building_name: document.getElementById('building_name').value,
        detailed_directions: document.getElementById('detailed_directions').value,
        preferred_branch_id: document.getElementById('selected-branch-id').value || null
    };
    
    fetch('/user/profile', {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify(formData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            console.log('Form data saved to profile');
        }
    })
    .catch(error => {
        console.error('Error saving form data to profile:', error);
    });
};

// Function to save current form data to profile with user feedback
window.saveCurrentFormToProfile = function() {
    console.log('Saving current form data to profile...');
    
    // Get current form data
    const formData = {
        name: document.getElementById('name').value.trim(),
        email: document.getElementById('email').value.trim(),
        phone: document.getElementById('phone').value.trim(),
        city: document.getElementById('city').value.trim(),
        ward_number: document.getElementById('ward_number').value.trim(),
        area_locality: document.getElementById('area_locality').value.trim(),
        building_name: document.getElementById('building_name').value.trim(),
        detailed_directions: document.getElementById('detailed_directions').value.trim(),
        preferred_branch_id: document.getElementById('selected-branch-id').value.trim() || null
    };
    
    // Validate that all required fields have data
    if (!formData.name || !formData.email || !formData.phone || !formData.city || !formData.ward_number || !formData.area_locality) {
        alert('Please fill in all required fields (Name, Email, Phone, City, Ward Number, and Area/Locality) before saving to profile.');
        return;
    }
    
    // Show loading state
    const saveBtn = event.target;
    const originalText = saveBtn.textContent;
    saveBtn.textContent = 'Saving...';
    saveBtn.disabled = true;
    
    // Save to profile using web route
    fetch('/user/profile', {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify(formData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Show success notification
            if (typeof showSuccessNotification === 'function') {
                showSuccessNotification(
                    '✅ Profile updated successfully!',
                    null,
                    null
                );
            } else {
                alert('Profile updated successfully!');
            }
            
            console.log('Current form data saved to profile:', formData);
        } else {
            throw new Error(data.message || 'Failed to save profile data');
        }
    })
    .catch(error => {
        console.error('Error saving form data to profile:', error);
        alert('Failed to save profile data. Please try again.');
    })
    .finally(() => {
        // Reset button
        saveBtn.textContent = originalText;
        saveBtn.disabled = false;
    });
};

// Function to validate form and move to payment
function moveToPayment() {
    console.log('moveToPayment called');
    
    // Get form data
    const formData = {
        name: document.getElementById('name').value.trim(),
        email: document.getElementById('email').value.trim(),
        phone: document.getElementById('phone').value.trim(),
        city: document.getElementById('city').value.trim(),
        ward_number: document.getElementById('ward_number').value.trim(),
        area_locality: document.getElementById('area_locality').value.trim(),
        building_name: document.getElementById('building_name').value.trim(),
        detailed_directions: document.getElementById('detailed_directions').value.trim()
    };
    
    // Validate required fields
    const requiredFields = ['name', 'email', 'phone', 'city', 'ward_number', 'area_locality'];
    const missingFields = requiredFields.filter(field => !formData[field]);
    
    if (missingFields.length > 0) {
        alert('Please fill in all required fields: ' + missingFields.join(', '));
        return;
    }
    
    // Validate email format
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(formData.email)) {
        alert('Please enter a valid email address.');
        return;
    }
    
    // Validate phone number (basic validation)
    const phoneRegex = /^[\d\s\-\+\(\)]+$/;
    if (!phoneRegex.test(formData.phone) || formData.phone.length < 10) {
        alert('Please enter a valid phone number.');
        return;
    }
    
    // Save form data to localStorage for payment page
    localStorage.setItem('checkout_data', JSON.stringify(formData));
    
    // Save user data to localStorage for future use
    UserDataManager.saveUserData(formData);
    
    // Save GPS location if available
    if (currentLocation) {
        localStorage.setItem('checkout_gps_location', JSON.stringify(currentLocation));
    }
    
    // Save form data to user profile (if user is logged in)
    saveFormDataToProfile();
    
    // Redirect to payment page
    window.location.href = '{{ route("payment") }}';
}

// Function to auto-select preferred branch
function autoSelectPreferredBranch() {
    // Check if user has a preferred branch from database
    const userData = @json($userData ?? null);
    if (userData && userData.preferred_branch_id) {
        // Set the selected branch ID
        document.getElementById('selected-branch-id').value = userData.preferred_branch_id;
        
        // Fetch branch details and display them
        fetch(`/api/branches/${userData.preferred_branch_id}`)
            .then(response => response.json())
            .then(data => {
                if (data.success && data.branch) {
                    window.selectedBranch = data.branch;
                    displaySelectedBranch(data.branch);
                }
            })
            .catch(error => {
                console.error('Error fetching preferred branch:', error);
            });
    }
}

// Checkout page functionality
document.addEventListener('DOMContentLoaded', function() {
    console.log('Checkout page loaded');
    
    // Auto-fill form with saved user data
    UserDataManager.autoFillForm();
    
    // Auto-select preferred branch if available
    autoSelectPreferredBranch();
    
    // Add event listeners to save form data as user types
    const formFields = ['name', 'email', 'phone', 'city', 'ward_number', 'area_locality', 'building_name', 'detailed_directions'];
    formFields.forEach(fieldId => {
        const field = document.getElementById(fieldId);
        if (field) {
            field.addEventListener('input', function() {
                // Save form data after a short delay to avoid too many saves
                clearTimeout(window.saveFormTimeout);
                window.saveFormTimeout = setTimeout(() => {
                    UserDataManager.saveCurrentFormData();
                }, 1000); // Save after 1 second of no typing
            });
        }
    });
    
    // Try to display cart immediately
    updateCheckoutPage();
    
    // Also set up a fallback in case CartManager loads later
    const checkCartManager = setInterval(() => {
        if (typeof window.cartManager !== 'undefined') {
            console.log('CartManager found, updating checkout');
            clearInterval(checkCartManager);
            updateCheckoutPage(); // Refresh display with CartManager
        }
    }, 100);
    
    // Stop checking after 5 seconds to avoid infinite loop
    setTimeout(() => {
        clearInterval(checkCartManager);
        console.log('CartManager check timeout - using localStorage fallback');
    }, 5000);
    
    // Handle move to payment button click
    document.getElementById('move-to-payment-btn').addEventListener('click', function(e) {
        e.preventDefault();
        validateAndProceedToPayment();
    });
    
    // Listen for localStorage changes (e.g., offer applied from another page)
    window.addEventListener('storage', function(event) {
        if (event.key === 'applied_offer' || event.key === 'momo_cart') {
            console.log('Storage change detected, updating checkout');
            updateCheckoutPage();
        }
    });
    
    // Listen for custom offerApplied event
    window.addEventListener('offerApplied', function(event) {
        console.log('Offer applied event detected, updating checkout');
        updateCheckoutPage();
    });

    // Test GPS functionality on page load
    console.log('🔍 Testing GPS functionality on page load...');
    testGPSFunctionality().catch(error => {
        console.error('Error testing GPS functionality:', error);
    });
});

function updateCheckoutPage() {
    console.log('updateCheckoutPage called');
    
    // Get cart data
    let cart = [];
    let itemCount = 0;
    
    if (window.cartManager && typeof window.cartManager.getCartItems === 'function') {
        console.log('Using cartManager to get cart data');
        cart = window.cartManager.getCartItems();
        itemCount = window.cartManager.getCartItemCount();
    } else {
        console.log('Using localStorage fallback to get cart data');
        // Fallback to localStorage if CartManager is not available
        const storedCart = localStorage.getItem('momo_cart');
        console.log('Stored cart from localStorage:', storedCart);
        cart = JSON.parse(storedCart || '[]');
        itemCount = cart.reduce((sum, item) => sum + item.quantity, 0);
    }
    
    console.log('Cart data:', cart);
    console.log('Item count:', itemCount);
    
    const itemsContainer = document.getElementById('checkout-items');
    
    if (cart.length === 0) {
        console.log('Cart is empty, showing empty state');
        // Show empty state
        itemsContainer.innerHTML = `
            <div class="text-center py-8">
                <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 13m0 0l-2.5 5M7 13l2.5 5m6-5v6a2 2 0 01-2 2H9a2 2 0 01-2-2v-6m8 0V9a2 2 0 00-2-2H9a2 2 0 00-2 2v4.01"></path>
                    </svg>
                </div>
                <h3 class="text-lg font-medium text-gray-900 mb-2">Your cart is empty</h3>
                <p class="text-gray-600 mb-6">Add some items to your cart to see them here.</p>
                <a href="{{ route('home') }}" class="inline-flex items-center px-4 py-2 bg-[#6E0D25] text-white rounded-lg hover:bg-[#8B0D2F] transition-colors">
                    Start Shopping
                </a>
            </div>
        `;
        return;
    }
    
    console.log('Displaying cart items');
    // Display items
    let itemsHtml = '';
    let subtotal = 0;
    
    cart.forEach((item) => {
        const itemTotal = item.price * item.quantity;
        subtotal += itemTotal;
        
        itemsHtml += `
            <div class="flex items-center gap-2 py-1">
                <div class="flex-shrink-0">
                    ${item.image ? 
                        `<img src="${item.image}" alt="${item.name}" class="w-8 h-8 object-cover rounded">` :
                        `<div class="w-8 h-8 bg-gray-200 rounded flex items-center justify-center">
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 002 2v12a2 2 0 002 2z"></path>
                            </svg>
                        </div>`
                    }
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex justify-between items-center">
                        <div>
                            <h3 class="text-xs font-medium text-gray-900 truncate">${item.name}</h3>
                            <p class="text-xs text-gray-500">Qty: ${item.quantity}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-xs font-bold text-[#6E0D25]">Rs.${itemTotal.toFixed(2)}</p>
                        </div>
                    </div>
                </div>
            </div>
        `;
    });
    
    itemsContainer.innerHTML = itemsHtml;
    
    // Calculate totals with offer support
    const deliveryFee = subtotal >= 25 ? 0 : 5;
    const taxRate = window.taxDeliverySettings.tax_rate || 13;
    const tax = subtotal * (taxRate / 100);
    
    // Handle applied offer
    let offer = null;
    let discountAmount = 0;
    
    try {
        // Try to get offer from cartManager first, then fallback to localStorage
        if (window.cartManager && typeof window.cartManager.getAppliedOffer === 'function') {
            offer = window.cartManager.getAppliedOffer();
            console.log('Offer from cartManager:', offer);
        } else {
            // Fallback to localStorage if cartManager is not available
            const storedOffer = localStorage.getItem('applied_offer');
            console.log('Stored offer from localStorage:', storedOffer);
            if (storedOffer) {
                offer = JSON.parse(storedOffer);
            }
        }
        
        if (offer && offer.discount) {
            // Convert discount to number if it's a string
            const discountValue = parseFloat(offer.discount);
            discountAmount = subtotal * (discountValue / 100);
            
            // Show offer section
            document.getElementById('checkout-offer-section').style.display = 'block';
            document.getElementById('checkout-offer-code').textContent = offer.code;
            document.getElementById('checkout-offer-discount').textContent = `(${discountValue}% OFF)`;
            document.getElementById('checkout-discount-amount').textContent = `-Rs.${discountAmount.toFixed(2)}`;
        } else {
            // Hide offer section
            document.getElementById('checkout-offer-section').style.display = 'none';
        }
    } catch (error) {
        console.error('Error processing offer:', error);
        document.getElementById('checkout-offer-section').style.display = 'none';
    }
    
    const total = subtotal + deliveryFee + tax - discountAmount;
    
    console.log('Totals calculated:', { subtotal, deliveryFee, tax, discountAmount, total });
    
    // Update totals
    document.getElementById('checkout-subtotal').textContent = `Rs.${subtotal.toFixed(2)}`;
    document.getElementById('checkout-tax').textContent = `Rs.${tax.toFixed(2)}`;
    document.getElementById('checkout-total').textContent = `Rs.${total.toFixed(2)}`;
    document.getElementById('checkout-delivery').textContent = deliveryFee === 0 ? 'Free' : `Rs.${deliveryFee.toFixed(2)}`;
}

// Test GPS functionality without actual location request
async function testGPSFunctionality() {
    console.log('Testing GPS functionality...');
    
    // Check if geolocation is supported
    if (!navigator.geolocation) {
        console.log('❌ Geolocation not supported');
        updateGPSButtonStatus('denied');
        return false;
    }
    
    // Check if we're on HTTPS or localhost
    const isSecure = window.location.protocol === 'https:' || window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1';
    console.log('🔒 Secure context:', isSecure);
    
    if (!isSecure) {
        console.log('❌ Not in secure context - GPS may not work');
        showGPSError('GPS requires secure connection (HTTPS)');
        const errorDiv = document.getElementById('gps-error');
        const helpText = errorDiv.querySelector('p:last-child');
        helpText.innerHTML = `
            <div class="space-y-2">
                <p class="text-xs text-red-600">GPS location requires a secure connection:</p>
                <div class="text-xs text-red-600 space-y-1">
                    <p>• Use HTTPS instead of HTTP</p>
                    <p>• Or use Manual Entry for your address</p>
                </div>
                <button type="button" onclick="enableManualEntry()" class="mt-2 px-3 py-1 text-xs bg-red-100 text-red-700 rounded border border-red-300 hover:bg-red-200 transition-colors">
                    Use Manual Entry
                </button>
            </div>
        `;
        updateGPSButtonStatus('denied');
        return false;
    }
    
    // Check permissions (if supported)
    if (navigator.permissions && navigator.permissions.query) {
        try {
            const result = await navigator.permissions.query({ name: 'geolocation' });
            console.log('📋 Geolocation permission status:', result.state);
            
            // Update button status based on permission
            updateGPSButtonStatus(result.state);
            
            // Show helpful message based on permission status
            if (result.state === 'denied') {
                showGPSError('Location access is blocked');
                const errorDiv = document.getElementById('gps-error');
                const helpText = errorDiv.querySelector('p:last-child');
                helpText.innerHTML = `
                    <div class="space-y-2">
                        <p class="text-xs text-red-600">Location access is currently blocked. To use GPS:</p>
                        <div class="text-xs text-red-600 space-y-1">
                            <p><strong>Desktop:</strong> Click the lock icon in address bar → Allow location</p>
                            <p><strong>Mobile:</strong> Settings → Privacy → Location Services → Enable for browser</p>
                            <p><strong>Alternative:</strong> Use "Manual Entry" or "Demo" buttons below</p>
                        </div>
                        <div class="flex gap-2 mt-2">
                            <button type="button" onclick="enableManualEntry()" class="px-3 py-1 text-xs bg-red-100 text-red-700 rounded border border-red-300 hover:bg-red-200 transition-colors">
                                Manual Entry
                            </button>
                            <button type="button" onclick="useDemoGPS()" class="px-3 py-1 text-xs bg-blue-100 text-blue-700 rounded border border-blue-300 hover:bg-blue-200 transition-colors">
                                Use Demo
                            </button>
                        </div>
                    </div>
                `;
            } else if (result.state === 'prompt') {
                // Show helpful message for first-time users
                const gpsStatus = document.getElementById('gps-status');
                if (gpsStatus) {
                    gpsStatus.classList.remove('hidden');
                    document.getElementById('gps-status-text').textContent = 'Click "Use GPS" to enable location access';
                    document.getElementById('gps-coordinates').textContent = 'You\'ll be prompted to allow location access';
                }
            }
        } catch (error) {
            console.error('Error checking permissions:', error);
            updateGPSButtonStatus('unknown');
        }
    } else {
        updateGPSButtonStatus('unknown');
    }
    
    return true;
}

async function useGPSLocation() {
    try {
        // Update button states
        document.getElementById('use-gps-btn').disabled = true;
        document.getElementById('manual-entry-btn').disabled = false;
        
        showGPSStatus('Getting your location...');

        // Get current position using native browser GPS
        const coords = await getCurrentLocation();
        currentLocation = coords;
        
        // Show success with coordinates
        showGPSSuccess(coords);

        // Get full address from coordinates
        showGPSStatus('Converting coordinates to address...');
        const fullAddress = await getAddressFromCoordinates(coords.lat, coords.lng);
        const addressComponents = extractAddressComponents(fullAddress);
        
        // Show the address section
        document.getElementById('address-section').classList.remove('hidden');
        
        // Fill in the form fields with actual address data
        document.getElementById('city').value = addressComponents.city;
        document.getElementById('area_locality').value = addressComponents.area;
        
        // Update success message with full address
        document.getElementById('gps-address').textContent = fullAddress;

        // Automatically find nearest branches after GPS location is set
        setTimeout(() => {
            findNearestBranches();
        }, 1000);

    } catch (error) {
        console.error('GPS Error:', error);
        
        // Re-enable manual entry
        document.getElementById('use-gps-btn').disabled = false;
        document.getElementById('manual-entry-btn').disabled = false;
        
        // Enhanced error handling with specific guidance
        if (error.message.includes('permission denied')) {
            showGPSError('Location permission denied');
            const errorDiv = document.getElementById('gps-error');
            const helpText = errorDiv.querySelector('p:last-child');
            helpText.innerHTML = `
                <div class="space-y-2">
                    <p class="text-xs text-red-600">Please enable location access or use manual entry:</p>
                    <div class="text-xs text-red-600 space-y-1">
                        <p><strong>Desktop:</strong> Click the lock icon in address bar → Allow location</p>
                        <p><strong>Mobile:</strong> Settings → Privacy → Location Services → Enable for browser</p>
                        <p><strong>Alternative:</strong> Use Manual Entry to continue with your order</p>
                    </div>
                    <div class="flex gap-2 mt-2">
                        <button type="button" onclick="retryGPS()" class="px-3 py-1 text-xs bg-blue-100 text-blue-700 rounded border border-blue-300 hover:bg-blue-200 transition-colors">
                            Try Again
                        </button>
                        <button type="button" onclick="enableManualEntry()" class="px-3 py-1 text-xs bg-red-100 text-red-700 rounded border border-red-300 hover:bg-red-200 transition-colors">
                            Use Manual Entry
                        </button>
                    </div>
                </div>
            `;
            
            // Auto-switch to manual entry after 5 seconds (increased from 3)
            setTimeout(() => {
                if (document.getElementById('gps-error').classList.contains('hidden') === false) {
                    enableManualEntry();
                }
            }, 5000);
            
        } else if (error.message.includes('timeout')) {
            showGPSError('Location request timed out');
            const errorDiv = document.getElementById('gps-error');
            const helpText = errorDiv.querySelector('p:last-child');
            helpText.innerHTML = `
                <div class="space-y-2">
                    <p class="text-xs text-red-600">GPS signal is weak or unavailable. Try:</p>
                    <div class="text-xs text-red-600 space-y-1">
                        <p>• Moving to an open area with better GPS signal</p>
                        <p>• Using manual entry instead</p>
                        <p>• Checking if GPS is enabled on your device</p>
                    </div>
                    <button type="button" onclick="enableManualEntry()" class="mt-2 px-3 py-1 text-xs bg-red-100 text-red-700 rounded border border-red-300 hover:bg-red-200 transition-colors">
                        Use Manual Entry
                    </button>
                </div>
            `;
            
        } else if (error.message.includes('unavailable')) {
            showGPSError('Location information unavailable');
            const errorDiv = document.getElementById('gps-error');
            const helpText = errorDiv.querySelector('p:last-child');
            helpText.innerHTML = `
                <div class="space-y-2">
                    <p class="text-xs text-red-600">GPS hardware may not be available. Try:</p>
                    <div class="text-xs text-red-600 space-y-1">
                        <p>• Using manual entry for your address</p>
                        <p>• Checking device GPS settings</p>
                        <p>• Contacting support if the issue persists</p>
                    </div>
                    <button type="button" onclick="enableManualEntry()" class="mt-2 px-3 py-1 text-xs bg-red-100 text-red-700 rounded border border-red-300 hover:bg-red-200 transition-colors">
                        Use Manual Entry
                    </button>
                </div>
            `;
            
        } else {
            showGPSError('Unable to get your location');
            const errorDiv = document.getElementById('gps-error');
            const helpText = errorDiv.querySelector('p:last-child');
            helpText.innerHTML = `
                <div class="space-y-2">
                    <p class="text-xs text-red-600">An unexpected error occurred. Please:</p>
                    <div class="text-xs text-red-600 space-y-1">
                        <p>• Use manual entry to continue with your order</p>
                        <p>• Try refreshing the page and try again</p>
                        <p>• Contact support if the issue persists</p>
                    </div>
                    <button type="button" onclick="enableManualEntry()" class="mt-2 px-3 py-1 text-xs bg-red-100 text-red-700 rounded border border-red-300 hover:bg-red-200 transition-colors">
                        Continue with Manual Entry
                    </button>
                </div>
            `;
        }
    }
}

// Event listeners for GPS buttons
document.addEventListener('DOMContentLoaded', function() {
    // Add event listeners for GPS buttons
    const useGpsBtn = document.getElementById('use-gps-btn');
    const manualEntryBtn = document.getElementById('manual-entry-btn');
    
    if (useGpsBtn) {
        useGpsBtn.addEventListener('click', useGPSLocation);
        console.log('✅ Use GPS button event listener added');
    }
    
    if (manualEntryBtn) {
        manualEntryBtn.addEventListener('click', enableManualEntry);
        console.log('✅ Manual Entry button event listener added');
    }
    
    // GPS Help Toggle
    const gpsHelpToggle = document.getElementById('gps-help-toggle');
    if (gpsHelpToggle) {
        gpsHelpToggle.addEventListener('click', toggleGPSHelp);
        console.log('✅ GPS Help toggle event listener added');
    }
    
    // Branch Help Toggle
    const branchHelpToggle = document.getElementById('branch-help-toggle');
    if (branchHelpToggle) {
        branchHelpToggle.addEventListener('click', toggleBranchHelp);
        console.log('✅ Branch Help toggle event listener added');
    }
    
    // Test GPS functionality on page load
    console.log('🔍 Testing GPS functionality on page load...');
    testGPSFunctionality().catch(error => {
        console.error('Error testing GPS functionality:', error);
    });
});

// Function to toggle GPS help section
function toggleGPSHelp() {
    const content = document.getElementById('gps-help-content');
    const arrow = document.getElementById('gps-help-arrow');
    
    if (content.classList.contains('hidden')) {
        // Show content
        content.classList.remove('hidden');
        arrow.classList.add('rotate-180');
    } else {
        // Hide content
        content.classList.add('hidden');
        arrow.classList.remove('rotate-180');
    }
}

// Function to toggle branch help section
function toggleBranchHelp() {
    const content = document.getElementById('branch-help-content');
    const arrow = document.getElementById('branch-help-arrow');
    
    if (content.classList.contains('hidden')) {
        // Show content
        content.classList.remove('hidden');
        arrow.classList.add('rotate-180');
    } else {
        // Hide content
        content.classList.add('hidden');
        arrow.classList.remove('rotate-180');
    }
}

// Function to convert GPS coordinates to address
async function getAddressFromCoordinates(lat, lng) {
    try {
        // Use OpenStreetMap Nominatim API (free, no API key required)
        const response = await fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}&zoom=18&addressdetails=1`);
        const data = await response.json();
        
        if (data && data.display_name) {
            return data.display_name;
        } else {
            return `GPS: ${lat.toFixed(6)}, ${lng.toFixed(6)}`;
        }
    } catch (error) {
        console.error('Error getting address:', error);
        return `GPS: ${lat.toFixed(6)}, ${lng.toFixed(6)}`;
    }
}

// Function to extract address components
function extractAddressComponents(address) {
    const parts = address.split(', ');
    const components = {
        city: '',
        area: '',
        fullAddress: address
    };
    
    // Try to extract city and area from address parts
    if (parts.length >= 2) {
        // Usually city is in the middle, area is near the end
        components.city = parts[1] || parts[0] || 'GPS Location';
        components.area = parts[0] || 'GPS Area';
    } else {
        components.city = 'GPS Location';
        components.area = address;
    }
    
    return components;
}

// Branch Selection Functions
let selectedBranch = null;

// Function to find nearest branches
async function findNearestBranches() {
    if (!currentLocation) {
        showBranchError('Please get your location first using GPS to find the nearest branch.');
        return;
    }

    showBranchLoading();

    try {
        console.log('🔍 Fetching branches with location:', currentLocation);
        
        const response = await fetch('/checkout/branches', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                latitude: currentLocation.lat,
                longitude: currentLocation.lng
            })
        });

        console.log('📡 Response status:', response.status);
        console.log('📡 Response headers:', Object.fromEntries(response.headers.entries()));

        // Check if response is JSON
        const contentType = response.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
            const text = await response.text();
            console.error('❌ Server returned non-JSON response:', text.substring(0, 500));
            showBranchError('Server error. Please try again.');
            return;
        }

        const data = await response.json();
        console.log('✅ Received data:', data);

        if (data.success) {
            displayBranchResults(data);
        } else {
            showBranchError('Failed to find branches');
        }
    } catch (error) {
        console.error('❌ Error finding branches:', error);
        showBranchError('Network error. Please try again.');
    }
}

// Function to show all branches
async function showAllBranches() {
    showBranchLoading();

    try {
        console.log('🔍 Fetching all branches...');
        
        // Prepare request data - include location if available
        const requestData = {};
        if (currentLocation) {
            requestData.latitude = currentLocation.lat;
            requestData.longitude = currentLocation.lng;
        }

        // Use GET request for all branches
        const url = new URL('/checkout/all-branches', window.location.origin);
        if (currentLocation) {
            url.searchParams.append('latitude', currentLocation.lat);
            url.searchParams.append('longitude', currentLocation.lng);
        }

        console.log('📡 Requesting URL:', url.toString());

        const response = await fetch(url, {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        });

        console.log('📡 Response status:', response.status);
        console.log('📡 Response headers:', Object.fromEntries(response.headers.entries()));

        // Check if response is JSON
        const contentType = response.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
            const text = await response.text();
            console.error('❌ Server returned non-JSON response:', text.substring(0, 500));
            showBranchError('Server error. Please try again.');
            return;
        }

        const data = await response.json();
        console.log('✅ Received data:', data);

        if (data.success) {
            displayAllBranches(data.branches);
        } else {
            showBranchError('Failed to load branches');
        }
    } catch (error) {
        console.error('❌ Error loading branches:', error);
        showBranchError('Network error. Please try again.');
    }
}

// Function to display branch results
function displayBranchResults(data) {
    hideBranchLoading();
    hideBranchError();

    const resultsDiv = document.getElementById('branch-selection-results');
    const nearestSuggestion = document.getElementById('nearest-branch-suggestion');
    const allBranchesList = document.getElementById('all-branches-list');

    resultsDiv.classList.remove('hidden');

    if (data.nearest_branch) {
        // Show nearest branch suggestion
        nearestSuggestion.classList.remove('hidden');
        allBranchesList.classList.add('hidden');

        const nearestCard = document.getElementById('nearest-branch-card');
        nearestCard.innerHTML = createBranchCard(data.nearest_branch, true);
    } else {
        // Show all branches if no nearest branch
        nearestSuggestion.classList.add('hidden');
        allBranchesList.classList.remove('hidden');
        displayAllBranches(data.branches);
    }
}

// Function to display all branches
function displayAllBranches(branches) {
    hideBranchLoading();
    hideBranchError();

    const resultsDiv = document.getElementById('branch-selection-results');
    const nearestSuggestion = document.getElementById('nearest-branch-suggestion');
    const allBranchesList = document.getElementById('all-branches-list');
    const branchesList = document.getElementById('branches-list');

    resultsDiv.classList.remove('hidden');
    nearestSuggestion.classList.add('hidden');
    allBranchesList.classList.remove('hidden');

    if (branches.length === 0) {
        branchesList.innerHTML = `
            <div class="text-center py-4 text-gray-500">
                <p>No branches available in your area.</p>
                <p class="text-sm">Please contact support for assistance.</p>
            </div>
        `;
        return;
    }

    branchesList.innerHTML = branches.map(branch => createBranchCard(branch, false)).join('');
}

// Function to create branch card HTML
function createBranchCard(branch, isRecommended = false) {
    const hasDistance = branch.distance !== null && branch.distance !== undefined;
    const statusClass = hasDistance && branch.is_within_radius ? 'text-green-600' : 'text-gray-600';
    const statusText = hasDistance ? (branch.is_within_radius ? 'Available' : 'Outside Delivery Area') : 'Contact for Delivery';
    const deliveryFeeText = branch.delivery_fee !== null ? `Rs.${branch.delivery_fee}` : 'Contact Branch';
    const distanceText = hasDistance ? `${branch.distance} km` : 'Contact Branch';
    const etaText = branch.estimated_delivery_time || 'Contact Branch';
    
    return `
        <div class="border rounded-lg p-4 ${isRecommended ? 'bg-green-50 border-green-200' : 'bg-white border-gray-200'} hover:shadow-md transition-shadow cursor-pointer" 
             onclick="selectBranch(${branch.id}, '${branch.name}', ${branch.delivery_fee || 0})">
            <div class="flex justify-between items-start mb-2">
                <h5 class="font-semibold text-gray-900">${branch.name}</h5>
                ${isRecommended ? '<span class="text-xs bg-green-100 text-green-800 px-2 py-1 rounded">Recommended</span>' : ''}
            </div>
            <p class="text-sm text-gray-600 mb-2">${branch.address}</p>
            <div class="grid grid-cols-2 gap-2 text-xs">
                <div>
                    <span class="text-gray-500">Distance:</span>
                    <span class="font-medium">${distanceText}</span>
                </div>
                <div>
                    <span class="text-gray-500">Delivery Fee:</span>
                    <span class="font-medium">${deliveryFeeText}</span>
                </div>
                <div>
                    <span class="text-gray-500">Status:</span>
                    <span class="font-medium ${statusClass}">${statusText}</span>
                </div>
                <div>
                    <span class="text-gray-500">ETA:</span>
                    <span class="font-medium">${etaText}</span>
                </div>
            </div>
            ${branch.contact_phone ? `<p class="text-xs text-gray-500 mt-2">📞 ${branch.contact_phone}</p>` : ''}
        </div>
    `;
}

// Function to select a branch
function selectBranch(branchId, branchName, deliveryFee) {
    selectedBranch = {
        id: branchId,
        name: branchName,
        deliveryFee: deliveryFee
    };

    // Update hidden input
    document.getElementById('selected-branch-id').value = branchId;

    // Update delivery fee in order summary
    document.getElementById('checkout-delivery').textContent = `Rs.${deliveryFee.toFixed(2)}`;

    // Recalculate total
    updateOrderTotal();

    // Show selection confirmation
    showBranchSelectionConfirmation(branchName, deliveryFee);

    // Highlight selected branch
    document.querySelectorAll('[onclick*="selectBranch"]').forEach(card => {
        card.classList.remove('ring-2', 'ring-[#6E0D25]');
    });
    event.currentTarget.classList.add('ring-2', 'ring-[#6E0D25]');
}

// Function to show branch selection confirmation
function showBranchSelectionConfirmation(branchName, deliveryFee) {
    // Create or update confirmation message
    let confirmationDiv = document.getElementById('branch-selection-confirmation');
    if (!confirmationDiv) {
        confirmationDiv = document.createElement('div');
        confirmationDiv.id = 'branch-selection-confirmation';
        document.getElementById('branch-selection-results').appendChild(confirmationDiv);
    }

    confirmationDiv.innerHTML = `
        <div class="bg-green-50 border border-green-200 rounded-lg p-3">
            <div class="flex items-center">
                <svg class="w-5 h-5 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <div>
                    <p class="text-sm font-medium text-green-800">Branch Selected: ${branchName}</p>
                    <p class="text-xs text-green-600">Delivery Fee: Rs.${deliveryFee.toFixed(2)}</p>
                </div>
            </div>
        </div>
    `;
}

// Function to update order total
function updateOrderTotal() {
    const subtotal = parseFloat(document.getElementById('checkout-subtotal').textContent.replace('Rs.', ''));
    const deliveryFee = selectedBranch ? selectedBranch.deliveryFee : 0;
    const taxRate = window.taxDeliverySettings.tax_rate || 13;
    const tax = subtotal * (taxRate / 100);
    const total = subtotal + deliveryFee + tax;

    document.getElementById('checkout-tax').textContent = `Rs.${tax.toFixed(2)}`;
    document.getElementById('checkout-total').textContent = `Rs.${total.toFixed(2)}`;
}

// Helper functions for branch selection UI
function showBranchLoading() {
    document.getElementById('branch-loading').classList.remove('hidden');
    document.getElementById('branch-selection-results').classList.add('hidden');
    document.getElementById('branch-error').classList.add('hidden');
}

function hideBranchLoading() {
    document.getElementById('branch-loading').classList.add('hidden');
}

function showBranchError(message) {
    hideBranchLoading();
    document.getElementById('branch-selection-results').classList.add('hidden');
    document.getElementById('branch-error').classList.remove('hidden');
    document.getElementById('branch-error-text').textContent = message;
}

function hideBranchError() {
    document.getElementById('branch-error').classList.add('hidden');
}

// Add event listeners for branch selection buttons
document.addEventListener('DOMContentLoaded', function() {
    const findNearestBtn = document.getElementById('find-nearest-branch-btn');
    const showAllBtn = document.getElementById('show-all-branches-btn');
    const moveToPaymentBtn = document.getElementById('move-to-payment-btn');

    if (findNearestBtn) {
        findNearestBtn.addEventListener('click', findNearestBranches);
    }

    if (showAllBtn) {
        showAllBtn.addEventListener('click', showAllBranches);
    }

    if (moveToPaymentBtn) {
        moveToPaymentBtn.addEventListener('click', validateAndProceedToPayment);
    }
});

// Function to validate form and proceed to payment
function validateAndProceedToPayment() {
    // Check if branch is selected
    const selectedBranchId = document.getElementById('selected-branch-id').value;
    const branchValidationError = document.getElementById('branch-validation-error');
    
    if (!selectedBranchId) {
        branchValidationError.classList.remove('hidden');
        // Scroll to branch selection section
        document.getElementById('selected-branch-id').scrollIntoView({ behavior: 'smooth', block: 'center' });
        return;
    }
    
    // Hide validation error if branch is selected
    branchValidationError.classList.add('hidden');
    
    // Validate other required fields
    const requiredFields = ['name', 'email', 'phone', 'city', 'ward_number', 'area_locality'];
    let isValid = true;
    
    requiredFields.forEach(fieldId => {
        const field = document.getElementById(fieldId);
        if (!field.value.trim()) {
            field.classList.add('border-red-500');
            isValid = false;
        } else {
            field.classList.remove('border-red-500');
        }
    });
    
    if (!isValid) {
        alert('Please fill in all required fields.');
        return;
    }
    
    // If all validation passes, proceed to payment
    proceedToPayment();
}

// Function to proceed to payment
function proceedToPayment() {
    // Store selected branch info in session storage for payment page
    if (selectedBranch) {
        sessionStorage.setItem('selectedBranch', JSON.stringify(selectedBranch));
    }
    
    // Get form data
    const formData = {
        name: document.getElementById('name').value,
        email: document.getElementById('email').value,
        phone: document.getElementById('phone').value,
        city: document.getElementById('city').value,
        ward_number: document.getElementById('ward_number').value,
        area_locality: document.getElementById('area_locality').value,
        building_name: document.getElementById('building_name').value,
        detailed_directions: document.getElementById('detailed_directions').value,
        branch_id: document.getElementById('selected-branch-id').value
    };
    
    // Store form data in session storage for payment page
    sessionStorage.setItem('checkoutFormData', JSON.stringify(formData));
    
    // Store GPS location in session storage if available
    if (currentLocation) {
        sessionStorage.setItem('checkoutGpsLocation', JSON.stringify(currentLocation));
    }
    
    // Get cart data
    let cart = [];
    if (window.cartManager && typeof window.cartManager.getCartItems === 'function') {
        cart = window.cartManager.getCartItems();
    } else {
        const storedCart = localStorage.getItem('momo_cart');
        cart = JSON.parse(storedCart || '[]');
    }
    
    // Store cart data in session storage for payment page
    sessionStorage.setItem('checkoutCart', JSON.stringify(cart));
    
    // Get applied offer
    let offer = null;
    if (window.cartManager && typeof window.cartManager.getAppliedOffer === 'function') {
        offer = window.cartManager.getAppliedOffer();
    } else {
        const storedOffer = localStorage.getItem('applied_offer');
        if (storedOffer) {
            offer = JSON.parse(storedOffer);
        }
    }
    
    if (offer) {
        sessionStorage.setItem('checkoutOffer', JSON.stringify(offer));
    }
    
    // Redirect to payment page
    window.location.href = '{{ route("payment") }}';
}
</script>
@endpush 