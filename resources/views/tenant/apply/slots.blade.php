<x-ksu-layout page-title="Interview Slots">
    <div class="space-y-8">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-ksu-900 sm:text-3xl">Book Your Interview Slot</h1>
                <p class="mt-1 text-sm text-slate-600">Choose an interview schedule that works for you. You can rebook until a decision is released.</p>
            </div>
            @if ($currentInterview)
                <x-ksu-badge variant="approved">
                    Booked: {{ $currentInterview->scheduled_at->format('M d, Y \\a\\t h:i A') }}
                </x-ksu-badge>
            @endif
        </div>

        <div class="grid gap-6 md:grid-cols-2">
            <x-ksu-card title="Booking Tips">
                <ul class="space-y-2 text-sm text-slate-600">
                    <li class="flex items-start gap-2">
                        <svg class="mt-0.5 h-4 w-4 text-ksu-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M5 13l4 4L19 7" />
                        </svg>
                        Arrive 15 minutes before your selected schedule and bring a valid student ID.
                    </li>
                    <li class="flex items-start gap-2">
                        <svg class="mt-0.5 h-4 w-4 text-ksu-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M5 13l4 4L19 7" />
                        </svg>
                        Slots marked as full are no longer available. Check back regularly for newly opened sessions.
                    </li>
                    <li class="flex items-start gap-2">
                        <svg class="mt-0.5 h-4 w-4 text-ksu-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M5 13l4 4L19 7" />
                        </svg>
                        If you need to rebook, simply choose another open slot and we will update your reservation.
                    </li>
                </ul>
            </x-ksu-card>

            <x-ksu-card title="Need Assistance?" description="The dorm team is ready to help.">
                <div class="space-y-2 text-sm text-slate-600">
                    <p>For scheduling concerns, contact the Dorm Office at <span class="font-semibold text-ksu-700">0912 345 6789</span> or email <a href="mailto:dorms@ksu.edu.ph" class="text-ksu-600 underline-offset-2 hover:underline">dorms@ksu.edu.ph</a>.</p>
                    <p>Please notify us at least a day in advance if you cannot attend your booked slot.</p>
                </div>
            </x-ksu-card>
        </div>

        @if ($errors->any())
            <div class="rounded-2xl border border-crimson/30 bg-crimson/5 px-5 py-4 text-sm text-crimson">
                {{ $errors->first() }}
            </div>
        @endif

        <x-ksu-table :headers="['Date', 'Time', 'Capacity Left', 'Action']">
            @forelse($slots as $slot)
                @php
                    $remaining = $slot->remaining_capacity;
                    $isCurrent = $currentInterview && $currentInterview->slot_id === $slot->id;
                    $isFull = $remaining <= 0;
                @endphp
                <tr>
                    <td class="px-5 py-4 text-sm font-medium text-ksu-900">
                        {{ $slot->starts_at->format('F d, Y') }}
                    </td>
                    <td class="px-5 py-4 text-sm text-slate-600">
                        {{ $slot->starts_at->format('h:i A') }} &ndash; {{ $slot->ends_at->format('h:i A') }}
                    </td>
                    <td class="px-5 py-4">
                        @if ($isFull)
                            <x-ksu-badge variant="full">Full</x-ksu-badge>
                        @else
                            <x-ksu-badge variant="vacant">
                                {{ $remaining }} of {{ $slot->capacity }}
                            </x-ksu-badge>
                        @endif
                    </td>
                    <td class="px-5 py-4 text-right">
                        @if ($isCurrent)
                            <x-ksu-badge variant="approved">Booked</x-ksu-badge>
                        @elseif ($isFull)
                            <span class="text-sm text-slate-400">Not available</span>
                        @else
                            <form
                                method="POST"
                                action="{{ route('tenant.apply.slot.book', $slot) }}"
                                x-data="{ loading: false }"
                                x-on:submit="if(!loading){ loading = true }"
                                class="inline-flex"
                            >
                                @csrf
                                <x-ksu-button type="submit" size="sm" x-bind:disabled="loading">
                                    <span x-show="!loading">Book Slot</span>
                                    <span x-cloak x-show="loading" class="flex items-center gap-2">
                                        <span class="loading-spinner" aria-hidden="true"></span>
                                        Booking...
                                    </span>
                                </x-ksu-button>
                            </form>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="px-5 py-6 text-center text-sm text-slate-500">
                        No open interview slots at the moment. Please check back later or contact the dorm master.
                    </td>
                </tr>
            @endforelse
        </x-ksu-table>

        <div class="flex justify-end">
            <x-ksu-button as="a" href="{{ route('tenant.apply.status') }}" variant="outline">
                View Application Status
            </x-ksu-button>
        </div>
    </div>
</x-ksu-layout>

