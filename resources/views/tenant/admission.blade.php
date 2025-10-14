<x-ksu-layout page-title="Admission Form">
    <div class="space-y-8">
        @if (session('status'))
            <div class="rounded-2xl border border-ksu-600/20 bg-ksu-100/60 px-5 py-4 text-sm font-medium text-ksu-800">
                {{ __(session('status')) }}
            </div>
        @endif

        <div>
            <h1 class="text-2xl font-semibold text-ksu-900 sm:text-3xl">Dorm Admission Details</h1>
            <p class="mt-1 text-sm text-slate-600">Keep your academic and contact information up to date so the dorm team can process your admission smoothly.</p>
        </div>

        <x-ksu-card>
            <form method="POST" action="{{ route('tenant.admission.update') }}" class="space-y-6">
                @csrf
                <div class="grid gap-5 sm:grid-cols-2">
                    <div class="space-y-2">
                        <x-input-label for="type" value="Applicant Type" />
                        <select
                            id="type"
                            name="type"
                            class="rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 shadow-sm focus:border-ksu-600 focus:outline-none focus:ring-ksu-400"
                        >
                            <option value="student" @selected($tenant->type === 'student')>Student</option>
                            <option value="employee" @selected($tenant->type === 'employee')>Employee</option>
                        </select>
                        <x-input-error :messages="$errors->get('type')" />
                    </div>

                    <div class="space-y-2">
                        <x-input-label for="university_id_no" value="University ID" />
                        <x-text-input id="university_id_no" name="university_id_no" type="text" :value="old('university_id_no', $tenant->university_id_no)" />
                        <x-input-error :messages="$errors->get('university_id_no')" />
                    </div>

                    <div class="space-y-2">
                        <x-input-label for="program" value="Program" />
                        <x-text-input id="program" name="program" type="text" :value="old('program', $tenant->program)" />
                    </div>

                    <div class="space-y-2">
                        <x-input-label for="year_level" value="Year Level" />
                        <x-text-input id="year_level" name="year_level" type="text" :value="old('year_level', $tenant->year_level)" />
                    </div>

                    <div class="space-y-2">
                        <x-input-label for="phone" value="Phone" />
                        <x-text-input id="phone" name="phone" type="text" :value="old('phone', $tenant->phone)" />
                        <x-input-error :messages="$errors->get('phone')" />
                    </div>

                    <div class="space-y-2">
                        <x-input-label for="emergency_contact_name" value="Emergency Contact Name" />
                        <x-text-input id="emergency_contact_name" name="emergency_contact_name" type="text" :value="old('emergency_contact_name', $tenant->emergency_contact_name)" />
                        <x-input-error :messages="$errors->get('emergency_contact_name')" />
                    </div>

                    <div class="space-y-2">
                        <x-input-label for="emergency_contact_phone" value="Emergency Contact Phone" />
                        <x-text-input id="emergency_contact_phone" name="emergency_contact_phone" type="text" :value="old('emergency_contact_phone', $tenant->emergency_contact_phone)" />
                        <x-input-error :messages="$errors->get('emergency_contact_phone')" />
                    </div>

                    <div class="sm:col-span-2 space-y-2">
                        <x-input-label for="medical_notes" value="Medical Notes" />
                        <textarea
                            id="medical_notes"
                            name="medical_notes"
                            rows="3"
                            class="rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 shadow-sm focus:border-ksu-600 focus:outline-none focus:ring-ksu-400"
                        >{{ old('medical_notes', $tenant->medical_notes) }}</textarea>
                    </div>

                    <div class="sm:col-span-2 space-y-2">
                        <x-input-label for="admission_form_additional" value="Additional Details" />
                        <textarea
                            id="admission_form_additional"
                            name="admission_form[additional]"
                            rows="3"
                            class="rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 shadow-sm focus:border-ksu-600 focus:outline-none focus:ring-ksu-400"
                        >{{ data_get($tenant->admission_form_json, 'additional') }}</textarea>
                        <p class="text-xs text-slate-500">Use this area for any other notes or requirements.</p>
                    </div>
                </div>

                <div class="flex flex-wrap gap-3">
                    <x-ksu-button type="submit" name="action" value="save" variant="subtle">
                        Save Draft
                    </x-ksu-button>
                    <x-ksu-button type="submit" name="action" value="submit">
                        Submit for Interview
                    </x-ksu-button>
                </div>
            </form>
        </x-ksu-card>

        <x-ksu-card title="Schedule Interview">
            <form method="POST" action="{{ route('tenant.interview.schedule') }}" class="space-y-4">
                @csrf
                <div class="space-y-2">
                    <x-input-label for="scheduled_at" value="Preferred Schedule" />
                    <x-text-input
                        id="scheduled_at"
                        name="scheduled_at"
                        type="datetime-local"
                        :value="old('scheduled_at', optional($upcomingInterview)->scheduled_at?->format('Y-m-d\TH:i'))"
                    />
                    <x-input-error :messages="$errors->get('scheduled_at')" />
                </div>

                <div class="space-y-2">
                    <x-input-label for="interview_notes" value="Notes" />
                    <textarea
                        id="interview_notes"
                        name="notes"
                        rows="3"
                        class="rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 shadow-sm focus:border-ksu-600 focus:outline-none focus:ring-ksu-400"
                    >{{ old('notes', optional($upcomingInterview)->notes) }}</textarea>
                </div>

                <x-ksu-button type="submit">
                    Save Schedule
                </x-ksu-button>
            </form>
        </x-ksu-card>
    </div>
</x-ksu-layout>
