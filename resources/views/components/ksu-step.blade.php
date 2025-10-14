@props([
    'steps' => [],
])

@php
    $stateStyles = [
        'completed' => 'border-ksu-600 bg-ksu-600 text-white',
        'current' => 'border-ksu-600 text-ksu-600 bg-white',
        'upcoming' => 'border-slate-300 text-slate-400 bg-white',
        'rejected' => 'border-crimson text-crimson bg-white',
        'recheck' => 'border-gold text-gold bg-white',
    ];

    $stateIcons = [
        'completed' => '<svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="m5 13 4 4L19 7"/></svg>',
        'current' => '<svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6v6l3 3m6-3a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>',
        'upcoming' => '<svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="1.5"/></svg>',
        'rejected' => '<svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="m15 9-6 6m0-6 6 6"/><circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="1.5"/></svg>',
        'recheck' => '<svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l2.5 2.5"/><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19.5 14.5A8.96 8.96 0 0 1 12 21a9 9 0 1 1 7.5-13.5"/></svg>',
    ];
@endphp

<div class="rounded-2xl border border-slate-200/70 bg-white p-6 shadow-ksu sm:px-8 sm:py-8">
    <div class="flex flex-col gap-6 sm:flex-row sm:items-center sm:gap-4">
        @foreach($steps as $index => $step)
            @php
                $state = $step['state'] ?? 'upcoming';
                $label = $step['label'] ?? "Step " . ($index + 1);
                $hint = $step['hint'] ?? null;
                $style = $stateStyles[$state] ?? $stateStyles['upcoming'];
                $icon = $stateIcons[$state] ?? $stateIcons['upcoming'];
            @endphp

            <div class="flex items-start gap-3 sm:flex-1 sm:flex-col sm:items-center sm:text-center">
                <span class="flex h-12 w-12 items-center justify-center rounded-full border-2 {{ $style }}">
                    {!! $icon !!}
                </span>
                <div class="space-y-1">
                    <p class="text-sm font-semibold text-ksu-900 sm:text-base">{{ $label }}</p>
                    @if($hint)
                        <p class="text-xs text-slate-500 sm:text-sm">{{ $hint }}</p>
                    @endif
                </div>
            </div>

            @if(!$loop->last)
                <div class="hidden h-px flex-1 bg-slate-200 sm:block"></div>
            @endif
        @endforeach
    </div>
</div>
