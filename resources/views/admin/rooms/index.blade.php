<x-ksu-layout page-title="Rooms">
    <div class="space-y-8">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-ksu-900 sm:text-3xl">Room Inventory</h1>
                <p class="text-sm text-slate-600">Monitor active dorm rooms, capacities, and occupancy in real time.</p>
            </div>
            @can('create', App\Models\Room::class)
                <x-ksu-button as="a" href="{{ route('admin.rooms.create') }}">
                    New Room
                </x-ksu-button>
            @endcan
        </div>

        @if (session('status'))
            <div class="rounded-2xl border border-ksu-600/20 bg-ksu-100/60 px-5 py-4 text-sm font-medium text-ksu-800">
                {{ __(session('status')) }}
            </div>
        @endif

        <x-ksu-table :headers="['Code', 'Location', 'Sex', 'Capacity', 'Occupied', 'Assignments', 'Status', '']">
            @forelse ($rooms as $room)
                @php
                    $statusVariant = match ($room->status) {
                        \App\Models\Room::STATUS_OPEN => 'approved',
                        \App\Models\Room::STATUS_MAINTENANCE => 'pending',
                        default => 'full',
                    };
                @endphp
                <tr>
                    <td class="px-5 py-4 text-sm font-semibold text-ksu-900">
                        {{ $room->code }}
                    </td>
                    <td class="px-5 py-4 text-sm text-slate-600">
                        {{ $room->building }} &middot; Floor {{ $room->floor }}
                    </td>
                    <td class="px-5 py-4 text-sm font-medium capitalize text-ksu-800">
                        {{ str_replace('_', ' ', $room->sex) }}
                    </td>
                    <td class="px-5 py-4 text-sm text-slate-600">
                        {{ $room->capacity }}
                    </td>
                    <td class="px-5 py-4 text-sm text-slate-600">
                        {{ $room->occupied_beds_count }}
                    </td>
                    <td class="px-5 py-4 text-sm text-slate-600">
                        {{ $room->active_assignments_count }}
                    </td>
                    <td class="px-5 py-4">
                        <x-ksu-badge :variant="$statusVariant" size="sm">
                            {{ \Illuminate\Support\Str::headline($room->status) }}
                        </x-ksu-badge>
                    </td>
                    <td class="px-5 py-4 text-right">
                        <div class="flex justify-end gap-2">
                            <x-ksu-button as="a" href="{{ route('admin.rooms.show', $room) }}" variant="subtle" size="sm">
                                View
                            </x-ksu-button>
                            @can('update', $room)
                                <x-ksu-button as="a" href="{{ route('admin.rooms.edit', $room) }}" variant="outline" size="sm">
                                    Edit
                                </x-ksu-button>
                            @endcan
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="px-5 py-6 text-center text-sm text-slate-500">
                        No rooms found.
                    </td>
                </tr>
            @endforelse
        </x-ksu-table>
    </div>
</x-ksu-layout>
