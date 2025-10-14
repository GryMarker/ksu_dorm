@props(['active' => false])

@php
$classes = ($active ?? false)
            ? 'block w-full ps-3 pe-4 py-2 border-l-4 border-ksu-600 text-start text-base font-semibold text-ksu-700 bg-ksu-100 focus:outline-none focus:bg-ksu-100 focus:text-ksu-700 transition'
            : 'block w-full ps-3 pe-4 py-2 border-l-4 border-transparent text-start text-base font-medium text-ksu-800 hover:text-ksu-600 hover:bg-ksu-100 hover:border-ksu-400 focus:outline-none focus:text-ksu-600 focus:bg-ksu-100 focus:border-ksu-400 transition';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
