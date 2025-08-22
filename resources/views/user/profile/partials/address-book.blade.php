<div class="bg-white rounded-lg shadow">
    <div class="px-6 py-4 border-b border-gray-200">
        <h2 class="text-lg font-semibold text-gray-900">Address Book</h2>
    </div>
    <div class="p-6">
        <!-- Add New Address Button -->
        <div class="mb-6">
            <button onclick="showAddAddressModal()" 
                    class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 flex items-center space-x-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg>
                <span>Add New Address</span>
            </button>
        </div>

        <!-- Address List -->
        <div class="space-y-4">
            <!-- Default Address (Current Profile Address) -->
            <div class="border border-gray-200 rounded-lg p-4 relative">
                <div class="flex items-start justify-between">
                    <div class="flex-1">
                        <div class="flex items-center space-x-2 mb-2">
                            <span class="text-sm font-medium text-gray-900">{{ $user->name }}</span>
                        </div>
                        <div class="text-sm text-gray-600 space-y-1">
                            <p>{{ $user->area_locality }}, {{ $user->ward_number }}</p>
                            <p>{{ $user->city }}</p>
                            @if($user->building_name)
                                <p>{{ $user->building_name }}</p>
                            @endif
                            @if($user->detailed_directions)
                                <p class="text-gray-500 italic">{{ $user->detailed_directions }}</p>
                            @endif
                        </div>
                    </div>
                    <div class="flex items-center space-x-2">
                        <button onclick="editDefaultAddress()" 
                                class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                            Edit
                        </button>
                    </div>
                </div>
            </div>

            <!-- Additional Addresses (Placeholder) -->
            <div class="border border-gray-200 rounded-lg p-4 relative">
                <div class="flex items-start justify-between">
                    <div class="flex-1">
                        <div class="flex items-center space-x-2 mb-2">
                            <span class="text-sm font-medium text-gray-900">Office Address</span>
                        </div>
                        <div class="text-sm text-gray-600 space-y-1">
                            <p>Thamel, Ward 26</p>
                            <p>Kathmandu</p>
                            <p>Office Building, 3rd Floor</p>
                        </div>
                    </div>
                    <div class="flex items-center space-x-2">
                        <button onclick="setAsDefault()" 
                                class="text-green-600 hover:text-green-800 text-sm font-medium">
                            Set as Default
                        </button>
                        <button onclick="editAddress(1)" 
                                class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                            Edit
                        </button>
                        <button onclick="deleteAddress(1)" 
                                class="text-red-600 hover:text-red-800 text-sm font-medium">
                            Delete
                        </button>
                    </div>
                </div>
            </div>

            <!-- Empty State -->
            <div id="emptyAddresses" class="text-center py-8 hidden">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">No additional addresses</h3>
                <p class="mt-1 text-sm text-gray-500">Add a new address to make ordering easier.</p>
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