@php
    $user->syncThemesWithBadges();
    $unlockedThemes = $user->unlockedThemes()->get();
    $activeTheme = $user->activeTheme;
@endphp

<div class="mb-8">
    <h2 class="text-xl font-semibold mb-4">Profile Themes</h2>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        @foreach($unlockedThemes as $theme)
            <div class="bg-white rounded-lg shadow-lg overflow-hidden border-2 transition-all duration-300 {{ $theme->is_active ? 'ring-4 ring-blue-500' : '' }}" style="{{ $theme->theme_styles['border'] }}">
                <div class="h-24 flex items-center justify-center" style="{{ $theme->theme_styles['background'] }}">
                    <div class="text-3xl">
                        @switch($theme->theme_name)
                            @case('bronze') 🥉 @break
                            @case('silver') 🥈 @break
                            @case('gold') 🥇 @break
                            @case('elite') 👑 @break
                            @default 🏆
                        @endswitch
                    </div>
                </div>
                <div class="p-4">
                    <h3 class="font-semibold mb-2" style="color: {{ $theme->theme_colors['text'] }};">
                        {{ $theme->theme_display_name }}
                    </h3>
                    <div class="flex space-x-1 mb-2">
                        <div class="w-4 h-4 rounded" style="background: {{ $theme->theme_colors['primary'] }};"></div>
                        <div class="w-4 h-4 rounded" style="background: {{ $theme->theme_colors['secondary'] }};"></div>
                        <div class="w-4 h-4 rounded" style="background: {{ $theme->theme_colors['accent'] }};"></div>
                    </div>
                    @if($theme->is_active)
                        <button class="w-full px-4 py-2 rounded-lg text-sm font-semibold text-white opacity-50 cursor-not-allowed" style="background: {{ $theme->theme_colors['primary'] }};">
                            Currently Active
                        </button>
                    @else
                        <button onclick="activateTheme('{{ $theme->theme_name }}')" class="w-full px-4 py-2 rounded-lg text-sm font-semibold text-white transition-all duration-200 hover:scale-105" style="background: {{ $theme->theme_colors['primary'] }};">
                            Activate Theme
                        </button>
                    @endif
                </div>
            </div>
        @endforeach
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
            if (typeof showToast === 'function') {
                showToast(data.message, 'success');
            } else {
                alert(data.message);
            }
            setTimeout(() => { window.location.reload(); }, 1000);
        } else {
            if (typeof showToast === 'function') {
                showToast(data.message, 'error');
            } else {
                alert(data.message);
            }
        }
    })
    .catch(error => {
        if (typeof showToast === 'function') {
            showToast('Failed to activate theme', 'error');
        } else {
            alert('Failed to activate theme');
        }
    });
}
</script> 