@props([
    'title' => 'AI Analysis Unavailable',
    'message' => 'The AI service is temporarily unavailable. Please try again later.'
])

<div class="bg-gray-50 border border-gray-200 rounded-lg p-6 text-center shadow-sm w-full">
    <div class="mx-auto flex flex-col items-center justify-center">
        <div class="h-12 w-12 rounded-full bg-white flex items-center justify-center mb-4 border border-gray-200 shadow-sm">
            <svg class="h-6 w-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
        </div>
        <h3 class="text-sm font-medium text-gray-900">{{ $title }}</h3>
        <p class="mt-1 text-sm text-gray-500 max-w-sm">{{ $message }}</p>
    </div>
</div>
