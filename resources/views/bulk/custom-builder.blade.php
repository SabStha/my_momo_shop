@extends('layouts.app')

@section('title', 'Customize Your Package')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-orange-50 via-white to-red-50">
    <div class="max-w-6xl mx-auto px-4 py-8">
        <!-- Header -->
        <div class="text-center mb-8">
            <h1 class="text-4xl font-bold text-gray-900 mb-4">
                ✨ Customize Your Package
            </h1>
            <p class="text-lg text-gray-600 max-w-2xl mx-auto">
                Make this package uniquely yours! Add, remove, or modify items to create your perfect order.
            </p>
  </div>

        <!-- Package Info Card -->
        <div class="bg-white rounded-2xl shadow-xl p-6 mb-8 border border-orange-200">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <div class="w-16 h-16 bg-gradient-to-br from-orange-400 to-red-500 rounded-xl flex items-center justify-center">
                        <span class="text-2xl text-white">
                            @if(request('package_type') === 'cooked')
                                🔥
                            @else
                                ❄️
                            @endif
                        </span>
    </div>
            <div>
                        <h2 class="text-2xl font-bold text-gray-900">{{ request('package_name', 'Custom Package') }}</h2>
                        <p class="text-gray-600">
                            @if(request('package_type') === 'cooked')
                                Hot & Ready Package
                            @else
                                Frozen Package
                            @endif
                        </p>
                        <p class="text-sm text-gray-500">Package Key: {{ request('package_key', 'N/A') }}</p>
            </div>
          </div>
                <div class="text-right">
                    <div class="text-3xl font-bold text-orange-600">Rs. {{ number_format(request('package_price', 0), 2) }}</div>
                    <div class="text-sm text-gray-500">Base Price</div>
          </div>
    </div>
  </div>

        <div x-data="packageCustomizer()" class="space-y-8">
            <!-- Current Package Items -->
            <div class="bg-white rounded-2xl shadow-lg p-6">
                <h3 class="text-xl font-semibold text-gray-900 mb-6 flex items-center">
                    <svg class="w-6 h-6 mr-3 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                    </svg>
                    Package Contents
                </h3>
                
                <div class="space-y-4">
                    <div x-show="packageItems.length === 0" class="text-center py-8 text-gray-500">
                        <svg class="w-16 h-16 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                        </svg>
                        <p class="text-lg">This package has no items</p>
                        <p class="text-sm">Add items from the menu below to customize your package</p>
  </div>

                    <template x-for="(item, index) in packageItems" :key="index">
                        <div class="flex items-center justify-between p-4 rounded-xl border border-gray-200" 
                             :class="item.bulkPrice !== undefined ? 'bg-green-50 border-green-200' : 'bg-gray-50'">
                            <div class="flex items-center space-x-4">
                                <!-- Product Image -->
                                <div class="w-12 h-12 bg-gray-200 rounded-lg overflow-hidden flex-shrink-0">
                                    <template x-if="item.image">
                                        <img :src="'/storage/' + item.image" 
                                             :alt="item.name"
                                             class="w-full h-full object-cover">
                                    </template>
                                    <template x-if="!item.image">
                                        <div class="w-full h-full bg-gradient-to-br from-gray-100 to-gray-200 flex items-center justify-center">
                                            <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                            </svg>
    </div>
                                    </template>
      </div>
                                <div>
                                    <h4 class="font-semibold text-gray-900" x-text="item.name"></h4>
                                    <p class="text-sm text-gray-600" x-text="item.category"></p>
                                    <template x-if="item.bulkPrice !== undefined">
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            ✨ Added
                                        </span>
                                    </template>
                                    <template x-if="item.bulkPrice === undefined">
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                            📦 Original
                                        </span>
                                    </template>
      </div>
    </div>
                            <div class="flex items-center space-x-4">
                                <div class="text-right">
                                    <!-- Show both original and bulk prices for all items -->
                                    <div class="space-y-1">
                                        <!-- Original Price -->
                                        <div class="text-sm text-gray-500 line-through" x-text="'Rs. ' + (item.price || 0).toFixed(2)"></div>
                                        
                                        <!-- Bulk Price -->
                                        <div class="font-semibold text-green-600" x-text="'Rs. ' + (item.bulkPrice || (item.price * (1 - bulkDiscountPercentage / 100))).toFixed(2)"></div>
                                        
                                        <!-- Discount Amount -->
                                        <div class="text-xs text-green-500 font-medium" x-text="'Save Rs. ' + ((item.price || 0) - (item.bulkPrice || (item.price * (1 - bulkDiscountPercentage / 100)))).toFixed(2)"></div>
            </div>
          </div>
                                <template x-if="item.bulkPrice !== undefined">
                                    <button @click="removeItem(index)" 
                                            class="p-2 text-red-500 hover:bg-red-50 rounded-lg transition-colors">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </template>
                                <template x-if="item.bulkPrice === undefined">
                                    <div class="w-9 h-9 flex items-center justify-center text-gray-400">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                        </svg>
                                    </div>
                                </template>
          </div>
        </div>
                    </template>
      </div>
  </div>

            <!-- Add Items from Menu -->
            <div class="bg-white rounded-2xl shadow-lg p-6">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-xl font-semibold text-gray-900 flex items-center">
                        <svg class="w-6 h-6 mr-3 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                        </svg>
                        Add Items from Menu
                    </h3>
                    <button @click="showProductSelector = true" 
                            class="bg-gradient-to-r from-green-500 to-blue-500 text-white px-6 py-3 rounded-xl hover:from-green-600 hover:to-blue-600 transition-all duration-200 flex items-center space-x-2 shadow-lg">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        <span>Browse Menu</span>
                    </button>
    </div>

                <!-- Product Selector Modal -->
                <div x-show="showProductSelector" 
                     x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="opacity-0"
                     x-transition:enter-end="opacity-100"
                     x-transition:leave="transition ease-in duration-200"
                     x-transition:leave-start="opacity-100"
                     x-transition:leave-end="opacity-0"
                     class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4"
                     style="display: none;">
                    <div class="bg-white rounded-2xl shadow-2xl max-w-4xl w-full max-h-[90vh] overflow-hidden">
                        <!-- Modal Header -->
                        <div class="bg-gradient-to-r from-orange-500 to-red-500 text-white p-6">
                            <div class="flex justify-between items-center">
                                <h3 class="text-2xl font-bold">Select Items to Add</h3>
                                <button @click="showProductSelector = false" 
                                        class="text-white hover:bg-white hover:bg-opacity-20 rounded-lg p-2 transition-colors">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </button>
                </div>
              </div>

                        <!-- Category Tabs -->
                        <div class="border-b border-gray-200">
                            <nav class="flex space-x-8 px-6" aria-label="Tabs">
                                <button @click="activeCategory = 'Food'" 
                                        :class="{'border-orange-500 text-orange-600': activeCategory === 'Food', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeCategory !== 'Food'}"
                                        class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                                    🍽️ Food
                                </button>
                                <button @click="activeCategory = 'Drinks'" 
                                        :class="{'border-orange-500 text-orange-600': activeCategory === 'Drinks', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeCategory !== 'Drinks'}"
                                        class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                                    🥤 Drinks
                                </button>
                                <button @click="activeCategory = 'Desserts'" 
                                        :class="{'border-orange-500 text-orange-600': activeCategory === 'Desserts', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeCategory !== 'Desserts'}"
                                        class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                                    🍰 Desserts
                                </button>
                                <button @click="activeCategory = 'Sides'" 
                                        :class="{'border-orange-500 text-orange-600': activeCategory === 'Sides', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeCategory !== 'Sides'}"
                                        class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                                    🍽️ Sides
                                </button>
                            </nav>
              </div>

                        <!-- Subcategory Tabs (for Food and Drinks) -->
                        <div x-show="activeCategory === 'Food' || activeCategory === 'Drinks'" class="border-b border-gray-200 bg-gray-50">
                            <nav class="flex space-x-6 px-6" aria-label="Subcategory Tabs">
                                <template x-for="subcategory in getSubcategories(activeCategory)" :key="subcategory">
                                    <button @click="selectedSubcategory = subcategory" 
                                            :class="selectedSubcategory === subcategory ? 'border-orange-500 text-orange-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                                            class="whitespace-nowrap py-3 px-1 border-b-2 font-medium text-sm">
                                        <span x-text="subcategory"></span>
                                    </button>
                                </template>
                            </nav>
            </div>

                        <!-- Products Grid -->
                        <div class="p-6 max-h-96 overflow-y-auto">
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                <template x-for="product in getProductsByCategory(activeCategory)" :key="product.id">
                                    <div class="border border-gray-200 rounded-lg overflow-hidden hover:shadow-lg cursor-pointer transition-all duration-200" 
                                         @click="addProductToPackage(product)">
                                        <!-- Product Image -->
                                        <div class="h-32 bg-gray-100 flex items-center justify-center overflow-hidden">
                                            <template x-if="product.image">
                                                <img :src="'/storage/' + product.image" 
                                                     :alt="product.name"
                                                     class="w-full h-full object-cover hover:scale-105 transition-transform duration-200">
                                            </template>
                                            <template x-if="!product.image">
                                                <div class="w-full h-full bg-gradient-to-br from-gray-100 to-gray-200 flex items-center justify-center">
                                                    <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                                    </svg>
          </div>
                                            </template>
  </div>

                                        <!-- Product Info -->
                                        <div class="p-3">
                                            <div class="flex justify-between items-start">
                                                <div class="flex-1">
                                                    <h4 class="font-medium text-gray-900 text-sm" x-text="product.name"></h4>
                                                    <p class="text-sm font-semibold text-orange-600 mt-1" x-text="'Rs. ' + product.price"></p>
    </div>
                                                <button type="button" class="bg-orange-600 text-white px-3 py-1 rounded text-xs hover:bg-orange-700 transition-colors">
                                                    Add
                                                </button>
            </div>
            </div>
          </div>
                                </template>
          </div>
        </div>
        </div>
    </div>
  </div>

            <!-- Customized Package Summary -->
            <div class="bg-white rounded-2xl shadow-lg p-6">
                <h3 class="text-xl font-semibold text-gray-900 mb-6 flex items-center">
                    <svg class="w-6 h-6 mr-3 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                    </svg>
                    Your Customized Package
                </h3>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    <!-- Package Details -->
  <div>
                        <h4 class="font-semibold text-gray-900 mb-4">Package Details</h4>
                        <div class="space-y-3">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Base Package:</span>
                                <span class="font-semibold">{{ request('package_name', 'Custom Package') }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Type:</span>
                                <span class="font-semibold">
                                    @if(request('package_type') === 'cooked')
                                        Hot & Ready
                                    @else
                                        Frozen
                                    @endif
                                </span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Total Items:</span>
                                <span class="font-semibold" x-text="packageItems.length">0</span>
    </div>
    </div>
  </div>

                    <!-- Pricing -->
                    <div>
                        <h4 class="font-semibold text-gray-900 mb-4">Pricing Breakdown</h4>
                        <div class="space-y-3">
                            <!-- Original Total -->
                            <div class="flex justify-between">
                                <span class="text-gray-600">Original Total:</span>
                                <span class="font-semibold text-gray-500 line-through" x-text="'Rs. ' + originalTotal.toFixed(2)">Rs. 0.00</span>
        </div>
                            
                            <!-- Base Package Price -->
                            <div class="flex justify-between">
                                <span class="text-gray-600">Base Package Price:</span>
                                <span class="font-semibold">Rs. {{ number_format(request('package_price', 0), 2) }}</span>
        </div>
                            
                            <!-- Additional Items -->
                            <div class="flex justify-between">
                                <span class="text-gray-600">Additional Items:</span>
                                <span class="font-semibold" x-text="'Rs. ' + additionalItemsTotal.toFixed(2)">Rs. 0.00</span>
      </div>
      
                            <!-- Total Savings -->
                            <div class="flex justify-between">
                                <span class="text-green-600 font-medium">Total Savings:</span>
                                <span class="font-semibold text-green-600" x-text="'Rs. ' + totalSavings.toFixed(2)">Rs. 0.00</span>
        </div>
                            
                            <div class="border-t border-gray-200 pt-3">
                                <div class="flex justify-between text-lg">
                                    <span class="font-semibold text-gray-900">Final Price:</span>
                                    <span class="font-bold text-orange-600" x-text="'Rs. ' + totalPrice.toFixed(2)">Rs. {{ number_format(request('package_price', 0), 2) }}</span>
                                </div>
        </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Action Buttons -->
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <button @click="clearCustomizations()" 
                        class="px-8 py-4 bg-gray-200 text-gray-700 rounded-xl hover:bg-gray-300 transition-colors font-semibold flex items-center justify-center space-x-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                    <span>Reset Package</span>
    </button>
                <button @click="addToCart()" 
                        class="px-8 py-4 bg-gradient-to-r from-orange-500 to-red-500 text-white rounded-xl hover:from-orange-600 hover:to-red-600 transition-all duration-200 font-semibold flex items-center justify-center space-x-2 shadow-lg">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 13m0 0l-2.5 5M7 13l2.5 5m6-5v6a2 2 0 01-2 2H9a2 2 0 01-2-2v-6m8 0V9a2 2 0 00-2-2H9a2 2 0 00-2 2v4.01"/>
                    </svg>
                    <span>Add to Cart</span>
    </button>
  </div>
        </div>
    </div>
</div>

<script>
function packageCustomizer() {
    return {
        showProductSelector: false,
        activeCategory: 'Food',
        selectedSubcategory: 'Buff',
        packageItems: @json($package->items ?? []),
        products: @json($products ?? []),
        bulkDiscountPercentage: {{ $bulkDiscountPercentage ?? 15 }},
        
        get additionalItemsTotal() {
            return this.packageItems.reduce((total, item) => {
                // Only count items that have bulkPrice (customized items)
                if (item.bulkPrice !== undefined) {
                    return total + (item.bulkPrice || 0);
                }
            return total;
            }, 0);
        },
        
        get totalPrice() {
            return {{ request('package_price', 0) }} + this.additionalItemsTotal;
        },
        
        get originalTotal() {
            return this.packageItems.reduce((total, item) => {
                return total + (item.price || 0);
            }, 0);
        },
        
        get totalSavings() {
            return this.packageItems.reduce((total, item) => {
                const originalPrice = item.price || 0;
                const bulkPrice = item.bulkPrice || (originalPrice * (1 - this.bulkDiscountPercentage / 100));
                return total + (originalPrice - bulkPrice);
            }, 0);
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
            const regularPrice = parseFloat(product.price);
            const bulkPrice = regularPrice * (1 - this.bulkDiscountPercentage / 100);
            
            this.packageItems.push({
                name: product.name,
                category: product.category,
                price: regularPrice,
                bulkPrice: bulkPrice,
                image: product.image
            });
            
            this.showProductSelector = false;
        },
        
        removeItem(index) {
            this.packageItems.splice(index, 1);
        },
        
        clearCustomizations() {
            this.packageItems = [];
        },
        
        addToCart() {
            if (this.packageItems.length === 0) {
                this.showError('Please add at least one item to customize your package');
                return;
            }
            
            const orderData = {
                packageId: {{ request('package_id', 0) }},
                packageName: '{{ request('package_name', 'Custom Package') }}',
                packageType: '{{ request('package_type', 'cooked') }}',
                basePrice: {{ request('package_price', 0) }},
                customItems: this.packageItems,
                totalPrice: this.totalPrice,
                bulkDiscountPercentage: this.bulkDiscountPercentage
            };
            
            console.log('Adding customized package to cart:', orderData);
            
            // Show success message
            this.showSuccessPopup();
        },
        
        showSuccessPopup() {
            // Create a simple success popup
            const popup = document.createElement('div');
            popup.className = 'fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50';
            popup.innerHTML = `
                <div class="bg-white rounded-2xl shadow-2xl p-8 max-w-md mx-4 text-center">
                    <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-2">Package Added to Cart!</h3>
                    <p class="text-gray-600 mb-6">Your customized package has been successfully added to your cart.</p>
                    <div class="flex space-x-3">
                        <button onclick="this.closest('.fixed').remove()" 
                                class="flex-1 bg-gray-200 text-gray-700 py-2 px-4 rounded-lg font-medium hover:bg-gray-300 transition-colors">
                            Continue Shopping
                        </button>
                        <button onclick="window.location.href='{{ route('cart') }}'" 
                                class="flex-1 bg-blue-600 text-white py-2 px-4 rounded-lg font-medium hover:bg-blue-700 transition-colors">
                            View Cart
                        </button>
                    </div>
                </div>
            `;
            document.body.appendChild(popup);
        },
        
        showError(message) {
            alert(message);
        }
    }
}
</script> 
@endsection
