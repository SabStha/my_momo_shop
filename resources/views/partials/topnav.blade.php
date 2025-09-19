{{-- TOP NAVBAR --}}
<nav class="fixed top-0 left-0 right-0 z-[60] text-white flex justify-between items-center px-4 py-2 h-12 min-h-[48px] shadow-lg" style="background-color: #152039; box-shadow: 0 2px 5px rgba(0,0,0,.08);">
    <!-- Shop Logo -->
    <a href="{{ route('home') }}" class="flex items-center h-full">
    <img 
    src="{{ asset('storage/logo/momokologo.png') }}" 
    alt="Ama Ko Momo Logo" 
    class="h-12 w-auto object-contain drop-shadow-lg"
    />
</a>

    <!-- Notification & Cart Icons -->
    <div class="flex items-center gap-4 relative" style="overflow: visible;">
        <!-- Enhanced Notification Bell with Dropdown - Only show on customer-facing pages -->
        @php
            $currentRoute = request()->route()->getName();
            // Make offers more visible - show on all customer-facing pages
            $showOffers = !in_array($currentRoute, [
                'login', 'register', 'password.request', 'password.reset', 'password.confirm', 'verification.notice', 'verification.verify',
                'admin.*', 'investor.*', 'creator.*', 'employee.*', 'supplier.*'
            ]) && !str_starts_with($currentRoute, 'admin.') && !str_starts_with($currentRoute, 'investor.') && 
            !str_starts_with($currentRoute, 'creator.') && !str_starts_with($currentRoute, 'employee.') && 
            !str_starts_with($currentRoute, 'supplier.');
        @endphp
        
        @if($showOffers)
        <!-- Debug: {{ isset($activeOffers) ? 'activeOffers exists with ' . $activeOffers->count() . ' offers' : 'activeOffers not set' }} -->
        <div x-data="{ open: false }" class="notification-container relative">
            <button @click="open = !open" class="notification-container focus:outline-none relative group" style="overflow: visible;">
                <!-- Heroicons Bell Outline with enhanced styling -->
                <svg class="w-6 h-6 text-white hover:text-amk-gold transition-all duration-300 group-hover:scale-110" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-5-5.917V4a1 1 0 10-2 0v1.083A6.002 6.002 0 006 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5" />
                  <path stroke-linecap="round" stroke-linejoin="round" d="M13.73 21a2 2 0 01-3.46 0" />
                </svg>
                <!-- Notification count badge -->
                @php
                    $notificationCount = isset($activeOffers) ? $activeOffers->count() : 0;
                @endphp
                <div class="notification-badge absolute -top-2 -right-2 bg-amk-gold text-black text-xs font-bold rounded-full h-5 w-5 flex items-center justify-center animate-pulse z-10" style="display: {{ $notificationCount > 0 ? 'flex' : 'none' }}; min-width: 20px; min-height: 20px; overflow: visible;">
                    {{ $notificationCount > 99 ? '99+' : $notificationCount }}
                </div>
            </button>
            <div x-show="open" @click.away="open = false" 
                 x-transition:enter="transition ease-out duration-300" 
                 x-transition:enter-start="opacity-0 scale-95 translate-y-2" 
                 x-transition:enter-end="opacity-100 scale-100 translate-y-0" 
                 x-transition:leave="transition ease-in duration-200" 
                 x-transition:leave-start="opacity-100 scale-100 translate-y-0" 
                 x-transition:leave-end="opacity-0 scale-95 translate-y-2" 
                 class="absolute right-0 mt-3 w-80 max-w-xs bg-white/95 backdrop-blur-xl border border-white/20 rounded-2xl shadow-2xl z-[70] overflow-hidden" 
                 style="display: none;">
                
                <!-- Enhanced Header -->
                <div class="flex items-center justify-between px-4 py-3 border-b border-white/10 bg-gradient-to-r from-amk-brown-1 to-amk-brown-2 rounded-t-2xl">
                    <div class="flex items-center gap-2">
                        <span class="text-xl animate-bounce">🎁</span>
                        <div>
                            <span class="font-bold text-white text-base">My Offers</span>
                            <div class="text-white/80 text-xs">{{ isset($activeOffers) ? $activeOffers->count() : 4 }} offers available</div>
                        </div>
                    </div>
                    <button @click="open = false" class="text-white/70 hover:text-white transition-colors duration-200 hover:scale-110">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                
                <!-- Enhanced Offers Container -->
                <div class="p-4 bg-white rounded-b-2xl max-h-[70vh] overflow-y-auto">
                    <div class="space-y-3">
                        @if(isset($activeOffers) && $activeOffers->count() > 0)
                            @php
                                $user = auth()->user();
                                $claimedOfferIds = $user ? $user->offerClaims()->pluck('offer_id')->toArray() : [];
                            @endphp
                            
                            @foreach($activeOffers->take(6) as $index => $offer)
                                @php
                                    $isClaimed = in_array($offer->id, $claimedOfferIds);
                                    $claim = $isClaimed ? $user->offerClaims()->where('offer_id', $offer->id)->first() : null;
                                    $isUsed = $claim && $claim->status === 'used';
                                    $isExpired = $claim && $claim->status === 'expired';
                                    $isAIGenerated = $offer->ai_generated;
                                    $isPersonalized = $offer->target_audience === 'personalized';
                                @endphp
                                
                                <div class="group relative bg-gradient-to-br from-gray-50 to-gray-100 border border-gray-200 rounded-lg p-3 text-gray-800 overflow-hidden transform transition-all duration-300 hover:scale-102 hover:shadow-md hover:border-amk-brown-1/30" 
                                     style="animation-delay: {{ $index * 0.1 }}s;">
                                    
                                    <!-- Enhanced Animated Background -->
                                    <div class="absolute inset-0 bg-gradient-to-r from-transparent via-amk-brown-1/5 to-transparent -translate-x-full group-hover:translate-x-full transition-transform duration-1000"></div>
                                    
                                    <!-- Enhanced Badge -->
                                    <div class="absolute top-2 right-2 flex gap-1">
                                        @if($isClaimed)
                                            @if($isUsed)
                                                <div class="bg-gray-500 text-white px-2 py-0.5 rounded-full text-xs font-semibold shadow-sm">
                                                    ✅ USED
                                                </div>
                                            @elseif($isExpired)
                                                <div class="bg-amk-brown-1 text-white px-2 py-0.5 rounded-full text-xs font-semibold shadow-sm">
                                                    ⏰ EXPIRED
                                                </div>
                                            @else
                                                <div class="bg-green-500 text-white px-2 py-0.5 rounded-full text-xs font-semibold shadow-sm">
                                                    🎯 CLAIMED
                                                </div>
                                            @endif
                                        @else
                                            <div class="bg-amk-gold text-black px-2 py-0.5 rounded-full text-xs font-semibold shadow-sm">
                                                NEW
                                            </div>
                                        @endif
                                    </div>
                                    
                                    <!-- Enhanced Icon -->
                                    <div class="text-2xl mb-2 transform group-hover:scale-110 transition-transform duration-300 animate-bounce" style="animation-delay: {{ $index * 0.2 }}s;">
                                        @switch(strtolower($offer->title))
                                            @case('first order discount')
                                                🎉
                                                @break
                                            @case('combo deal')
                                                🥟
                                                @break
                                            @case('weekend special')
                                                🌅
                                                @break
                                            @case('loyalty rewards')
                                                👑
                                                @break
                                            @case('bulk discount')
                                                📦
                                                @break
                                            @case('flash sale')
                                                ⚡
                                                @break
                                            @default
                                                🎁
                                        @endswitch
                                    </div>
                                    
                                    <!-- Enhanced Content -->
                                    <h4 class="font-bold text-sm mb-1 group-hover:text-amk-brown-1 transition-colors">
                                        {{ $offer->title }}
                                        @if($isClaimed && $claim)
                                            <span class="text-xs text-gray-500 ml-1">(Claimed {{ $claim->claimed_at->diffForHumans() }})</span>
                                        @endif
                                    </h4>
                                    <p class="text-gray-600 text-xs mb-2 leading-relaxed group-hover:text-gray-800 transition-colors">
                                        {{ Str::limit($offer->description, 50) }}
                                        @if($offer->code)
                                            <br><span class="font-mono bg-amk-brown-1/10 text-amk-brown-1 px-1.5 py-0.5 rounded text-xs hover:bg-amk-brown-1/20 transition-colors cursor-pointer" onclick="copyToClipboard('{{ $offer->code }}')" title="Click to copy">{{ $offer->code }}</span>
                                        @endif
                                    </p>
                                    
                                    <!-- Enhanced Progress Bar for Limited Offers -->
                                    @if($offer->valid_until)
                                        <div class="mb-2">
                                            <div class="flex justify-between text-xs text-gray-500 mb-1">
                                                <span>⏰ Ends {{ $offer->valid_until->diffForHumans() }}</span>
                                                <span class="text-amk-brown-1 font-semibold">{{ $offer->discount }}% OFF</span>
                                            </div>
                                            <div class="w-full bg-gray-200 rounded-full h-1 overflow-hidden">
                                                @php
                                                    $totalDuration = $offer->valid_until->diffInSeconds($offer->valid_from);
                                                    $remainingDuration = $offer->valid_until->diffInSeconds(now());
                                                    $progressPercentage = max(0, min(100, (($totalDuration - $remainingDuration) / $totalDuration) * 100));
                                                @endphp
                                                <div class="bg-gradient-to-r from-amk-brown-1 to-amk-brown-2 h-1 rounded-full transition-all duration-1000 relative overflow-hidden" style="width: {{ $progressPercentage }}%">
                                                    <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/30 to-transparent animate-pulse"></div>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                    
                                    <!-- Enhanced Button -->
                                    @if($isClaimed)
                                        @if($isUsed)
                                            <button disabled class="w-full bg-gray-400 text-white px-3 py-1.5 rounded-md text-xs font-semibold cursor-not-allowed">
                                                Already Used
                                            </button>
                                        @elseif($isExpired)
                                            <button disabled class="w-full bg-red-400 text-white px-3 py-1.5 rounded-md text-xs font-semibold cursor-not-allowed">
                                                Expired
                                            </button>
                                        @else
                                            <button onclick="applyClaimedOffer('{{ $claim->id }}', this)" 
                                                    class="w-full bg-green-600 text-white px-3 py-1.5 rounded-md text-xs font-semibold hover:bg-green-700 transition-all duration-300 transform hover:-translate-y-0.5 shadow-sm group-hover:shadow-md relative overflow-hidden group/btn">
                                                <span class="relative z-10 group-hover/btn:scale-105 transition-transform">Use Offer</span>
                                                <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/20 to-transparent -translate-x-full group-hover/btn:translate-x-full transition-transform duration-700"></div>
                                            </button>
                                        @endif
                                    @else
                                        <button onclick="claimOffer('{{ $offer->code }}', this)" 
                                                class="w-full bg-amk-brown-1 text-white px-3 py-1.5 rounded-md text-xs font-semibold hover:bg-amk-brown-2 transition-all duration-300 transform hover:-translate-y-0.5 shadow-sm group-hover:shadow-md relative overflow-hidden group/btn">
                                            <span class="relative z-10 group-hover/btn:scale-105 transition-transform">Claim Offer</span>
                                            <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/20 to-transparent -translate-x-full group-hover/btn:translate-x-full transition-transform duration-700"></div>
                                        </button>
                                    @endif
                                    
                                </div>
                            @endforeach
                        @else
                            <!-- Enhanced Fallback Hardcoded Offers -->
                            <!-- First Order Discount -->
                            <div class="group relative bg-gradient-to-br from-gray-50 to-gray-100 border border-gray-200 rounded-lg p-3 text-gray-800 overflow-hidden transform transition-all duration-300 hover:scale-102 hover:shadow-md hover:border-amk-brown-1/30">
                                <div class="absolute inset-0 bg-gradient-to-r from-transparent via-amk-brown-1/5 to-transparent -translate-x-full group-hover:translate-x-full transition-transform duration-1000"></div>
                                
                                <div class="absolute top-2 right-2 bg-amk-gold text-black px-2 py-0.5 rounded-full text-xs font-semibold shadow-sm animate-bounce">
                                    NEW
                                </div>
                                
                                <div class="text-2xl mb-2 transform group-hover:scale-110 transition-transform duration-300 animate-bounce">🎉</div>
                                
                                <h4 class="font-bold text-sm mb-1 group-hover:text-amk-brown-1 transition-colors">First Order 20% Off</h4>
                                <p class="text-gray-600 text-xs mb-2 leading-relaxed group-hover:text-gray-800 transition-colors">
                                    New customers get 20% off. Use code: <span class="font-mono bg-amk-brown-1/10 text-amk-brown-1 px-1.5 py-0.5 rounded text-xs hover:bg-amk-brown-1/20 transition-colors cursor-pointer" onclick="copyToClipboard('WELCOME20')" title="Click to copy">WELCOME20</span>
                                </p>
                                
                                <div class="mb-2">
                                    <div class="flex justify-between text-xs mb-1">
                                        <span class="text-gray-500">Claimed: 847</span>
                                        <span class="text-amk-brown-1 font-semibold">20% OFF</span>
                                    </div>
                                    <div class="w-full bg-gray-200 rounded-full h-1 overflow-hidden">
                                        <div class="bg-gradient-to-r from-amk-brown-1 to-amk-brown-2 h-1 rounded-full transition-all duration-1000 relative overflow-hidden" style="width: 85%">
                                            <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/30 to-transparent animate-pulse"></div>
                                        </div>
                                    </div>
                                </div>
                                
                                <button onclick="claimOffer('WELCOME20', this)" 
                                        class="w-full bg-amk-brown-1 text-white px-3 py-1.5 rounded-md text-xs font-semibold hover:bg-amk-brown-2 transition-all duration-300 transform hover:-translate-y-0.5 shadow-sm group-hover:shadow-md relative overflow-hidden group/btn">
                                    <span class="relative z-10 group-hover/btn:scale-105 transition-transform">Claim Offer</span>
                                    <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/20 to-transparent -translate-x-full group-hover/btn:translate-x-full transition-transform duration-700"></div>
                                </button>
                            </div>

                            <!-- Combo Deal -->
                            <div class="group relative bg-gradient-to-br from-gray-50 to-gray-100 border border-gray-200 rounded-lg p-3 text-gray-800 overflow-hidden transform transition-all duration-300 hover:scale-102 hover:shadow-md hover:border-amk-brown-1/30">
                                <div class="absolute inset-0 bg-gradient-to-r from-transparent via-amk-brown-1/5 to-transparent -translate-x-full group-hover:translate-x-full transition-transform duration-1000"></div>
                                
                                <div class="absolute top-2 right-2 bg-amk-gold text-black px-2 py-0.5 rounded-full text-xs font-semibold shadow-sm">
                                    🔥 POPULAR
                                </div>
                                
                                <div class="text-2xl mb-2 transform group-hover:scale-110 transition-transform duration-300 animate-bounce" style="animation-delay: 0.2s;">🥟</div>
                                
                                <h4 class="font-bold text-sm mb-1 group-hover:text-amk-brown-1 transition-colors">Buy 2 Get 1 Free</h4>
                                <p class="text-gray-600 text-xs mb-2 leading-relaxed group-hover:text-gray-800 transition-colors">
                                    Order any 2 momo dishes and get 1 free!
                                </p>
                                
                                <div class="mb-2">
                                    <div class="inline-flex items-center gap-1 bg-amk-brown-1/10 px-2 py-0.5 rounded-full text-xs group-hover:bg-amk-brown-1/20 transition-colors">
                                        <span>💰</span>
                                        <span class="text-amk-brown-1">Save up to Rs.8.99</span>
                                    </div>
                                </div>
                                
                                <button onclick="addComboToCart('bogo', this)" 
                                        class="w-full bg-amk-brown-1 text-white px-3 py-1.5 rounded-md text-xs font-semibold hover:bg-amk-brown-2 transition-all duration-300 transform hover:-translate-y-0.5 shadow-sm group-hover:shadow-md relative overflow-hidden group/btn">
                                    <span class="relative z-10 group-hover/btn:scale-105 transition-transform">Order Now</span>
                                    <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/20 to-transparent -translate-x-full group-hover/btn:translate-x-full transition-transform duration-700"></div>
                                </button>
                            </div>

                            <!-- Flash Sale -->
                            <div class="group relative bg-gradient-to-br from-gray-50 to-gray-100 border border-gray-200 rounded-lg p-3 text-gray-800 overflow-hidden transform transition-all duration-300 hover:scale-102 hover:shadow-md hover:border-amk-brown-1/30">
                                <div class="absolute inset-0 bg-gradient-to-r from-transparent via-amk-brown-1/5 to-transparent -translate-x-full group-hover:translate-x-full transition-transform duration-1000"></div>
                                
                                <div class="absolute top-2 right-2 bg-red-500 text-white px-2 py-0.5 rounded-full text-xs font-semibold shadow-sm animate-pulse">
                                    ⚡ FLASH
                                </div>
                                
                                <div class="text-2xl mb-2 transform group-hover:scale-110 transition-transform duration-300 animate-bounce" style="animation-delay: 0.4s;">⚡</div>
                                
                                <h4 class="font-bold text-sm mb-1 group-hover:text-amk-brown-1 transition-colors">Flash Sale - 30% Off</h4>
                                <p class="text-gray-600 text-xs mb-2 leading-relaxed group-hover:text-gray-800 transition-colors">
                                    Limited time! 30% off all steamed momos.
                                </p>
                                
                                <div class="mb-2">
                                    <div class="text-xs text-gray-500 mb-1">⏰ Time Remaining</div>
                                    <div class="flex gap-1" id="flash-sale-timer">
                                        <div class="bg-amk-brown-1/10 text-amk-brown-1 rounded px-1.5 py-0.5 text-xs font-mono animate-pulse">02</div>
                                        <div class="bg-amk-brown-1/10 text-amk-brown-1 rounded px-1.5 py-0.5 text-xs font-mono animate-pulse" style="animation-delay: 0.5s;">00</div>
                                        <div class="bg-amk-brown-1/10 text-amk-brown-1 rounded px-1.5 py-0.5 text-xs font-mono animate-pulse" style="animation-delay: 1s;">00</div>
                                    </div>
                                </div>
                                
                                <button onclick="addFlashSale(this)" 
                                        class="w-full bg-amk-brown-1 text-white px-3 py-1.5 rounded-md text-xs font-semibold hover:bg-amk-brown-2 transition-all duration-300 transform hover:-translate-y-0.5 shadow-sm group-hover:shadow-md relative overflow-hidden group/btn">
                                    <span class="relative z-10 group-hover/btn:scale-105 transition-transform">Shop Now</span>
                                    <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/20 to-transparent -translate-x-full group-hover/btn:translate-x-full transition-transform duration-700"></div>
                                </button>
                            </div>

                            <!-- Loyalty Program -->
                            <div class="group relative bg-gradient-to-br from-gray-50 to-gray-100 border border-gray-200 rounded-lg p-3 text-gray-800 overflow-hidden transform transition-all duration-300 hover:scale-102 hover:shadow-md hover:border-amk-brown-1/30">
                                <div class="absolute inset-0 bg-gradient-to-r from-transparent via-amk-brown-1/5 to-transparent -translate-x-full group-hover:translate-x-full transition-transform duration-1000"></div>
                                
                                <div class="absolute top-2 right-2 bg-amk-brown-1 text-white px-2 py-0.5 rounded-full text-xs font-semibold shadow-sm">
                                    👑 LOYALTY
                                </div>
                                
                                <div class="text-2xl mb-2 transform group-hover:scale-110 transition-transform duration-300 animate-bounce" style="animation-delay: 0.6s;">👑</div>
                                
                                <h4 class="font-bold text-sm mb-1 group-hover:text-amk-brown-1 transition-colors">Earn Points & Save</h4>
                                <p class="text-gray-600 text-xs mb-2 leading-relaxed group-hover:text-gray-800 transition-colors">
                                    Join our loyalty program. 100 points = Rs.5 off!
                                </p>
                                
                                <div class="mb-2">
                                    <div class="flex items-center gap-1 text-xs">
                                        <div class="w-4 h-4 bg-amk-brown-1/10 rounded-full flex items-center justify-center animate-pulse">🎯</div>
                                        <span class="text-gray-500">Join 2,847 members</span>
                                    </div>
                                </div>
                                
                                <button onclick="joinLoyalty(this)" 
                                        class="w-full bg-amk-brown-1 text-white px-3 py-1.5 rounded-md text-xs font-semibold hover:bg-amk-brown-2 transition-all duration-300 transform hover:-translate-y-0.5 shadow-sm group-hover:shadow-md relative overflow-hidden group/btn">
                                    <span class="relative z-10 group-hover/btn:scale-105 transition-transform">Join Now</span>
                                    <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/20 to-transparent -translate-x-full group-hover/btn:translate-x-full transition-transform duration-700"></div>
                                </button>
                            </div>
                        @endif

                        <!-- Enhanced View All Offers Link -->
                        <div class="text-center pt-3 border-t border-gray-200">
                            <a href="{{ route('home') }}" 
                               class="inline-flex items-center gap-1 text-amk-brown-1 text-xs font-semibold hover:text-[#8B0D2F] transition-colors duration-300 group/link">
                                <span>View All Offers</span>
                                <span class="group-hover/link:translate-x-0.5 transition-transform duration-300">→</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- Help Link -->
        <a href="{{ route('help') }}" class="focus:outline-none relative group mr-2">
            <svg class="w-6 h-6 text-white hover:text-amk-gold transition-all duration-300 group-hover:scale-110" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </a>

        <!-- Cart Icon (clickable, Heroicons outline) -->
        <a href="{{ route('cart') }}" class="cart-icon focus:outline-none relative group">
            <svg class="w-6 h-6 text-white hover:text-amk-gold transition-all duration-300 group-hover:scale-110" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4" />
              <circle cx="7" cy="21" r="1.5" />
              <circle cx="17" cy="21" r="1.5" />
            </svg>
            <!-- Cart count badge -->
            <div class="cart-count absolute -top-2 -right-2 text-green-500 text-sm font-bold animate-pulse" style="display: none;">0</div>
        </a>

    </div>
</nav>

<!-- Cart Modal -->
@include('components.cart-modal')
