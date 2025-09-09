@props([
    'type' => 'button',
    'ariaLabel' => null,
    'ariaDescribedby' => null,
    'ariaExpanded' => null,
    'ariaControls' => null,
    'ariaPressed' => null,
    'role' => null,
    'tabindex' => null,
    'disabled' => false,
    'class' => ''
])

<button 
    type="{{ $type }}"
    @if($ariaLabel) aria-label="{{ $ariaLabel }}" @endif
    @if($ariaDescribedby) aria-describedby="{{ $ariaDescribedby }}" @endif
    @if($ariaExpanded) aria-expanded="{{ $ariaExpanded }}" @endif
    @if($ariaControls) aria-controls="{{ $ariaControls }}" @endif
    @if($ariaPressed) aria-pressed="{{ $ariaPressed }}" @endif
    @if($role) role="{{ $role }}" @endif
    @if($tabindex) tabindex="{{ $tabindex }}" @endif
    @if($disabled) disabled @endif
    class="{{ $class }}"
    {{ $attributes }}
>
    {{ $slot }}
</button> 