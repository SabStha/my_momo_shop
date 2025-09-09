@props([
    'src',
    'alt' => '',
    'width' => null,
    'height' => null,
    'class' => '',
    'lazy' => true,
    'priority' => false,
    'ariaLabel' => null,
    'ariaDescribedby' => null
])

@php
    // Generate optimized image paths
    $pathInfo = pathinfo($src);
    $basePath = $pathInfo['dirname'] . '/' . $pathInfo['filename'];
    $extension = $pathInfo['extension'] ?? 'jpg';
    
    // Check if optimized versions exist
    $avifPath = $basePath . '.avif';
    $webpPath = $basePath . '.webp';
    $originalPath = $src;
    
    // Determine if optimized versions exist (we'll assume they do for now)
    $hasAvif = true;
    $hasWebp = true;
@endphp

<picture>
    @if($hasAvif)
        <source srcset="{{ $avifPath }}" type="image/avif">
    @endif
    @if($hasWebp)
        <source srcset="{{ $webpPath }}" type="image/webp">
    @endif
    <img 
        src="{{ $originalPath }}" 
        alt="{{ $alt }}"
        @if($width) width="{{ $width }}" @endif
        @if($height) height="{{ $height }}" @endif
        @if($lazy && !$priority) loading="lazy" @endif
        @if($priority) fetchpriority="high" @endif
        @if($ariaLabel) aria-label="{{ $ariaLabel }}" @endif
        @if($ariaDescribedby) aria-describedby="{{ $ariaDescribedby }}" @endif
        decoding="async"
        class="{{ $class }}"
        {{ $attributes }}
    >
</picture> 