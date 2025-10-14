@php
    $kpis = [
        [
            'label' => 'Vacancy Rate',
            'value' => $stats['vacant_beds'],
            'hint' => 'Beds currently open across all dorms',
            'icon' => '<svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 6h16M4 10h16M4 18h16M4 14h16"/></svg>',
            'accent' => 'bg-gold/10 text-gold',
        ],
        [
            'label' => 'Pending Interviews',
            'value' => $stats['pending_interviews'],
            'hint' => 'Awaiting schedule results',
            'icon' => '<svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 7v5l3 3m6-3a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>',
            'accent' => 'bg-ksu-600/10 text-ksu-600',
        ],
        [
            'label' => 'Pending Reservations',
            'value' => $stats['pending_reservations'],
            'hint' => 'Requests awaiting approval',
            'icon' => '<svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M5 13l4 4 10-10"/><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 5.5v-1a2.5 2.5 0 0 1 5 0v1A2.5 2.5 0 1 1 12 5.5Z"/></svg>',
            'accent' => 'bg-crimson/10 text-crimson',
        ],
        [
            'label' => 'Today Check-ins',
            'value' => $stats['today_in'],
            'hint' => 'In-person or RFID logs',
            'icon' => '<svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 3v4m0 10v4m9-9h-4M7 12H3m13.95-5.95-2.828 2.829M7.879 16.121 5.05 18.95m13.9 0-2.829-2.829M7.879 7.879 5.05 5.05"/></svg>',
            'accent' => 'bg-ksu-100 text-ksu-700',
        ],
        [
            'label' => 'Today Check-outs',
            'value' => $stats['today_out'],
            'hint' => 'Departures logged today',
            'icon' => '<svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="m9 16 6-4-6-4v8Z"/><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>',
            'accent' => 'bg-slate-100 text-slate-600',
        ],
        [
            'label' => 'Total Tenants',
            'value' => $stats['total_tenants'],
            'hint' => 'Active residents in the dorm',
            'icon' => '<svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 19a3 3 0 1 0-6 0m9-8v-1a3 3 0 0 0-3-3H9a3 3 0 0 0-3 3v1m12 8V17a3 3 0 0 0-3-3H9a3 3 0 0 0-3 3v2m12 0h2m-2 0H6m-2 0h2"/></svg>',
            'accent' => 'bg-ksu-800/10 text-ksu-800',
        ],
    ];
@endphp

<x-ksu-layout page-title="Admin Dashboard">
    <div class="space-y-10">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-3xl font-semibold text-ksu-900 sm:text-4xl">Dorm Operations Overview</h1>
                <p class="mt-1 text-sm text-slate-600">Monitor key metrics across interviews, reservations, and attendance.</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <x-ksu-button as="a" href="{{ route('admin.rooms.index') }}" size="sm" variant="subtle">Manage Rooms</x-ksu-button>
                <x-ksu-button as="a" href="{{ route('admin.reservations.index') }}" size="sm">Review Reservations</x-ksu-button>
            </div>
        </div>

        <div class="grid gap-6 md:grid-cols-2 xl:grid-cols-3 stats-grid">
            @foreach($kpis as $kpi)
                <x-ksu-card>
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">{{ $kpi['label'] }}</p>
                            <p class="mt-3 text-3xl font-semibold text-ksu-900"><span class="stat-number updated">{{ number_format($kpi['value']) }}</span></p>
                            <p class="text-xs text-slate-500">{{ $kpi['hint'] }}</p>
                        </div>
                        <span class="flex h-10 w-10 items-center justify-center rounded-xl {!! $kpi['accent'] !!}">
                            {!! $kpi['icon'] !!}
                        </span>
                    </div>
                </x-ksu-card>
            @endforeach
        </div>

        <div class="grid gap-6 lg:grid-cols-[3fr,2fr]">
            <x-ksu-card title="Recent Reservations">
                @if ($recentReservations->isEmpty())
                    <p class="text-sm text-slate-500">No recent reservations. You will see new submissions here as they arrive.</p>
                @else
                    <x-ksu-table :headers="['Tenant', 'Room', 'Type', 'Status', 'Requested']">
                        @foreach ($recentReservations as $reservation)
                            @php
                                $statusVariant = match ($reservation->status) {
                                    \App\Models\Reservation::STATUS_APPROVED => 'approved',
                                    \App\Models\Reservation::STATUS_DECLINED => 'rejected',
                                    default => 'pending',
                                };
                            @endphp
                            <tr>
                                <td class="px-5 py-4 text-sm text-ksu-900">
                                    {{ $reservation->tenant->user->name }}
                                </td>
                                <td class="px-5 py-4 text-sm text-slate-600">
                                    {{ $reservation->room->code }}
                                </td>
                                <td class="px-5 py-4 text-sm font-semibold text-ksu-800">
                                    {{ \Illuminate\Support\Str::headline($reservation->type) }}
                                </td>
                                <td class="px-5 py-4">
                                    <x-ksu-badge :variant="$statusVariant" size="sm" uppercase>
                                        {{ strtoupper($reservation->status) }}
                                    </x-ksu-badge>
                                </td>
                                <td class="px-5 py-4 text-sm text-slate-500">
                                    {{ $reservation->requested_at->timezone(config('app.timezone'))->format('M d, Y h:i A') }}
                                </td>
                            </tr>
                        @endforeach
                    </x-ksu-table>
                @endif
            </x-ksu-card>

            <x-ksu-card title="Quick Links">
                <div class="grid gap-3">
                    <x-ksu-button as="a" href="{{ route('admin.interviews.index') }}" full variant="subtle">Interview Queue</x-ksu-button>
                    <x-ksu-button as="a" href="{{ route('admin.attendance.index') }}" full variant="outline">Attendance Reports</x-ksu-button>
                    <x-ksu-button as="a" href="{{ route('admin.rooms.index') }}" full>Manage Room Inventory</x-ksu-button>
                    <x-ksu-button as="a" href="{{ route('admin.reservations.index') }}" full variant="subtle">Pending Reservations</x-ksu-button>
                </div>
            </x-ksu-card>
        </div>
    </div>
</x-ksu-layout>
