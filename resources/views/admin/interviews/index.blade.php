<x-ksu-layout page-title="Interviews">
    <div class="space-y-8">
        <div>
            <h1 class="text-2xl font-semibold text-ksu-900 sm:text-3xl">Interview Management</h1>
            <p class="mt-1 text-sm text-slate-600">Track scheduled interviews, record decisions, and capture notes for applicants.</p>
        </div>

        <x-ksu-card>
            @if ($interviews->isEmpty())
                <p class="text-sm text-slate-500">No interviews scheduled.</p>
            @else
                <x-ksu-table :headers="['Tenant', 'Schedule', 'Result', 'Notes', 'Action']">
                    @foreach ($interviews as $interview)
                        @php
                            $badgeVariantMap = [
                                'approved' => 'approved',
                                'rejected' => 'rejected',
                                'recheck' => 'pending',
                            ];
                            $badgeVariant = $badgeVariantMap[$interview->result] ?? 'info';
                        @endphp
                        <tr>
                            <td class="px-5 py-4 text-sm text-ksu-900">
                                <div class="font-semibold">{{ $interview->tenant->user->name }}</div>
                                <div class="text-xs text-slate-500">{{ $interview->tenant->university_id_no ?? 'No ID' }}</div>
                            </td>
                            <td class="px-5 py-4 text-sm text-slate-600">
                                {{ $interview->scheduled_at->timezone(config('app.timezone'))->format('M d, Y h:i A') }}
                            </td>
                            <td class="px-5 py-4">
                                <x-ksu-badge :variant="$badgeVariant" size="sm">
                                    {{ \Illuminate\Support\Str::headline($interview->result ?? 'Pending') }}
                                </x-ksu-badge>
                            </td>
                            <td class="px-5 py-4 text-sm text-slate-600">
                                {{ $interview->notes ?? 'No notes yet' }}
                            </td>
                            <td class="px-5 py-4">
                                <div class="space-y-3">
                                    <div class="flex flex-wrap gap-2">
                                        <x-ksu-button
                                            type="button"
                                            variant="outline"
                                            size="sm"
                                            x-on:click="$dispatch('open-modal', 'interview-applicant-{{ $interview->id }}')"
                                        >
                                            View Application
                                        </x-ksu-button>
                                    </div>
                                    <form method="POST" action="{{ route('admin.interviews.result', $interview) }}" class="space-y-2 rounded-2xl border border-slate-200/70 bg-white/80 p-4 shadow-sm">
                                        @csrf
                                        @method('PATCH')
                                        <label class="text-xs font-semibold text-ksu-800">
                                            Decision
                                            <select
                                                name="result"
                                                class="mt-1 block w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 shadow-sm focus:border-ksu-600 focus:outline-none focus:ring-ksu-400"
                                            >
                                                <option value="approved" @selected($interview->result === 'approved')>Approve</option>
                                                <option value="rejected" @selected($interview->result === 'rejected')>Reject</option>
                                                <option value="recheck" @selected($interview->result === 'recheck')>Recheck</option>
                                            </select>
                                        </label>
                                        <label class="text-xs font-semibold text-ksu-800">
                                            Conducted At
                                            <x-text-input
                                                id="conducted_at_{{ $interview->id }}"
                                                name="conducted_at"
                                                type="datetime-local"
                                                :value="optional($interview->conducted_at)->format('Y-m-d\TH:i') ?? now()->format('Y-m-d\TH:i')"
                                            />
                                        </label>
                                        <textarea
                                            name="notes"
                                            rows="2"
                                            placeholder="Notes"
                                            class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 shadow-sm focus:border-ksu-600 focus:outline-none focus:ring-ksu-400"
                                        >{{ $interview->notes }}</textarea>
                                        <x-ksu-button type="submit" size="sm" full>Save</x-ksu-button>
                                    </form>
                                </div>

                            </td>
                        </tr>
                    @endforeach
                </x-ksu-table>

                <div class="mt-4">
                    {{ $interviews->withQueryString()->links() }}
                </div>
            @endif
        </x-ksu-card>

        @foreach ($interviews as $interview)
            <x-modal name="interview-applicant-{{ $interview->id }}" maxWidth="2xl">
                <div class="space-y-4 p-6">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Application</p>
                            <h2 class="text-lg font-semibold text-ksu-900">Student Details</h2>
                        </div>
                        <button
                            type="button"
                            class="text-slate-500 transition hover:text-ksu-700"
                            x-on:click="$dispatch('close-modal', 'interview-applicant-{{ $interview->id }}')"
                            aria-label="Close"
                        >
                            x
                        </button>
                    </div>
                    <x-applicant-details :tenant="$interview->tenant" />
                </div>
            </x-modal>
        @endforeach
    </div>
</x-ksu-layout>
