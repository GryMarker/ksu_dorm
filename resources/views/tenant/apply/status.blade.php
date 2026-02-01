@php
    use App\Models\Tenant as TenantModel;

    $status = $tenant->onboarding_status;

    $steps = [
        [
            'label' => 'Application',
            'hint' => 'Submit all required information.',
            'state' => $status === TenantModel::STATUS_DRAFT ? 'current' : 'completed',
        ],
        [
            'label' => 'Dorm Master Review',
            'hint' => 'Application is screened for eligibility.',
            'state' => match ($status) {
                TenantModel::STATUS_FOR_APPROVAL => 'current',
                TenantModel::STATUS_FOR_INTERVIEW, TenantModel::STATUS_APPROVED, TenantModel::STATUS_RECHECK => 'completed',
                TenantModel::STATUS_REJECTED => 'rejected',
                default => 'upcoming',
            },
        ],
        [
            'label' => 'Interview',
            'hint' => 'Attend the scheduled screening.',
            'state' => match ($status) {
                TenantModel::STATUS_FOR_INTERVIEW => 'current',
                TenantModel::STATUS_APPROVED, TenantModel::STATUS_RECHECK => 'completed',
                TenantModel::STATUS_REJECTED => 'rejected',
                default => 'upcoming',
            },
        ],
        [
            'label' => 'Decision',
            'hint' => 'Final dorm admission result.',
            'state' => match ($status) {
                TenantModel::STATUS_APPROVED => 'completed',
                TenantModel::STATUS_REJECTED => 'rejected',
                TenantModel::STATUS_RECHECK => 'recheck',
                default => 'upcoming',
            },
        ],
    ];

    $badgeVariant = match ($status) {
        TenantModel::STATUS_APPROVED => 'approved',
        TenantModel::STATUS_REJECTED => 'rejected',
        TenantModel::STATUS_FOR_INTERVIEW, TenantModel::STATUS_FOR_APPROVAL, TenantModel::STATUS_RECHECK => 'pending',
        default => 'info',
    };
@endphp

<x-ksu-layout page-title="Application Status">
    <div class="space-y-8">
        @if ($errors->any())
            <div class="rounded-2xl border border-crimson/20 bg-crimson/5 px-5 py-4 text-sm text-crimson">
                {{ $errors->first() }}
            </div>
        @endif
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-ksu-900 sm:text-3xl">Application Status</h1>
                <p class="mt-1 text-sm text-slate-600">Track your dorm admission progress and take action when needed.</p>
            </div>
            <x-ksu-badge :variant="$badgeVariant" class="uppercase tracking-wide">
                {{ \Illuminate\Support\Str::headline($status) }}
            </x-ksu-badge>
        </div>

        <x-ksu-step :steps="$steps" />

        <x-ksu-card>
            <div class="space-y-6 text-sm text-slate-600">
                <div class="space-y-1">
                    <p class="font-semibold text-ksu-900">Latest Update</p>
                    <p>Last updated {{ $tenant->updated_at->diffForHumans() }}</p>
                </div>

                <div class="space-y-1">
                    @switch($status)
                        @case(TenantModel::STATUS_DRAFT)
                            <p>Finish your application form and accept the dormitory policies to move to the interview stage.</p>
                            @break
                        @case(TenantModel::STATUS_FOR_APPROVAL)
                            <p>Your application is pending Dorm Master review. We will notify you once an interview is scheduled.</p>
                            @break
                        @case(TenantModel::STATUS_FOR_INTERVIEW)
                            <p>Your interview has been scheduled. Please prepare your requirements and arrive on time.</p>
                            @break
                        @case(TenantModel::STATUS_APPROVED)
                            <p>Congratulations! You are approved for dorm admission. Reserve a room to complete your onboarding.</p>
                            @break
                        @case(TenantModel::STATUS_REJECTED)
                            <p>Your application was not approved. You may reach out to the dorm master for clarifications.</p>
                            @break
                        @case(TenantModel::STATUS_RECHECK)
                            <p>Additional information is required. Update your application and await a new review.</p>
                            @break
                        @default
                            <p>Finish your application and submit it for review to continue.</p>
                    @endswitch
                </div>
            </div>
        </x-ksu-card>

        <x-ksu-card title="Interview Details">
            @if($latestInterview)
                <dl class="grid gap-6 sm:grid-cols-2 text-sm text-slate-600">
                    <div>
                        <dt class="font-semibold text-ksu-900">Scheduled Slot</dt>
                        <dd>{{ $latestInterview->scheduled_at->format('M d, Y \\a\\t h:i A') }}</dd>
                    </div>
                    @if($latestInterview->result)
                        <div>
                            <dt class="font-semibold text-ksu-900">Decision</dt>
                            <dd>{{ \Illuminate\Support\Str::headline($latestInterview->result) }}</dd>
                        </div>
                    @endif
                    @if($latestInterview->notes)
                        <div class="sm:col-span-2">
                            <dt class="font-semibold text-ksu-900">Notes</dt>
                            <dd>{{ $latestInterview->notes }}</dd>
                        </div>
                    @endif
                </dl>
            @else
                <p class="text-sm text-slate-500">No interview scheduled yet. Book a slot to continue with the admission process.</p>
            @endif
        </x-ksu-card>

        <div class="flex flex-wrap justify-end gap-3">
            <x-ksu-button as="a" href="{{ route('tenant.apply.form') }}" variant="outline">Update Application</x-ksu-button>
            @if($status === TenantModel::STATUS_FOR_INTERVIEW || ! $latestInterview)
                <x-ksu-button as="a" href="{{ route('tenant.apply.slots') }}" variant="subtle">Choose Interview Slot</x-ksu-button>
            @endif
            @if($status === TenantModel::STATUS_APPROVED)
                <x-ksu-button as="a" href="{{ route('tenant.dashboard') }}">Go to Dashboard</x-ksu-button>
            @endif
        </div>
    </div>
</x-ksu-layout>
