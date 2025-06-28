<!-- Shop Info Section -->
<section class="py-4 sm:py-6 px-1 sm:px-2">
    <div class="max-w-6xl mx-auto">
        <div class="bg-white/70 backdrop-blur-md rounded-2xl p-2 sm:p-4 md:p-6 shadow-xl">
            <!-- Section Header -->
            <div class="text-center mb-2 sm:mb-4">
                <h2 class="text-lg sm:text-xl lg:text-2xl font-bold text-[#6E0D25] mb-0.5">📍 Visit Us</h2>
                <p class="text-xs sm:text-base text-gray-600">Find us and get in touch</p>
            </div>

            <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-xl p-2 sm:p-6 flex flex-row gap-2 sm:gap-6 items-stretch mt-2">
                <!-- Business Hours -->
                <div class="flex-1 flex flex-col justify-between min-w-0">
                    <div class="flex items-center gap-2 mb-2">
                        <span class="text-xl sm:text-3xl">🕒</span>
                        <h3 class="text-base sm:text-lg font-semibold text-gray-800">Business Hours</h3>
                    </div>
                    <div class="space-y-1 mb-2">
                        <div class="flex justify-between">
                            <span class="text-xs sm:text-base text-gray-700">Open 7 days a week</span>
                            <span class="font-semibold text-xs sm:text-base text-gray-800">10:00 AM - 9:00 PM</span>
                        </div>
                    </div>
                    <div class="mt-2 p-1 bg-green-200 rounded-lg text-center">
                        <span class="text-green-800 font-semibold text-xs sm:text-sm">🟢 Currently Open</span>
                    </div>
                </div>
                <!-- Divider -->
                <div class="w-px bg-gray-200 mx-2"></div>
                <!-- Map -->
                <div class="flex-1 flex flex-col justify-between items-center text-center min-w-0">
                    <div class="flex flex-col items-center">
                        <div class="text-2xl sm:text-4xl mb-1 sm:mb-2">🗺️</div>
                        <h3 class="text-base sm:text-lg font-semibold text-gray-800 mb-1 sm:mb-2">Find Us on the Map</h3>
                    </div>
                <button onclick="openMap()" 
                            class="bg-[#6E0D25] text-white px-3 sm:px-6 py-1.5 sm:py-2 rounded-lg hover:bg-[#8B0D2F] transition-colors duration-300 text-xs sm:text-base">
                    📍 Get Directions
                </button>
                </div>
        </div> 

            <!-- Combined Social & Contact Card -->
            <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-xl p-2 sm:p-6 flex flex-col sm:flex-row gap-2 sm:gap-6 items-stretch mt-4 sm:mt-6">
                <!-- Follow Us -->
                <div class="flex-1 flex flex-col justify-between">
                    <h3 class="text-base sm:text-lg font-semibold text-gray-800 mb-1 sm:mb-2">Follow Us</h3>
                    <p class="text-xs sm:text-sm text-gray-600 mb-2 sm:mb-4">Stay updated with our latest offers and behind-the-scenes content</p>
                    <div class="flex gap-2 sm:gap-4 mb-2 sm:mb-0">
                        <a href="#" class="bg-blue-600 text-white p-2 sm:p-3 rounded-full hover:bg-blue-700 transition-colors duration-300">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M24 4.557c-.883.392-1.832.656-2.828.775 1.017-.609 1.798-1.574 2.165-2.724-.951.564-2.005.974-3.127 1.195-.897-.957-2.178-1.555-3.594-1.555-3.179 0-5.515 2.966-4.797 6.045-4.091-.205-7.719-2.165-10.148-5.144-1.29 2.213-.669 5.108 1.523 6.574-.806-.026-1.566-.247-2.229-.616-.054 2.281 1.581 4.415 3.949 4.89-.693.188-1.452.232-2.224.084.626 1.956 2.444 3.379 4.6 3.419-2.07 1.623-4.678 2.348-7.29 2.04 2.179 1.397 4.768 2.212 7.548 2.212 9.142 0 14.307-7.721 13.995-14.646.962-.695 1.797-1.562 2.457-2.549z"/>
                            </svg>
                        </a>
                        <a href="#" class="bg-pink-600 text-white p-2 sm:p-3 rounded-full hover:bg-pink-700 transition-colors duration-300">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12.017 0C5.396 0 .029 5.367.029 11.987c0 5.079 3.158 9.417 7.618 11.174-.105-.949-.199-2.403.041-3.439.219-.937 1.406-5.957 1.406-5.957s-.359-.72-.359-1.781c0-1.663.967-2.911 2.168-2.911 1.024 0 1.518.769 1.518 1.688 0 1.029-.653 2.567-.992 3.992-.285 1.193.6 2.165 1.775 2.165 2.128 0 3.768-2.245 3.768-5.487 0-2.861-2.063-4.869-5.008-4.869-3.41 0-5.409 2.562-5.409 5.199 0 1.033.394 2.143.889 2.741.099.12.112.225.085.345-.09.375-.293 1.199-.334 1.363-.053.225-.172.271-.402.165-1.495-.69-2.433-2.878-2.878-4.646 0-3.776 2.748-7.252 7.92-7.252 4.158 0 7.392 2.967 7.392 6.923 0 4.135-2.607 7.462-6.233 7.462-1.214 0-2.357-.629-2.746-1.378l-.748 2.853c-.271 1.043-1.002 2.35-1.492 3.146C9.57 23.812 10.763 24.009 12.017 24.009c6.624 0 11.99-5.367 11.99-11.988C24.007 5.367 18.641.001 12.017.001z"/>
                            </svg>
                        </a>
                        <a href="#" class="bg-red-600 text-white p-2 sm:p-3 rounded-full hover:bg-red-700 transition-colors duration-300">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z"/>
                            </svg>
                        </a>
                        <a href="#" class="bg-blue-800 text-white p-2 sm:p-3 rounded-full hover:bg-blue-900 transition-colors duration-300">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                            </svg>
                        </a>
                    </div>
                </div>
                <!-- Divider for desktop -->
                <div class="hidden sm:block w-px bg-gray-200 mx-2"></div>
                <!-- Contact Info -->
                <div class="flex-1 flex flex-col justify-between">
                    <div class="flex items-center gap-2 mb-2">
                        <span class="text-xl sm:text-3xl">📞</span>
                        <h3 class="text-base sm:text-lg font-semibold text-gray-800">Contact Information</h3>
                    </div>
                    <div class="space-y-2 mb-2">
                        <div class="space-y-1 mb-2">
                            <div class="flex items-center gap-2">
                                <svg class="w-3 h-3 sm:w-4 sm:h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                </svg>
                                <span class="text-xs sm:text-base text-gray-800">+1 (555) 123-4567</span>
                                <svg class="w-3 h-3 sm:w-4 sm:h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                </svg>
                                <span class="text-xs sm:text-base text-gray-800">info@amakoshop.com</span>
                            </div>
                        </div>
                    </div>
                    <div class="mb-2">
                        <p class="text-xs sm:text-sm text-gray-600">Need help? We're here to assist you!</p>
                    </div>
                    <div class="flex gap-2">
                        <button onclick="callUs()" 
                                class="bg-green-600 text-white px-3 sm:px-4 py-1.5 sm:py-2 rounded-lg hover:bg-green-700 transition-colors duration-300 text-xs sm:text-sm flex items-center gap-1">
                            <svg class="w-3 h-3 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                            </svg>
                            Call Now
                        </button>
                        <button onclick="emailUs()" 
                                class="bg-blue-600 text-white px-3 sm:px-4 py-1.5 sm:py-2 rounded-lg hover:bg-blue-700 transition-colors duration-300 text-xs sm:text-sm flex items-center gap-1">
                            <svg class="w-3 h-3 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            </svg>
                            Email
                        </button>
                        <button onclick="chatWithUs()" 
                                class="bg-purple-600 text-white px-3 sm:px-4 py-1.5 sm:py-2 rounded-lg hover:bg-purple-700 transition-colors duration-300 text-xs sm:text-sm flex items-center gap-1">
                            <svg class="w-3 h-3 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                            </svg>
                            Chat
                        </button>
                    </div>
                </div>
            </div>

            
        </div>
    </div>
</section> <?php /**PATH C:\Users\sabst\momo_shop\resources\views/home/sections/shop-info.blade.php ENDPATH**/ ?>