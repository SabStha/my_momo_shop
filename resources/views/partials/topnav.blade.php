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
        @livewire('notification-bell')
        @endif

        <!-- Help Link -->
        <a href="{{ route('help') }}" class="focus:outline-none relative group mr-2">
            <svg class="w-6 h-6 text-white hover:text-amk-gold transition-all duration-300 group-hover:scale-110" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </a>

        <!-- Cart Icon (clickable, Heroicons outline) -->
        @livewire('cart-manager')

    </div>
</nav>

<!-- Cart Modal -->
@include('components.cart-modal')
