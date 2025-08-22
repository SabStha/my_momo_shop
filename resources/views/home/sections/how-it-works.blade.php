<!-- How It Works Section (Animated) -->
<section class="py-10 bg-gradient-to-b from-white via-[#FFF8F0] to-[#FCEDC0]">
    <div class="max-w-5xl mx-auto text-center">
        <h2 class="text-3xl sm:text-4xl font-extrabold mb-8 text-[#6E0D25] tracking-tight animate-fade-in">How It Works</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-8">
            <!-- Step 1 -->
            <div class="flex flex-col items-center group animate-slide-up delay-100">
                <div class="relative mb-3">
                    <div class="w-20 h-20 rounded-full bg-gradient-to-br from-[#FDE68A] to-[#F59E42] flex items-center justify-center text-5xl shadow-lg group-hover:scale-110 transition-transform duration-300">📖</div>
                    <div class="absolute -bottom-3 left-1/2 -translate-x-1/2 w-2 h-2 bg-[#6E0D25] rounded-full animate-pulse"></div>
                </div>
                <h3 class="font-bold text-lg text-[#6E0D25] mb-1">Browse Menu</h3>
                <p class="text-xs text-gray-600">Explore combos, momos, drinks, and more.</p>
            </div>
            <!-- Step 2 -->
            <div class="flex flex-col items-center group animate-slide-up delay-200">
                <div class="relative mb-3">
                    <div class="w-20 h-20 rounded-full bg-gradient-to-br from-[#FCA5A5] to-[#F87171] flex items-center justify-center text-5xl shadow-lg group-hover:scale-110 transition-transform duration-300">🛒</div>
                    <div class="absolute -bottom-3 left-1/2 -translate-x-1/2 w-2 h-2 bg-[#6E0D25] rounded-full animate-pulse"></div>
                </div>
                <h3 class="font-bold text-lg text-[#6E0D25] mb-1">Add to Cart</h3>
                <p class="text-xs text-gray-600">Pick your favorites and customize your order.</p>
            </div>
            <!-- Step 3 -->
            <div class="flex flex-col items-center group animate-slide-up delay-300">
                <div class="relative mb-3">
                    <div class="w-20 h-20 rounded-full bg-gradient-to-br from-[#A7F3D0] to-[#34D399] flex items-center justify-center text-5xl shadow-lg group-hover:scale-110 transition-transform duration-300">💳</div>
                    <div class="absolute -bottom-3 left-1/2 -translate-x-1/2 w-2 h-2 bg-[#6E0D25] rounded-full animate-pulse"></div>
                </div>
                <h3 class="font-bold text-lg text-[#6E0D25] mb-1">Checkout & Pay</h3>
                <p class="text-xs text-gray-600">Enter delivery info, choose payment method.</p>
            </div>
            <!-- Step 4 -->
            <div class="flex flex-col items-center group animate-slide-up delay-400">
                <div class="relative mb-3">
                    <div class="w-20 h-20 rounded-full bg-gradient-to-br from-[#C7D2FE] to-[#6366F1] flex items-center justify-center text-5xl shadow-lg group-hover:scale-110 transition-transform duration-300">🏅</div>
                    <div class="absolute -bottom-3 left-1/2 -translate-x-1/2 w-2 h-2 bg-[#6E0D25] rounded-full animate-pulse"></div>
                </div>
                <h3 class="font-bold text-lg text-[#6E0D25] mb-1">Enjoy & Earn Badges!</h3>
                <p class="text-xs text-gray-600">Get food delivered, collect credits, unlock badges.</p>
            </div>
        </div>
        <!-- Animated connecting line for desktop -->
        <div class="hidden md:block absolute left-1/2 -translate-x-1/2 mt-[-60px] w-[60%] h-2 bg-gradient-to-r from-[#FDE68A] via-[#FCA5A5] to-[#C7D2FE] rounded-full blur-sm opacity-60 animate-pulse"></div>
    </div>
    <style>
        @keyframes fade-in {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .animate-fade-in { animation: fade-in 0.8s cubic-bezier(0.4,0,0.2,1) both; }
        @keyframes slide-up {
            from { opacity: 0; transform: translateY(40px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .animate-slide-up { animation: slide-up 0.8s cubic-bezier(0.4,0,0.2,1) both; }
        .delay-100 { animation-delay: 0.1s; }
        .delay-200 { animation-delay: 0.2s; }
        .delay-300 { animation-delay: 0.3s; }
        .delay-400 { animation-delay: 0.4s; }
    </style>
</section> 