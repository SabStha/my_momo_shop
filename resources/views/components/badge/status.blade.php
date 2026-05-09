@props(['status'])

@php
    $statusInput = strtolower($status ?? '');
    $colorClass = match($statusInput) {
        'active', 'completed', 'ready', 'success', 'published' => 'bg-green-100 text-green-800',
        'inactive', 'cancelled', 'failed', 'error', 'banned' => 'bg-red-100 text-red-800',
        'pending', 'waiting', 'processing' => 'bg-yellow-100 text-yellow-800',
        'draft', 'archived', 'paused' => 'bg-gray-100 text-gray-800',
        default => 'bg-blue-100 text-blue-800',
    };
@endphp

<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $colorClass }}">
    {{ ucfirst($status) }}
</span>
