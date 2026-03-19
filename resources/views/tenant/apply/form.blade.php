@php
    use App\Models\Tenant as TenantModel;

    $status = $tenant->onboarding_status ?? TenantModel::STATUS_DRAFT;

    $steps = [
        [
            'label' => 'Draft Application',
            'hint' => 'Complete your personal and guardian details.',
            'state' => $status === TenantModel::STATUS_DRAFT ? 'current' : 'completed',
        ],
        [
            'label' => 'Interview',
            'hint' => 'Book your schedule and attend the screening.',
            'state' => match ($status) {
                TenantModel::STATUS_FOR_INTERVIEW => 'current',
                TenantModel::STATUS_FOR_APPROVAL, TenantModel::STATUS_APPROVED, TenantModel::STATUS_REJECTED, TenantModel::STATUS_RECHECK => 'completed',
                default => 'upcoming',
            },
        ],
        [
            'label' => 'Decision',
            'hint' => 'Receive the dorm admission result.',
            'state' => match ($status) {
                TenantModel::STATUS_FOR_APPROVAL => 'current',
                TenantModel::STATUS_APPROVED => 'completed',
                TenantModel::STATUS_REJECTED => 'rejected',
                TenantModel::STATUS_RECHECK => 'recheck',
                default => 'upcoming',
            },
        ],
    ];
@endphp

<x-ksu-layout page-title="Application Form">
    <div class="space-y-8">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-ksu-900 sm:text-3xl">Dorm Admission Application</h1>
                <p class="mt-1 text-sm text-slate-600">Update your information and save as you progress through the admission steps.</p>
            </div>
            <x-ksu-badge :variant="$status === TenantModel::STATUS_APPROVED ? 'approved' : ($status === TenantModel::STATUS_REJECTED ? 'rejected' : 'pending')" class="uppercase tracking-wide">
                {{ \Illuminate\Support\Str::headline($status) }}
            </x-ksu-badge>
        </div>

        <x-ksu-step :steps="$steps" />

        <x-ksu-card>
            <form
                method="POST"
                action="{{ route('tenant.apply.submit') }}"
                class="space-y-10"
                x-data="{ loading: false }"
                x-on:submit="if (!loading) { loading = true }"
            >
                @csrf

                <div class="grid gap-8 lg:grid-cols-[2fr,1fr]">
                    <div class="space-y-8">
                        <div class="grid gap-6 md:grid-cols-2">
                            <div class="space-y-2">
                                <x-input-label for="full_name" value="Full Name" />
                                <x-text-input id="full_name" name="full_name" type="text" class="mt-1 block w-full" :value="old('full_name', $tenant->full_name)" required autofocus />
                                <x-input-error :messages="$errors->get('full_name')" />
                            </div>
                            <div class="space-y-2">
                                <x-input-label for="nickname" value="Nickname" />
                                <x-text-input id="nickname" name="nickname" type="text" class="mt-1 block w-full" :value="old('nickname', $tenant->nickname)" />
                                <x-input-error :messages="$errors->get('nickname')" />
                            </div>
                            <div class="space-y-2">
                                <x-input-label for="gender" value="Gender" />
                                <select
                                    id="gender"
                                    name="gender"
                                    class="mt-1 block w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 shadow-sm focus:border-ksu-600 focus:outline-none focus:ring-ksu-400"
                                    required
                                >
                                    <option value="" disabled @selected(!old('gender', $tenant->gender))>Select gender</option>
                                    <option value="male" @selected(old('gender', $tenant->gender) === 'male')>Male</option>
                                    <option value="female" @selected(old('gender', $tenant->gender) === 'female')>Female</option>
                                </select>
                                <x-input-error :messages="$errors->get('gender')" />
                            </div>
                            <div class="space-y-2">
                                <x-input-label for="dob" value="Date of Birth" />
                                <x-text-input id="dob" name="dob" type="date" class="mt-1 block w-full" :value="old('dob', optional($tenant->dob)->format('Y-m-d'))" required />
                                <x-input-error :messages="$errors->get('dob')" />
                            </div>
                            <div class="space-y-2">
                                <x-input-label for="age" value="Age" />
                                <x-text-input id="age" name="age" type="number" min="15" class="mt-1 block w-full" :value="old('age', $tenant->age)" required />
                                <x-input-error :messages="$errors->get('age')" />
                            </div>
                            <div class="space-y-2 md:col-span-2">
                                <x-input-label for="home_address" value="Home Address" />
                                <textarea id="home_address" name="home_address" rows="3" class="mt-1 block w-full rounded-xl border border-slate-300 bg-white p-3 text-sm text-slate-700 shadow-sm focus:border-ksu-600 focus:outline-none focus:ring-ksu-400" required>{{ old('home_address', $tenant->home_address) }}</textarea>
                                <x-input-error :messages="$errors->get('home_address')" />
                            </div>
                            <div class="space-y-2">
                                <x-input-label for="place_of_birth" value="Place of Birth" />
                                <x-text-input id="place_of_birth" name="place_of_birth" type="text" class="mt-1 block w-full" :value="old('place_of_birth', $tenant->place_of_birth)" required />
                                <x-input-error :messages="$errors->get('place_of_birth')" />
                            </div>
                            <div class="space-y-2">
                                <x-input-label for="university_id_no" value="Student ID (KSU)" />
                                <x-text-input
                                    id="university_id_no"
                                    name="university_id_no"
                                    type="text"
                                    class="mt-1 block w-full"
                                    :value="old('university_id_no', $tenant->university_id_no)"
                                    placeholder="KSU-0000"
                                    required
                                />
                                <x-input-error :messages="$errors->get('university_id_no')" />
                            </div>
                            <div class="space-y-2">
                                <x-input-label for="program" value="Course / Program" />
                                <x-text-input id="program" name="program" type="text" class="mt-1 block w-full" :value="old('program', $tenant->program ?? $tenant->course_year)" required />
                                <x-input-error :messages="$errors->get('program')" />
                            </div>
                            <div class="space-y-2">
                                <x-input-label for="year_level" value="Year Level" />
                                <x-text-input id="year_level" name="year_level" type="text" class="mt-1 block w-full" :value="old('year_level', $tenant->year_level)" placeholder="e.g., 1, 2, 3, 4" required />
                                <x-input-error :messages="$errors->get('year_level')" />
                            </div>
                            <div class="space-y-2">
                                <x-input-label for="cellphone" value="Cellphone Number" />
                                <x-text-input id="cellphone" name="cellphone" type="text" class="mt-1 block w-full" :value="old('cellphone', $tenant->cellphone ?? $tenant->phone)" required />
                                <x-input-error :messages="$errors->get('cellphone')" />
                            </div>
                        </div>

                        <div class="grid gap-6 md:grid-cols-2">
                            <div class="space-y-2">
                                <x-input-label for="father_name" value="Father's Name" />
                                <x-text-input id="father_name" name="father_name" type="text" class="mt-1 block w-full" :value="old('father_name', $tenant->father_name)" required />
                                <x-input-error :messages="$errors->get('father_name')" />
                            </div>
                            <div class="space-y-2">
                                <x-input-label for="father_contact" value="Father's Contact" />
                                <x-text-input id="father_contact" name="father_contact" type="text" class="mt-1 block w-full" :value="old('father_contact', $tenant->father_contact)" required />
                                <x-input-error :messages="$errors->get('father_contact')" />
                            </div>
                            <div class="space-y-2">
                                <x-input-label for="mother_name" value="Mother's Name" />
                                <x-text-input id="mother_name" name="mother_name" type="text" class="mt-1 block w-full" :value="old('mother_name', $tenant->mother_name)" required />
                                <x-input-error :messages="$errors->get('mother_name')" />
                            </div>
                            <div class="space-y-2">
                                <x-input-label for="mother_contact" value="Mother's Contact" />
                                <x-text-input id="mother_contact" name="mother_contact" type="text" class="mt-1 block w-full" :value="old('mother_contact', $tenant->mother_contact)" required />
                                <x-input-error :messages="$errors->get('mother_contact')" />
                            </div>
                        </div>
                    </div>

                    <div class="space-y-6 rounded-2xl border border-ksu-600/30 bg-ksu-100/50 p-6">
                        <h2 class="text-lg font-semibold text-ksu-800">Helpful Reminders</h2>
                        <ul class="space-y-3 text-sm text-ksu-800/90">
                            <li class="flex items-start gap-3">
                                <span class="mt-0.5 inline-flex h-5 w-5 items-center justify-center rounded-full bg-white text-xs font-semibold text-ksu-600 shadow-ksu">1</span>
                                Keep your contact numbers updated so we can reach you for interview notices.
                            </li>
                            <li class="flex items-start gap-3">
                                <span class="mt-0.5 inline-flex h-5 w-5 items-center justify-center rounded-full bg-white text-xs font-semibold text-ksu-600 shadow-ksu">2</span>
                                Save your progress frequently. You can come back to complete pending details anytime.
                            </li>
                            <li class="flex items-start gap-3">
                                <span class="mt-0.5 inline-flex h-5 w-5 items-center justify-center rounded-full bg-white text-xs font-semibold text-ksu-600 shadow-ksu">3</span>
                                Accepting the policies is required to submit your application for screening.
                            </li>
                        </ul>
                    </div>
                </div>

                <div class="space-y-4">
                    <div>
                        <h3 class="text-lg font-semibold text-ksu-900">Policies & Terms</h3>
                        <p class="text-sm text-slate-600">Please review the KSU dormitory policies before submitting your application.</p>
                    </div>
                    <div class="max-h-56 overflow-y-auto rounded-2xl border border-slate-200 bg-white/80 p-5 text-sm leading-relaxed text-slate-600">
                        <pre class="whitespace-pre-wrap font-sans text-xs sm:text-sm">
DORMITORY POLICIES AND GUIDELINES

1.KSU student/s who wants to stay in the dormitory while enrolled in the University must file an application at the Office of the Student Development Services and Placement Services with the attached enrolment form and photocopy of school ID.
2. After the office receives the application form, the dormitory in-charge will interview the applicant.
3. Applicants who passed the interview and the screening will be called to sign the agreement form.
4. Duly signed agreement form shall be signed by the dorm-in-charge and presented to the accounting office as basis for payments as initial/full payment of dorm fee as stipulated and approved by school authorities. For this purpose, the dorm fee is Php500.00 per month inclusive of electricity and water.
5. The dormitarians must abide by the house rules ser forth by the dorm-in-charge which are as follows:
5.1 Must provide his/her beddings, kitchen utensils, among others;
5.2 Observe the proper use, care and maintenance of all dorm facilities like the comfort room, lockers and other furniture;
5.3 Help in the maintenance of cleanliness and sanitation of the dormitory and its premises;
5.4 Observe at all times conservation measures and safety practices such as switching off lights, putting off electronic gadgets and equipment (radio, flat irons, and electric fans) and the closing of water faucets and the like when not in use;
5.5 Must fill-up and sign properly the logbook whenever leaving the dormitory except when going to school to attend classes;
5.6 Observe the curfew hour set by school authorities at 7:00 PM to 4:00 AM. In case the dorm occupant goes out and return back within the curfew period, he/she must secure a pass slip duly signed by the in-charge, which will serve as her passport to be presented to the guard on duty;
5.7 Visitors are to be entertained only at the dorm lobby;
5.8 Parents or guardians may be allowed to sleep overnight subject to house rules; and
5.9 One room in the dormitory is reserved for transient visitors with a corresponding fee.
6. Prohibited Items. The following items are strictly prohibited in the dormitory:
6.1 Illegal drugs and paraphernalia
6.2 Alcoholic beverages
6.3 Firearms and other weapons
6.4 Flammable materials (e.g., candles, fireworks)
6.5 Pets (with exceptions for service animals)
6.6 Cooking appliances in rooms (unless designated kitchen areas are provided)
7. Violations of these policies may result in disciplinary action, including:
7.1 First offense- Verbal Warnings to be written in the anecdotal record
7.2 Second offense- Written Letter and conference with the OSDSPS and MDS to be written in the anecdotal record.
7.3 Third Offense- Room restrictions, Loss of dormitory privileges and Eviction from the dormitory to be written in the anecdotal record.
8. Grievance Procedure:
8.1 Residents with concerns or grievances should first attempt to resolve them with their roommates or dormitory staff.
8.2 If the issue remains unresolved, residents may submit a formal grievance to the appropriate authorities.
9. Amendments:
9.1 These policies are subject to change at the discretion of the dormitory administration.
9.2 Residents will be notified of any policy changes in a timely manner.

I hereby adhere to all above-mentioned policies; any violations of the policies on my part will mean my removal form the dormitory.
                        </pre>
                    </div>
                    <label class="flex items-start gap-3 text-sm text-slate-700">
                        <input
                            type="checkbox"
                            name="accept_terms"
                            value="1"
                            class="mt-1 rounded border-slate-300 text-ksu-600 focus:ring-ksu-400"
                            @checked(old('accept_terms', $tenant->policy_accepted_at ? 1 : 0))
                            required
                        >
                        <span>I have read and agree to the KSU dormitory policies and terms of stay.</span>
                    </label>
                    <x-input-error :messages="$errors->get('accept_terms')" />
                </div>

                <div class="flex flex-col-reverse gap-3 sm:flex-row sm:items-center sm:justify-end">
                    @unless ($hasInterviewSlot)
                        <x-ksu-button type="reset" variant="subtle" class="sm:w-auto">Clear Form</x-ksu-button>
                    @endunless
                    <x-ksu-button
                        type="submit"
                        class="sm:w-auto"
                        x-bind:disabled="loading"
                    >
                        <span x-show="!loading">Save &amp; Continue</span>
                        <span x-cloak x-show="loading" class="flex items-center gap-2">
                            <span class="loading-spinner" aria-hidden="true"></span>
                            Saving...
                        </span>
                    </x-ksu-button>
                </div>
            </form>
        </x-ksu-card>
    </div>
</x-ksu-layout>
