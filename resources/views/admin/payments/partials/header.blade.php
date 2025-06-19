@php /* Header Partial */ @endphp
<!-- Header Section -->
<header class="bg-white shadow px-6 py-3">
    <div class="flex justify-between items-center mb-3">
        <div class="flex items-center space-x-4">
            <a href="{{ route('admin.dashboard', ['branch' => $branch->id ?? 1]) }}" class="text-gray-600 hover:text-gray-800">
                <i class="fas fa-arrow-left mr-2"></i> Back to Dashboard
            </a>
            <h2 class="text-lg font-semibold">Payment Management</h2>
            @if(isset($branch))
                <span class="text-sm text-gray-500">• {{ $branch->name }}</span>
            @endif
        </div>
        <div class="flex items-center space-x-4">
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