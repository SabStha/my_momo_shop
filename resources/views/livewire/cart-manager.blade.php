{{-- Navbar Cart Icon with live count badge --}}
{{-- Events (cartUpdated) drive updates; poll is a 10 s safety-net fallback --}}
<div class="cart-manager-container relative" wire:poll.10000ms="loadCart">
    <a href="{{ route('cart') }}" class="relative group focus:outline-none" aria-label="Cart">
        <svg class="w-6 h-6 text-white hover:text-amk-gold transition-all duration-300 group-hover:scale-110"
             fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round"
                  d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 13m0 0l-2.5 5M7 13l2.5 5m6-5v1a1 1 0 01-1 1H9a1 1 0 01-1-1v-1"/>
        </svg>
        @if($cartCount > 0)
            <span class="absolute -top-2 -right-2 bg-amk-gold text-black text-[10px] font-bold rounded-full h-4 w-4 flex items-center justify-center animate-pulse">
                {{ $cartCount > 9 ? '9+' : $cartCount }}
            </span>
        @endif
    </a>
</div>
