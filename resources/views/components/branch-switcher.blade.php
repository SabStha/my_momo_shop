@props(['branches', 'currentBranch'])

<div class="relative" x-data="{ open: false }">
    <button @click="open = !open" class="flex items-center space-x-2 px-3 py-2 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors">
        <i class="fas fa-store text-gray-600"></i>
        <span class="text-gray-700">{{ $currentBranch ? $currentBranch->name : 'Select Branch' }}</span>
        <i class="fas fa-chevron-down text-xs text-gray-500"></i>
    </button>

    <div x-show="open" 
         @click.away="open = false" 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="transform opacity-0 scale-95"
         x-transition:enter-end="transform opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="transform opacity-100 scale-100"
         x-transition:leave-end="transform opacity-0 scale-95"
         class="absolute left-0 mt-2 w-56 bg-white rounded-lg shadow-lg z-50 border border-gray-200">
        <div class="py-1">
            @foreach($branches as $branch)
                <a href="{{ route('admin.dashboard', ['branch' => $branch->id]) }}" 
                   class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 {{ $currentBranch && $currentBranch->id === $branch->id ? 'bg-gray-100' : '' }}">
                    <i class="fas fa-store text-gray-400 mr-2"></i>
                    <span>{{ $branch->name }}</span>
                    @if($currentBranch && $currentBranch->id === $branch->id)
                        <i class="fas fa-check text-green-500 ml-auto"></i>
                    @endif
                </a>
            @endforeach
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const branchLinks = document.querySelectorAll('[href*="admin/dashboard"]');
    branchLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const branchId = this.getAttribute('href').split('branch=')[1];
            window.location.href = `/admin/dashboard?branch=${branchId}`;
        });
    });
});
</script> 