<button {{ $attributes->merge([
    'type' => 'submit',
    'class' => 'inline-flex items-center gap-2 rounded-xl bg-ksu-600 px-4 py-2 text-sm font-semibold text-white shadow-ksu transition hover:bg-ksu-700 focus:outline-none focus:ring-2 focus:ring-ksu-400 focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-60',
]) }}>
    {{ $slot }}
</button>
