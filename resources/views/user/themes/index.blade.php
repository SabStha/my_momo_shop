@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        <!-- Header -->
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-gray-800 mb-2">🎨 Profile Themes</h1>
            <p class="text-gray-600">Customize your profile appearance based on your achievements!</p>
        </div>

        <!-- Current Active Theme -->
        @if($activeTheme)
            <div class="mb-8">
                <h2 class="text-xl font-semibold text-gray-800 mb-4">Current Theme</h2>
                <div class="bg-white rounded-lg shadow-lg p-6 border-2" style="{{ $activeTheme->theme_styles['border'] }}">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-4">
                            <div class="w-12 h-12 rounded-full flex items-center justify-center text-2xl" style="background: {{ $activeTheme->theme_colors['primary'] }}; color: white;">
                                @switch($activeTheme->theme_name)
                                    @case('bronze')
                                        🥉
                                        @break
                                    @case('silver')
                                        🥈
                                        @break
                                    @case('gold')
                                        🥇
                                        @break
                                    @case('elite')
                                        👑
                                        @break
                                    @default
                                        🏆
                                @endswitch
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold" style="color: {{ $activeTheme->theme_colors['text'] }};">
                                    {{ $activeTheme->theme_display_name }}
                                </h3>
                                <p class="text-sm text-gray-600">
                                    Unlocked {{ $activeTheme->unlocked_at ? $activeTheme->unlocked_at->diffForHumans() : 'recently' }}
                                </p>
                            </div>
                        </div>
                        <div class="px-4 py-2 rounded-full text-sm font-semibold text-white" style="background: {{ $activeTheme->theme_colors['primary'] }};">
                            ACTIVE
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- Available Themes -->
        <div class="mb-8">
            <h2 class="text-xl font-semibold text-gray-800 mb-4">Available Themes</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                @foreach($unlockedThemes as $theme)
                    <div class="bg-white rounded-lg shadow-lg overflow-hidden border-2 transition-all duration-300 hover:shadow-xl {{ $theme->is_active ? 'ring-4 ring-blue-500' : '' }}" 
                         style="{{ $theme->theme_styles['border'] }}">
                        
                        <!-- Theme Preview -->
                        <div class="h-32 relative" style="{{ $theme->theme_styles['background'] }}">
                            <div class="absolute inset-0 flex items-center justify-center">
                                <div class="text-4xl">
                                    @switch($theme->theme_name)
                                        @case('bronze')
                                            🥉
                                            @break
                                        @case('silver')
                                            🥈
                                            @break
                                        @case('gold')
                                            🥇
                                            @break
                                        @case('elite')
                                            👑
                                            @break
                                        @default
                                            🏆
                                    @endswitch
                                </div>
                            </div>
                            
                            @if($theme->is_active)
                                <div class="absolute top-2 right-2">
                                    <div class="w-6 h-6 bg-green-500 rounded-full flex items-center justify-center">
                                        <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                        </svg>
                                    </div>
                                </div>
                            @endif
                        </div>
                        
                        <!-- Theme Info -->
                        <div class="p-4">
                            <h3 class="font-semibold mb-2" style="color: {{ $theme->theme_colors['text'] }};">
                                {{ $theme->theme_display_name }}
                            </h3>
                            
                            <div class="space-y-2 mb-4">
                                <div class="flex space-x-1">
                                    <div class="w-4 h-4 rounded" style="background: {{ $theme->theme_colors['primary'] }};"></div>
                                    <div class="w-4 h-4 rounded" style="background: {{ $theme->theme_colors['secondary'] }};"></div>
                                    <div class="w-4 h-4 rounded" style="background: {{ $theme->theme_colors['accent'] }};"></div>
                                </div>
                            </div>
                            
                            @if($theme->is_active)
                                <button class="w-full px-4 py-2 rounded-lg text-sm font-semibold text-white opacity-50 cursor-not-allowed" 
                                        style="background: {{ $theme->theme_colors['primary'] }};">
                                    Currently Active
                                </button>
                            @else
                                <button onclick="activateTheme('{{ $theme->theme_name }}')" 
                                        class="w-full px-4 py-2 rounded-lg text-sm font-semibold text-white transition-all duration-200 hover:scale-105" 
                                        style="background: {{ $theme->theme_colors['primary'] }};">
                                    Activate Theme
                                </button>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Theme Info -->
        <div class="bg-gray-50 rounded-lg p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-3">How to Unlock Themes</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="flex items-start space-x-3">
                    <div class="w-8 h-8 bg-amber-600 rounded-full flex items-center justify-center text-white text-sm font-bold">🥉</div>
                    <div>
                        <h4 class="font-semibold text-gray-800">Bronze Theme</h4>
                        <p class="text-sm text-gray-600">Unlocked with any Bronze badge</p>
                    </div>
                </div>
                <div class="flex items-start space-x-3">
                    <div class="w-8 h-8 bg-gray-400 rounded-full flex items-center justify-center text-white text-sm font-bold">🥈</div>
                    <div>
                        <h4 class="font-semibold text-gray-800">Silver Theme</h4>
                        <p class="text-sm text-gray-600">Unlocked with any Silver badge</p>
                    </div>
                </div>
                <div class="flex items-start space-x-3">
                    <div class="w-8 h-8 bg-yellow-500 rounded-full flex items-center justify-center text-white text-sm font-bold">🥇</div>
                    <div>
                        <h4 class="font-semibold text-gray-800">Gold Theme</h4>
                        <p class="text-sm text-gray-600">Unlocked with any Gold badge</p>
                    </div>
                </div>
                <div class="flex items-start space-x-3">
                    <div class="w-8 h-8 bg-purple-600 rounded-full flex items-center justify-center text-white text-sm font-bold">👑</div>
                    <div>
                        <h4 class="font-semibold text-gray-800">Elite Theme</h4>
                        <p class="text-sm text-gray-600">Unlocked with Elite badge</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function activateTheme(themeName) {
    fetch('/user/themes/activate', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({ theme_name: themeName })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Show success message
            if (typeof showToast === 'function') {
                showToast(data.message, 'success');
            } else {
                alert(data.message);
            }
            
            // Reload page to show updated theme
            setTimeout(() => {
                window.location.reload();
            }, 1000);
        } else {
            if (typeof showToast === 'function') {
                showToast(data.message, 'error');
            } else {
                alert(data.message);
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
        if (typeof showToast === 'function') {
            showToast('Failed to activate theme', 'error');
        } else {
            alert('Failed to activate theme');
        }
    });
}
</script>
@endsection 