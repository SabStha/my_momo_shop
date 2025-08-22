{{-- BOTTOM NAVBAR --}}
<nav class="fixed bottom-0 left-0 right-0 z-50 bg-[#6E0D25]/80 backdrop-blur-md text-white flex justify-around items-center py-1.5">
    @php
        $navItems = [
            ['route' => 'home', 'label' => 'Home', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M4 12L12 4l8 8M5 10v10h14V10"/>' ],
            ['route' => 'menu', 'label' => 'Menu', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/>' ],
            ['route' => 'finds', 'label' => "Ama's Finds", 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V4a2 2 0 10-4 0v1.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5"/>' ],
            ['route' => 'bulk', 'label' => 'Bulk', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M21 16V8a2 2 0 00-1-1.73L13 3.27a2 2 0 00-2 0L4 6.27A2 2 0 003 8v8a2 2 0 001 1.73l7 4a2 2 0 002 0l7-4a2 2 0 001-1.73z"/><path stroke-linecap="round" stroke-linejoin="round" d="M3.27 6.96L12 12.01l8.73-5.05"/>' ],
            ['route' => 'help', 'label' => 'Help', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>' ],
            ['route' => 'profile.edit', 'label' => 'Profile', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 14c-2.5 0-4.5 1.5-4.5 3.5v.5h9v-.5c0-2-2-3.5-4.5-3.5z"/><circle cx="12" cy="9" r="3"/><circle cx="12" cy="12" r="10" stroke-width="3" fill="none"/>' ],
        ];
    @endphp

    @foreach ($navItems as $item)
        <a href="{{ route($item['route']) }}"
           class="flex flex-col items-center text-xs transition-colors active:scale-95 {{ request()->routeIs($item['route'].'*') ? 'text-[#FFD700]' : 'text-white hover:text-[#FFD700]' }}">
            <svg class="w-5 h-5 mb-0.5" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"
                 xmlns="http://www.w3.org/2000/svg">
                {!! $item['icon'] !!}
            </svg>
            {{ $item['label'] }}
        </a>
    @endforeach
</nav>
