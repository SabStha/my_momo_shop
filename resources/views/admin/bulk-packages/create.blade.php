@extends('layouts.admin')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold text-gray-900">Create New Bulk Package</h1>
            <a href="{{ route('admin.bulk-packages.index') }}" class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 transition">
                Back to Packages
            </a>
        </div>

        <form action="{{ route('admin.bulk-packages.store') }}" method="POST" enctype="multipart/form-data" x-data="packageForm()">
            @csrf
            
            <div class="bg-white shadow-md rounded-lg p-6 space-y-6">
                <!-- Basic Information -->
                <div class="grid md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Package Name</label>
                        <input type="text" name="name" value="{{ old('name') }}" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        @error('name')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Emoji</label>
                        <input type="text" name="emoji" value="{{ old('emoji') }}" required placeholder="🎉"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        @error('emoji')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                    <textarea name="description" rows="3" required
                              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">{{ old('description') }}</textarea>
                    @error('description')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid md:grid-cols-3 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Type</label>
                        <select name="type" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">Select Type</option>
                            <option value="cooked" {{ old('type') === 'cooked' ? 'selected' : '' }}>Cooked</option>
                            <option value="frozen" {{ old('type') === 'frozen' ? 'selected' : '' }}>Frozen</option>
                        </select>
                        @error('type')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Package Key</label>
                        <input type="text" name="package_key" value="{{ old('package_key') }}" required placeholder="party"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        @error('package_key')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Sort Order</label>
                        <input type="number" name="sort_order" value="{{ old('sort_order', 0) }}" min="0"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        @error('sort_order')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Package Image Upload -->
                <div class="bg-gray-50 border border-gray-200 rounded-lg p-4 mb-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">📸 Package Image</h3>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Upload Package Image</label>
                            <input type="file" name="image" accept="image/*" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <p class="text-xs text-gray-500 mt-1">Recommended: 400x300px or larger. JPG, PNG, or WebP formats.</p>
                            @error('image')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <div id="image-preview" class="hidden">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Preview</label>
                            <div class="relative inline-block">
                                <img id="preview-img" src="" alt="Package Preview" class="w-32 h-24 object-cover rounded-lg border border-gray-300">
                                <button type="button" id="remove-image" class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full w-6 h-6 flex items-center justify-center text-xs hover:bg-red-600">
                                    ×
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Package Marketing Fields -->
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                    <h3 class="text-lg font-semibold text-blue-900 mb-4">📢 Marketing & Display Information</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Deal Title</label>
                            <input type="text" name="deal_title" value="{{ old('deal_title', 'Party Pack Deal') }}" placeholder="Party Pack Deal"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            @error('deal_title')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Feeds People</label>
                            <input type="text" name="feeds_people" value="{{ old('feeds_people', '8–10 people') }}" placeholder="8–10 people"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            @error('feeds_people')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Savings Description</label>
                            <input type="text" name="savings_description" value="{{ old('savings_description', 'Save Rs. 250+ vs buying individually') }}" placeholder="Save Rs. 250+ vs buying individually"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            @error('savings_description')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Original Price (for comparison)</label>
                            <input type="number" name="original_price" x-model="totalPrice" step="0.01" readonly
                                   value="{{ old('original_price') }}" placeholder="2567.00"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 bg-gray-50">
                            <p class="text-xs text-gray-500 mt-1">Auto-calculated from individual prices</p>
                            @error('original_price')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Delivery Note</label>
                            <input type="text" name="delivery_note" value="{{ old('delivery_note', 'Order before 2PM for same-day delivery') }}" placeholder="Order before 2PM for same-day delivery"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            @error('delivery_note')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Package Items -->
                <div>
                    <div class="flex justify-between items-center mb-4">
                        <div>
                            <h3 class="text-lg font-medium text-gray-900">Package Items</h3>
                            <p class="text-sm text-gray-600">Select items from our menu to include in this package</p>
                        </div>
                        <button type="button" @click="showProductSelector = true" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                            Add from Menu
                        </button>
                    </div>
                    
                    <!-- Selected Items -->
                    <div class="space-y-4" x-show="items.length > 0">
                        <template x-for="(item, index) in items" :key="index">
                            <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                                <div class="flex gap-3 items-center">
                                    <div class="flex-1">
                                        <div class="font-medium text-gray-900" x-text="item.name"></div>
                                        <div class="text-sm text-gray-600" x-text="'Category: ' + item.category"></div>
                                    </div>
                                    <div class="w-24">
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Quantity</label>
                                        <input type="number" x-model.number="item.quantity" :name="'items[' + index + '][quantity]'" min="1" required
                                               class="w-full px-2 py-1 border border-gray-300 rounded text-center">
                                    </div>
                                    <div class="w-32">
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Individual Price (Rs.)</label>
                                        <input type="number" x-model.number="item.price" :name="'items[' + index + '][price]'" step="0.01" required
                                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    </div>
                                    <div class="w-32">
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Bulk Price (Rs.)</label>
                                        <input type="number" x-model.number="item.bulk_price" :name="'items[' + index + '][bulk_price]'" step="0.01" required
                                               class="w-full px-3 py-2 border border-green-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500">
                                    </div>
                                    <button type="button" @click="removeItem(index)" 
                                            class="bg-red-600 text-white px-3 py-2 rounded-md hover:bg-red-700 flex items-center gap-1">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                        </svg>
                                        Remove
                                    </button>
                                </div>
                                <input type="hidden" :name="'items[' + index + '][name]'" x-model="item.name">
                                <input type="hidden" :name="'items[' + index + '][category]'" x-model="item.category">
                            </div>
                        </template>
                    </div>
                    
                    <!-- Empty state -->
                    <div x-show="items.length === 0" class="text-center py-8 bg-gray-50 rounded-lg border-2 border-dashed border-gray-300">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">No items added yet</h3>
                        <p class="mt-1 text-sm text-gray-500">Select items from our menu to create your package.</p>
                        <div class="mt-6">
                            <button type="button" @click="showProductSelector = true" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700">
                                Browse Menu
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Product Selector Modal -->
                <div x-show="showProductSelector" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-black bg-opacity-50 z-50" @click="showProductSelector = false">
                    <div class="flex items-center justify-center min-h-screen p-4" @click.stop>
                        <div class="bg-white rounded-lg shadow-xl max-w-4xl w-full max-h-[80vh] overflow-hidden">
                            <!-- Modal Header -->
                            <div class="flex items-center justify-between p-6 border-b border-gray-200">
                                <h3 class="text-lg font-medium text-gray-900">Select Items from Menu</h3>
                                <button @click="showProductSelector = false" class="text-gray-400 hover:text-gray-600">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </button>
                            </div>
                            
                            <!-- Category Tabs -->
                            <div class="border-b border-gray-200">
                                <nav class="-mb-px flex space-x-8 px-6" aria-label="Tabs">
                                    <template x-for="category in categories" :key="category">
                                        <button @click="selectedCategory = category" 
                                                :class="selectedCategory === category ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                                                class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                                            <span x-text="category"></span>
                                        </button>
                                    </template>
                                </nav>
                            </div>
                            
                            <!-- Subcategory Tabs (for Food and Drinks) -->
                            <div x-show="selectedCategory === 'Food' || selectedCategory === 'Drinks'" class="border-b border-gray-200 bg-gray-50">
                                <nav class="flex space-x-6 px-6" aria-label="Subcategory Tabs">
                                    <template x-for="subcategory in getSubcategories(selectedCategory)" :key="subcategory">
                                        <button @click="selectedSubcategory = subcategory" 
                                                :class="selectedSubcategory === subcategory ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                                                class="whitespace-nowrap py-3 px-1 border-b-2 font-medium text-sm">
                                            <span x-text="subcategory"></span>
                                        </button>
                                    </template>
                                </nav>
                            </div>
                            
                            <!-- Products Grid -->
                            <div class="p-6 overflow-y-auto max-h-96">
                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                    <template x-for="product in getProductsByCategory(selectedCategory)" :key="product.id">
                                        <div class="border border-gray-200 rounded-lg p-4 hover:bg-gray-50 cursor-pointer" 
                                             @click="addProductToPackage(product)">
                                            <div class="flex justify-between items-start">
                                                <div class="flex-1">
                                                    <h4 class="font-medium text-gray-900" x-text="product.name"></h4>
                                                    <p class="text-sm text-gray-600" x-text="'Rs. ' + product.price"></p>
                                                </div>
                                                <button type="button" class="bg-blue-600 text-white px-3 py-1 rounded text-sm hover:bg-blue-700">
                                                    Add
                                                </button>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Package Summary -->
                <div class="bg-blue-50 p-4 rounded-lg border border-blue-200">
                    <h4 class="text-lg font-medium text-blue-900 mb-3">Package Summary</h4>
                    <div class="grid md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-blue-700 mb-2">Total Items in Package</label>
                            <div class="text-2xl font-bold text-blue-900" x-text="items.length + ' items'"></div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-blue-700 mb-2">Total Price (Rs.) - Individual Price</label>
                            <input type="number" name="total_price" x-model="totalPrice" step="0.01" required
                                   value="{{ old('total_price', 0) }}"
                                   class="w-full px-3 py-2 border border-blue-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-lg font-semibold">
                            <p class="text-xs text-gray-500 mt-1">Regular price when buying items individually</p>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-green-700 mb-2">Bulk Price (Rs.) - Package Price</label>
                            <input type="number" name="bulk_price" x-model="totalBulkPrice" step="0.01" required readonly
                                   value="{{ old('bulk_price', 0) }}"
                                   class="w-full px-3 py-2 border border-green-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 text-lg font-semibold bg-green-50">
                            <p class="text-xs text-gray-500 mt-1">Auto-calculated from item bulk prices</p>
                        </div>
                    </div>
                    @error('total_price')
                        <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex justify-end space-x-4">
                    <a href="{{ route('admin.bulk-packages.index') }}" class="bg-gray-600 text-white px-6 py-2 rounded-lg hover:bg-gray-700 transition">
                        Cancel
                    </a>
                    <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition">
                        Create Package
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
function packageForm() {
    return {
        items: [],
        showProductSelector: false,
        selectedCategory: 'Food',
        selectedSubcategory: 'Buff',
        categories: ['Food', 'Drinks', 'Desserts', 'Sides'],
        products: @json($products ?? []),
        
        init() {
            // Update total price field initially
            this.$nextTick(() => {
                this.updateTotalPriceField();
            });
        },
        
        get totalPrice() {
            return this.items.reduce((sum, item) => sum + (parseFloat(item.price) || 0), 0);
        },
        
        get totalBulkPrice() {
            return this.items.reduce((sum, item) => sum + (parseFloat(item.bulk_price) || 0), 0);
        },
        
        updateTotalPriceField() {
            const totalPriceInput = document.querySelector('input[name="total_price"]');
            const bulkPriceInput = document.querySelector('input[name="bulk_price"]');
            const originalPriceInput = document.querySelector('input[name="original_price"]');
            
            if (totalPriceInput) {
                totalPriceInput.value = this.totalPrice;
            }
            
            if (bulkPriceInput) {
                bulkPriceInput.value = this.totalBulkPrice;
            }
            
            if (originalPriceInput) {
                originalPriceInput.value = this.totalPrice;
            }
        },
        
        getSubcategories(category) {
            if (category === 'Food') {
                return ['Buff', 'Chicken', 'Veg', 'Others'];
            } else if (category === 'Drinks') {
                return ['Hot', 'Cold'];
            }
            return [];
        },
        
        getProductsByCategory(category) {
            if (category === 'Food') {
                if (this.selectedSubcategory === 'Buff') {
                    return this.products.filter(p => p.category === 'buff');
                } else if (this.selectedSubcategory === 'Chicken') {
                    return this.products.filter(p => p.category === 'chicken');
                } else if (this.selectedSubcategory === 'Veg') {
                    return this.products.filter(p => p.category === 'veg');
                } else if (this.selectedSubcategory === 'Others') {
                    return this.products.filter(p => ['main', 'Momo'].includes(p.category));
                }
                return [];
            } else if (category === 'Drinks') {
                if (this.selectedSubcategory === 'Hot') {
                    return this.products.filter(p => p.category === 'hot');
                } else if (this.selectedSubcategory === 'Cold') {
                    return this.products.filter(p => ['cold', 'boba'].includes(p.category));
                }
                return [];
            } else if (category === 'Desserts') {
                return this.products.filter(p => p.category === 'desserts');
            } else if (category === 'Sides') {
                return this.products.filter(p => p.category === 'side');
            }
            return [];
        },
        
        addProductToPackage(product) {
            // Check if product already exists
            const existingItem = this.items.find(item => item.name === product.name);
            const productPrice = parseFloat(product.price);
            if (existingItem) {
                existingItem.quantity += 1;
                existingItem.price = (productPrice * existingItem.quantity).toFixed(2);
                existingItem.bulk_price = (productPrice * existingItem.quantity).toFixed(2);
            } else {
                this.items.push({
                    name: product.name,
                    category: product.category,
                    quantity: 1,
                    price: productPrice.toFixed(2),
                    bulk_price: productPrice.toFixed(2)
                });
            }
            
            this.$nextTick(() => {
                this.updateTotalPriceField();
            });
        },
        
        removeItem(index) {
            this.items.splice(index, 1);
            this.$nextTick(() => {
                this.updateTotalPriceField();
            });
        }
    }
}

// Image preview functionality
document.addEventListener('DOMContentLoaded', function() {
    const imageInput = document.querySelector('input[name="image"]');
    const imagePreview = document.getElementById('image-preview');
    const previewImg = document.getElementById('preview-img');
    const removeImageBtn = document.getElementById('remove-image');

    if (imageInput) {
        imageInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    previewImg.src = e.target.result;
                    imagePreview.classList.remove('hidden');
                };
                reader.readAsDataURL(file);
            }
        });
    }

    if (removeImageBtn) {
        removeImageBtn.addEventListener('click', function() {
            imageInput.value = '';
            imagePreview.classList.add('hidden');
            previewImg.src = '';
        });
    }
});
</script>
@endsection 