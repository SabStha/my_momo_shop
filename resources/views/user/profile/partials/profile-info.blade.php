<!-- User Profile Header Section -->
<div class="bg-gradient-to-br from-blue-50 to-indigo-100 rounded-xl shadow-lg border border-blue-200 mb-4 lg:mb-6 overflow-hidden">
    <!-- Header with subtle pattern -->
    <div class="relative">
        <div class="absolute inset-0 bg-gradient-to-r from-blue-600/10 to-indigo-600/10"></div>
        <div class="relative px-6 py-6">
            <!-- Desktop Layout -->
            <div class="hidden md:flex items-center justify-between">
                <!-- Left Side: User Info -->
                <div class="flex items-center space-x-6">
                    <!-- Profile Picture -->
                    <div class="relative group">
                        <div class="w-24 h-24 rounded-full border-4 border-white shadow-xl overflow-hidden bg-white ring-4 ring-blue-100">
                            @if($user->profile_picture)
                                <img class="w-full h-full object-cover" 
                                     src="{{ Storage::url($user->profile_picture) }}" 
                                     alt="{{ $user->name }}"
                                     id="profileImage">
                            @else
                                <div class="w-full h-full flex items-center justify-center bg-gradient-to-br from-blue-100 to-indigo-100" id="profilePlaceholder">
                                    <svg class="h-12 w-12 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                    </svg>
                                </div>
                            @endif
                        </div>
                        
                        <!-- Camera Icon Overlay -->
                        <div class="absolute -bottom-1 -right-1 bg-blue-600 rounded-full p-2 cursor-pointer hover:bg-blue-700 transition-all duration-200 shadow-lg group-hover:scale-110">
                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                        </div>
                    </div>
                    
                    <!-- User Name and Info -->
                    <div class="space-y-2">
                        <div>
                            <h3 class="text-2xl font-bold text-gray-900 mb-1">{{ $user->name }}</h3>
                            <div class="flex items-center space-x-2">
                                <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"></path>
                                </svg>
                                <p class="text-gray-600 font-medium">{{ $user->email }}</p>
                            </div>
                        </div>
                        <div class="flex items-center space-x-4 text-sm text-gray-500">
                            <div class="flex items-center space-x-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                                <span>Member since {{ $user->created_at->format('M Y') }}</span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Right Side: Current Badge -->
                <div class="text-right">
                    @php
                        $highestBadge = $user->userBadges()->with(['badgeClass', 'badgeRank', 'badgeTier'])->active()->orderBy('badge_rank_id', 'desc')->orderBy('badge_tier_id', 'desc')->first();
                    @endphp
                    
                    @if($highestBadge)
                        <div class="bg-white/80 backdrop-blur-sm rounded-2xl p-4 shadow-lg border border-white/50">
                            <div class="text-sm font-medium text-gray-600 mb-3">Current Achievement</div>
                            <div class="flex items-center space-x-3">
                                <div class="w-14 h-14 bg-gradient-to-br from-{{ $highestBadge->badgeRank->code === 'gold' ? 'yellow' : ($highestBadge->badgeRank->code === 'silver' ? 'gray' : 'amber') }}-400 to-{{ $highestBadge->badgeRank->code === 'gold' ? 'yellow' : ($highestBadge->badgeRank->code === 'silver' ? 'gray' : 'amber') }}-600 rounded-full flex items-center justify-center shadow-lg ring-2 ring-white">
                                    <span class="text-xl text-white">{{ $highestBadge->badgeClass->icon }}</span>
                                </div>
                                <div class="text-left">
                                    <div class="font-bold text-gray-900 text-lg">{{ $highestBadge->badgeClass->name }}</div>
                                    <div class="text-sm text-gray-600">{{ $highestBadge->badgeRank->name }} • {{ $highestBadge->badgeTier->name }}</div>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="bg-white/80 backdrop-blur-sm rounded-2xl p-4 shadow-lg border border-white/50">
                            <div class="text-sm font-medium text-gray-600 mb-3">Current Achievement</div>
                            <div class="flex items-center space-x-3">
                                <div class="w-14 h-14 bg-gradient-to-br from-gray-200 to-gray-300 rounded-full flex items-center justify-center shadow-lg ring-2 ring-white">
                                    <span class="text-xl text-gray-500">🏆</span>
                                </div>
                                <div class="text-left">
                                    <div class="font-bold text-gray-900 text-lg">No Badge Yet</div>
                                    <div class="text-sm text-gray-600">Start earning badges!</div>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Mobile Layout -->
            <div class="md:hidden space-y-6">
                <!-- User Info Section -->
                <div class="flex items-center space-x-4">
                    <!-- Profile Picture -->
                    <div class="relative group">
                        <div class="w-20 h-20 rounded-full border-4 border-white shadow-xl overflow-hidden bg-white ring-4 ring-blue-100">
                            @if($user->profile_picture)
                                <img class="w-full h-full object-cover" 
                                     src="{{ Storage::url($user->profile_picture) }}" 
                                     alt="{{ $user->name }}"
                                     id="profileImageMobile">
                            @else
                                <div class="w-full h-full flex items-center justify-center bg-gradient-to-br from-blue-100 to-indigo-100" id="profilePlaceholderMobile">
                                    <svg class="h-10 w-10 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                    </svg>
                                </div>
                            @endif
                        </div>
                        
                        <!-- Camera Icon Overlay -->
                        <div class="absolute -bottom-1 -right-1 bg-blue-600 rounded-full p-1.5 cursor-pointer hover:bg-blue-700 transition-all duration-200 shadow-lg group-hover:scale-110">
                            <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                        </div>
                    </div>
                    
                    <!-- User Name and Info -->
                    <div class="flex-1 min-w-0">
                        <h3 class="text-xl font-bold text-gray-900 mb-1">{{ $user->name }}</h3>
                        <div class="flex items-center space-x-1 mb-1">
                            <svg class="w-3 h-3 text-blue-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"></path>
                            </svg>
                            <p class="text-sm text-gray-600 truncate">{{ $user->email }}</p>
                        </div>
                        <div class="flex items-center space-x-1 text-xs text-gray-500">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                            <span>Member since {{ $user->created_at->format('M Y') }}</span>
                        </div>
                    </div>
                </div>
                
                <!-- Current Badge Section -->
                <div class="bg-white/80 backdrop-blur-sm rounded-2xl p-4 shadow-lg border border-white/50">
                    <div class="text-center">
                        <div class="text-sm font-medium text-gray-600 mb-4">Current Achievement</div>
                        @php
                            $highestBadge = $user->userBadges()->with(['badgeClass', 'badgeRank', 'badgeTier'])->active()->orderBy('badge_rank_id', 'desc')->orderBy('badge_tier_id', 'desc')->first();
                        @endphp
                        
                        @if($highestBadge)
                            <div class="flex flex-col items-center space-y-4">
                                <div class="w-20 h-20 bg-gradient-to-br from-{{ $highestBadge->badgeRank->code === 'gold' ? 'yellow' : ($highestBadge->badgeRank->code === 'silver' ? 'gray' : 'amber') }}-400 to-{{ $highestBadge->badgeRank->code === 'gold' ? 'yellow' : ($highestBadge->badgeRank->code === 'silver' ? 'gray' : 'amber') }}-600 rounded-full flex items-center justify-center shadow-lg ring-4 ring-white">
                                    <span class="text-3xl text-white">{{ $highestBadge->badgeClass->icon }}</span>
                                </div>
                                <div class="text-center">
                                    <div class="font-bold text-gray-900 text-xl mb-1">{{ $highestBadge->badgeClass->name }}</div>
                                    <div class="text-sm text-gray-600 bg-gray-100 rounded-full px-3 py-1 inline-block">{{ $highestBadge->badgeRank->name }} • {{ $highestBadge->badgeTier->name }}</div>
                                </div>
                            </div>
                        @else
                            <div class="flex flex-col items-center space-y-4">
                                <div class="w-20 h-20 bg-gradient-to-br from-gray-200 to-gray-300 rounded-full flex items-center justify-center shadow-lg ring-4 ring-white">
                                    <span class="text-3xl text-gray-500">🏆</span>
                                </div>
                                <div class="text-center">
                                    <div class="font-bold text-gray-900 text-xl mb-1">No Badge Yet</div>
                                    <div class="text-sm text-gray-600 bg-gray-100 rounded-full px-3 py-1 inline-block">Start earning badges!</div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
        
        <!-- Profile Picture Upload Section -->
        <div class="bg-white/60 backdrop-blur-sm border-t border-white/30 px-6 py-4">
            <div class="space-y-4">
                <input type="file" name="profile_picture" id="profile_picture" accept="image/*"
                       class="hidden"
                       onchange="handleImageUpload(this)">
                
                <div id="fileName" class="text-sm text-gray-700 font-medium"></div>
                
                @error('profile_picture')
                    <p class="text-sm text-red-600 bg-red-50 border border-red-200 rounded-lg px-3 py-2">{{ $message }}</p>
                @enderror
            </div>

            <!-- Action Buttons -->
            <div class="flex flex-col space-y-3 mt-4" id="actionButtons" style="display: none;">
                <button type="button" onclick="openCropper()" 
                        class="w-full px-4 py-3 bg-white/80 text-gray-700 rounded-xl text-sm font-medium hover:bg-white transition-all duration-200 shadow-sm border border-white/50"
                        id="cropButton">
                    <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                    Crop Image
                </button>
                <button type="button" onclick="uploadPicture()" 
                        class="w-full px-4 py-3 bg-blue-600 text-white rounded-xl text-sm font-medium hover:bg-blue-700 transition-all duration-200 shadow-lg"
                        id="uploadButton">
                    <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                    </svg>
                    Upload Picture
                </button>
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
            // Update desktop image
            const desktopImg = document.getElementById('profileImage') || document.getElementById('profilePlaceholder');
            if (desktopImg.tagName === 'IMG') {
                desktopImg.src = e.target.result;
            } else {
                // Replace placeholder with image
                const newImg = document.createElement('img');
                newImg.src = e.target.result;
                newImg.className = 'w-full h-full object-cover';
                newImg.id = 'profileImage';
                desktopImg.parentNode.replaceChild(newImg, desktopImg);
            }
            
            // Update mobile image
            const mobileImg = document.getElementById('profileImageMobile') || document.getElementById('profilePlaceholderMobile');
            if (mobileImg.tagName === 'IMG') {
                mobileImg.src = e.target.result;
            } else {
                // Replace placeholder with image
                const newImg = document.createElement('img');
                newImg.src = e.target.result;
                newImg.className = 'w-full h-full object-cover';
                newImg.id = 'profileImageMobile';
                mobileImg.parentNode.replaceChild(newImg, mobileImg);
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

</script>

<!-- Include Cropper.js -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.12/cropper.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.12/cropper.min.js"></script>