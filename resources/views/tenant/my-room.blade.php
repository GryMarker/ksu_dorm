@php
    $assignment = $tenant->activeAssignment;
    $room = $assignment?->room;
    $roommates = collect();

    if ($room) {
        $roommates = $room->assignments()
            ->with(['tenant.user', 'bed'])
            ->where('is_active', true)
            ->get();
    }
@endphp

<x-ksu-layout page-title="My Room">
    <div class="space-y-8">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-3xl font-semibold text-ksu-900 sm:text-4xl">My Room</h1>
                <p class="mt-1 text-sm text-slate-600">Details about your current room assignment and fellow dorm mates.</p>
            </div>
            <x-ksu-button as="a" href="{{ route('tenant.availability') }}" variant="outline">
                Request Transfer
            </x-ksu-button>
        </div>

        @if(! $assignment)
            <x-ksu-card>
                <div class="flex flex-col items-center justify-center gap-4 py-12 text-center">
                    <svg class="h-16 w-16 text-slate-200" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 10.5 12 4l9 6.5v7.5a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2Z"/>
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 21v-6h6v6"/>
                    </svg>
                    <div class="space-y-2">
                        <p class="text-lg font-semibold text-ksu-900">No room assigned yet</p>
                        <p class="text-sm text-slate-500">Once your reservation is approved, your room and bed details will appear here.</p>
                    </div>
                    <x-ksu-button as="a" href="{{ route('tenant.availability') }}">Browse Available Rooms</x-ksu-button>
                </div>
            </x-ksu-card>
        @else
            <div class="grid gap-6 lg:grid-cols-[2fr,3fr]">
                <x-ksu-card title="Assignment Details">
                    <dl class="grid gap-4 text-sm text-slate-600 sm:grid-cols-2">
                        <div>
                            <dt class="font-semibold text-ksu-900">Room</dt>
                            <dd>{{ $room->code }}</dd>
                        </div>
                        <div>
                            <dt class="font-semibold text-ksu-900">Bed</dt>
                            <dd>{{ $assignment->bed->bed_label }}</dd>
                        </div>
                        <div>
                            <dt class="font-semibold text-ksu-900">Building / Floor</dt>
                            <dd>{{ $room->building }} &middot; Floor {{ $room->floor }}</dd>
                        </div>
                        <div>
                            <dt class="font-semibold text-ksu-900">Assigned Since</dt>
                            <dd>{{ optional($assignment->start_date)->format('M d, Y') ?? 'Pending' }}</dd>
                        </div>
                    </dl>
                </x-ksu-card>

                <x-ksu-card title="Roommates" description="Connect and coordinate with the students sharing your space.">
                    @if($roommates->isEmpty())
                        <p class="text-sm text-slate-500">No active roommates at the moment.</p>
                    @else
                        <ul class="space-y-3">
                            @foreach($roommates as $mate)
                                @php
                                    $name = $mate->tenant?->user?->name ?? $mate->tenant?->full_name ?? 'Unknown Tenant';
                                    $initials = collect(explode(' ', $name))->filter()->take(2)->map(fn ($part) => strtoupper(substr($part, 0, 1)))->implode('');
                                    $isSelf = $mate->tenant_id === $tenant->id;
                                @endphp
                                <li class="flex items-center justify-between rounded-2xl border border-slate-200/80 bg-white/70 px-4 py-3">
                                    <div class="flex items-center gap-3">
                                        <span class="flex h-10 w-10 items-center justify-center rounded-full bg-ksu-100 text-sm font-semibold text-ksu-700">
                                            {{ $initials ?: 'T' }}
                                        </span>
                                        <div>
                                            <p class="text-sm font-semibold text-ksu-900">
                                                {{ $name }}
                                                @if($isSelf)
                                                    <span class="ml-2 rounded-full bg-ksu-100 px-2 py-0.5 text-xs font-semibold text-ksu-700">You</span>
                                                @endif
                                            </p>
                                            <p class="text-xs text-slate-500">Bed {{ $mate->bed?->bed_label ?? 'N/A' }}</p>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-2 text-xs text-slate-500">
                                        <svg class="h-4 w-4 text-ksu-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7v10M16 7v10M4 12h16"/>
                                        </svg>
                                        Active since {{ optional($mate->start_date)->format('M d, Y') ?? 'N/A' }}
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </x-ksu-card>
            </div>
        @endif
    </div>
</x-ksu-layout>
