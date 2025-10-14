@props([
    'variant' => 'pending',
    'size' => 'md',
    'uppercase' => false,
])

@php
    $base = 'inline-flex items-center justify-center font-semibold rounded-full';

    $sizes = [
        'sm' => 'px-2.5 py-1 text-xs',
        'md' => 'px-3 py-1.5 text-xs',
        'lg' => 'px-3.5 py-2 text-sm',
    ];

    $variants = [
        'approved' => 'bg-ksu-100 text-ksu-700',
        'pending' => 'bg-yellow-100 text-yellow-800',
        'rejected' => 'bg-red-100 text-red-700',
        'vacant' => 'bg-emerald-100 text-emerald-800',
        'full' => 'bg-slate-200 text-slate-600',
        'info' => 'bg-slate-100 text-slate-700',
    ];

    $classes = implode(' ', [
        $base,
        $sizes[$size] ?? $sizes['md'],
        $variants[$variant] ?? $variants['info'],
        $uppercase ? 'uppercase tracking-wide' : '',
    ]);
@endphp

<span {{ $attributes->merge(['class' => trim($classes)]) }}>
    {{ $slot }}
</span>
