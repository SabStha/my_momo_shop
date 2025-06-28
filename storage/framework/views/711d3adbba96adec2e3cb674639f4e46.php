<?php $__env->startSection('content'); ?>
<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Page Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">Checkout</h1>
            <p class="text-gray-600 mt-2">Complete your order</p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Order Summary -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                    <div class="p-6 border-b border-gray-200">
                        <h2 class="text-lg font-semibold text-gray-900">Order Summary</h2>
                    </div>
                    
                    <div class="p-6">
                        <div id="checkout-items" class="space-y-4 mb-6">
                            <!-- Items will be populated by JavaScript -->
                        </div>

                        <!-- Order Totals -->
                        <div class="border-t border-gray-200 pt-4 space-y-3">
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">Subtotal</span>
                                <span class="font-medium text-gray-900" id="checkout-subtotal">Rs.0.00</span>
                            </div>
                            
                            <!-- Applied Offer Section -->
                            <div id="checkout-offer-section" style="display: none;">
                                <div class="flex justify-between items-center text-sm bg-green-50 border border-green-200 rounded-lg px-3 py-2 mb-2">
                                    <div>
                                        <span class="font-semibold text-green-700">Offer Applied:</span>
                                        <span class="font-mono text-green-800" id="checkout-offer-code"></span>
                                        <span class="text-xs text-green-600 ml-1" id="checkout-offer-discount"></span>
                                    </div>
                                </div>
                                <div class="flex justify-between text-sm">
                                    <span class="text-green-700">Discount</span>
                                    <span class="font-medium text-green-700" id="checkout-discount-amount">-Rs.0.00</span>
                                </div>
                            </div>
                            
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">Delivery Fee</span>
                                <span class="font-medium text-gray-900" id="checkout-delivery">Rs.5.00</span>
                            </div>
                            
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">Tax (13%)</span>
                                <span class="font-medium text-gray-900" id="checkout-tax">Rs.0.00</span>
                            </div>
                            
                            <div class="border-t border-gray-200 pt-3">
                                <div class="flex justify-between text-lg font-bold">
                                    <span class="text-gray-900">Total</span>
                                    <span class="text-[#6E0D25]" id="checkout-total">Rs.0.00</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Checkout Form -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                    <div class="p-6 border-b border-gray-200">
                        <h2 class="text-lg font-semibold text-gray-900">Delivery Information</h2>
                    </div>
                    
                    <form id="checkout-form" class="p-6 space-y-6" <?php if($userData): ?> data-user-logged-in="true" <?php endif; ?>>
                        <!-- Name -->
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                                Full Name
                                <?php if($userData && $userData['name']): ?>
                                    <span class="text-xs text-green-600 ml-1">✓ Auto-filled</span>
                                <?php endif; ?>
                            </label>
                            <input type="text" id="name" name="name" required
                                   value="<?php echo e($userData['name'] ?? ''); ?>"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#6E0D25] focus:border-[#6E0D25] transition-colors <?php echo e($userData && $userData['name'] ? 'bg-green-50 border-green-200' : ''); ?>"
                                   oninput="console.log('Name field input:', this.value)"
                                   placeholder="Enter your full name...">
                        </div>

                        <!-- Email -->
                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                                Email Address
                                <?php if($userData && $userData['email']): ?>
                                    <span class="text-xs text-green-600 ml-1">✓ Auto-filled</span>
                                <?php endif; ?>
                            </label>
                            <input type="email" id="email" name="email" required
                                   value="<?php echo e($userData['email'] ?? ''); ?>"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#6E0D25] focus:border-[#6E0D25] transition-colors <?php echo e($userData && $userData['email'] ? 'bg-green-50 border-green-200' : ''); ?>"
                                   placeholder="Enter your email address...">
                        </div>

                        <!-- Phone Number -->
                        <div>
                            <label for="phone" class="block text-sm font-medium text-gray-700 mb-2">Phone Number</label>
                            <input type="tel" id="phone" name="phone" required
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#6E0D25] focus:border-[#6E0D25] transition-colors"
                                   placeholder="Enter your phone number..." value="<?php echo e($userData['phone'] ?? ''); ?>">
                        </div>

                        <!-- Location Section -->
                        <div class="space-y-4">
                            <div class="flex items-center justify-between">
                                <label class="block text-sm font-medium text-gray-700">Delivery Location</label>
                                <div class="flex items-center space-x-2 gps-location-controls">
                                    <button type="button" id="use-gps-btn" 
                                            class="gps-button inline-flex items-center px-3 py-1 text-xs font-medium text-[#6E0D25] bg-[#6E0D25]/10 border border-[#6E0D25]/20 rounded-md hover:bg-[#6E0D25]/20 transition-colors">
                                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        </svg>
                                        Use GPS
                                    </button>
                                    <button type="button" id="manual-entry-btn" 
                                            class="gps-button inline-flex items-center px-3 py-1 text-xs font-medium text-gray-600 bg-gray-100 border border-gray-300 rounded-md hover:bg-gray-200 transition-colors">
                                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                        </svg>
                                        Manual Entry
                                    </button>
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

                        <!-- City / Municipality (Required) -->
                        <div>
                            <label for="city" class="block text-sm font-medium text-gray-700 mb-2">City / Municipality <span class="text-red-500">*</span></label>
                            <input type="text" id="city" name="city" required
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#6E0D25] focus:border-[#6E0D25] transition-colors"
                                   placeholder="Enter your city or municipality..." value="<?php echo e($userData['city'] ?? ''); ?>">
                        </div>

                        <!-- Ward Number (Required) -->
                        <div>
                            <label for="ward_number" class="block text-sm font-medium text-gray-700 mb-2">Ward Number <span class="text-red-500">*</span></label>
                            <input type="text" id="ward_number" name="ward_number" required
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#6E0D25] focus:border-[#6E0D25] transition-colors"
                                   placeholder="Enter your ward number..." value="<?php echo e($userData['ward_number'] ?? ''); ?>">
                        </div>

                        <!-- Area / Locality / Tole / Nearby Landmark (Required) -->
                        <div>
                            <label for="area_locality" class="block text-sm font-medium text-gray-700 mb-2">Area / Locality / Tole / Nearby Landmark <span class="text-red-500">*</span></label>
                            <input type="text" id="area_locality" name="area_locality" required
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#6E0D25] focus:border-[#6E0D25] transition-colors"
                                   placeholder="Enter your area, locality, tole, or nearby landmark..." value="<?php echo e($userData['area_locality'] ?? ''); ?>">
                        </div>

                        <!-- House / Apartment / Building Name (Optional) -->
                        <div>
                            <label for="building_name" class="block text-sm font-medium text-gray-700 mb-2">House / Apartment / Building Name (Optional)</label>
                            <input type="text" id="building_name" name="building_name"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#6E0D25] focus:border-[#6E0D25] transition-colors"
                                   placeholder="Enter house, apartment, or building name..." value="<?php echo e($userData['building_name'] ?? ''); ?>">
                        </div>

                        <!-- Detailed Directions / Instructions (Optional) -->
                        <div>
                            <label for="detailed_directions" class="block text-sm font-medium text-gray-700 mb-2">Detailed Directions / Instructions (Optional)</label>
                            <textarea id="detailed_directions" name="detailed_directions" rows="3"
                                      class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#6E0D25] focus:border-[#6E0D25] transition-colors"
                                      placeholder="Any additional directions or instructions for delivery..."><?php echo e($userData['detailed_directions'] ?? ''); ?></textarea>
                        </div>

                        <!-- Payment Method -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-3">Payment Method</label>
                            <div class="grid grid-cols-3 gap-3">
                                <label class="flex flex-col items-center p-3 border border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50 transition-colors">
                                    <input type="radio" name="payment_method" value="wallet" checked
                                           class="hidden">
                                    <span class="text-2xl mb-2">💰</span>
                                    <span class="text-xs text-gray-700 text-center">Wallet</span>
                                </label>
                                <label class="flex flex-col items-center p-3 border border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50 transition-colors">
                                    <input type="radio" name="payment_method" value="cash"
                                           class="hidden">
                                    <span class="text-2xl mb-2">💵</span>
                                    <span class="text-xs text-gray-700 text-center">Cash</span>
                                </label>
                                <label class="flex flex-col items-center p-3 border border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50 transition-colors">
                                    <input type="radio" name="payment_method" value="fonepay"
                                           class="hidden">
                                    <span class="text-2xl mb-2">📱</span>
                                    <span class="text-xs text-gray-700 text-center">FonePay</span>
                                </label>
                                <label class="flex flex-col items-center p-3 border border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50 transition-colors">
                                    <input type="radio" name="payment_method" value="esewa"
                                           class="hidden">
                                    <span class="text-2xl mb-2">💳</span>
                                    <span class="text-xs text-gray-700 text-center">eSewa</span>
                                </label>
                                <label class="flex flex-col items-center p-3 border border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50 transition-colors">
                                    <input type="radio" name="payment_method" value="khalti"
                                           class="hidden">
                                    <span class="text-2xl mb-2">💜</span>
                                    <span class="text-xs text-gray-700 text-center">Khalti</span>
                                </label>
                                <label class="flex flex-col items-center p-3 border border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50 transition-colors">
                                    <input type="radio" name="payment_method" value="card"
                                           class="hidden">
                                    <span class="text-2xl mb-2">💳</span>
                                    <span class="text-xs text-gray-700 text-center">Card</span>
                                </label>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <button type="submit" 
                                id="place-order-btn"
                                class="w-full bg-[#6E0D25] text-white py-3 px-4 rounded-lg font-semibold hover:bg-[#8B0D2F] transition-colors duration-300 flex items-center justify-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            Place Order
                        </button>

                        <!-- Back to Cart -->
                        <a href="<?php echo e(route('cart')); ?>" 
                           class="block w-full text-center bg-gray-100 text-gray-700 py-3 px-4 rounded-lg font-semibold hover:bg-gray-200 transition-colors duration-300">
                            Back to Cart
                        </a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Cart Modal -->
<?php echo $__env->make('components.cart-modal', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script src="<?php echo e(asset('js/cart.js')); ?>"></script>
<script>
console.log('Checkout script starting...');

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
        detailed_directions: document.getElementById('detailed_directions').value
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
        detailed_directions: document.getElementById('detailed_directions').value.trim()
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

// Checkout page functionality
document.addEventListener('DOMContentLoaded', function() {
    console.log('Checkout page loaded');
    
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
    
    // Handle form submission
    document.getElementById('checkout-form').addEventListener('submit', function(e) {
        e.preventDefault();
        placeOrder();
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

    // GPS Location Functionality
    let currentLocation = null;

    // GPS Location Functions
    function showGPSStatus(message, coordinates = '') {
        document.getElementById('gps-status').classList.remove('hidden');
        document.getElementById('gps-error').classList.add('hidden');
        document.getElementById('gps-success').classList.add('hidden');
        document.getElementById('gps-status-text').textContent = message;
        document.getElementById('gps-coordinates').textContent = coordinates;
    }

    function showGPSError(message) {
        document.getElementById('gps-status').classList.add('hidden');
        document.getElementById('gps-error').classList.remove('hidden');
        document.getElementById('gps-success').classList.add('hidden');
        document.getElementById('gps-error-text').textContent = message;
    }

    function showGPSSuccess(coordinates) {
        document.getElementById('gps-status').classList.add('hidden');
        document.getElementById('gps-error').classList.add('hidden');
        document.getElementById('gps-success').classList.remove('hidden');
        document.getElementById('gps-address').textContent = `GPS Location: ${coordinates.lat.toFixed(6)}, ${coordinates.lng.toFixed(6)}`;
    }

    function hideAllGPSStatus() {
        document.getElementById('gps-status').classList.add('hidden');
        document.getElementById('gps-error').classList.add('hidden');
        document.getElementById('gps-success').classList.add('hidden');
    }

    function getCurrentLocation() {
        return new Promise((resolve, reject) => {
            if (!navigator.geolocation) {
                reject(new Error('Geolocation is not supported by this browser.'));
                return;
            }

            const options = {
                enableHighAccuracy: true,
                timeout: 10000,
                maximumAge: 60000
            };

            navigator.geolocation.getCurrentPosition(
                (position) => {
                    const { latitude, longitude } = position.coords;
                    resolve({ lat: latitude, lng: longitude });
                },
                (error) => {
                    let errorMessage = 'Unknown error occurred';
                    switch (error.code) {
                        case error.PERMISSION_DENIED:
                            errorMessage = 'Location permission denied. Please enable location access in your browser settings.';
                            break;
                        case error.POSITION_UNAVAILABLE:
                            errorMessage = 'Location information is unavailable.';
                            break;
                        case error.TIMEOUT:
                            errorMessage = 'Location request timed out. Please try again.';
                            break;
                    }
                    reject(new Error(errorMessage));
                },
                options
            );
        });
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

            // Fill in the form fields with GPS coordinates
            document.getElementById('city').value = 'GPS Location';
            document.getElementById('area_locality').value = `GPS: ${coords.lat.toFixed(6)}, ${coords.lng.toFixed(6)}`;

        } catch (error) {
            console.error('GPS Error:', error);
            showGPSError(error.message);
            
            // Re-enable manual entry
            document.getElementById('use-gps-btn').disabled = false;
            document.getElementById('manual-entry-btn').disabled = false;
            
            // If permission denied, show helpful message
            if (error.message.includes('permission denied')) {
                document.getElementById('gps-error-text').textContent = 'Location permission denied';
                const errorDiv = document.getElementById('gps-error');
                const helpText = errorDiv.querySelector('p:last-child');
                helpText.innerHTML = 'Please enable location access in your browser settings or use manual entry. <br><small>On mobile: Settings > Privacy > Location Services</small>';
            }
        }
    }

    function enableManualEntry() {
        // Update button states
        document.getElementById('use-gps-btn').disabled = false;
        document.getElementById('manual-entry-btn').disabled = true;
        
        // Hide all GPS status messages
        hideAllGPSStatus();
        
        // Clear any GPS-derived data
        currentLocation = null;
        
        // Clear form fields if they were filled by GPS
        if (document.getElementById('city').value.includes('GPS:') || 
            document.getElementById('city').value === 'Unknown City') {
            document.getElementById('city').value = '';
        }
        if (document.getElementById('area_locality').value.includes('GPS:')) {
            document.getElementById('area_locality').value = '';
        }
    }

    // Event listeners for GPS buttons
    document.getElementById('use-gps-btn').addEventListener('click', useGPSLocation);
    document.getElementById('manual-entry-btn').addEventListener('click', enableManualEntry);

    // Check if user has previously used GPS and restore state
    const savedLocation = localStorage.getItem('checkout_gps_location');
    if (savedLocation) {
        try {
            const locationData = JSON.parse(savedLocation);
            currentLocation = locationData;
            
            // Pre-fill form if location data exists
            if (locationData.lat && locationData.lng) {
                document.getElementById('city').value = 'GPS Location';
                document.getElementById('area_locality').value = `GPS: ${locationData.lat.toFixed(6)}, ${locationData.lng.toFixed(6)}`;
                showGPSSuccess(locationData);
            }
        } catch (error) {
            console.error('Error parsing saved location:', error);
            localStorage.removeItem('checkout_gps_location');
        }
    }
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
                <a href="<?php echo e(route('home')); ?>" class="inline-flex items-center px-4 py-2 bg-[#6E0D25] text-white rounded-lg hover:bg-[#8B0D2F] transition-colors">
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
            <div class="flex items-center gap-4 p-4 bg-gray-50 rounded-lg">
                <div class="flex-shrink-0">
                    ${item.image ? 
                        `<img src="${item.image}" alt="${item.name}" class="w-12 h-12 object-cover rounded-lg">` :
                        `<div class="w-12 h-12 bg-gray-200 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 002 2v12a2 2 0 002 2z"></path>
                            </svg>
                        </div>`
                    }
                </div>
                <div class="flex-1 min-w-0">
                    <h3 class="text-sm font-medium text-gray-900 truncate">${item.name}</h3>
                    <p class="text-sm text-gray-500">Quantity: ${item.quantity}</p>
                </div>
                <div class="text-right">
                    <p class="text-sm font-bold text-[#6E0D25]">Rs.${itemTotal.toFixed(2)}</p>
                </div>
            </div>
        `;
    });
    
    itemsContainer.innerHTML = itemsHtml;
    
    // Calculate totals with offer support
    const deliveryFee = subtotal >= 25 ? 0 : 5;
    const tax = subtotal * 0.13;
    
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

function placeOrder() {
    console.log('placeOrder called');
    const form = document.getElementById('checkout-form');
    const submitBtn = document.getElementById('place-order-btn');
    
    // Get cart items
    let cartItems = [];
    if (typeof window.cartManager !== 'undefined') {
        cartItems = window.cartManager.getCartItems();
        console.log('Cart items from cartManager:', cartItems);
    } else {
        cartItems = JSON.parse(localStorage.getItem('momo_cart') || '[]');
        console.log('Cart items from localStorage:', cartItems);
    }
    
    if (cartItems.length === 0) {
        alert('Your cart is empty!');
        return;
    }
    
    // Get form data
    const formData = new FormData(form);
    const orderData = {
        name: formData.get('name'),
        email: formData.get('email'),
        phone: formData.get('phone'),
        city: formData.get('city'),
        ward_number: formData.get('ward_number'),
        area_locality: formData.get('area_locality'),
        building_name: formData.get('building_name'),
        detailed_directions: formData.get('detailed_directions'),
        payment_method: formData.get('payment_method'),
        items: cartItems,
        total: parseFloat(document.getElementById('checkout-total').textContent.replace('Rs.', '')),
        applied_offer: localStorage.getItem('applied_offer') // Include applied offer
    };

    // Add GPS location data if available
    if (currentLocation) {
        orderData.gps_location = {
            latitude: currentLocation.lat,
            longitude: currentLocation.lng,
            coordinates: `${currentLocation.lat.toFixed(6)}, ${currentLocation.lng.toFixed(6)}`
        };
        
        // Save location to localStorage for future use
        localStorage.setItem('checkout_gps_location', JSON.stringify(currentLocation));
    }
    
    console.log('Order data:', orderData);
    
    // Show loading state
    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML = `
        <div class="flex items-center justify-center gap-2">
            <div class="w-4 h-4 border-2 border-current border-t-transparent rounded-full animate-spin"></div>
            <span>Processing...</span>
        </div>
    `;
    submitBtn.disabled = true;
    
    // Save form data to user profile (if user is logged in)
    saveFormDataToProfile();
    
    // Submit order to backend
    fetch('/orders', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify(orderData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Clear cart
            localStorage.removeItem('momo_cart');
            localStorage.removeItem('applied_offer');
            
            if (typeof window.cartManager !== 'undefined') {
                window.cartManager.clearCart();
            }
            
            // Show success message
            alert('Order placed successfully! We\'ll contact you soon.');
            
            // Redirect to home
            window.location.href = '<?php echo e(route("home")); ?>';
        } else {
            throw new Error(data.message || 'Failed to place order');
        }
    })
    .catch(error => {
        console.error('Error placing order:', error);
        alert('Failed to place order. Please try again.');
        
        // Reset button
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    });
}
</script>
<?php $__env->stopPush(); ?> 
<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\sabst\momo_shop\resources\views/checkout.blade.php ENDPATH**/ ?>