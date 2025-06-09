@props(['currentBranch'])

<div class="relative">
    <select 
        id="branchSelector"
        class="block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md"
        onchange="window.location.href = '{{ request()->url() }}?branch=' + this.value"
    >
        <option value="">Select Branch</option>
        @foreach($branches as $branch)
            <option 
                value="{{ $branch->id }}" 
                {{ $currentBranch && $currentBranch->id === $branch->id ? 'selected' : '' }}
            >
                {{ $branch->name }}
            </option>
        @endforeach
    </select>

    @if($currentBranch)
        <div class="mt-2 flex items-center space-x-2">
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">
                Current Branch: {{ $currentBranch->name }}
            </span>
            <a href="{{ request()->url() }}" class="text-sm text-gray-600 hover:text-gray-900">
                Exit Branch View
            </a>
        </div>
    @endif
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const branchSelector = document.getElementById('branchSelector');
        if (branchSelector) {
            branchSelector.addEventListener('change', function() {
                const selectedBranch = this.value;
                if (selectedBranch) {
                    window.location.href = '{{ request()->url() }}?branch=' + selectedBranch;
                }
            });
        }
    });
</script>
@endpush 