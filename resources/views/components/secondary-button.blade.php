<button {{ $attributes->merge([
    'type' => 'button',
    'class' => 'inline-flex items-center gap-2 rounded-xl border border-ksu-600 bg-white px-4 py-2 text-sm font-semibold text-ksu-700 shadow-sm transition hover:bg-ksu-100 focus:outline-none focus:ring-2 focus:ring-ksu-400 focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-60',
]) }}>
    {{ $slot }}
</button>
