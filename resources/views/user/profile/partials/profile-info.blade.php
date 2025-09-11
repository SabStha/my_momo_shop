<!-- User Profile Header Section -->
<div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6">
    <div class="px-6 py-4 border-b border-gray-200">
        <h2 class="text-lg font-semibold text-gray-900">Profile Information</h2>
    </div>
    <div class="p-6">
        <div class="flex items-center justify-between">
            <!-- Left Side: User Info -->
            <div class="flex items-center space-x-4">
                <!-- Profile Picture -->
                <div class="relative">
                    <div class="w-20 h-20 rounded-full border-4 border-gray-200 overflow-hidden bg-gray-100">
                        @if($user->profile_picture)
                            <img class="w-full h-full object-cover" 
                                 src="{{ Storage::url($user->profile_picture) }}" 
                                 alt="{{ $user->name }}"
                                 id="profileImage">
                        @else
                            <div class="w-full h-full flex items-center justify-center" id="profilePlaceholder">
                                <svg class="h-10 w-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                            </div>
                        @endif
                    </div>
                    
                    <!-- Camera Icon Overlay -->
                    <div class="absolute bottom-0 right-0 bg-blue-500 rounded-full p-1.5 cursor-pointer hover:bg-blue-600 transition-colors">
                        <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                    </div>
                </div>
                
                <!-- User Name and Info -->
                <div class="space-y-1">
                    <h3 class="text-xl font-bold text-gray-900">{{ $user->name }}</h3>
                    <p class="text-sm text-gray-600">{{ $user->email }}</p>
                    <p class="text-xs text-gray-500">Member since {{ $user->created_at->format('M Y') }}</p>
                </div>
            </div>
            
            <!-- Right Side: Current Badge -->
            <div class="flex items-center space-x-3">
                @php
                    $highestBadge = $user->userBadges()->with(['badgeClass', 'badgeRank', 'badgeTier'])->active()->orderBy('badge_rank_id', 'desc')->orderBy('badge_tier_id', 'desc')->first();
                @endphp
                
                @if($highestBadge)
                    <div class="text-right">
                        <div class="text-sm text-gray-600 mb-1">Current Badge</div>
                        <div class="flex items-center space-x-2">
                            <div class="w-12 h-12 bg-gradient-to-br from-{{ $highestBadge->badgeRank->code === 'gold' ? 'yellow' : ($highestBadge->badgeRank->code === 'silver' ? 'gray' : 'amber') }}-400 to-{{ $highestBadge->badgeRank->code === 'gold' ? 'yellow' : ($highestBadge->badgeRank->code === 'silver' ? 'gray' : 'amber') }}-600 rounded-full flex items-center justify-center shadow-lg">
                                <span class="text-lg text-white">{{ $highestBadge->badgeClass->icon }}</span>
                            </div>
                            <div class="text-right">
                                <div class="font-semibold text-gray-900">{{ $highestBadge->badgeClass->name }}</div>
                                <div class="text-sm text-gray-600">{{ $highestBadge->badgeRank->name }} • {{ $highestBadge->badgeTier->name }}</div>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="text-right">
                        <div class="text-sm text-gray-600 mb-1">Current Badge</div>
                        <div class="flex items-center space-x-2">
                            <div class="w-12 h-12 bg-gray-200 rounded-full flex items-center justify-center">
                                <span class="text-lg text-gray-400">🏆</span>
                            </div>
                            <div class="text-right">
                                <div class="font-semibold text-gray-900">No Badge Yet</div>
                                <div class="text-sm text-gray-600">Start earning badges!</div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
        
        <!-- Profile Picture Upload Section -->
        <div class="mt-6 pt-6 border-t border-gray-200">
            <div class="space-y-3">
                <input type="file" name="profile_picture" id="profile_picture" accept="image/*"
                       class="hidden"
                       onchange="handleImageUpload(this)">
                
                <div id="fileName" class="text-sm text-gray-600 font-medium"></div>
                
                @error('profile_picture')
                    <p class="text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Action Buttons -->
            <div class="flex flex-col space-y-2 mt-4" id="actionButtons" style="display: none;">
                <button type="button" onclick="openCropper()" 
                        class="w-full px-4 py-2 bg-gray-100 text-gray-700 rounded-lg text-sm font-medium hover:bg-gray-200 transition-colors"
                        id="cropButton">
                    Crop Image
                </button>
                <button type="button" onclick="uploadPicture()" 
                        class="w-full px-4 py-2 bg-blue-500 text-white rounded-lg text-sm font-medium hover:bg-blue-600 transition-colors"
                        id="uploadButton">
                    Upload Picture
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Wallet Information -->
@include('user.profile.partials.wallet')

<!-- Credit Card Top-up Section -->
<div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6">
    <div class="px-6 py-4 border-b border-gray-200">
        <h2 class="text-lg font-semibold text-gray-900">Top-up Credits</h2>
    </div>
    <div class="p-6">
        <div class="space-y-4">
            <div class="text-center mb-4">
                <p class="text-sm text-gray-600">Scan a QR code or enter barcode to top up your account</p>
            </div>
            
            <!-- Barcode Scanner Section -->
            <div class="space-y-4">
                <div class="text-center">
                    <button type="button" 
                            onclick="startBarcodeScanner()"
                            class="inline-flex items-center px-6 py-3 bg-blue-600 text-white rounded-lg font-medium hover:bg-blue-700 focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V6a1 1 0 00-1-1H5a1 1 0 00-1 1v1a1 1 0 001 1zm12 0h2a1 1 0 001-1V6a1 1 0 00-1-1h-2a1 1 0 00-1 1v1a1 1 0 001 1zM5 20h2a1 1 0 001-1v-1a1 1 0 00-1-1H5a1 1 0 00-1 1v1a1 1 0 001 1z"></path>
                        </svg>
                        Scan Barcode
                    </button>
                </div>

                <!-- Manual Entry Option -->
                <div class="text-center">
                    <button type="button" 
                            onclick="showManualEntry()"
                            class="text-blue-600 hover:text-blue-700 text-sm font-medium">
                        Or enter code manually
                    </button>
                </div>

                <!-- Manual Entry Form (Hidden by default) -->
                <div id="manualEntryForm" class="hidden space-y-4">
                    <div>
                        <label for="barcode_input" class="block text-sm font-medium text-gray-700 mb-2">
                            QR Code or Barcode
                        </label>
                        <input type="text" 
                               id="barcode_input" 
                               name="barcode_input" 
                               placeholder="Enter QR code data or 12-digit barcode"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                               oninput="formatBarcode(this)">
                        <p class="text-xs text-gray-500 mt-1">Enter QR code data (JSON) or 12-digit barcode</p>
                    </div>
                    
                    <div class="flex space-x-3">
                        <button type="button" 
                                onclick="processBarcode()"
                                class="flex-1 px-6 py-3 bg-green-600 text-white rounded-lg font-medium hover:bg-green-700 focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition-colors">
                            <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            Process Card
                        </button>
                        
                        <button type="button" 
                                onclick="hideManualEntry()"
                                class="px-6 py-3 border border-gray-300 text-gray-700 rounded-lg font-medium hover:bg-gray-50 focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-colors">
                            Cancel
                        </button>
                    </div>
                </div>
            </div>
            
            <!-- Scanner Modal -->
            <div id="scannerModal" class="fixed inset-0 bg-black bg-opacity-75 hidden z-50">
                <div class="flex items-center justify-center min-h-screen p-4">
                    <div class="bg-white rounded-xl max-w-md w-full p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-semibold text-gray-900">Scan Credit Card Barcode</h3>
                            <button onclick="closeScanner()" class="text-gray-400 hover:text-gray-600">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>
                        
                        <div class="mb-6">
                            <div id="scannerContainer" class="relative overflow-hidden rounded-lg bg-gray-100 h-64 flex items-center justify-center">
                                <div class="text-center">
                                    <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V6a1 1 0 00-1-1H5a1 1 0 00-1 1v1a1 1 0 001 1zm12 0h2a1 1 0 001-1V6a1 1 0 00-1-1h-2a1 1 0 00-1 1v1a1 1 0 001 1zM5 20h2a1 1 0 001-1v-1a1 1 0 00-1-1H5a1 1 0 00-1 1v1a1 1 0 001 1z"></path>
                                    </svg>
                                    <p class="text-gray-600">Position the barcode within the frame</p>
                                    <p class="text-sm text-gray-500 mt-2">Camera access required</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="flex space-x-3">
                            <button type="button" onclick="closeScanner()" 
                                    class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                                Cancel
                            </button>
                            <button type="button" onclick="toggleScanner()" 
                                    class="flex-1 px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors">
                                <span id="scannerButtonText">Start Scanner</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Top-up Status -->
            <div id="topUpStatus" class="hidden mt-4 p-4 rounded-lg">
                <div id="topUpSuccess" class="hidden bg-green-50 border border-green-200 text-green-800 p-4 rounded-lg">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 001.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                        </svg>
                        <span class="font-medium">Top-up successful!</span>
                    </div>
                    <p class="text-sm mt-1" id="topUpMessage"></p>
                </div>
                
                <div id="topUpError" class="hidden bg-red-50 border border-red-200 text-red-800 p-4 rounded-lg">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                        </svg>
                        <span class="font-medium">Top-up failed!</span>
                    </div>
                    <p class="text-sm mt-1" id="topUpErrorMessage"></p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Account Information -->
<div class="bg-white rounded-lg shadow mb-6">
    <div class="px-6 py-4 border-b border-gray-200">
        <h2 class="text-lg font-semibold text-gray-900">Account Information</h2>
    </div>
    <div class="p-6">
        <div class="space-y-4">
            <div class="flex items-center justify-between">
                <span class="text-sm font-medium text-gray-500">Member Since</span>
                <span class="text-sm text-gray-900">{{ $user->created_at->format('M d, Y') }}</span>
            </div>
            <div class="flex items-center justify-between">
                <span class="text-sm font-medium text-gray-500">Last Updated</span>
                <span class="text-sm text-gray-900">{{ $user->updated_at->format('M d, Y') }}</span>
            </div>
            
            <!-- Verification Status -->
            <div class="border-t pt-4 mt-4">
                <h4 class="text-sm font-medium text-gray-700 mb-3">Verification Status</h4>
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-2">
                            <svg class="w-5 h-5 {{ $user->email_verified_at ? 'text-green-500' : 'text-gray-400' }}" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                            </svg>
                            <span class="text-sm font-medium text-gray-700">Email Address</span>
                        </div>
                        <div class="flex items-center space-x-2">
                            @if($user->email_verified_at)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                    </svg>
                                    Verified
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                    </svg>
                                    Unverified
                                </span>
                                <span class="text-blue-600 text-sm font-medium">
                                    Verification not available
                                </span>
                            @endif
                        </div>
                    </div>
                    
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-2">
                            <svg class="w-5 h-5 {{ $user->phone_verified_at ? 'text-green-500' : 'text-gray-400' }}" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                            </svg>
                            <span class="text-sm font-medium text-gray-700">Phone Number</span>
                        </div>
                        <div class="flex items-center space-x-2">
                            @if($user->phone_verified_at)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                    </svg>
                                    Verified
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                    </svg>
                                    Unverified
                                </span>
                                <span class="text-blue-600 text-sm font-medium">
                                    Verification not available
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Instagram-style Image Cropper Modal -->
<div id="cropperModal" class="fixed inset-0 bg-black bg-opacity-75 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-xl max-w-md w-full p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Crop Profile Picture</h3>
                <button onclick="closeCropper()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            
            <div class="mb-6">
                <div id="cropperContainer" class="relative overflow-hidden rounded-lg bg-gray-100">
                    <img id="cropperImage" src="" alt="Crop preview" class="max-w-full h-auto">
                </div>
            </div>
            
            <div class="flex space-x-3">
                <button type="button" onclick="closeCropper()" 
                        class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                    Cancel
                </button>
                <button type="button" onclick="applyCrop()" 
                        class="flex-1 px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors">
                    Apply
                </button>
            </div>
        </div>
    </div>
</div>

<script>
let cropper = null;
let selectedFile = null;

function handleImageUpload(input) {
    const file = input.files[0];
    if (file) {
        selectedFile = file;
        
        // Show filename
        document.getElementById('fileName').textContent = file.name;
        
        // Show preview
        const reader = new FileReader();
        reader.onload = function(e) {
            const img = document.getElementById('profileImage') || document.getElementById('profilePlaceholder');
            if (img.tagName === 'IMG') {
                img.src = e.target.result;
            } else {
                // Replace placeholder with image
                const newImg = document.createElement('img');
                newImg.src = e.target.result;
                newImg.className = 'w-full h-full object-cover';
                newImg.id = 'profileImage';
                img.parentNode.replaceChild(newImg, img);
            }
            
            // Show action buttons
            document.getElementById('actionButtons').style.display = 'flex';
        };
        reader.readAsDataURL(file);
    } else {
        // Clear filename if no file selected
        document.getElementById('fileName').textContent = '';
    }
}

function openCropper() {
    if (!selectedFile) return;
    
    const reader = new FileReader();
    reader.onload = function(e) {
        const cropperImage = document.getElementById('cropperImage');
        cropperImage.src = e.target.result;
        
        document.getElementById('cropperModal').classList.remove('hidden');
        
        // Initialize cropper
        if (cropper) {
            cropper.destroy();
        }
        
        cropper = new Cropper(cropperImage, {
            aspectRatio: 1,
            viewMode: 1,
            dragMode: 'move',
            autoCropArea: 1,
            restore: false,
            guides: true,
            center: true,
            highlight: false,
            cropBoxMovable: true,
            cropBoxResizable: true,
            toggleDragModeOnDblclick: false,
        });
    };
    reader.readAsDataURL(selectedFile);
}

function closeCropper() {
    document.getElementById('cropperModal').classList.add('hidden');
    if (cropper) {
        cropper.destroy();
        cropper = null;
    }
}

function applyCrop() {
    if (!cropper) return;
    
    const canvas = cropper.getCroppedCanvas({
        width: 400,
        height: 400,
    });
    
    canvas.toBlob(function(blob) {
        // Create a new file from the cropped blob
        selectedFile = new File([blob], 'cropped-image.jpg', { type: 'image/jpeg' });
        
        // Update preview
        const img = document.getElementById('profileImage');
        if (img) {
            img.src = canvas.toDataURL();
        }
        
        closeCropper();
    }, 'image/jpeg');
}

function uploadPicture() {
    if (!selectedFile) {
        showErrorToast('No file selected');
        return;
    }
    
    const formData = new FormData();
    formData.append('profile_picture', selectedFile);
    formData.append('_token', '{{ csrf_token() }}');
    
    // Show loading
    if (typeof showLoading === 'function') {
        showLoading();
    }
    
    fetch('{{ route("profile.picture") }}', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        const contentType = response.headers.get('content-type');
        if (contentType && contentType.includes('application/json')) {
            return response.json();
        } else {
            throw new Error('Server returned non-JSON response');
        }
    })
    .then(data => {
        if (typeof hideLoading === 'function') {
            hideLoading();
        }
        if (data.success) {
            if (typeof showSuccessToast === 'function') {
                showSuccessToast('Profile picture updated successfully!');
            }
            // Reset buttons
            document.getElementById('actionButtons').style.display = 'none';
            selectedFile = null;
        } else {
            if (data.errors && data.errors.profile_picture) {
                const errorMessage = Array.isArray(data.errors.profile_picture) 
                    ? data.errors.profile_picture[0] 
                    : data.errors.profile_picture;
                showErrorToast(errorMessage);
            } else {
                showErrorToast(data.message || 'Failed to update profile picture');
            }
        }
    })
    .catch(error => {
        if (typeof hideLoading === 'function') {
            hideLoading();
        }
        showErrorToast('An error occurred while uploading the image');
    });
}

// Close cropper modal when clicking outside
document.addEventListener('DOMContentLoaded', function() {
    const cropperModal = document.getElementById('cropperModal');
    if (cropperModal) {
        cropperModal.addEventListener('click', function(e) {
            if (e.target === this) {
                closeCropper();
            }
        });
    }
});

// Error toast function
function showErrorToast(message) {
    const toast = document.createElement('div');
    toast.className = 'fixed top-4 right-4 bg-red-500 text-white px-6 py-3 rounded-lg shadow-lg z-50 transform transition-all duration-300 ease-in-out translate-y-0 opacity-100';
    toast.innerHTML = `
        <div class="flex items-center space-x-2">
            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
            </svg>
            <span>${message}</span>
        </div>
    `;
    document.body.appendChild(toast);
    
    setTimeout(() => {
        toast.classList.add('translate-y-2', 'opacity-0');
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

// Barcode Scanner Functions
let scanner = null;
let isScanning = false;

// Barcode formatting
function formatBarcode(input) {
    let value = input.value.replace(/\D/g, ''); // Remove non-digits
    input.value = value.substring(0, 12); // Limit to 12 digits
}

// Manual entry functions
function showManualEntry() {
    document.getElementById('manualEntryForm').classList.remove('hidden');
}

function hideManualEntry() {
    document.getElementById('manualEntryForm').classList.add('hidden');
    document.getElementById('barcode_input').value = '';
}

// Scanner functions
function startBarcodeScanner() {
    document.getElementById('scannerModal').classList.remove('hidden');
}

function closeScanner() {
    document.getElementById('scannerModal').classList.add('hidden');
    if (scanner) {
        scanner.stop();
        scanner = null;
    }
    isScanning = false;
    document.getElementById('scannerButtonText').textContent = 'Start Scanner';
}

function toggleScanner() {
    if (isScanning) {
        stopScanner();
    } else {
        startScanner();
    }
}

function startScanner() {
    const scannerContainer = document.getElementById('scannerContainer');
    const buttonText = document.getElementById('scannerButtonText');
    
    // Check if browser supports camera access
    if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
        showErrorToast('Camera access not supported in this browser');
        return;
    }
    
    // Set scanning state
    isScanning = true;
    buttonText.textContent = 'Stop Scanner';
    
    // Load HTML5 QR Code scanner library
    if (typeof Html5Qrcode === 'undefined') {
        // Load the library if not already loaded
        const script = document.createElement('script');
        script.src = 'https://unpkg.com/html5-qrcode';
        script.onload = function() {
            initializeQRScanner();
        };
        script.onerror = function() {
            showErrorToast('Failed to load QR scanner library');
            isScanning = false;
            buttonText.textContent = 'Start Scanner';
        };
        document.head.appendChild(script);
    } else {
        initializeQRScanner();
    }
            
    function initializeQRScanner() {
        // Clear container
        scannerContainer.innerHTML = '';
        
        // Use Html5Qrcode directly for better camera control
        scanner = new Html5Qrcode("scannerContainer");
        
        // Get available cameras and prefer back camera
        Html5Qrcode.getCameras().then(cameras => {
            if (cameras && cameras.length) {
                // Find back camera (environment facing)
                let selectedCamera = cameras.find(camera => 
                    camera.label.toLowerCase().includes('back') || 
                    camera.label.toLowerCase().includes('rear') ||
                    camera.label.toLowerCase().includes('environment')
                );
                
                // If no back camera found, use the last camera (usually back camera)
                if (!selectedCamera) {
                    selectedCamera = cameras[cameras.length - 1];
                }
                
                console.log('Selected camera:', selectedCamera);
                
                // Start scanning with selected camera
                scanner.start(
                    selectedCamera.id,
                    {
                        fps: 10,
                        qrbox: { width: 250, height: 250 },
                        aspectRatio: 1.0
                    },
                    function(decodedText, decodedResult) {
                        // QR code detected
                        console.log('QR Code detected:', decodedText);
                        console.log('QR Code type:', typeof decodedText);
                        console.log('QR Code length:', decodedText.length);
                        
                        // Try to parse as JSON to see the structure
                        try {
                            const qrData = JSON.parse(decodedText);
                            console.log('Parsed QR Data:', qrData);
                            console.log('QR Data keys:', Object.keys(qrData));
                        } catch (e) {
                            console.log('QR Code is not JSON:', e.message);
                        }
                        
                        // Stop the scanner
                        scanner.stop().then(() => {
                            scanner.clear();
                            scanner = null;
                            
                            // Close scanner modal
                            closeScanner();
                            
                            // Show processing message
                            showProcessingMessage();
                            
                            // Process the result
                            processBarcodeResult(decodedText);
                        }).catch(err => {
                            console.error('Error stopping scanner:', err);
                        });
                    },
                    function(error) {
                        // Scan error (ignore common errors)
                        if (error && !error.includes('No QR code found')) {
                            console.warn('QR scan error:', error);
                        }
                    }
                ).catch(err => {
                    console.error('Error starting scanner:', err);
                    alert('Failed to start camera. Please check permissions.');
                });
            } else {
                console.error('No cameras found');
                alert('No cameras found on this device.');
            }
        }).catch(err => {
            console.error('Error getting cameras:', err);
            alert('Failed to access cameras. Please check permissions.');
        });
    }
}

function stopScanner() {
    if (scanner) {
        // Stop the QR scanner properly
        scanner.stop().then(() => {
            scanner.clear();
            scanner = null;
            isScanning = false;
            resetScannerUI();
        }).catch(err => {
            console.error('Error stopping scanner:', err);
            scanner.clear();
            scanner = null;
            isScanning = false;
            resetScannerUI();
        });
    } else {
        isScanning = false;
        resetScannerUI();
    }
}

function resetScannerUI() {
    document.getElementById('scannerButtonText').textContent = 'Start Scanner';
    
    // Reset scanner container
    const scannerContainer = document.getElementById('scannerContainer');
    scannerContainer.innerHTML = `
        <div class="text-center">
            <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V6a1 1 0 00-1-1H5a1 1 0 00-1 1v1a1 1 0 001 1zm12 0h2a1 1 0 001-1V6a1 1 0 00-1-1h-2a1 1 0 00-1 1v1a1 1 0 001 1zM5 20h2a1 1 0 001-1v-1a1 1 0 00-1-1H5a1 1 0 00-1 1v1a1 1 0 001 1z"></path>
            </svg>
            <p class="text-gray-600">Position the QR code within the frame</p>
            <p class="text-sm text-gray-500 mt-2">Camera access required</p>
        </div>
    `;
}

// Process barcode (manual entry)
function processBarcode() {
    const barcode = document.getElementById('barcode_input').value.trim();
    
    if (!barcode) {
        showErrorToast('Please enter a QR code or barcode');
        return;
    }
    
    // Check if it's a 12-digit barcode
    if (barcode.length === 12 && /^\d{12}$/.test(barcode)) {
        processBarcodeResult(barcode);
        return;
    }
    
    // Check if it's JSON (QR code data)
    try {
        const qrData = JSON.parse(barcode);
        if (typeof qrData === 'object' && qrData !== null) {
            processBarcodeResult(barcode);
            return;
        }
    } catch (e) {
        // Not JSON, continue with validation
    }
    
    showErrorToast('Please enter a valid QR code (JSON) or 12-digit barcode');
}

// Show processing message
function showProcessingMessage() {
    // Show a processing message in the main content area
    const topUpStatus = document.getElementById('topUpStatus');
    const topUpSuccess = document.getElementById('topUpSuccess');
    const topUpError = document.getElementById('topUpError');
    const topUpMessage = document.getElementById('topUpMessage');
    
    if (topUpStatus && topUpMessage) {
        topUpStatus.classList.remove('hidden');
        topUpSuccess.classList.add('hidden');
        topUpError.classList.add('hidden');
        topUpMessage.textContent = 'Processing QR code...';
    }
}

// Process barcode result (from scanner or manual entry)
function processBarcodeResult(barcode) {
    // Hide manual entry form
    hideManualEntry();
    
    // Show processing message
    showProcessingMessage();
    
    const formData = new FormData();
    formData.append('barcode', barcode);
    formData.append('_token', '{{ csrf_token() }}');
    
    fetch('{{ route("profile.topup") }}', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        const contentType = response.headers.get('content-type');
        if (contentType && contentType.includes('application/json')) {
            return response.json();
        } else {
            throw new Error('Server returned non-JSON response');
        }
    })
    .then(data => {
        if (typeof hideLoading === 'function') {
            hideLoading();
        }
        
        if (data.success) {
            if (typeof showSuccessToast === 'function') {
                showSuccessToast('Top-up successful!');
            }
            document.getElementById('topUpStatus').classList.remove('hidden');
            document.getElementById('topUpSuccess').classList.remove('hidden');
            document.getElementById('topUpError').classList.add('hidden');
            document.getElementById('topUpMessage').textContent = data.message;
        } else {
            document.getElementById('topUpStatus').classList.remove('hidden');
            document.getElementById('topUpSuccess').classList.add('hidden');
            document.getElementById('topUpError').classList.remove('hidden');
            document.getElementById('topUpErrorMessage').textContent = data.message || 'Failed to process credit card';
            showErrorToast(data.message || 'Failed to process credit card');
        }
    })
    .catch(error => {
        if (typeof hideLoading === 'function') {
            hideLoading();
        }
        showErrorToast('An error occurred while processing the credit card');
    });
}
</script>

<!-- Include Cropper.js -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.12/cropper.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.12/cropper.min.js"></script>