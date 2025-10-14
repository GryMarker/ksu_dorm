<x-ksu-layout page-title="Pending Reservations">
    <div class="space-y-8">
        <div>
            <h1 class="text-2xl font-semibold text-ksu-900 sm:text-3xl">Pending Reservations</h1>
            <p class="mt-1 text-sm text-slate-600">Review new room and transfer requests, assign beds, and add notes for coordination.</p>
        </div>

        @if (session('status'))
            <div class="rounded-2xl border border-ksu-600/20 bg-ksu-100/60 px-5 py-4 text-sm font-medium text-ksu-800">
                {{ __(session('status')) }}
            </div>
        @endif

        @if ($errors->any())
            <div class="rounded-2xl border border-crimson/30 bg-crimson/5 px-5 py-4 text-sm text-crimson">
                {{ $errors->first() }}
            </div>
        @endif

        <x-ksu-card>
            @if ($reservations->isEmpty())
                <p class="text-sm text-slate-500">No pending reservations at the moment.</p>
            @else
                <x-ksu-table :headers="['Tenant', 'Room', 'Type', 'Requested', 'Preferred Bed', 'Decision']">
                    @foreach ($reservations as $reservation)
                        @php
                            $vacantBeds = $reservation->room->beds->where('is_occupied', false);
                        @endphp
                        <tr>
                            <td class="px-5 py-4 text-sm text-ksu-900">
                                <div class="font-semibold">{{ $reservation->tenant->user->name }}</div>
                                <div class="text-xs text-slate-500">{{ $reservation->tenant->university_id_no ?? 'No ID' }}</div>
                            </td>
                            <td class="px-5 py-4 text-sm text-slate-600">
                                <div>{{ $reservation->room->code }}</div>
                                <div class="text-xs text-slate-500">Vacant beds: {{ $vacantBeds->count() }}</div>
                            </td>
                            <td class="px-5 py-4 text-sm font-semibold capitalize text-ksu-800">
                                {{ str_replace('_', ' ', $reservation->type) }}
                            </td>
                            <td class="px-5 py-4 text-sm text-slate-600">
                                {{ $reservation->requested_at->timezone(config('app.timezone'))->format('M d, Y h:i A') }}
                            </td>
                            <td class="px-5 py-4 text-sm text-slate-600">
                                {{ optional($reservation->bed)->bed_label ?? 'Any' }}
                            </td>
                            <td class="px-5 py-4">
                                <div class="grid gap-3 sm:grid-cols-2">
                                    <form method="POST" action="{{ route('admin.reservations.approve', $reservation) }}" class="space-y-2 rounded-2xl border border-ksu-600/20 bg-ksu-100/40 p-4">
                                        @csrf
                                        @method('PATCH')
                                        <label class="text-xs font-semibold text-ksu-800">
                                            Assign Bed
                                            <select
                                                name="bed_id"
                                                class="mt-1 block w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 shadow-sm focus:border-ksu-600 focus:outline-none focus:ring-ksu-400"
                                            >
                                                <option value="">Auto assign</option>
                                                @foreach ($vacantBeds as $bed)
                                                    <option value="{{ $bed->id }}">Bed {{ $bed->bed_label }}</option>
                                                @endforeach
                                            </select>
                                        </label>
                                        <textarea
                                            name="notes"
                                            rows="2"
                                            placeholder="Notes"
                                            class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 shadow-sm focus:border-ksu-600 focus:outline-none focus:ring-ksu-400"
                                        >{{ old('notes', $reservation->notes) }}</textarea>
                                        <x-ksu-button type="submit" size="sm" full>
                                            Approve
                                        </x-ksu-button>
                                    </form>

                                    <form method="POST" action="{{ route('admin.reservations.decline', $reservation) }}" class="space-y-2 rounded-2xl border border-crimson/20 bg-crimson/5 p-4">
                                        @csrf
                                        @method('PATCH')
                                        <textarea
                                            name="notes"
                                            rows="3"
                                            placeholder="Reason for decline"
                                            class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 shadow-sm focus:border-ksu-600 focus:outline-none focus:ring-ksu-400"
                                        ></textarea>
                                        <x-ksu-button type="submit" variant="outline" size="sm" full>
                                            Decline
                                        </x-ksu-button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </x-ksu-table>

                <div class="mt-4">
                    {{ $reservations->withQueryString()->links() }}
                </div>
            @endif
        </x-ksu-card>
    </div>
</x-ksu-layout>
