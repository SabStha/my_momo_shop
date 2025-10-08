<!-- Address Book -->
<div class="bg-gradient-to-br from-green-50 to-emerald-100 rounded-xl shadow-lg border border-green-200 mb-4 lg:mb-6 overflow-hidden">
    <!-- Header Section -->
    <div class="relative">
        <div class="absolute inset-0 bg-gradient-to-r from-green-600/10 to-emerald-600/10"></div>
        <div class="relative px-6 py-6">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between space-y-4 md:space-y-0">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900 mb-2">Address Book</h2>
                    <p class="text-gray-600">Manage your delivery addresses</p>
                </div>
                
                <!-- Add New Address Button -->
                <button onclick="showAddAddressModal()" 
                        class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-green-600 to-emerald-600 text-white font-medium rounded-lg hover:from-green-700 hover:to-emerald-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition-all duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    Add New Address
                </button>
            </div>
        </div>
    </div>

    <!-- Content Section -->
    <div class="bg-white/60 backdrop-blur-sm border-t border-white/30 px-6 py-6">
        <!-- Address List -->
        <div class="space-y-4" id="addressesContainer">
            <!-- Default Address (Current Profile Address) -->
            <div class="address-card bg-white/90 backdrop-blur-sm border border-gray-200 rounded-xl p-6 hover:shadow-lg hover:border-green-300 transition-all duration-300 transform hover:-translate-y-1 relative">
                <!-- Default Badge -->
                <div class="absolute -top-2 -right-2">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800 border border-green-200">
                        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                        </svg>
                        Default
                    </span>
                </div>
                
                <div class="flex items-start space-x-4">
                    <div class="w-12 h-12 bg-gradient-to-br from-green-500 to-emerald-600 rounded-full flex items-center justify-center shadow-lg flex-shrink-0">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                    </div>
                    
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center space-x-2 mb-3">
                            <h3 class="text-lg font-semibold text-gray-900">{{ $user->name }}</h3>
                            <span class="text-sm text-gray-500">• Home</span>
                        </div>
                        
                        <div class="space-y-2">
                            <div class="flex items-center text-gray-600">
                                <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                </svg>
                                <span>{{ $user->area_locality }}, Ward {{ $user->ward_number }}</span>
                            </div>
                            <div class="flex items-center text-gray-600">
                                <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                </svg>
                                <span>{{ $user->city }}</span>
                            </div>
                            @if($user->building_name)
                                <div class="flex items-center text-gray-600">
                                    <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                    </svg>
                                    <span>{{ $user->building_name }}</span>
                                </div>
                            @endif
                            @if($user->detailed_directions)
                                <div class="flex items-start text-gray-500">
                                    <svg class="w-4 h-4 mr-2 mt-0.5 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    <span class="italic">{{ $user->detailed_directions }}</span>
                                </div>
                            @endif
                        </div>
                    </div>
                    
                    <div class="flex flex-col space-y-2">
                        <button onclick="editDefaultAddress()" 
                                class="inline-flex items-center px-3 py-2 text-sm font-medium text-blue-600 bg-blue-50 rounded-lg hover:bg-blue-100 transition-colors">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                            </svg>
                            Edit
                        </button>
                    </div>
                </div>
            </div>

            <!-- Additional Addresses - Only show if exists in future (when addresses table is built) -->
            {{-- Placeholder for future additional addresses from addresses table --}}
            {{-- When addresses table is implemented, loop through $user->addresses here --}}

            <!-- Empty State Message (Shown since no additional addresses table exists yet) -->
            <div id="emptyAddresses" class="text-center py-12">
                <div class="w-24 h-24 bg-gradient-to-br from-green-100 to-emerald-100 rounded-full flex items-center justify-center mx-auto mb-6 shadow-lg">
                    <svg class="w-12 h-12 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                </div>
                <h3 class="text-xl font-semibold text-gray-900 mb-2">Only Default Address Saved</h3>
                <p class="text-gray-600 mb-8 max-w-md mx-auto">You can add multiple addresses (Home, Office, etc.) to make ordering easier. For now, your profile address above is used.</p>
                <button onclick="showAddAddressModal()" 
                        class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-green-600 to-emerald-600 text-white font-medium rounded-lg hover:from-green-700 hover:to-emerald-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition-all duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    Add Your First Address
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Add/Edit Address Modal -->
<div id="addressModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg max-w-md w-full p-6">
            <h3 id="modalTitle" class="text-lg font-semibold text-gray-900 mb-4">Add New Address</h3>
            
            <form id="addressForm" action="#" method="POST">
                @csrf
                <div class="space-y-4">
                    <div>
                        <label for="address_name" class="block text-sm font-medium text-gray-700 mb-2">Address Name</label>
                        <input type="text" name="address_name" id="address_name" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500" 
                               placeholder="e.g., Home, Office, etc." required>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="address_city" class="block text-sm font-medium text-gray-700 mb-2">City</label>
                            <input type="text" name="city" id="address_city" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500" 
                                   required>
                        </div>
                        <div>
                            <label for="address_ward" class="block text-sm font-medium text-gray-700 mb-2">Ward Number</label>
                            <input type="text" name="ward_number" id="address_ward" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500" 
                                   required>
                        </div>
                    </div>
                    
                    <div>
                        <label for="address_area" class="block text-sm font-medium text-gray-700 mb-2">Area/Locality</label>
                        <input type="text" name="area_locality" id="address_area" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500" 
                               required>
                    </div>
                    
                    <div>
                        <label for="address_building" class="block text-sm font-medium text-gray-700 mb-2">Building Name (Optional)</label>
                        <input type="text" name="building_name" id="address_building" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    
                    <div>
                        <label for="address_directions" class="block text-sm font-medium text-gray-700 mb-2">Detailed Directions (Optional)</label>
                        <textarea name="detailed_directions" id="address_directions" rows="2"
                                  class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                  placeholder="Any additional directions to help us find you"></textarea>
                    </div>
                </div>
                
                <div class="flex space-x-3 mt-6">
                    <button type="button" onclick="hideAddressModal()" 
                            class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded hover:bg-gray-50">
                        Cancel
                    </button>
                    <button type="submit" 
                            class="flex-1 px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                        Save Address
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function showAddAddressModal() {
    document.getElementById('modalTitle').textContent = 'Add New Address';
    document.getElementById('addressForm').reset();
    document.getElementById('addressModal').classList.remove('hidden');
}

function editDefaultAddress() {
    document.getElementById('modalTitle').textContent = 'Edit Default Address';
    // Pre-fill with current user data
    document.getElementById('address_name').value = 'Default Address';
    document.getElementById('address_city').value = '{{ $user->city }}';
    document.getElementById('address_ward').value = '{{ $user->ward_number }}';
    document.getElementById('address_area').value = '{{ $user->area_locality }}';
    document.getElementById('address_building').value = '{{ $user->building_name }}';
    document.getElementById('address_directions').value = '{{ $user->detailed_directions }}';
    document.getElementById('addressModal').classList.remove('hidden');
}

function editAddress(id) {
    document.getElementById('modalTitle').textContent = 'Edit Address';
    // In a real implementation, you would fetch the address data
    document.getElementById('addressModal').classList.remove('hidden');
}

function deleteAddress(id) {
    if (confirm('Are you sure you want to delete this address?')) {
        // In a real implementation, you would send a delete request
        console.log('Deleting address:', id);
    }
}

function setAsDefault() {
    // In a real implementation, you would send a request to set as default
    console.log('Setting as default address');
}

function hideAddressModal() {
    document.getElementById('addressModal').classList.add('hidden');
}

// Close modal when clicking outside
document.getElementById('addressModal').addEventListener('click', function(e) {
    if (e.target === this) {
        hideAddressModal();
    }
});

// Handle form submission
document.getElementById('addressForm').addEventListener('submit', function(e) {
    e.preventDefault();
    // In a real implementation, you would send the form data
    console.log('Saving address:', new FormData(this));
    hideAddressModal();
});
</script>