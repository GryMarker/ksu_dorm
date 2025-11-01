<x-ksu-layout page-title="Interview Slots">
    <div class="space-y-8">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-ksu-900 sm:text-3xl">Interview Slot Management</h1>
                <p class="mt-1 text-sm text-slate-600">Create and monitor interview slots so applicants can self-book times that fit their schedule.</p>
            </div>
            @if(auth()->user()?->isDormMaster())
                <x-ksu-button as="a" href="{{ route('admin.interview-slots.create') }}">
                    New Slot
                </x-ksu-button>
            @endif
        </div>

        <x-ksu-card>
            @if($errors->any())
                <div class="mb-4 rounded-2xl border border-crimson/30 bg-crimson/5 px-4 py-3 text-sm text-crimson">
                    {{ $errors->first() }}
                </div>
            @endif

            @if($slots->isEmpty())
                <p class="text-sm text-slate-500">No interview slots configured. Dorm masters can add one using the “New Slot” button.</p>
            @else
                <x-ksu-table :headers="['Starts', 'Ends', 'Status', 'Capacity', 'Booked', 'Remaining', 'Actions']">
                    @foreach($slots as $slot)
                        @php
                            $booked = $slot->interviews_count;
                            $remaining = max(0, $slot->capacity - $booked);
                            $badgeVariant = $slot->status === 'open' ? 'approved' : 'pending';
                        @endphp
                        <tr>
                            <td class="px-5 py-4 text-sm text-ksu-900">
                                {{ $slot->starts_at->timezone(config('app.timezone'))->format('M d, Y h:i A') }}
                            </td>
                            <td class="px-5 py-4 text-sm text-ksu-900">
                                {{ $slot->ends_at->timezone(config('app.timezone'))->format('M d, Y h:i A') }}
                            </td>
                            <td class="px-5 py-4">
                                <x-ksu-badge :variant="$badgeVariant" size="sm">
                                    {{ \Illuminate\Support\Str::headline($slot->status) }}
                                </x-ksu-badge>
                            </td>
                            <td class="px-5 py-4 text-sm text-slate-600">
                                {{ $slot->capacity }}
                            </td>
                            <td class="px-5 py-4 text-sm text-slate-600">
                                {{ $booked }}
                            </td>
                            <td class="px-5 py-4 text-sm {{ $remaining === 0 ? 'text-crimson font-semibold' : 'text-ksu-800' }}">
                                {{ $remaining }}
                            </td>
                            <td class="px-5 py-4">
                                <div class="flex flex-wrap items-center gap-2">
                                    @if(auth()->user()?->isDormMaster())
                                        <x-ksu-button as="a" href="{{ route('admin.interview-slots.edit', $slot) }}" size="sm" variant="subtle">
                                            Edit
                                        </x-ksu-button>
                                        <form method="POST" action="{{ route('admin.interview-slots.destroy', $slot) }}" onsubmit="return confirm('Delete this slot?');">
                                            @csrf
                                            @method('DELETE')
                                            <x-ksu-button type="submit" size="sm" variant="outline" :disabled="$booked > 0">
                                                Delete
                                            </x-ksu-button>
                                        </form>
                                    @else
                                        <span class="text-xs text-slate-500">View only</span>
                                    @endif
                                </div>
                                @if($booked > 0)
                                    <p class="mt-2 text-xs text-slate-500">Unassign tenants before deleting.</p>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </x-ksu-table>

                <div class="mt-4">
                    {{ $slots->withQueryString()->links() }}
                </div>
            @endif
        </x-ksu-card>
    </div>
</x-ksu-layout>
