<button {{ $attributes->merge([
    'type' => 'submit',
    'class' => 'inline-flex items-center gap-2 rounded-xl bg-crimson px-4 py-2 text-sm font-semibold text-white shadow-ksu transition hover:bg-crimson/90 focus:outline-none focus:ring-2 focus:ring-crimson focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-60',
]) }}>
    {{ $slot }}
</button>
