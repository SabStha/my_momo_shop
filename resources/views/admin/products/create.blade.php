@extends('layouts.admin')

@section('content')
<div class="py-12">
    <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 bg-white border-b border-gray-200">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-2xl font-semibold text-gray-800">Create New Product</h2>
                    <a href="{{ route('admin.products.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-md text-sm font-medium">
                        Back to Products
                    </a>
                </div>

                <!-- Alert Container -->
                <div id="alert-container"></div>

                <form id="createProductForm" action="{{ route('admin.products.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                    @csrf

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700">Product Name *</label>
                            <input type="text" name="name" id="name" required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#6E0D25] focus:ring focus:ring-[#6E0D25] focus:ring-opacity-50">
                            <div id="name-error" class="text-red-500 text-sm mt-1"></div>
                        </div>

                        <div>
                            <label for="code" class="block text-sm font-medium text-gray-700">Product Code *</label>
                            <div class="flex space-x-2">
                                <input type="text" name="code" id="code" required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#6E0D25] focus:ring focus:ring-[#6E0D25] focus:ring-opacity-50">
                                <button type="button" onclick="generateCode()" 
                                    class="mt-1 px-3 py-2 bg-gray-500 text-white rounded-md text-sm hover:bg-gray-600">
                                    Generate
                                </button>
                            </div>
                            <div id="code-error" class="text-red-500 text-sm mt-1"></div>
                        </div>

                        <div>
                            <label for="price" class="block text-sm font-medium text-gray-700">Price *</label>
                            <input type="number" name="price" id="price" step="0.01" required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#6E0D25] focus:ring focus:ring-[#6E0D25] focus:ring-opacity-50">
                            <div id="price-error" class="text-red-500 text-sm mt-1"></div>
                        </div>

                        <div>
                            <label for="stock" class="block text-sm font-medium text-gray-700">Stock *</label>
                            <input type="number" name="stock" id="stock" required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#6E0D25] focus:ring focus:ring-[#6E0D25] focus:ring-opacity-50">
                            <div id="stock-error" class="text-red-500 text-sm mt-1"></div>
                        </div>

                        <div>
                            <label for="category" class="block text-sm font-medium text-gray-700">Category</label>
                            <input type="text" name="category" id="category"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#6E0D25] focus:ring focus:ring-[#6E0D25] focus:ring-opacity-50">
                            <div id="category-error" class="text-red-500 text-sm mt-1"></div>
                        </div>

                        <div>
                            <label for="tag" class="block text-sm font-medium text-gray-700">Tag</label>
                            <input type="text" name="tag" id="tag"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#6E0D25] focus:ring focus:ring-[#6E0D25] focus:ring-opacity-50">
                            <div id="tag-error" class="text-red-500 text-sm mt-1"></div>
                        </div>
                    </div>

                    <div>
                        <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                        <textarea name="description" id="description" rows="3"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#6E0D25] focus:ring focus:ring-[#6E0D25] focus:ring-opacity-50"></textarea>
                        <div id="description-error" class="text-red-500 text-sm mt-1"></div>
                    </div>

                    <div>
                        <label for="image" class="block text-sm font-medium text-gray-700">Product Image</label>
                        <input type="file" name="image" id="image" accept="image/*"
                            class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-[#6E0D25] file:text-white hover:file:bg-[#8B0D2F]">
                        <div id="image-error" class="text-red-500 text-sm mt-1"></div>
                    </div>

                    <div class="flex items-center space-x-6">
                        <div class="flex items-center">
                            <input type="checkbox" name="is_active" id="is_active" value="1"
                                class="h-4 w-4 text-[#6E0D25] focus:ring-[#6E0D25] border-gray-300 rounded" checked>
                            <label for="is_active" class="ml-2 block text-sm text-gray-700">
                                Active
                            </label>
                        </div>

                        <div class="flex items-center">
                            <input type="checkbox" name="is_featured" id="is_featured" value="1"
                                class="h-4 w-4 text-[#6E0D25] focus:ring-[#6E0D25] border-gray-300 rounded">
                            <label for="is_featured" class="ml-2 block text-sm text-gray-700">
                                Featured Product
                            </label>
                        </div>
                    </div>

                    <div class="flex justify-end">
                        <button type="submit" id="submitBtn"
                            class="bg-[#6E0D25] hover:bg-[#8B0D2F] text-white px-4 py-2 rounded-md text-sm font-medium focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#6E0D25]">
                            <span id="submitText">Create Product</span>
                            <span id="loadingText" class="hidden">Creating...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
// Generate unique product code
function generateCode() {
    const timestamp = Date.now().toString().slice(-6);
    const random = Math.random().toString(36).substring(2, 5).toUpperCase();
    const code = `PROD-${timestamp}-${random}`;
    document.getElementById('code').value = code;
}

document.getElementById('createProductForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    // Clear previous errors
    clearErrors();
    
    // Show loading state
    const submitBtn = document.getElementById('submitBtn');
    const submitText = document.getElementById('submitText');
    const loadingText = document.getElementById('loadingText');
    
    submitBtn.disabled = true;
    submitText.classList.add('hidden');
    loadingText.classList.remove('hidden');
    
    // Get form data
    const formData = new FormData(this);
    
    // Ensure CSRF token is included
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    
    fetch(this.action, {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': csrfToken
        }
    })
    .then(response => {
        if (!response.ok) {
            if (response.status === 422) {
                return response.json().then(data => {
                    throw new Error('Validation failed: ' + JSON.stringify(data.errors));
                });
            }
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            showAlert('success', data.message);
            // Reset form
            this.reset();
            // Redirect after 2 seconds
            setTimeout(() => {
                window.location.href = '{{ route("admin.products.index") }}';
            }, 2000);
        } else {
            showAlert('error', data.message || 'Failed to create product');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('error', 'An error occurred while creating the product: ' + error.message);
    })
    .finally(() => {
        // Reset button state
        submitBtn.disabled = false;
        submitText.classList.remove('hidden');
        loadingText.classList.add('hidden');
    });
});

function clearErrors() {
    const errorElements = document.querySelectorAll('[id$="-error"]');
    errorElements.forEach(element => {
        element.textContent = '';
    });
}

function showAlert(type, message) {
    const alertContainer = document.getElementById('alert-container');
    const alertClass = type === 'success' ? 'bg-green-100 text-green-800 border-green-200' : 'bg-red-100 text-red-800 border-red-200';
    
    alertContainer.innerHTML = `
        <div class="p-4 rounded-md border ${alertClass} mb-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'}"></i>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium">${message}</p>
                </div>
            </div>
        </div>
    `;
    
    // Auto-hide after 5 seconds
    setTimeout(() => {
        alertContainer.innerHTML = '';
    }, 5000);
}
</script>
@endsection 