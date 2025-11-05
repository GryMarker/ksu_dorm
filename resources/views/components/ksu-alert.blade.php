@props([
    'type' => 'info',
])

@php
    $variants = [
        'success' => 'border-emerald-200 bg-emerald-50 text-emerald-700',
        'error' => 'border-crimson/40 bg-crimson/5 text-crimson',
        'warning' => 'border-amber-200 bg-amber-50 text-amber-700',
        'info' => 'border-slate-200 bg-white text-slate-700',
    ];

    $iconClasses = [
        'success' => 'text-emerald-500',
        'error' => 'text-crimson',
        'warning' => 'text-amber-500',
        'info' => 'text-ksu-600',
    ];

    $variant = $variants[$type] ?? $variants['info'];
    $iconClass = $iconClasses[$type] ?? $iconClasses['info'];
@endphp

<div {{ $attributes->class(["flex items-start gap-3 rounded-2xl border px-4 py-3 text-sm {$variant}"]) }}>
    <svg class="h-5 w-5 {{ $iconClass }}" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm-.75-10.75a.75.75 0 111.5 0v3.5a.75.75 0 01-1.5 0v-3.5zM10 14.5a.875.875 0 110-1.75.875.875 0 010 1.75z" clip-rule="evenodd" />
    </svg>
    <div class="space-y-1">
        {{ $slot }}
    </div>
</div>
