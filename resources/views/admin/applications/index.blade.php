@php
    use Illuminate\Support\Str;
@endphp

<x-ksu-layout page-title="Applications">
    <div class="space-y-8">
        @if (session('status'))
            <div class="rounded-2xl border border-ksu-600/20 bg-ksu-100/60 px-5 py-4 text-sm font-medium text-ksu-800">
                {{ __(session('status')) }}
            </div>
        @endif
        @if ($errors->any())
            <div class="rounded-2xl border border-crimson/20 bg-crimson/5 px-5 py-4 text-sm text-crimson">
                {{ $errors->first() }}
            </div>
        @endif

        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-ksu-900 sm:text-3xl">Pending Applications</h1>
                <p class="mt-1 text-sm text-slate-600">Review student submissions, approve eligible KSU students, and set an interview schedule.</p>
            </div>
            <x-ksu-badge variant="info">
                {{ $tenants->total() }} waiting
            </x-ksu-badge>
        </div>

        <x-ksu-card>
            @if($tenants->isEmpty())
                <p class="text-sm text-slate-500">No pending applications at the moment.</p>
            @else
                <div class="space-y-6">
                    @foreach($tenants as $tenant)
                        <div class="rounded-2xl border border-slate-200/70 bg-slate-50/40 p-5 shadow-sm">
                            <div class="flex flex-col gap-4 lg:flex-row lg:gap-6">
                                <div class="flex-1 space-y-3">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <p class="text-lg font-semibold text-ksu-900">{{ $tenant->full_name ?? $tenant->user->name }}</p>
                                        <x-ksu-badge variant="pending" size="sm" class="uppercase">
                                            {{ Str::headline($tenant->onboarding_status) }}
                                        </x-ksu-badge>
                                        <x-ksu-button
                                            type="button"
                                            size="sm"
                                            variant="outline"
                                            x-data
                                            x-on:click="$dispatch('open-modal', 'application-form-{{ $tenant->id }}')"
                                        >
                                            View Form
                                        </x-ksu-button>
                                    </div>
                                    <dl class="grid gap-2 text-sm text-slate-600 sm:grid-cols-2">
                                        <div class="flex items-center justify-between rounded-xl bg-white px-3 py-2">
                                            <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Student ID</dt>
                                            <dd class="font-semibold text-ksu-900">{{ $tenant->university_id_no }}</dd>
                                        </div>
                                        <div class="flex items-center justify-between rounded-xl bg-white px-3 py-2">
                                            <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Course</dt>
                                            <dd class="font-semibold text-ksu-900">{{ $tenant->program }}</dd>
                                        </div>
                                        <div class="flex items-center justify-between rounded-xl bg-white px-3 py-2">
                                            <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Year Level</dt>
                                            <dd class="font-semibold text-ksu-900">{{ $tenant->year_level }}</dd>
                                        </div>
                                        <div class="flex items-center justify-between rounded-xl bg-white px-3 py-2">
                                            <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Phone</dt>
                                            <dd class="font-semibold text-ksu-900">{{ $tenant->cellphone ?? $tenant->phone }}</dd>
                                        </div>
                                    </dl>
                                    <p class="text-xs text-slate-500">Submitted {{ optional($tenant->policy_accepted_at ?? $tenant->updated_at)->diffForHumans() }}</p>
                                </div>

                                <div class="w-full max-w-xl space-y-3 rounded-2xl border border-ksu-200 bg-white p-4">
                                    <div class="flex items-center justify-between">
                                        <p class="text-sm font-semibold text-ksu-900">Approve &amp; Schedule Interview</p>
                                        <x-ksu-badge variant="info" size="sm">Email notification</x-ksu-badge>
                                    </div>
                                    <form method="POST" action="{{ route('admin.applications.approve', $tenant) }}" class="space-y-3">
                                        @csrf
                                        @method('PATCH')

                                        <div class="space-y-2">
                                            <x-input-label for="slot_id_{{ $tenant->id }}" value="Choose open slot (optional)" />
                                            <select
                                                id="slot_id_{{ $tenant->id }}"
                                                name="slot_id"
                                                class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 shadow-sm focus:border-ksu-600 focus:outline-none focus:ring-ksu-400"
                                            >
                                                <option value="">-- No slot selected --</option>
                                                @foreach($openSlots as $slot)
                                                    @php
                                                        $remaining = max(0, $slot->capacity - $slot->interviews_count);
                                                    @endphp
                                                    <option value="{{ $slot->id }}">
                                                        {{ $slot->starts_at->format('M d, Y h:i A') }} ({{ $remaining }} left)
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="space-y-2">
                                            <x-input-label for="scheduled_at_{{ $tenant->id }}" value="Manual schedule (if no slot)" />
                                            <x-text-input
                                                id="scheduled_at_{{ $tenant->id }}"
                                                name="scheduled_at"
                                                type="datetime-local"
                                                class="w-full"
                                            />
                                            <p class="text-xs text-slate-500">Required when no slot is selected.</p>
                                        </div>

                                        <div class="space-y-2">
                                            <x-input-label for="notes_{{ $tenant->id }}" value="Notes to applicant (optional)" />
                                            <textarea
                                                id="notes_{{ $tenant->id }}"
                                                name="notes"
                                                rows="2"
                                                class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 shadow-sm focus:border-ksu-600 focus:outline-none focus:ring-ksu-400"
                                            >{{ old('notes') }}</textarea>
                                        </div>

                                        <div class="rounded-xl border border-slate-200/70 bg-slate-50/70 px-3 py-2 text-xs text-slate-600">
                                            Email will be sent to the student automatically after approval.
                                        </div>

                                        <div class="flex flex-wrap gap-2">
                                            <x-ksu-button type="submit">Approve &amp; Notify</x-ksu-button>
                                            <x-ksu-button
                                                type="submit"
                                                name="skip"
                                                value="1"
                                                class="hidden"
                                            />
                                        </div>
                                    </form>

                                    <form method="POST" action="{{ route('admin.applications.reject', $tenant) }}" class="space-y-2 border-t border-slate-200 pt-3">
                                        @csrf
                                        @method('PATCH')
                                        <x-input-label for="reject_notes_{{ $tenant->id }}" value="Reject with note (optional)" />
                                        <textarea
                                            id="reject_notes_{{ $tenant->id }}"
                                            name="notes"
                                            rows="2"
                                            class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 shadow-sm focus:border-ksu-600 focus:outline-none focus:ring-ksu-400"
                                        ></textarea>
                                        <div class="text-xs text-slate-600">
                                            Email log is saved automatically.
                                        </div>
                                        <x-ksu-button type="submit" variant="outline" class="text-crimson border-crimson/40 hover:border-crimson hover:text-crimson">Reject</x-ksu-button>
                                    </form>
                                </div>
                            </div>

                            <x-modal name="application-form-{{ $tenant->id }}" maxWidth="2xl">
                                <div class="space-y-4 p-6 sm:p-8">
                                    <div class="flex items-start justify-between gap-4 border-b border-slate-200 pb-4">
                                        <div>
                                            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Application Form</p>
                                            <h2 class="text-xl font-semibold text-ksu-900">{{ $tenant->full_name ?? $tenant->user->name }}</h2>
                                        </div>
                                        <x-ksu-button
                                            type="button"
                                            size="sm"
                                            variant="outline"
                                            x-data
                                            x-on:click="$dispatch('close-modal', 'application-form-{{ $tenant->id }}')"
                                        >
                                            Close
                                        </x-ksu-button>
                                    </div>

                                    <div class="max-h-[75vh] overflow-y-auto pr-1">
                                        <x-applicant-details :tenant="$tenant" />
                                    </div>
                                </div>
                            </x-modal>
                        </div>
                    @endforeach
                </div>

                <div class="mt-6">
                    {{ $tenants->links() }}
                </div>
            @endif
        </x-ksu-card>
    </div>
</x-ksu-layout>
