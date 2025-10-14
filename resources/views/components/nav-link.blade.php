@props(['active'])

@php
$classes = ($active ?? false)
            ? 'inline-flex items-center border-b-2 border-ksu-600 px-1 pt-1 text-sm font-semibold text-ksu-600 focus:outline-none focus:border-ksu-600 transition'
            : 'inline-flex items-center border-b-2 border-transparent px-1 pt-1 text-sm font-semibold text-ksu-800 hover:text-ksu-600 hover:border-ksu-400 focus:outline-none focus:text-ksu-600 focus:border-ksu-400 transition';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
