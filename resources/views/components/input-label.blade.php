@props(['value'])

<label {{ $attributes->merge(['class' => 'block text-sm font-semibold text-ksu-800']) }}>
    {{ $value ?? $slot }}
</label>
