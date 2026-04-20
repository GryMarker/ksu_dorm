@php
    use App\Models\Tenant as TenantModel;

    $status = $tenant->onboarding_status ?? TenantModel::STATUS_DRAFT;
    $displayRate = number_format($tenant->monthly_rate ?? TenantModel::DEFAULT_EMPLOYEE_MONTHLY_RATE, 2);
    $familyMembersValue = implode("\n", (array) $tenant->family_members);
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
                                <x-input-label for="sex" value="Sex" />
                                <select
                                    id="sex"
                                    name="sex"
                                    class="mt-1 block w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 shadow-sm focus:border-ksu-600 focus:outline-none focus:ring-ksu-400"
                                    required
                                >
                                    <option value="" disabled @selected(!old('sex', $tenant->sex))>Select sex</option>
                                    <option value="male" @selected(old('sex', $tenant->sex) === 'male')>Male</option>
                                    <option value="female" @selected(old('sex', $tenant->sex) === 'female')>Female</option>
                                </select>
                                <x-input-error :messages="$errors->get('sex')" />
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
                            <div class="space-y-2 md:col-span-2">
                                <x-input-label for="department" value="Department" />
                                <x-text-input id="department" name="department" type="text" class="mt-1 block w-full" :value="old('department', $tenant->course_year)" required />
                                <x-input-error :messages="$errors->get('department')" />
                            </div>
                            <div class="space-y-2">
                                <x-input-label for="cellphone" value="Mobile Number" />
                                <x-text-input id="cellphone" name="cellphone" type="text" class="mt-1 block w-full" :value="old('cellphone', $tenant->cellphone)" required />
                                <x-input-error :messages="$errors->get('cellphone')" />
                            </div>
                            <div class="space-y-2">
                                <x-input-label value="Monthly Housing Rate" />
                                <div class="mt-1 flex items-center rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm font-semibold text-ksu-900">
                                    <span class="mr-1 text-slate-500">&#8369;</span>
                                    <span>{{ $displayRate }}</span>
                                </div>
                            </div>
                            <div class="space-y-2">
                                <x-input-label for="salary_deduction" value="Salary Deduction" />
                                <label class="mt-1 flex items-center gap-3 rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-700">
                                    <input
                                        id="salary_deduction"
                                        name="salary_deduction"
                                        type="checkbox"
                                        value="1"
                                        @checked(old('salary_deduction', $tenant->salary_deduction))
                                        class="h-4 w-4 rounded border-slate-300 text-ksu-600 focus:ring-ksu-500"
                                    >
                                    <span>Deduct from salary payroll</span>
                                </label>
                                <x-input-error :messages="$errors->get('salary_deduction')" />
                            </div>
                            <div class="space-y-2 md:col-span-2">
                                <x-input-label for="family_members" value="Family Members (one name per line)" />
                                <textarea
                                    id="family_members"
                                    name="family_members"
                                    rows="4"
                                    class="mt-1 block w-full rounded-xl border border-slate-300 px-3 py-2 text-sm text-slate-700 shadow-sm focus:border-ksu-600 focus:outline-none focus:ring-ksu-400"
                                >{{ old('family_members', $familyMembersValue) }}</textarea>
                                <x-input-error :messages="$errors->get('family_members')" />
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
                                <li>Status updates are reflected here once a decision is made.</li>
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
