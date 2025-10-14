<x-ksu-layout :page-title="'Room '.$room->code">
    <div class="space-y-8">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-ksu-900 sm:text-3xl">Room {{ $room->code }}</h1>
                <p class="text-sm text-slate-600">Located at {{ $room->building }}, floor {{ $room->floor }}{{ $room->wing ? ' · '.$room->wing.' wing' : '' }}.</p>
            </div>
            <div class="flex flex-wrap gap-2">
                @can('update', $room)
                    <x-ksu-button as="a" href="{{ route('admin.rooms.edit', $room) }}" variant="subtle">
                        Edit
                    </x-ksu-button>
                @endcan
                @can('delete', $room)
                    <form method="POST" action="{{ route('admin.rooms.destroy', $room) }}" onsubmit="return confirm('Delete this room?')" class="inline-flex">
                        @csrf
                        @method('DELETE')
                        <x-ksu-button type="submit" variant="outline">
                            Delete
                        </x-ksu-button>
                    </form>
                @endcan
            </div>
        </div>

        <x-ksu-card title="Room Overview">
            <dl class="grid gap-6 text-sm text-slate-600 sm:grid-cols-2">
                <div>
                    <dt class="font-semibold text-ksu-900">Building</dt>
                    <dd class="mt-1">{{ $room->building }}</dd>
                </div>
                <div>
                    <dt class="font-semibold text-ksu-900">Floor</dt>
                    <dd class="mt-1">{{ $room->floor }}</dd>
                </div>
                <div>
                    <dt class="font-semibold text-ksu-900">Wing</dt>
                    <dd class="mt-1">{{ $room->wing ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="font-semibold text-ksu-900">Gender</dt>
                    <dd class="mt-1 capitalize">{{ $room->gender }}</dd>
                </div>
                <div>
                    <dt class="font-semibold text-ksu-900">Capacity</dt>
                    <dd class="mt-1">{{ $room->capacity }}</dd>
                </div>
                <div>
                    <dt class="font-semibold text-ksu-900">Status</dt>
                    <dd class="mt-1">
                        <x-ksu-badge :variant="match ($room->status) {
                            \App\Models\Room::STATUS_OPEN => 'approved',
                            \App\Models\Room::STATUS_MAINTENANCE => 'pending',
                            default => 'full',
                        }">
                            {{ \Illuminate\Support\Str::headline($room->status) }}
                        </x-ksu-badge>
                    </dd>
                </div>
            </dl>
        </x-ksu-card>

        <x-ksu-card title="Beds" description="Monitor occupancy and current assignees.">
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @forelse ($room->beds as $bed)
                    @php
                        $occupied = $bed->is_occupied;
                    @endphp
                    <div class="rounded-2xl border border-slate-200/70 bg-white px-4 py-4 shadow-ksu">
                        <div class="flex items-center justify-between">
                            <p class="text-sm font-semibold text-ksu-900">Bed {{ $bed->bed_label }}</p>
                            <x-ksu-badge :variant="$occupied ? 'full' : 'vacant'" size="sm">
                                {{ $occupied ? 'Occupied' : 'Vacant' }}
                            </x-ksu-badge>
                        </div>
                        @if ($occupied && $bed->occupant)
                            <p class="mt-3 text-sm text-slate-600">
                                {{ $bed->occupant->user->name }}
                            </p>
                        @else
                            <p class="mt-3 text-sm text-slate-500">No tenant assigned.</p>
                        @endif
                    </div>
                @empty
                    <p class="text-sm text-slate-500">No beds configured for this room.</p>
                @endforelse
            </div>
        </x-ksu-card>

        <x-ksu-card title="Assignment History">
            @if ($room->assignments->isEmpty())
                <p class="text-sm text-slate-500">No assignments recorded yet.</p>
            @else
                <x-ksu-table :headers="['Tenant', 'Bed', 'Start Date', 'End Date', 'Active']">
                    @foreach ($room->assignments as $assignment)
                        <tr>
                            <td class="px-5 py-4 text-sm text-ksu-900">
                                {{ $assignment->tenant->user->name }}
                            </td>
                            <td class="px-5 py-4 text-sm text-slate-600">
                                {{ $assignment->bed->bed_label }}
                            </td>
                            <td class="px-5 py-4 text-sm text-slate-600">
                                {{ optional($assignment->start_date)->format('M d, Y') }}
                            </td>
                            <td class="px-5 py-4 text-sm text-slate-600">
                                {{ optional($assignment->end_date)->format('M d, Y') ?? '—' }}
                            </td>
                            <td class="px-5 py-4">
                                <x-ksu-badge :variant="$assignment->is_active ? 'approved' : 'full'" size="sm">
                                    {{ $assignment->is_active ? 'Active' : 'Inactive' }}
                                </x-ksu-badge>
                            </td>
                        </tr>
                    @endforeach
                </x-ksu-table>
            @endif
        </x-ksu-card>
    </div>
</x-ksu-layout>
