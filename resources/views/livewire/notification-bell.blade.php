<div wire:poll.30000ms="loadNotifications" class="relative" x-data="{ open: false }" @click.away="open = false">
    {{-- Bell button --}}
    <button @click="open = !open" class="relative group focus:outline-none">
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

    {{-- Dropdown panel --}}
    <div
        x-show="open"
        x-transition:enter="transition ease-out duration-150"
        x-transition:enter-start="opacity-0 scale-95 translate-y-1"
        x-transition:enter-end="opacity-100 scale-100 translate-y-0"
        x-transition:leave="transition ease-in duration-100"
        x-transition:leave-start="opacity-100 scale-100 translate-y-0"
        x-transition:leave-end="opacity-0 scale-95 translate-y-1"
        class="absolute right-0 top-10 w-80 bg-white rounded-lg shadow-xl border border-gray-200 z-[100]"
        style="display: none;"
    >
        {{-- Header --}}
        <div class="flex items-center justify-between px-4 py-3 border-b border-gray-200">
            <span class="text-sm font-semibold text-gray-900">Notifications</span>
            @if($count > 0)
                <button
                    wire:click="markAllRead"
                    class="text-xs text-blue-600 hover:text-blue-800 transition-colors"
                >
                    Mark all read
                </button>
            @endif
        </div>

        {{-- Notification list --}}
        <div class="max-h-72 overflow-y-auto divide-y divide-gray-100">
            @forelse($notifications as $notification)
                <div class="px-4 py-3 hover:bg-gray-50 transition-colors {{ !$notification['read_at'] ? 'bg-blue-50' : '' }}">
                    <div class="flex items-start gap-2">
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900 truncate">
                                {{ $notification['data']['title'] ?? 'Notification' }}
                            </p>
                            <p class="text-xs text-gray-600 mt-0.5 line-clamp-2">
                                {{ $notification['data']['message'] ?? '' }}
                            </p>
                            <p class="text-xs text-gray-400 mt-1">
                                {{ \Carbon\Carbon::parse($notification['created_at'])->diffForHumans() }}
                            </p>
                        </div>
                        @if(!$notification['read_at'])
                            <span class="mt-1 flex-shrink-0 w-2 h-2 bg-blue-500 rounded-full"></span>
                        @endif
                    </div>
                </div>
            @empty
                <div class="px-4 py-8 text-center">
                    <p class="text-sm text-gray-500">You're all caught up!</p>
                </div>
            @endforelse
        </div>

        {{-- Footer --}}
        <div class="border-t border-gray-200">
            <a
                href="{{ route('notifications') }}"
                @click="open = false"
                class="block px-4 py-3 text-center text-sm text-blue-600 hover:bg-gray-50 transition-colors font-medium"
            >
                View all notifications
            </a>
        </div>
    </div>
</div>
