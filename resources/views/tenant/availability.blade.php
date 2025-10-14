@php
    use App\Models\Tenant as TenantModel;

    $buildings = $rooms->pluck('building')->filter()->unique()->sort()->values();
    $genders = $rooms->pluck('gender')->filter()->unique()->sort()->values();
@endphp

<x-ksu-layout page-title="Room Availability" container="false">
    <div
        class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8 py-10"
        x-data="{
            gender: 'all',
            building: 'all',
            showVacantOnly: false,
            openRoom: null,
        }"
        x-on:keydown.escape.window="openRoom = null"
    >
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-3xl font-semibold text-ksu-900 sm:text-4xl">Room Availability</h1>
                <p class="mt-1 text-sm text-slate-600">Browse open rooms and request a bed once your admission is approved.</p>
            </div>
            @if ($tenant->admission_status !== TenantModel::STATUS_APPROVED)
                <x-ksu-badge variant="pending" class="uppercase tracking-wide">
                    Waiting for approval
                </x-ksu-badge>
            @endif
        </div>

        @if ($errors->any())
            <div class="rounded-2xl border border-crimson/30 bg-crimson/5 px-5 py-4 text-sm text-crimson">
                {{ $errors->first() }}
            </div>
        @endif

        <div class="grid gap-6 lg:grid-cols-[2fr,3fr]">
            <x-ksu-card title="Quick Filters">
                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="space-y-2">
                        <label class="text-sm font-semibold text-slate-600">Gender</label>
                        <select
                            class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 shadow-sm focus:border-ksu-600 focus:outline-none focus:ring-ksu-400"
                            x-model="gender"
                        >
                            <option value="all">All</option>
                            @foreach($genders as $gender)
                                <option value="{{ $gender }}">{{ \Illuminate\Support\Str::headline($gender) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="space-y-2">
                        <label class="text-sm font-semibold text-slate-600">Building</label>
                        <select
                            class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 shadow-sm focus:border-ksu-600 focus:outline-none focus:ring-ksu-400"
                            x-model="building"
                        >
                            <option value="all">All</option>
                            @foreach($buildings as $building)
                                <option value="{{ $building }}">{{ $building }}</option>
                            @endforeach
                        </select>
                    </div>
                    <label class="flex items-center gap-3 rounded-2xl border border-slate-200/70 bg-slate-50/60 px-4 py-3 text-sm text-slate-600 sm:col-span-2">
                        <input type="checkbox" class="rounded border-slate-300 text-ksu-600 focus:ring-ksu-400" x-model="showVacantOnly">
                        Show rooms with vacancies only
                    </label>
                </div>
            </x-ksu-card>

            <x-ksu-card title="Reservation Notes">
                <ul class="space-y-3 text-sm text-slate-600">
                    <li class="flex items-start gap-3">
                        <span class="mt-1 inline-flex h-6 w-6 items-center justify-center rounded-full bg-ksu-100 text-xs font-semibold text-ksu-700">1</span>
                        Approved admission is required before submitting a reservation request.
                    </li>
                    <li class="flex items-start gap-3">
                        <span class="mt-1 inline-flex h-6 w-6 items-center justify-center rounded-full bg-ksu-100 text-xs font-semibold text-ksu-700">2</span>
                        Pending reservations must be resolved before you can request another room.
                    </li>
                    <li class="flex items-start gap-3">
                        <span class="mt-1 inline-flex h-6 w-6 items-center justify-center rounded-full bg-ksu-100 text-xs font-semibold text-ksu-700">3</span>
                        You can request a specific bed or let the dorm master assign one for you.
                    </li>
                </ul>
            </x-ksu-card>
        </div>

        <x-ksu-table :headers="['Room', 'Details', 'Vacancy', 'Action']" class="shadow-none">
            @forelse ($rooms as $room)
                @php
                    $vacantBeds = $room->beds->where('is_occupied', false);
                    $vacantCount = $vacantBeds->count();
                    $isEligible = $tenant->admission_status === TenantModel::STATUS_APPROVED;
                @endphp
                <tr
                    x-show="
                        (gender === 'all' || gender === '{{ $room->gender }}') &&
                        (building === 'all' || building === '{{ $room->building }}') &&
                        (!showVacantOnly || {{ $vacantCount }} > 0)
                    "
                    x-cloak
                >
                    <td class="px-5 py-4 text-sm font-semibold text-ksu-900">
                        <div>{{ $room->code }}</div>
                        <div class="text-xs text-slate-500">
                            {{ $room->building }} &bull; Floor {{ $room->floor }}
                        </div>
                    </td>
                    <td class="px-5 py-4 text-sm text-slate-600">
                        <div class="flex flex-wrap gap-2 text-xs">
                            <span class="rounded-full bg-ksu-100 px-3 py-1 font-semibold text-ksu-700">{{ \Illuminate\Support\Str::headline($room->gender) }}</span>
                            <span class="rounded-full bg-slate-100 px-3 py-1 font-semibold text-slate-600">Capacity {{ $room->capacity }}</span>
                            <span class="rounded-full bg-slate-100 px-3 py-1 font-semibold text-slate-600">Occupied {{ $room->occupied_beds_count }}</span>
                        </div>
                    </td>
                    <td class="px-5 py-4">
                        @if ($vacantCount > 0)
                            <x-ksu-badge variant="vacant">{{ $vacantCount }} beds open</x-ksu-badge>
                        @else
                            <x-ksu-badge variant="full">Full</x-ksu-badge>
                        @endif
                    </td>
                    <td class="px-5 py-4">
                        <div class="flex flex-col items-end gap-2 sm:flex-row sm:items-center">
                            <x-ksu-button type="button" variant="subtle" size="sm" @click="openRoom = {{ $room->id }}">
                                View Beds
                            </x-ksu-button>
                            @if (! $isEligible)
                                <span class="text-xs text-slate-400">Approval required</span>
                            @elseif ($vacantCount === 0)
                                <span class="text-xs text-slate-400">No beds available</span>
                            @else
                                <form
                                    method="POST"
                                    action="{{ route('tenant.reservations.store') }}"
                                    class="inline-flex items-center gap-2"
                                    x-data="{ loading: false }"
                                    x-on:submit="if(!loading){ loading = true }"
                                >
                                    @csrf
                                    <input type="hidden" name="room_id" value="{{ $room->id }}">
                                    <select
                                        name="bed_id"
                                        class="hidden rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 shadow-sm focus:border-ksu-600 focus:outline-none focus:ring-ksu-400 sm:block"
                                    >
                                        <option value="">Any available bed</option>
                                        @foreach ($vacantBeds as $bed)
                                            <option value="{{ $bed->id }}">Bed {{ $bed->bed_label }}</option>
                                        @endforeach
                                    </select>
                                    <x-ksu-button type="submit" size="sm" x-bind:disabled="loading">
                                        <span x-show="!loading">Request</span>
                                        <span x-cloak x-show="loading" class="flex items-center gap-2">
                                            <span class="loading-spinner" aria-hidden="true"></span>
                                            Sending...
                                        </span>
                                    </x-ksu-button>
                                </form>
                            @endif
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="px-5 py-6 text-center text-sm text-slate-500">
                        No rooms configured yet.
                    </td>
                </tr>
            @endforelse
        </x-ksu-table>

        @foreach ($rooms as $room)
            @php
                $modalVacantBeds = $room->beds->where('is_occupied', false);
            @endphp
            <div
                x-cloak
                x-show="openRoom === {{ $room->id }}"
                class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/40 px-4 py-8"
            >
                <div
                    class="w-full max-w-3xl rounded-2xl bg-white p-6 shadow-ksu sm:p-8"
                    x-data="{
                        selectedBed: '{{ optional($modalVacantBeds->first())->id }}',
                        anyBed: {{ $modalVacantBeds->isNotEmpty() ? 'false' : 'true' }},
                    }"
                    @click.away="openRoom = null"
                >
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <h2 class="text-2xl font-semibold text-ksu-900">{{ $room->code }}</h2>
                            <p class="text-sm text-slate-600">{{ $room->building }} &bull; Floor {{ $room->floor }} &bull; {{ \Illuminate\Support\Str::headline($room->gender) }}</p>
                        </div>
                        <button type="button" class="text-slate-400 hover:text-slate-600" @click="openRoom = null" aria-label="Close">
                            <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="m15 9-6 6m0-6 6 6"/>
                                <circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="1.5"/>
                            </svg>
                        </button>
                    </div>

                    <div class="mt-6 grid gap-4 sm:grid-cols-2">
                        @foreach ($room->beds as $bed)
                            <button
                                type="button"
                                class="rounded-2xl border px-4 py-3 text-left transition focus:outline-none focus:ring-2 focus:ring-ksu-400"
                                :class="{
                                    'border-ksu-600 bg-ksu-100/70 text-ksu-700': selectedBed === '{{ $bed->id }}',
                                    'border-slate-200 bg-slate-50 text-slate-500 cursor-not-allowed': {{ $bed->is_occupied ? 'true' : 'false' }}
                                }"
                                @click="
                                    if (!{{ $bed->is_occupied ? 'true' : 'false' }}) {
                                        selectedBed = '{{ $bed->id }}';
                                        anyBed = false;
                                    }
                                "
                            >
                                <div class="flex items-center justify-between">
                                    <span class="text-sm font-semibold">Bed {{ $bed->bed_label }}</span>
                                    @if ($bed->is_occupied)
                                        <x-ksu-badge variant="full" size="sm">Occupied</x-ksu-badge>
                                    @else
                                        <x-ksu-badge variant="vacant" size="sm">Vacant</x-ksu-badge>
                                    @endif
                                </div>
                                <p class="mt-2 text-xs text-slate-500">
                                    {{ $bed->is_occupied ? 'Currently assigned to a tenant.' : 'Ready for assignment.' }}
                                </p>
                            </button>
                        @endforeach
                    </div>

                    <form
                        method="POST"
                        action="{{ route('tenant.reservations.store') }}"
                        class="mt-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between"
                        x-data="{ loading: false }"
                        x-on:submit="if(!loading){ loading = true }"
                    >
                        @csrf
                        <input type="hidden" name="room_id" value="{{ $room->id }}">
                        <input type="hidden" name="bed_id" x-bind:value="anyBed ? '' : selectedBed">

                        <label class="flex items-center gap-3 text-sm text-slate-600">
                            <input type="checkbox" class="rounded border-slate-300 text-ksu-600 focus:ring-ksu-400" x-model="anyBed" @change="if (anyBed) { selectedBed = '{{ optional($modalVacantBeds->first())->id }}'; }">
                            Assign any available bed
                        </label>

                        <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                            <x-ksu-button type="button" variant="subtle" @click="openRoom = null" class="sm:w-auto">
                                Close
                            </x-ksu-button>
                            <x-ksu-button type="submit" x-bind:disabled="loading || (!anyBed && !selectedBed)">
                                <span x-show="!loading">Request Bed</span>
                                <span x-cloak x-show="loading" class="flex items-center gap-2">
                                    <span class="loading-spinner" aria-hidden="true"></span>
                                    Sending...
                                </span>
                            </x-ksu-button>
                        </div>
                    </form>
                </div>
            </div>
        @endforeach
    </div>
</x-ksu-layout>

