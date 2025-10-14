@props([
    'variant' => 'solid',
    'size' => 'md',
    'icon' => null,
    'type' => 'button',
    'full' => false,
    'loading' => false,
    'disabled' => false,
    'as' => 'button',
])

@php
    $baseClasses = 'inline-flex items-center justify-center gap-2 rounded-xl font-semibold transition-colors duration-150 focus:outline-none focus:ring-2 focus:ring-ksu-400 focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-60';
    $variants = [
        'solid' => 'bg-ksu-600 text-white hover:bg-ksu-700',
        'outline' => 'border border-ksu-600 text-ksu-700 hover:bg-ksu-100',
        'subtle' => 'bg-ksu-100 text-ksu-700 hover:bg-ksu-100/80',
        'link' => 'text-ksu-600 hover:text-ksu-700 hover:underline border border-transparent',
    ];

    $variantClasses = $variants[$variant] ?? $variants['solid'];

    $sizes = [
        'sm' => 'px-3 py-1.5 text-sm',
        'md' => 'px-4 py-2 text-sm',
        'lg' => 'px-5 py-3 text-base',
    ];

    $sizeClasses = $sizes[$size] ?? $sizes['md'];

    $widthClass = $full ? 'w-full' : '';

    $classes = trim("{$baseClasses} {$variantClasses} {$sizeClasses} {$widthClass}");
@endphp

@if($as === 'a')
    <a
        {{ $attributes->merge(['class' => $classes, 'role' => 'button']) }}
        @if($disabled || $loading) aria-disabled="true" tabindex="-1" @endif
    >
        @if($loading)
            <span class="loading-spinner" aria-hidden="true"></span>
        @elseif($icon)
            <span class="h-4 w-4 text-current">
                {!! $icon !!}
            </span>
        @endif
        <span>{{ $slot }}</span>
    </a>
@else
    <button
        type="{{ $type }}"
        @disabled($disabled || $loading)
        @if($loading) aria-busy="true" @endif
        {{ $attributes->merge(['class' => $classes]) }}
    >
        @if($loading)
            <span class="loading-spinner" aria-hidden="true"></span>
        @elseif($icon)
            <span class="h-4 w-4 text-current">
                {!! $icon !!}
            </span>
        @endif
        <span>{{ $slot }}</span>
    </button>
@endif
