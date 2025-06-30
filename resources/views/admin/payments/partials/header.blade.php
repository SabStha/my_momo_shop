@php /* Header Partial */ @endphp
<!-- Header Section -->
<header class="bg-white shadow px-6 py-3">
    <div class="flex justify-between items-center mb-3">
        <div class="flex items-center space-x-4">
            <a href="{{ route('admin.dashboard.branch', ['branch' => $branch->id ?? 1]) }}" class="text-gray-600 hover:text-gray-800">
                <i class="fas fa-arrow-left mr-2"></i> Back to Dashboard
            </a>
            <h2 class="text-lg font-semibold">Payment Management</h2>
            @if(isset($branch))
                <span class="text-sm text-gray-500">• {{ $branch->name }}</span>
            @endif
        </div>
        <div class="flex items-center space-x-4">
            <!-- Sound Controls -->
            <div class="flex items-center space-x-2 bg-gray-100 rounded-lg px-3 py-1">
                <button id="soundMuteBtn" onclick="toggleSoundMute()" class="text-gray-600 hover:text-gray-800 transition-colors" title="Mute sounds">
                    <i class="fas fa-volume-up"></i>
                </button>
                <input type="range" id="soundVolumeSlider" min="0" max="100" value="70" 
                       onchange="setSoundVolume(this.value / 100)" 
                       class="w-16 h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer">
                <span class="text-xs text-gray-500">Sound</span>
                <!-- Test buttons -->
                <button onclick="playPaymentSuccess()" class="text-xs text-green-600 hover:text-green-800 px-2 py-1 rounded" title="Test success sound">
                    <i class="fas fa-check"></i>
                </button>
                <button onclick="playPaymentFailed()" class="text-xs text-red-600 hover:text-red-800 px-2 py-1 rounded" title="Test failure sound">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="text-sm text-gray-600">Welcome, {{ Auth::user()->name }}</div>
            <form method="POST" action="{{ route('logout') }}" class="inline">
                @csrf
                <button type="submit" class="text-gray-600 hover:text-gray-800">
                    <i class="fas fa-sign-out-alt mr-1"></i> Logout
                </button>
            </form>
        </div>
    </div>
</header> 