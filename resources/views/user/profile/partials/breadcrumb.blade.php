<div class="mb-4 flex items-center justify-between">
    <nav class="flex text-sm text-gray-500" aria-label="Breadcrumb">
        <ol class="inline-flex items-center space-x-1 md:space-x-2">
            <li class="inline-flex items-center">
                <a href="/" class="hover:text-blue-600">Home</a>
            </li>
            <li>
                <svg class="w-3 h-3 mx-2 text-gray-400" fill="currentColor" viewBox="0 0 20 20"><path d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 111.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"/></svg>
            </li>
            <li>
                <span class="text-gray-700">Profile</span>
            </li>
        </ol>
    </nav>
    <div class="flex items-center space-x-2">
        <span class="text-xs text-gray-600">Profile Completion</span>
        <div class="w-32 bg-gray-200 rounded-full h-2.5">
            <div class="bg-green-500 h-2.5 rounded-full" style="width: {{ $completionPercentage }}%"></div>
        </div>
        <span class="text-xs font-semibold text-green-700">{{ $completionPercentage }}%</span>
    </div>
</div> 