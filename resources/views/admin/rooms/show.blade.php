<x-ksu-layout :page-title="'Room '.$room->code">
    @php
        $vacantBeds = $room->beds->where('is_occupied', false)->values();
        $studentOptions = $assignableStudents->map(fn ($tenant) => [
            'id' => $tenant->id,
            'name' => $tenant->full_name ?? $tenant->user->name,
            'student_id' => $tenant->university_id_no,
            'email' => $tenant->user->email,
            'current_room' => $tenant->activeAssignment?->room?->code,
        ]);
    @endphp
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

        @can('assign', $room)
            <x-ksu-card title="Direct Student Assignment" description="Assign a student to a vacant bed without waiting for a reservation request.">
                @if ($vacantBeds->isEmpty())
                    <p class="text-sm text-slate-500">This room has no vacant beds available for direct assignment.</p>
                @elseif ($room->status !== \App\Models\Room::STATUS_OPEN)
                    <p class="text-sm text-slate-500">Direct assignment is only available while the room status is open.</p>
                @elseif ($assignableStudents->isEmpty())
                    <p class="text-sm text-slate-500">No approved students are currently available for assignment.</p>
                @else
                    <form
                        method="POST"
                        action="{{ route('admin.rooms.assign', $room) }}"
                        class="space-y-5"
                        x-data='{
                            query: "",
                            selectedTenantId: "",
                            students: @json($studentOptions),
                            get filteredStudents() {
                                const term = this.query.trim().toLowerCase();
                                return this.students.filter((student) => {
                                    if (!term) return true;

                                    return [student.name, student.student_id, student.email, student.current_room]
                                        .filter(Boolean)
                                        .some((value) => value.toLowerCase().includes(term));
                                }).slice(0, 12);
                            },
                            selectStudent(student) {
                                this.selectedTenantId = String(student.id);
                                this.query = `${student.name} (${student.student_id ?? "No ID"})`;
                            },
                            isSelected(student) {
                                return this.selectedTenantId === String(student.id);
                            }
                        }'
                    >
                        @csrf

                        <div class="grid gap-6 lg:grid-cols-[1.2fr,0.8fr]">
                            <div class="space-y-3">
                                <div class="space-y-2">
                                    <x-input-label for="student_search" value="Search student" />
                                    <x-text-input
                                        id="student_search"
                                        type="text"
                                        x-model="query"
                                        placeholder="Search by name, student ID, or email"
                                        class="w-full"
                                        autocomplete="off"
                                    />
                                    <input type="hidden" name="tenant_id" x-model="selectedTenantId">
                                    <x-input-error :messages="$errors->get('tenant_id')" />
                                </div>

                                <div class="max-h-72 space-y-2 overflow-y-auto rounded-2xl border border-slate-200/70 bg-slate-50/40 p-3">
                                    <template x-for="student in filteredStudents" :key="student.id">
                                        <button
                                            type="button"
                                            class="flex w-full items-start justify-between gap-3 rounded-xl border px-3 py-3 text-left transition"
                                            :class="isSelected(student)
                                                ? 'border-ksu-500 bg-ksu-50'
                                                : 'border-slate-200 bg-white hover:border-ksu-300 hover:bg-ksu-50/40'"
                                            x-on:click="selectStudent(student)"
                                        >
                                            <span class="space-y-1">
                                                <span class="block text-sm font-semibold text-ksu-900" x-text="student.name"></span>
                                                <span class="block text-xs text-slate-500" x-text="student.student_id ?? 'No student ID'"></span>
                                                <span class="block text-xs text-slate-500" x-text="student.email"></span>
                                            </span>
                                            <span class="shrink-0 text-right text-xs text-slate-500" x-text="student.current_room ? `Current room: ${student.current_room}` : 'No active room'"></span>
                                        </button>
                                    </template>

                                    <p x-show="filteredStudents.length === 0" class="text-sm text-slate-500">
                                        No matching students found.
                                    </p>
                                </div>
                            </div>

                            <div class="space-y-4">
                                <div class="space-y-2">
                                    <x-input-label for="bed_id" value="Vacant bed" />
                                    <select
                                        id="bed_id"
                                        name="bed_id"
                                        class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 shadow-sm focus:border-ksu-600 focus:outline-none focus:ring-ksu-400"
                                        required
                                    >
                                        <option value="">Select a bed</option>
                                        @foreach ($vacantBeds as $bed)
                                            <option value="{{ $bed->id }}" @selected(old('bed_id') == $bed->id)>
                                                Bed {{ $bed->bed_label }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <x-input-error :messages="$errors->get('bed_id')" />
                                </div>

                                <div class="rounded-2xl border border-slate-200/70 bg-slate-50/70 px-4 py-3 text-sm text-slate-600">
                                    If the selected student already has an active room assignment, it will be closed automatically and replaced with this room and bed.
                                </div>

                                <x-ksu-button type="submit" class="w-full sm:w-auto">
                                    Assign Student
                                </x-ksu-button>
                            </div>
                        </div>
                    </form>
                @endif
            </x-ksu-card>
        @endcan

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
