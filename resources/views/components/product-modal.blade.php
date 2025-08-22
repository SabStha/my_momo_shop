<!-- Enhanced Product Details Modal -->
<div id="productModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl p-6 max-w-lg w-full mx-4 transform transition-all duration-300 scale-95 opacity-0" id="modalContent">
        <div class="flex justify-between items-start mb-4">
            <h3 class="text-2xl font-bold text-gray-900" id="modalTitle"></h3>
            <button onclick="closeProductModal()" class="text-gray-400 hover:text-gray-600 transition-colors hover:scale-110 transform active:scale-95">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        <p class="text-gray-700 mb-6" id="modalDescription"></p>
        <div class="text-2xl font-bold text-red-600 mb-6" id="modalPrice"></div>
        <div class="flex space-x-3">
            <button onclick="addToCartFromModal()" class="flex-1 bg-red-600 text-white py-3 px-6 rounded-xl font-bold text-lg hover:bg-red-700 transition-all duration-300 transform hover:scale-105 hover:shadow-lg active:scale-95">
                🛒 Add to Cart
            </button>
            <button onclick="closeProductModal()" class="flex-1 bg-gray-200 text-gray-800 py-3 px-6 rounded-xl font-bold text-lg hover:bg-gray-300 transition-all duration-300 transform hover:scale-105 active:scale-95">
                Close
            </button>
        </div>
    </div>
</div> 