<div wire:poll.30000ms="loadNotifications" class="relative">
    <button class="relative group focus:outline-none">
        <svg class="w-6 h-6 text-white hover:text-amk-gold transition-all duration-300 group-hover:scale-110" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-5-5.917V4a1 1 0 10-2 0v1.083A6.002 6.002 0 006 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M13.73 21a2 2 0 01-3.46 0" />
        </svg>
        @if($count > 0)
            <span class="absolute -top-2 -right-2 bg-amk-gold text-black text-[10px] font-bold rounded-full h-4 w-4 flex items-center justify-center animate-pulse">
                {{ $count > 9 ? '9+' : $count }}
            </span>
        @endif
    </button>
</div>
