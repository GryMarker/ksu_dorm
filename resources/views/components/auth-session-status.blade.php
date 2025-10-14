@props(['status'])

@if ($status)
    <div {{ $attributes->merge(['class' => 'rounded-xl border border-ksu-600/30 bg-ksu-100 px-4 py-3 text-sm font-medium text-ksu-700']) }}>
        {{ $status }}
    </div>
@endif
