@props([
    'href' => '#',
    'ariaLabel' => null,
    'ariaDescribedby' => null,
    'ariaExpanded' => null,
    'ariaControls' => null,
    'ariaCurrent' => null,
    'role' => null,
    'tabindex' => null,
    'target' => null,
    'rel' => null,
    'class' => ''
])

<a 
    href="{{ $href }}"
    @if($ariaLabel) aria-label="{{ $ariaLabel }}" @endif
    @if($ariaDescribedby) aria-describedby="{{ $ariaDescribedby }}" @endif
    @if($ariaExpanded) aria-expanded="{{ $ariaExpanded }}" @endif
    @if($ariaControls) aria-controls="{{ $ariaControls }}" @endif
    @if($ariaCurrent) aria-current="{{ $ariaCurrent }}" @endif
    @if($role) role="{{ $role }}" @endif
    @if($tabindex) tabindex="{{ $tabindex }}" @endif
    @if($target) target="{{ $target }}" @endif
    @if($rel) rel="{{ $rel }}" @endif
    class="{{ $class }}"
    {{ $attributes }}
>
    {{ $slot }}
</a> 