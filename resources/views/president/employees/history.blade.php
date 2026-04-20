<x-ksu-layout page-title="Employee History">
    @php
        $employeeName = $tenant->full_name ?: ($tenant->user?->name ?? 'Unknown employee');
        $employeeEmail = $tenant->user?->email ?? 'No email on record';
        $activeCottage = $tenant->cottage ?? $tenant->cottageRequest;
    @endphp

    <div class="space-y-8">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-ksu-900 sm:text-3xl">Employee Audit Trail</h1>
                <p class="mt-1 text-sm text-slate-600">Print-ready history of employee housing, cottage, and payment records.</p>
            </div>
            <div class="flex items-center gap-2">
                <x-ksu-button type="button" variant="subtle" onclick="window.print()">Print</x-ksu-button>
                <x-ksu-badge :variant="$tenant->onboarding_status === \App\Models\Tenant::STATUS_APPROVED ? 'approved' : 'pending'">
                    {{ \Illuminate\Support\Str::headline($tenant->onboarding_status ?? 'pending') }}
                </x-ksu-badge>
            </div>
        </div>

        <x-ksu-card>
            <div class="grid gap-4 lg:grid-cols-3">
                <div class="lg:col-span-2 space-y-2">
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Employee</p>
                    <p class="text-2xl font-semibold text-ksu-900">{{ $employeeName }}</p>
                    <p class="text-sm text-slate-500">{{ $employeeEmail }}</p>
                </div>
                <div class="space-y-2 rounded-2xl border border-slate-200/70 bg-slate-50/60 p-4">
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-slate-500">Employee ID</span>
                        <span class="font-semibold text-ksu-900">{{ $tenant->employee_id_number ?? 'N/A' }}</span>
                    </div>
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-slate-500">Department</span>
                        <span class="font-semibold text-ksu-900">{{ $tenant->course_year ?? 'N/A' }}</span>
                    </div>
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-slate-500">Monthly Rate</span>
                        <span class="font-semibold text-ksu-900">&#8369; {{ number_format($tenant->monthly_rate ?? \App\Models\Tenant::DEFAULT_EMPLOYEE_MONTHLY_RATE, 2) }}</span>
                    </div>
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-slate-500">Cottage</span>
                        <span class="font-semibold text-ksu-900">{{ $activeCottage?->code ?? 'None' }}</span>
                    </div>
                </div>
            </div>
        </x-ksu-card>

        <div class="grid gap-4 lg:grid-cols-2">
            <x-ksu-card title="Cottage Record">
                @if (! $activeCottage)
                    <p class="text-sm text-slate-500">No cottage request or assignment recorded.</p>
                @else
                    <div class="space-y-3 text-sm text-slate-700">
                        <div class="rounded-xl border border-slate-200/70 bg-white px-3 py-2">
                            <div class="flex items-center justify-between">
                                <span class="font-semibold text-ksu-900">{{ $activeCottage->code }}</span>
                                <x-ksu-badge :variant="$activeCottage->status === 'occupied' ? 'approved' : 'pending'" size="sm">
                                    {{ \Illuminate\Support\Str::headline($activeCottage->status) }}
                                </x-ksu-badge>
                            </div>
                            <p class="text-xs text-slate-500">{{ $activeCottage->building }} {{ $activeCottage->wing ? '- '.$activeCottage->wing : '' }}</p>
                        </div>

                        @if (! empty($activeCottage->family_members))
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Family Members</p>
                                <ul class="mt-2 space-y-1">
                                    @foreach ($activeCottage->family_members as $member)
                                        <li>{{ $member }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                    </div>
                @endif
            </x-ksu-card>

            <x-ksu-card title="Payment History">
                @if ($tenant->employeePayments->isEmpty())
                    <p class="text-sm text-slate-500">No payment records submitted.</p>
                @else
                    <ul class="space-y-2 text-sm text-slate-700">
                        @foreach ($tenant->employeePayments as $payment)
                            @php
                                $badgeVariant = match ($payment->status) {
                                    \App\Models\EmployeePayment::STATUS_APPROVED => 'approved',
                                    \App\Models\EmployeePayment::STATUS_REJECTED => 'rejected',
                                    default => 'pending',
                                };
                            @endphp
                            <li class="rounded-xl border border-slate-200/70 bg-white px-3 py-2">
                                <div class="flex items-center justify-between">
                                    <span class="font-semibold text-ksu-900">{{ $payment->billing_month->format('F Y') }}</span>
                                    <x-ksu-badge :variant="$badgeVariant" size="sm">
                                        {{ \Illuminate\Support\Str::headline($payment->status) }}
                                    </x-ksu-badge>
                                </div>
                                <p class="text-xs text-slate-500">
                                    &#8369; {{ number_format($payment->amount, 2) }}
                                    @if ($payment->reviewed_at)
                                        &middot; Reviewed {{ $payment->reviewed_at->format('M d, Y') }}
                                    @endif
                                </p>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </x-ksu-card>
        </div>
    </div>
</x-ksu-layout>
