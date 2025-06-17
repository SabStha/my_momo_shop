@props(['type' => 'info', 'title', 'message', 'timestamp' => null])

@php
    $typeClasses = [
        'info' => 'bg-blue-50 border-blue-200 text-blue-800',
        'warning' => 'bg-yellow-50 border-yellow-200 text-yellow-800',
        'danger' => 'bg-red-50 border-red-200 text-red-800',
        'success' => 'bg-green-50 border-green-200 text-green-800'
    ];

    $iconClasses = [
        'info' => 'fas fa-info-circle text-blue-500',
        'warning' => 'fas fa-exclamation-triangle text-yellow-500',
        'danger' => 'fas fa-exclamation-circle text-red-500',
        'success' => 'fas fa-check-circle text-green-500'
    ];
@endphp

<div class="notification-card {{ $typeClasses[$type] }} border rounded-lg p-4 mb-3 shadow-sm">
    <div class="flex items-start">
        <div class="flex-shrink-0">
            <i class="{{ $iconClasses[$type] }} text-xl"></i>
        </div>
        <div class="ml-3 flex-1">
            <h4 class="text-sm font-semibold">{{ $title }}</h4>
            <p class="text-sm mt-1">{{ $message }}</p>
            @if($timestamp)
                <p class="text-xs mt-2 text-gray-500">{{ $timestamp }}</p>
            @endif
        </div>
        <button onclick="this.parentElement.parentElement.remove()" class="ml-4 flex-shrink-0 text-gray-400 hover:text-gray-500">
            <i class="fas fa-times"></i>
        </button>
    </div>
</div> 