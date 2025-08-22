<div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6">
    <div class="px-6 py-4 border-b border-gray-200">
        <h2 class="text-lg font-semibold text-gray-900">Profile Picture</h2>
    </div>
    <div class="p-8">
        <div class="flex flex-col items-center space-y-6">
            <!-- Instagram-style Profile Picture -->
            <div class="relative">
                <div class="w-32 h-32 rounded-full border-4 border-gray-200 overflow-hidden bg-gray-100">
                    @if($user->profile_picture)
                        <img class="w-full h-full object-cover" 
                             src="{{ Storage::url($user->profile_picture) }}" 
                             alt="{{ $user->name }}"
                             id="profileImage">
                    @else
                        <div class="w-full h-full flex items-center justify-center" id="profilePlaceholder">
                            <svg class="h-16 w-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                        </div>
                    @endif
                </div>
                
                <!-- Instagram-style Camera Icon Overlay -->
                <div class="absolute bottom-0 right-0">
                    <label for="profile_picture" class="cursor-pointer">
                        <div class="w-10 h-10 bg-blue-500 rounded-full flex items-center justify-center shadow-lg hover:bg-blue-600 transition-colors">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                        </div>
                    </label>
                </div>
            </div>

            <!-- Instagram-style Upload Section -->
            <div class="text-center space-y-4 w-full max-w-md">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">{{ $user->name }}</h3>
                    <p class="text-sm text-gray-500">Update your profile picture</p>
                </div>

                <div class="space-y-3">
                    <input type="file" name="profile_picture" id="profile_picture" accept="image/*"
                           class="hidden"
                           onchange="handleImageUpload(this)">
                    
                    <div id="fileName" class="text-sm text-gray-600 font-medium"></div>
                    
                    @error('profile_picture')
                        <p class="text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    
                    <p class="text-xs text-gray-400">JPG, PNG, GIF up to 10MB</p>
                </div>

                <!-- Instagram-style Action Buttons -->
                <div class="flex flex-col space-y-2" id="actionButtons" style="display: none;">
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
                newImg.className = 'h-20 w-20 rounded-full object-cover';
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
    showLoading();
    
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
        hideLoading();
        if (data.success) {
            showSuccessToast('Profile picture updated successfully!');
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
        hideLoading();
        showErrorToast('An error occurred while uploading the image');
    });
}

// Close cropper modal when clicking outside
document.getElementById('cropperModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeCropper();
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