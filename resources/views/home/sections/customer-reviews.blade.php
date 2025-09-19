<!-- Customer Reviews Section -->
<section class="py-4 sm:py-6 px-0 sm:px-2 bg-gradient-to-b from-[#FFF8E6] via-[#FFF8E6] to-white">
    <div class="max-w-6xl mx-auto">
        <div class="bg-white/95 backdrop-blur-md rounded-2xl p-1 sm:p-2 md:p-4 shadow-xl border border-[#e9dfca]">
            @if($errors->any())
                <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded-lg">
                    <div class="flex items-center mb-2">
                        <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                        </svg>
                        Please fix the following errors:
                    </div>
                    <ul class="list-disc list-inside text-sm">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Section Header -->
            <div class="text-center mb-0.5 sm:mb-1">
                <h2 class="text-lg sm:text-xl lg:text-2xl font-bold text-[#E36414] mb-0">💬 Customer Reviews</h2>
                <p class="text-[10px] sm:text-xs text-gray-600">See what our happy customers are saying about us</p>
            </div>

            <!-- Overall Rating -->
            <div class="text-center mb-0">
                <div class="text-xl sm:text-2xl mb-0">⭐</div>
                @if(isset($statistics['customer_rating']) && $statistics['customer_rating'] !== null)
                    <div class="text-base sm:text-lg font-bold text-[#E36414] mb-0" data-stat="customer_rating">{{ $statistics['customer_rating'] }}/5</div>
                    <div class="text-[10px] sm:text-xs text-gray-600 mb-0">Based on {{ $statistics['happy_customers'] ?? '0' }} reviews</div>
                    <div class="flex justify-center gap-0.5 sm:gap-1 mb-0">
                        @php
                            $rating = $statistics['customer_rating'];
                            $fullStars = floor($rating);
                            $hasHalfStar = ($rating - $fullStars) >= 0.5;
                        @endphp
                        @for($i = 1; $i <= 5; $i++)
                            @if($i <= $fullStars)
                                <span class="text-yellow-400 text-xs sm:text-base">★</span>
                            @elseif($i == $fullStars + 1 && $hasHalfStar)
                                <span class="text-yellow-400 text-xs sm:text-base">☆</span>
                            @else
                                <span class="text-gray-300 text-xs sm:text-base">☆</span>
                            @endif
                        @endfor
                    </div>
                @else
                    <div class="text-base sm:text-lg font-bold text-[#E36414] mb-0">No ratings yet</div>
                    <div class="text-[10px] sm:text-xs text-gray-600 mb-0">Be the first to rate us!</div>
                    <div class="flex justify-center gap-0.5 sm:gap-1 mb-0">
                        <span class="text-gray-300 text-xs sm:text-base">☆☆☆☆☆</span>
                    </div>
                @endif
            </div>

            <!-- Reviews Slider (Mobile) / Grid (Desktop) -->
            @php
                // Only use real testimonials from database, no hardcoded fallbacks
                $reviewsData = $testimonials->isNotEmpty() ? $testimonials->toArray() : [];
            @endphp
            
            @if(count($reviewsData) > 0)
                <!-- Review Cards Grid -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 sm:gap-6">
                    @foreach($reviewsData as $review)
                        <div class="bg-white rounded-xl p-3 sm:p-6 shadow-lg">
                            <div class="flex items-center mb-2 sm:mb-4">
                            <div class="w-9 h-9 sm:w-12 sm:h-12 bg-gradient-to-br {{ $review['color'] }} rounded-full flex items-center justify-center text-white font-bold text-sm sm:text-base mr-2 sm:mr-3">
                                {{ $review['avatar'] }}
                            </div>
                                <div>
                                <div class="font-semibold text-sm sm:text-base text-gray-800">{{ $review['name'] }}</div>
                                <div class="text-yellow-400 text-xs sm:text-sm">
                                    @for($i = 1; $i <= 5; $i++)
                                        @if($i <= $review['stars'])
                                            ⭐
                                        @else
                                            ☆
                                        @endif
                                    @endfor
                                </div>
                            </div>
                        </div>
                        <p class="text-gray-700 text-sm sm:text-base mb-2 sm:mb-4">"{{ $review['comment'] }}"</p>
                        <div class="text-xs sm:text-sm text-gray-500">Ordered: {{ $review['order'] }}</div>
                    </div>
                    @endforeach
                </div>
            @else
                <!-- No Reviews Yet - Call to Action -->
                <div class="text-center py-8 sm:py-12">
                        <div class="bg-gradient-to-r from-[#FFF8E6] to-white rounded-2xl p-6 sm:p-8 border border-[#e9dfca]">
                            <div class="w-16 h-16 bg-[#E36414] rounded-full flex items-center justify-center mx-auto mb-4">
                                <svg class="w-8 h-8 text-white" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                </svg>
                            </div>
                            <h3 class="text-lg sm:text-xl font-bold text-[#E36414] mb-2">Be the First to Review!</h3>
                            <p class="text-gray-600 text-sm sm:text-base mb-4">Share your momo experience with others and help them discover our delicious offerings.</p>
                            @auth
                                <button type="button" @click="showReviewModal = true" class="inline-flex items-center gap-2 bg-red-500 text-white px-4 sm:px-6 py-2 sm:py-3 rounded-full font-semibold hover:bg-red-600 transition-colors duration-300 text-sm sm:text-base">
                                    ✍️ Write the First Review
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                    </svg>
                                </button>
                            @else
                                <a href="{{ route('login') }}" class="inline-flex items-center gap-2 bg-red-500 text-white px-4 sm:px-6 py-2 sm:py-3 rounded-full font-semibold hover:bg-red-600 transition-colors duration-300 text-sm sm:text-base">
                                    ✍️ Write the First Review
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                    </svg>
                                </a>
                            @endauth
                    </div>
                </div>
            @endif

            <!-- Review Stats -->
            <div class="mt-0 mb-1 sm:mt-0 sm:mb-2 overflow-x-auto">
                <div class="grid grid-cols-3 gap-1 sm:gap-2 text-center">
                    <div class="bg-white border border-[#e9dfca] rounded-lg p-1.5 sm:p-3 shadow-sm">
                        <div class="text-xs sm:text-lg font-bold text-[#E36414]">{{ $statistics['average_delivery_time'] ?? '25' }}min</div>
                        <div class="text-[9px] sm:text-xs text-gray-600">Avg Delivery Time</div>
                    </div>
                    <div class="bg-white border border-[#e9dfca] rounded-lg p-1.5 sm:p-3 shadow-sm">
                        <div class="text-xs sm:text-lg font-bold text-[#E36414]" data-stat="happy_customers">{{ $statistics['happy_customers'] ?? '500+' }}</div>
                        <div class="text-[9px] sm:text-xs text-gray-600">Happy Reviews</div>
                    </div>
                    <div class="bg-white border border-[#e9dfca] rounded-lg p-1.5 sm:p-3 shadow-sm">
                        <div class="text-xs sm:text-lg font-bold text-[#E36414]" data-stat="customer_rating">
                            @if(isset($statistics['customer_rating']) && $statistics['customer_rating'] !== null)
                                {{ $statistics['customer_rating'] }}
                            @else
                                N/A
                            @endif
                        </div>
                        <div class="text-[9px] sm:text-xs text-gray-600">Average Rating</div>
                    </div>
                </div>
            </div>

            <!-- Write Review CTA and Modal -->
            @auth
            <div id="review-modal-container" x-data="{ showReviewModal: false, selectedRating: 0, hoverRating: 0 }" x-cloak @closeReviewModal.window="showReviewModal = false">
                <div class="text-center mt-1 sm:mt-2">
                    <button type="button" @click="showReviewModal = true" class="inline-flex items-center gap-1 bg-red-500 text-white px-2 sm:px-3 py-1 sm:py-1.5 rounded-full font-semibold hover:bg-red-600 transition-colors duration-300 text-[10px] sm:text-xs">
                        ✍️ Write a Review
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </button>
                </div>
                <!-- Modal Overlay -->
                <div x-show="showReviewModal" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm z-40" @click="showReviewModal = false"></div>
                <!-- Modal -->
                <div x-show="showReviewModal" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95" class="fixed inset-0 flex items-center justify-center z-50 p-4">
                    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md relative overflow-hidden">
                        <!-- Header -->
                        <div class="bg-gradient-to-r from-[#6E0D25] to-[#8B0D2F] p-6 text-white">
                            <div class="flex justify-between items-center">
                                <h3 class="text-xl font-bold">Share Your Experience</h3>
                                <button @click="showReviewModal = false" class="text-white hover:text-gray-200 transition-colors">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </button>
                            </div>
                            <p class="text-sm opacity-90 mt-1">We'd love to hear about your momo experience!</p>
                        </div>
                        
                        <!-- Form Content -->
                        <div class="p-6">
                            <form id="review-form" class="space-y-6">
                                @csrf
                                <input type="hidden" name="product_id" value="1">
                                
                                <!-- Rating Section -->
                                <div>
                                    <label class="block text-lg font-semibold text-gray-800 mb-4 text-center">How would you rate your experience?</label>
                                    <div class="flex justify-center space-x-2 mb-2">
                                        @for($i = 1; $i <= 5; $i++)
                                            <button type="button" @click="selectedRating = {{ $i }}" @mouseenter="hoverRating = {{ $i }}" @mouseleave="hoverRating = 0" class="transform hover:scale-110 transition-all duration-200 focus:outline-none" :class="(selectedRating >= {{ $i }} || hoverRating >= {{ $i }}) ? 'text-yellow-400' : 'text-gray-300'">
                                                <svg class="w-12 h-12" fill="currentColor" viewBox="0 0 20 20">
                                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                                </svg>
                                            </button>
                                        @endfor
                                    </div>
                                    <div class="text-center">
                                        <span class="text-sm text-gray-600" x-text="selectedRating > 0 ? selectedRating + ' Star' + (selectedRating > 1 ? 's' : '') : 'Click to rate'"></span>
                                    </div>
                                    <input type="hidden" name="rating" x-model="selectedRating" required>
                                </div>
                                
                                <!-- Review Section -->
                                <div>
                                    <label for="review" class="block text-lg font-semibold text-gray-800 mb-3">Tell us more about your experience (optional)</label>
                                    <textarea name="review" id="review" rows="4" placeholder="Share your thoughts about our momos, service, or anything else you'd like us to know... (optional)" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-[#6E0D25] focus:ring-2 focus:ring-[#6E0D25]/20 transition-all duration-200 resize-none"></textarea>
                                </div>
                                
                                <!-- Submit Button -->
                                <div class="pt-4">
                                    <button type="submit" id="submit-review-btn" class="w-full bg-gradient-to-r from-[#6E0D25] to-[#8B0D2F] text-white py-3 px-6 rounded-xl font-semibold text-lg hover:from-[#8B0D2F] hover:to-[#6E0D25] transform hover:scale-105 transition-all duration-200 shadow-lg hover:shadow-xl">
                                        <span id="submit-text">Submit Review</span>
                                        <span id="loading-text" class="hidden">Submitting...</span>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            @else
            <div class="text-center mt-1 sm:mt-2">
                <a href="{{ route('login') }}" class="inline-flex items-center gap-1 bg-red-500 text-white px-2 sm:px-3 py-1 sm:py-1.5 rounded-full font-semibold hover:bg-red-600 transition-colors duration-300 text-[10px] sm:text-xs">
                    ✍️ Write a Review
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </a>
            </div>
            @endauth

            <!-- Success Popup Modal -->
            <div id="success-popup" class="fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
                <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md relative overflow-hidden transform transition-all duration-300 scale-95 opacity-0" id="success-popup-content">
                    <!-- Header -->
                    <div class="bg-gradient-to-r from-green-500 to-green-600 p-6 text-white">
                        <div class="flex justify-between items-center">
                            <h3 class="text-xl font-bold">🎉 Thank You!</h3>
                            <button onclick="closeSuccessPopup()" class="text-white hover:text-gray-200 transition-colors">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Content -->
                    <div class="p-6">
                        <div class="mb-6 text-center">
                            <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                <svg class="w-8 h-8 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <h4 class="text-lg font-semibold text-gray-800 mb-2">Review Submitted!</h4>
                            <p class="text-gray-600 text-sm">Thank you for sharing your experience with us. Your feedback helps us improve our service and helps other customers make informed decisions.</p>
                        </div>
                        
                        <!-- Gift Information -->
                        <div id="gift-info" class="hidden mb-6 p-4 bg-gradient-to-r from-yellow-50 to-orange-50 border border-yellow-200 rounded-xl">
                            <div class="flex items-start space-x-3">
                                <div class="flex-shrink-0">
                                    <div class="w-10 h-10 bg-yellow-100 rounded-full flex items-center justify-center">
                                        <svg class="w-5 h-5 text-yellow-600" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                        </svg>
                                    </div>
                                </div>
                                <div class="flex-1">
                                    <h5 id="gift-title" class="font-semibold text-gray-800 mb-1">AI-Generated Gift</h5>
                                    <p id="gift-description" class="text-sm text-gray-600 mb-2">Personalized gift based on your review and preferences</p>
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center space-x-2">
                                            <span id="gift-discount" class="text-lg font-bold text-green-600">10%</span>
                                            <span class="text-sm text-gray-500">OFF</span>
                                        </div>
                                        <div class="text-right">
                                            <div id="gift-code" class="font-mono text-sm bg-white px-2 py-1 rounded border text-gray-700">GIFT123</div>
                                            <div class="text-xs text-gray-500 mt-1">Valid until <span id="gift-valid-until">30 days</span></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- No Gift Message -->
                        <div id="no-gift-info" class="hidden mb-6 p-4 bg-gradient-to-r from-blue-50 to-indigo-50 border border-blue-200 rounded-xl">
                            <div class="flex items-start space-x-3">
                                <div class="flex-shrink-0">
                                    <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                                        <svg class="w-5 h-5 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                        </svg>
                                    </div>
                                </div>
                                <div class="flex-1">
                                    <h5 class="font-semibold text-gray-800 mb-1">Thank You for Your Feedback!</h5>
                                    <p class="text-sm text-gray-600 mb-2">We appreciate you taking the time to share your experience with us. Your feedback helps us improve our service.</p>
                                    <div class="bg-white border border-blue-200 rounded-lg p-3">
                                        <p class="text-xs text-blue-700">
                                            <strong>💡 Tip:</strong> Leave a 4-5 star review with detailed feedback to receive special gifts and discounts!
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Gift Button -->
                        <div id="gift-button-section" class="space-y-3">
                            <button onclick="receiveGift()" class="w-full bg-gradient-to-r from-yellow-400 to-yellow-500 text-white py-3 px-6 rounded-xl font-semibold text-lg hover:from-yellow-500 hover:to-yellow-600 transform hover:scale-105 transition-all duration-200 shadow-lg hover:shadow-xl">
                                🎁 Receive Your Gift
                            </button>
                            <button onclick="closeSuccessPopup()" class="w-full bg-gray-100 text-gray-700 py-3 px-6 rounded-xl font-semibold text-lg hover:bg-gray-200 transition-all duration-200">
                                Close
                            </button>
                        </div>
                        
                        <!-- No Gift Button -->
                        <div id="no-gift-button-section" class="space-y-3 hidden">
                            <button onclick="closeSuccessPopup()" class="w-full bg-gradient-to-r from-blue-500 to-blue-600 text-white py-3 px-6 rounded-xl font-semibold text-lg hover:from-blue-600 hover:to-blue-700 transform hover:scale-105 transition-all duration-200 shadow-lg hover:shadow-xl">
                                Got it, thanks!
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Gift Code Popup Modal -->
            <div id="gift-code-popup" class="fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
                <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md relative overflow-hidden transform transition-all duration-300 scale-95 opacity-0" id="gift-code-popup-content">
                    <!-- Header -->
                    <div class="bg-gradient-to-r from-yellow-400 to-yellow-500 p-6 text-white">
                        <div class="flex justify-between items-center">
                            <h3 class="text-xl font-bold">🎁 Your Gift Code</h3>
                            <button onclick="closeGiftCodePopup()" class="text-white hover:text-gray-200 transition-colors">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Content -->
                    <div class="p-6">
                        <div class="text-center mb-6">
                            <div class="w-16 h-16 bg-yellow-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                <svg class="w-8 h-8 text-yellow-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                </svg>
                            </div>
                            <h4 class="text-lg font-semibold text-gray-800 mb-2">Your Gift is Ready!</h4>
                            <p class="text-gray-600 text-sm">Copy this code and use it during checkout to get your discount.</p>
                        </div>
                        
                        <!-- Gift Code Display -->
                        <div class="bg-gradient-to-r from-yellow-50 to-orange-50 border-2 border-yellow-200 rounded-xl p-4 mb-6">
                            <div class="text-center">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Your Gift Code</label>
                                <div class="flex items-center justify-center space-x-2">
                                    <div id="gift-code-display" class="font-mono text-2xl font-bold text-gray-800 bg-white px-4 py-3 rounded-lg border-2 border-yellow-300 shadow-sm select-all">
                                        GIFTPBLATM
                                    </div>
                                    <button onclick="copyGiftCode()" id="copy-btn" class="bg-yellow-500 hover:bg-yellow-600 text-white p-3 rounded-lg transition-colors duration-200 flex-shrink-0">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                        </svg>
                                    </button>
                                </div>
                                <div id="copy-status" class="text-sm text-green-600 mt-2 hidden">
                                    ✅ Code copied to clipboard!
                                </div>
                            </div>
                        </div>
                        
                        <!-- Instructions -->
                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                            <h5 class="font-semibold text-blue-800 mb-2">How to use your gift:</h5>
                            <ol class="text-sm text-blue-700 space-y-1">
                                <li>1. Add items to your cart</li>
                                <li>2. Go to checkout</li>
                                <li>3. Enter the gift code above</li>
                                <li>4. Enjoy your discount!</li>
                            </ol>
                        </div>
                        
                        <!-- Buttons -->
                        <div class="space-y-3">
                            <button onclick="closeGiftCodePopup()" class="w-full bg-gray-100 text-gray-700 py-3 px-6 rounded-xl font-semibold text-lg hover:bg-gray-200 transition-all duration-200">
                                Got it, thanks!
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <script>
                // Global function to close review modal
                function closeReviewModal() {
                    // Try multiple approaches to close the modal
                    const reviewModalContainer = document.getElementById('review-modal-container');
                    
                    // Approach 1: Direct Alpine.js access
                    if (reviewModalContainer && window.Alpine) {
                        try {
                            const alpineComponent = window.Alpine.$data(reviewModalContainer);
                            if (alpineComponent) {
                                alpineComponent.showReviewModal = false;
                                return;
                            }
                        } catch (e) {
                            console.log('Alpine.js approach failed:', e);
                        }
                    }
                    
                    // Approach 2: Dispatch custom event
                    try {
                        const event = new CustomEvent('closeReviewModal');
                        document.dispatchEvent(event);
                    } catch (e) {
                        console.log('Custom event approach failed:', e);
                    }
                    
                    // Approach 3: Hide modal overlay directly
                    const modalOverlay = document.querySelector('[x-show="showReviewModal"]');
                    if (modalOverlay) {
                        modalOverlay.style.display = 'none';
                    }
                }

                // AJAX Review Submission - No toasts
                document.addEventListener('DOMContentLoaded', function() {
                    const form = document.getElementById('review-form');
                    const submitBtn = document.getElementById('submit-review-btn');
                    const submitText = document.getElementById('submit-text');
                    const loadingText = document.getElementById('loading-text');

                    // Check if all required elements exist
                    if (!form || !submitBtn || !submitText || !loadingText) {
                        console.log('Review form elements not found, skipping AJAX setup');
                        return;
                    }

                    // Check if CSRF token is available
                    const csrfTokenElement = document.querySelector('meta[name="csrf-token"]');
                    if (!csrfTokenElement) {
                        console.error('CSRF token meta tag not found');
                        return;
                    }

                    form.addEventListener('submit', function(e) {
                        e.preventDefault();
                        
                        // Create FormData object
                        const formData = new FormData(form);
                        
                        // Show loading state
                        submitBtn.disabled = true;
                        submitText.classList.add('hidden');
                        loadingText.classList.remove('hidden');
                        
                        // Submit via AJAX
                        fetch('{{ route("reviews.store") }}', {
                            method: 'POST',
                            body: formData,
                            headers: {
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': csrfTokenElement.getAttribute('content')
                            }
                        })
                        .then(response => {
                            console.log('Response status:', response.status);
                            console.log('Response headers:', response.headers);
                            
                            if (!response.ok) {
                                throw new Error(`HTTP error! status: ${response.status}`);
                            }
                            
                            return response.json();
                        })
                        .then(data => {
                            console.log('Response data:', data);
                            
                            if (data.success) {
                                // Hide loading state
                                submitBtn.disabled = false;
                                submitText.classList.remove('hidden');
                                loadingText.classList.add('hidden');
                                
                                // Reset form
                                form.reset();
                                
                                // Close review modal using Alpine.js
                                closeReviewModal();
                                
                                // Show gift information if available
                                if (data.gift) {
                                    console.log('Gift data received:', data.gift);
                                    
                                    document.getElementById('gift-title').textContent = data.gift.title;
                                    document.getElementById('gift-description').textContent = data.gift.description;
                                    document.getElementById('gift-discount').textContent = data.gift.discount + '%';
                                    document.getElementById('gift-code').textContent = data.gift.code;
                                    
                                    // Format valid until date
                                    const validUntil = new Date(data.gift.valid_until);
                                    const daysUntil = Math.ceil((validUntil - new Date()) / (1000 * 60 * 60 * 24));
                                    document.getElementById('gift-valid-until').textContent = daysUntil + ' days';
                                    
                                    // Show gift info section and hide no-gift message
                                    document.getElementById('gift-info').classList.remove('hidden');
                                    document.getElementById('no-gift-info').classList.add('hidden');
                                    document.getElementById('gift-button-section').classList.remove('hidden');
                                    document.getElementById('no-gift-button-section').classList.add('hidden');
                                    console.log('Gift info displayed successfully');
                                } else {
                                    console.log('No gift data received');
                                    // Hide gift info and show no-gift message
                                    document.getElementById('gift-info').classList.add('hidden');
                                    document.getElementById('no-gift-info').classList.remove('hidden');
                                    document.getElementById('gift-button-section').classList.add('hidden');
                                    document.getElementById('no-gift-button-section').classList.remove('hidden');
                                }
                                
                                // Show success popup
                                showSuccessPopup();
                            } else {
                                throw new Error(data.message || 'Unknown error occurred');
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            
                            // Hide loading state
                            submitBtn.disabled = false;
                            submitText.classList.remove('hidden');
                            loadingText.classList.add('hidden');
                        });
                    });
                });

                // Success popup functions
                function showSuccessPopup() {
                    const popup = document.getElementById('success-popup');
                    const content = document.getElementById('success-popup-content');
                    
                    if (popup && content) {
                        popup.classList.remove('hidden');
                        popup.classList.add('flex');
                        
                        // Animate in
                        setTimeout(() => {
                            content.classList.remove('scale-95', 'opacity-0');
                            content.classList.add('scale-100', 'opacity-100');
                        }, 10);
                    }
                }

                function closeSuccessPopup() {
                    const popup = document.getElementById('success-popup');
                    const content = document.getElementById('success-popup-content');
                    
                    if (popup && content) {
                        // Animate out
                        content.classList.remove('scale-100', 'opacity-100');
                        content.classList.add('scale-95', 'opacity-0');
                        
                        setTimeout(() => {
                            popup.classList.add('hidden');
                            popup.classList.remove('flex');
                        }, 300);
                    }
                }

                function receiveGift() {
                    console.log('Receive gift button clicked');
                    
                    // Get the gift code from the success popup
                    const giftCode = document.getElementById('gift-code').textContent;
                    console.log('Gift code:', giftCode);
                    
                    if (!giftCode || giftCode === 'GIFT123') {
                        alert('🎁 Gift code not available. Please try submitting a review first.');
                        closeSuccessPopup();
                        return;
                    }
                    
                    // Set the gift code in the new popup
                    document.getElementById('gift-code-display').textContent = giftCode;
                    
                    // Close success popup and show gift code popup
                    closeSuccessPopup();
                    showGiftCodePopup();
                }

                // Show gift code popup
                function showGiftCodePopup() {
                    const popup = document.getElementById('gift-code-popup');
                    const content = document.getElementById('gift-code-popup-content');
                    
                    popup.classList.remove('hidden');
                    popup.classList.add('flex');
                    
                    // Animate in
                    setTimeout(() => {
                        content.classList.remove('scale-95', 'opacity-0');
                        content.classList.add('scale-100', 'opacity-100');
                    }, 10);
                }

                // Close gift code popup
                function closeGiftCodePopup() {
                    const popup = document.getElementById('gift-code-popup');
                    const content = document.getElementById('gift-code-popup-content');
                    
                    // Animate out
                    content.classList.remove('scale-100', 'opacity-100');
                    content.classList.add('scale-95', 'opacity-0');
                    
                    setTimeout(() => {
                        popup.classList.add('hidden');
                        popup.classList.remove('flex');
                    }, 300);
                }

                // Copy gift code to clipboard
                function copyGiftCode() {
                    const giftCode = document.getElementById('gift-code-display').textContent;
                    const copyBtn = document.getElementById('copy-btn');
                    const copyStatus = document.getElementById('copy-status');
                    
                    if (navigator.clipboard && window.isSecureContext) {
                        navigator.clipboard.writeText(giftCode).then(function() {
                            console.log('Gift code copied to clipboard successfully');
                            showCopySuccess(copyBtn, copyStatus);
                        }).catch(function(err) {
                            console.error('Failed to copy to clipboard:', err);
                            fallbackCopy(giftCode, copyBtn, copyStatus);
                        });
                    } else {
                        fallbackCopy(giftCode, copyBtn, copyStatus);
                    }
                }

                // Show copy success animation
                function showCopySuccess(copyBtn, copyStatus) {
                    // Change button to success state
                    copyBtn.innerHTML = `
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                    `;
                    copyBtn.classList.remove('bg-yellow-500', 'hover:bg-yellow-600');
                    copyBtn.classList.add('bg-green-500');
                    
                    // Show success message
                    copyStatus.classList.remove('hidden');
                    
                    // Reset after 3 seconds
                    setTimeout(() => {
                        copyBtn.innerHTML = `
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                            </svg>
                        `;
                        copyBtn.classList.remove('bg-green-500');
                        copyBtn.classList.add('bg-yellow-500', 'hover:bg-yellow-600');
                        copyStatus.classList.add('hidden');
                    }, 3000);
                }

                // Fallback copy method
                function fallbackCopy(giftCode, copyBtn, copyStatus) {
                    // Create temporary textarea
                    const textarea = document.createElement('textarea');
                    textarea.value = giftCode;
                    textarea.style.position = 'fixed';
                    textarea.style.opacity = '0';
                    document.body.appendChild(textarea);
                    
                    // Select and copy
                    textarea.select();
                    try {
                        document.execCommand('copy');
                        showCopySuccess(copyBtn, copyStatus);
                    } catch (err) {
                        console.error('Fallback copy failed:', err);
                        alert(`🎁 Your gift code is: ${giftCode}\n\nCopy this code and use it during checkout.`);
                    }
                    
                    // Clean up
                    document.body.removeChild(textarea);
                }

                // Close popup when clicking outside
                document.addEventListener('click', function(e) {
                    const popup = document.getElementById('success-popup');
                    const content = document.getElementById('success-popup-content');
                    
                    if (popup && content && e.target === popup) {
                        closeSuccessPopup();
                    }
                    
                    // Gift code popup
                    const giftPopup = document.getElementById('gift-code-popup');
                    const giftContent = document.getElementById('gift-code-popup-content');
                    
                    if (giftPopup && giftContent && e.target === giftPopup) {
                        closeGiftCodePopup();
                    }
                });
            </script>
        </div>
    </div>
</section> 