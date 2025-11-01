@php
    use App\Models\Tenant as TenantModel;

    $status = $tenant->onboarding_status ?? TenantModel::STATUS_DRAFT;
    $statusCopy = match ($status) {
        TenantModel::STATUS_APPROVED => 'Your access has been approved. You can now open the dashboard.',
        TenantModel::STATUS_REJECTED => 'Unfortunately, your request was rejected. Please contact the dorm office for more details.',
        TenantModel::STATUS_RECHECK => 'Additional information is required. Our staff will reach out shortly.',
        TenantModel::STATUS_FOR_APPROVAL => 'Your application is awaiting review from the University President.',
        TenantModel::STATUS_FOR_INTERVIEW => 'An interview step is pending. The dorm staff will coordinate if needed.',
        default => 'Complete your onboarding form so we can process your access request.',
    };
@endphp

<x-ksu-layout page-title="Employee Status">
    <div class="space-y-6">
        <div class="flex items-start justify-between gap-4">
            <div>
                <h1 class="text-2xl font-semibold text-ksu-900 sm:text-3xl">Employee Onboarding Status</h1>
                <p class="mt-1 text-sm text-slate-600">
                    Track where you are in the approval process.
                </p>
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

        <x-ksu-card class="space-y-6">
            <div>
                <h2 class="text-lg font-semibold text-ksu-900">Next Steps</h2>
                <p class="mt-2 text-sm text-slate-600">
                    {{ $statusCopy }}
                </p>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div class="rounded-xl border border-slate-200 bg-slate-50 p-4 text-sm text-slate-600">
                    <span class="block text-xs font-semibold uppercase tracking-wide text-slate-500">Employee ID</span>
                    <span class="mt-1 block text-base font-semibold text-ksu-900">
                        {{ $tenant->employee_id_number ?? 'Pending' }}
                    </span>
                </div>
                <div class="rounded-xl border border-slate-200 bg-slate-50 p-4 text-sm text-slate-600">
                    <span class="block text-xs font-semibold uppercase tracking-wide text-slate-500">Policy Accepted</span>
                    <span class="mt-1 block text-base font-semibold text-ksu-900">
                        {{ optional($tenant->policy_accepted_at)->format('M d, Y g:i A') ?? 'Not yet' }}
                    </span>
                </div>
            </div>

            <div class="flex flex-wrap items-center justify-between gap-3">
                <x-ksu-button :href="route('employee.apply.form')" variant="secondary">
                    Update Application
                </x-ksu-button>

                @if ($status === TenantModel::STATUS_APPROVED)
                    <x-ksu-button :href="route('employee.dashboard')">
                        Go to Dashboard
                    </x-ksu-button>
                @endif
            </div>
        </x-ksu-card>
    </div>
</x-ksu-layout>
