<div class="bg-white rounded-lg shadow mb-6">
    <div class="px-6 py-4 border-b border-gray-200">
        <h2 class="text-lg font-semibold text-gray-900">Profile Picture</h2>
    </div>
    <div class="p-6">
        <div class="flex items-center space-x-6">
            <div class="flex-shrink-0">
                @if($user->profile_picture)
                    <img class="h-20 w-20 rounded-full object-cover" 
                         src="{{ Storage::url($user->profile_picture) }}" 
                         alt="{{ $user->name }}"
                         id="profileImage">
                @else
                    <div class="h-20 w-20 rounded-full bg-gray-300 flex items-center justify-center" id="profilePlaceholder">
                        <svg class="h-10 w-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                    </div>
                @endif
            </div>
            <div class="flex-1">
                <div class="space-y-3">
                    <div>
                        <label for="profile_picture" class="block text-sm font-medium text-gray-700 mb-2">Upload New Picture</label>
                        <input type="file" name="profile_picture" id="profile_picture" accept="image/*"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('profile_picture') border-red-500 @enderror"
                               onchange="handleImageUpload(this)">
                        @error('profile_picture')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-xs text-gray-500">JPG, PNG, GIF up to 2MB</p>
                    </div>
                    <div class="flex space-x-2">
                        <button type="button" onclick="openCropper()" 
                                class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                                id="cropButton" style="display: none;">
                            Crop Image
                        </button>
                        <button type="button" onclick="uploadPicture()" 
                                class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500"
                                id="uploadButton" style="display: none;">
                            Upload Picture
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Image Cropper Modal -->
<div id="cropperModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg max-w-2xl w-full p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Crop Profile Picture</h3>
            
            <div class="mb-4">
                <div id="cropperContainer" class="relative overflow-hidden rounded-lg bg-gray-100">
                    <img id="cropperImage" src="" alt="Crop preview" class="max-w-full h-auto">
                </div>
            </div>
            
            <div class="flex space-x-3">
                <button type="button" onclick="closeCropper()" 
                        class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded hover:bg-gray-50">
                    Cancel
                </button>
                <button type="button" onclick="applyCrop()" 
                        class="flex-1 px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                    Apply Crop
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
            
            // Show crop and upload buttons
            document.getElementById('cropButton').style.display = 'inline-block';
            document.getElementById('uploadButton').style.display = 'inline-block';
        };
        reader.readAsDataURL(file);
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
        imageSmoothingEnabled: true,
        imageSmoothingQuality: 'high'
    });
    
    canvas.toBlob(function(blob) {
        // Create a new file from the cropped blob with proper MIME type
        selectedFile = new File([blob], 'profile-picture.jpg', { 
            type: 'image/jpeg',
            lastModified: Date.now()
        });
        
        console.log('Cropped file created:', selectedFile.name, 'Size:', selectedFile.size, 'Type:', selectedFile.type);
        
        // Update preview
        const img = document.getElementById('profileImage');
        if (img) {
            img.src = canvas.toDataURL('image/jpeg', 0.9);
        }
        
        closeCropper();
    }, 'image/jpeg', 0.9);
}

function uploadPicture() {
    if (!selectedFile) return;
    
    // First, let's test the simple route
    console.log('Testing simple route first...');
    
    fetch('{{ route("profile.picture.test") }}', {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(response => {
        console.log('Test route response status:', response.status);
        const contentType = response.headers.get('content-type');
        console.log('Test route Content-Type:', contentType);
        
        if (contentType && contentType.includes('application/json')) {
            return response.json();
        } else {
            return response.text().then(text => {
                console.log('Test route non-JSON response:', text);
                throw new Error('Test route returned non-JSON response: ' + text.substring(0, 100));
            });
        }
    })
    .then(data => {
        console.log('Test route success:', data);
        
        // If test route works, proceed with actual upload
        return performActualUpload();
    })
    .catch(error => {
        console.error('Test route failed:', error);
        showErrorToast('Test route failed: ' + error.message);
        
        // Fallback to traditional form submission
        console.log('Falling back to traditional form submission...');
        fallbackFormSubmission();
    });
}

function performActualUpload() {
    const formData = new FormData();
    formData.append('profile_picture', selectedFile);
    formData.append('_token', '{{ csrf_token() }}');
    
    // Show loading
    showLoading();
    
    console.log('Uploading file:', selectedFile.name, 'Size:', selectedFile.size, 'Type:', selectedFile.type);
    
    // Debug FormData contents
    for (let [key, value] of formData.entries()) {
        console.log('FormData entry:', key, value);
    }
    
    fetch('{{ route("profile.picture") }}', {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(response => {
        console.log('Response status:', response.status);
        console.log('Response headers:', response.headers);
        
        // Check if response is JSON
        const contentType = response.headers.get('content-type');
        console.log('Content-Type:', contentType);
        
        if (contentType && contentType.includes('application/json')) {
            return response.json();
        } else {
            // If not JSON, read as text to see what we got
            return response.text().then(text => {
                console.log('Non-JSON response:', text);
                throw new Error('Server returned non-JSON response: ' + text.substring(0, 100));
            });
        }
    })
    .then(data => {
        console.log('Response data:', data);
        hideLoading();
        if (data.success) {
            showSuccessToast('Profile picture updated successfully!');
            // Reset buttons
            document.getElementById('cropButton').style.display = 'none';
            document.getElementById('uploadButton').style.display = 'none';
            selectedFile = null;
        } else {
            let errorMessage = data.message || 'Failed to update profile picture';
            
            // Handle validation errors
            if (data.errors && data.errors.profile_picture) {
                errorMessage = data.errors.profile_picture[0];
            }
            
            // Log all errors for debugging
            if (data.errors) {
                console.log('Validation errors:', data.errors);
                // Show all validation errors
                const errorMessages = [];
                Object.keys(data.errors).forEach(key => {
                    data.errors[key].forEach(error => {
                        errorMessages.push(`${key}: ${error}`);
                    });
                });
                errorMessage = errorMessages.join(', ');
            }
            
            showErrorToast(errorMessage);
        }
    })
    .catch(error => {
        hideLoading();
        console.error('Upload error:', error);
        
        if (error.message.includes('Server returned non-JSON response')) {
            showErrorToast('Server error: ' + error.message);
            
            // Fallback to traditional form submission
            console.log('Falling back to traditional form submission...');
            fallbackFormSubmission();
        } else {
            showErrorToast('An error occurred while uploading the image: ' + error.message);
        }
    });
}

function fallbackFormSubmission() {
    // Create a temporary form and submit it
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '{{ route("profile.picture") }}';
    form.enctype = 'multipart/form-data';
    
    // Add CSRF token
    const csrfInput = document.createElement('input');
    csrfInput.type = 'hidden';
    csrfInput.name = '_token';
    csrfInput.value = '{{ csrf_token() }}';
    form.appendChild(csrfInput);
    
    // Add file input
    const fileInput = document.createElement('input');
    fileInput.type = 'file';
    fileInput.name = 'profile_picture';
    fileInput.style.display = 'none';
    
    // Create a FileList-like object
    const dataTransfer = new DataTransfer();
    dataTransfer.items.add(selectedFile);
    fileInput.files = dataTransfer.files;
    
    form.appendChild(fileInput);
    
    // Submit the form
    document.body.appendChild(form);
    form.submit();
    document.body.removeChild(form);
}

// Close cropper modal when clicking outside
document.getElementById('cropperModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeCropper();
    }
});

// Simple error toast function
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