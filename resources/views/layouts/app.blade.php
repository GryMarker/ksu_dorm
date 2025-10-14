<x-ksu-layout>
    <div class="space-y-8">
        @isset($header)
            <div class="rounded-2xl border border-slate-200/70 bg-white px-6 py-5 shadow-ksu sm:px-8 sm:py-6">
                {{ $header }}
            </div>
        @endisset

        {{ $slot }}
    </div>
</x-ksu-layout>
