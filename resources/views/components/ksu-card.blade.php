@props([
    'title' => null,
    'description' => null,
])

<div {{ $attributes->merge(['class' => 'bg-white rounded-2xl border border-slate-200/70 shadow-ksu']) }}>
    @if($title || isset($header))
        <div class="flex flex-col gap-1 border-b border-slate-200/60 px-6 py-5 sm:px-8 sm:py-6">
            @isset($header)
                {{ $header }}
            @else
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <h3 class="text-lg font-semibold text-ksu-800">{{ $title }}</h3>
                        @if($description)
                            <p class="mt-1 text-sm text-slate-600">{{ $description }}</p>
                        @endif
                    </div>
                    @isset($actions)
                        <div class="flex items-center gap-2">
                            {{ $actions }}
                        </div>
                    @endisset
                </div>
            @endisset
        </div>
    @endif

    <div class="px-6 py-5 sm:px-8 sm:py-6">
        {{ $slot }}
    </div>
</div>
