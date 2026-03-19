@props(['tenant'])

@php
    $tenantName = $tenant->full_name ?: $tenant->user->name;
    $contactDetails = [
        'University ID' => $tenant->university_id_no,
        'Program / Year' => trim(($tenant->program ?? $tenant->course_year ?? '') . ($tenant->year_level ? ' - Year ' . $tenant->year_level : '')),
        'Nickname' => $tenant->nickname,
        'Gender' => $tenant->gender ? \Illuminate\Support\Str::headline($tenant->gender) : null,
        'Date of Birth' => optional($tenant->dob)?->format('M d, Y'),
        'Age' => $tenant->age,
        'Phone' => $tenant->cellphone ?? $tenant->phone,
        'Email' => $tenant->user->email,
        'Address' => $tenant->home_address,
        'Place of Birth' => $tenant->place_of_birth,
    ];

    $guardianDetails = [
        "Father's Name" => $tenant->father_name,
        "Father's Contact" => $tenant->father_contact,
        "Mother's Name" => $tenant->mother_name,
        "Mother's Contact" => $tenant->mother_contact,
    ];
@endphp

<div class="space-y-6">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Applicant</p>
            <h3 class="text-xl font-semibold text-ksu-900">{{ $tenantName }}</h3>
            <p class="text-sm text-slate-600">{{ $tenant->user->email }}</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <x-ksu-badge :variant="$tenant->onboarding_status === \App\Models\Tenant::STATUS_APPROVED ? 'approved' : 'pending'" size="sm">
                {{ \Illuminate\Support\Str::headline($tenant->onboarding_status ?? 'Unknown') }}
            </x-ksu-badge>
            <x-ksu-badge variant="info" size="sm">
                {{ $tenant->type === \App\Models\Tenant::TYPE_EMPLOYEE ? 'Employee' : 'Student' }}
            </x-ksu-badge>
            <x-ksu-button type="button" size="sm" variant="outline" x-on:click="window.print()">
                Print
            </x-ksu-button>
        </div>
    </div>

    <div class="grid gap-4 md:grid-cols-2">
        <div class="space-y-3 rounded-2xl border border-slate-200/80 bg-ksu-50/60 p-5">
            <div class="flex items-center justify-between">
                <h4 class="text-sm font-semibold text-ksu-900">Student Information</h4>
                @if ($tenant->policy_accepted_at)
                    <span class="text-xs font-medium text-slate-500">Submitted {{ $tenant->policy_accepted_at->format('M d, Y') }}</span>
                @endif
            </div>
            <dl class="grid grid-cols-1 gap-2 text-sm text-slate-700">
                @foreach ($contactDetails as $label => $value)
                    <div class="flex items-start justify-between gap-3 rounded-xl bg-white/70 px-3 py-2">
                        <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ $label }}</dt>
                        <dd class="text-right font-medium text-ksu-900">{{ $value ?: 'N/A' }}</dd>
                    </div>
                @endforeach
            </dl>
        </div>

        <div class="space-y-3 rounded-2xl border border-slate-200/80 bg-white p-5">
            <div class="flex items-center justify-between">
                <h4 class="text-sm font-semibold text-ksu-900">Guardians & Contacts</h4>
            </div>
            <dl class="grid grid-cols-1 gap-2 text-sm text-slate-700">
                @foreach ($guardianDetails as $label => $value)
                    <div class="flex items-start justify-between gap-3 rounded-xl bg-ksu-50/60 px-3 py-2">
                        <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ $label }}</dt>
                        <dd class="text-right font-medium text-ksu-900">{{ $value ?: 'N/A' }}</dd>
                    </div>
                @endforeach
            </dl>
        </div>
    </div>
</div>
