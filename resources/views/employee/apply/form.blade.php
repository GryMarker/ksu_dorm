@php
    use App\Models\Tenant as TenantModel;

    $status = $tenant->onboarding_status ?? TenantModel::STATUS_DRAFT;
@endphp

<x-ksu-layout page-title="Employee Onboarding">
    <div class="space-y-8">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-ksu-900 sm:text-3xl">Employee Onboarding Form</h1>
                <p class="mt-1 text-sm text-slate-600">Provide your details so the University President can review your access request.</p>
            </div>
            <x-ksu-badge :variant="$status === TenantModel::STATUS_APPROVED ? 'approved' : ($status === TenantModel::STATUS_REJECTED ? 'rejected' : 'pending')" class="uppercase tracking-wide">
                {{ \Illuminate\Support\Str::headline($status) }}
            </x-ksu-badge>
        </div>

        @if (session('status'))
            <x-ksu-alert type="success">
                {{ session('status') }}
            </x-ksu-alert>
        @endif

        <x-ksu-card>
            <form
                method="POST"
                action="{{ route('employee.apply.submit') }}"
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
                            <div class="space-y-2 md:col-span-2">
                                <x-input-label for="home_address" value="Home Address" />
                                <textarea
                                    id="home_address"
                                    name="home_address"
                                    rows="3"
                                    class="mt-1 block w-full rounded-xl border border-slate-300 px-3 py-2 text-sm text-slate-700 shadow-sm focus:border-ksu-600 focus:outline-none focus:ring-ksu-400"
                                    required
                                >{{ old('home_address', $tenant->home_address) }}</textarea>
                                <x-input-error :messages="$errors->get('home_address')" />
                            </div>
                            <div class="space-y-2">
                                <x-input-label for="age" value="Age" />
                                <x-text-input id="age" name="age" type="number" min="18" class="mt-1 block w-full" :value="old('age', $tenant->age)" required />
                                <x-input-error :messages="$errors->get('age')" />
                            </div>
                            <div class="space-y-2">
                                <x-input-label for="place_of_birth" value="Place of Birth" />
                                <x-text-input id="place_of_birth" name="place_of_birth" type="text" class="mt-1 block w-full" :value="old('place_of_birth', $tenant->place_of_birth)" required />
                                <x-input-error :messages="$errors->get('place_of_birth')" />
                            </div>
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
                            <div class="space-y-2">
                                <x-input-label for="course_year" value="Course / Department" />
                                <x-text-input id="course_year" name="course_year" type="text" class="mt-1 block w-full" :value="old('course_year', $tenant->course_year)" required />
                                <x-input-error :messages="$errors->get('course_year')" />
                            </div>
                            <div class="space-y-2">
                                <x-input-label for="cellphone" value="Mobile Number" />
                                <x-text-input id="cellphone" name="cellphone" type="text" class="mt-1 block w-full" :value="old('cellphone', $tenant->cellphone)" required />
                                <x-input-error :messages="$errors->get('cellphone')" />
                            </div>
                        </div>

                        <div class="space-y-4 rounded-xl border border-slate-200 bg-slate-50 p-4">
                            <label class="flex items-start gap-3">
                                <input
                                    type="checkbox"
                                    name="accept_terms"
                                    class="mt-1 h-4 w-4 rounded border-slate-300 text-ksu-600 focus:ring-ksu-500"
                                    value="1"
                                    @checked(old('accept_terms', $tenant->policy_accepted_at ? 1 : 0))
                                    required
                                >
                                <span class="text-sm text-slate-700">
                                    I acknowledge that all information provided is accurate and agree to follow the dormitory guidelines and policies.
                                </span>
                            </label>
                            <x-input-error :messages="$errors->get('accept_terms')" />
                        </div>
                    </div>

                    <div class="space-y-6">
                        <div class="rounded-xl border border-slate-200 bg-slate-50 p-4 text-sm text-slate-600">
                            <h2 class="text-base font-semibold text-ksu-900">Approval Process</h2>
                            <ul class="mt-3 space-y-2">
                                <li>Submit your personal profile for administrative records.</li>
                                <li>Your request will be routed to the University President for approval.</li>
                                <li>You'll receive an email once your access is granted.</li>
                            </ul>
                        </div>
                        <div class="rounded-xl border border-slate-200 bg-white p-4">
                            <h2 class="text-base font-semibold text-ksu-900">Current Status</h2>
                            <p class="mt-2 text-sm text-slate-600">
                                {{ \Illuminate\Support\Str::headline($status) }}
                            </p>
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3">
                    <x-ksu-button type="submit" x-bind:disabled="loading">
                        <span x-show="!loading">Submit Application</span>
                        <span x-show="loading">Submitting…</span>
                    </x-ksu-button>
                </div>
            </form>
        </x-ksu-card>
    </div>
</x-ksu-layout>
