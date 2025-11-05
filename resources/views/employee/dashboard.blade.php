@php
    use App\Models\EmployeePayment;
    use Illuminate\Support\Str;

    $displayRate = number_format($tenant->monthly_rate ?? \App\Models\Tenant::DEFAULT_EMPLOYEE_MONTHLY_RATE, 2);
    $currentMonthLabel = now()->format('F Y');
    $activeCottage = $tenant->cottage;
    $pendingCottage = $tenant->cottageRequest;
@endphp

<x-ksu-layout page-title="Employee Dashboard">
    <div class="space-y-6">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-ksu-900 sm:text-3xl">Welcome, {{ $tenant->full_name ?? auth()->user()->name }}</h1>
                <p class="mt-1 text-sm text-slate-600">Manage your housing details, family occupancy, and monthly payment records.</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <x-ksu-button :href="route('employee.cottages.index')" size="sm" variant="secondary">
                    View Cottages
                </x-ksu-button>
                <x-ksu-button :href="route('employee.payments.index')" size="sm">
                    Manage Payments
                </x-ksu-button>
            </div>
        </div>

        @if (! $hasCurrentMonthRecord)
            <x-ksu-alert type="warning">
                No payment record has been submitted for {{ $currentMonthLabel }}. Please create one to keep your housing record updated.
            </x-ksu-alert>
        @endif

        @if ($pendingPayment)
            <x-ksu-alert type="info">
                Payment for {{ $pendingPayment->billing_month->format('F Y') }} is awaiting president approval.
            </x-ksu-alert>
        @endif

        <div class="grid gap-6 lg:grid-cols-[1.1fr,1fr]">
            <x-ksu-card class="space-y-6">
                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                        <span class="block text-xs font-semibold uppercase tracking-wide text-slate-500">Approval Date</span>
                        <span class="mt-1 block text-base font-semibold text-ksu-900">
                            {{ optional($tenant->updated_at)->format('M d, Y g:i A') ?? 'Pending' }}
                        </span>
                    </div>
                    <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                        <span class="block text-xs font-semibold uppercase tracking-wide text-slate-500">Monthly Rate</span>
                        <span class="mt-1 block text-base font-semibold text-ksu-900">&#8369; {{ $displayRate }}</span>
                    </div>
                    <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                        <span class="block text-xs font-semibold uppercase tracking-wide text-slate-500">Salary Deduction</span>
                        <span class="mt-1 block text-base font-semibold text-ksu-900">
                            {{ $tenant->salary_deduction ? 'Enabled' : 'Not Enabled' }}
                        </span>
                    </div>
                    <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                        <span class="block text-xs font-semibold uppercase tracking-wide text-slate-500">Cottage Assignment</span>
                        @if ($activeCottage)
                            <span class="mt-1 block text-base font-semibold text-ksu-900">
                                {{ $activeCottage->code }}
                            </span>
                            <span class="mt-1 block text-xs text-slate-500">
                                {{ $activeCottage->building }} • {{ $activeCottage->wing }}
                            </span>
                        @elseif ($pendingCottage)
                            <span class="mt-1 block text-base font-semibold text-amber-700">
                                {{ $pendingCottage->code }} (Pending Approval)
                            </span>
                            <span class="mt-1 block text-xs text-slate-500">
                                Requested {{ optional($pendingCottage->requested_at)->diffForHumans() }}
                            </span>
                        @else
                            <span class="mt-1 block text-base font-semibold text-slate-600">Not yet requested</span>
                        @endif
                    </div>
                    @if ($activeCottage && ! empty($activeCottage->family_members))
                        <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                            <span class="block text-xs font-semibold uppercase tracking-wide text-slate-500">Registered Occupants</span>
                            <ul class="mt-2 space-y-1 text-xs text-slate-600">
                                @foreach ($activeCottage->family_members as $member)
                                    <li>{{ $member }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                </div>

                <div class="space-y-4">
                    <h2 class="text-lg font-semibold text-ksu-900">Recent Payment Records</h2>
                    @if ($recentPayments->isEmpty())
                        <p class="text-sm text-slate-500">No payment records yet. Create your first record to start tracking monthly housing payments.</p>
                    @else
                        <div class="overflow-hidden rounded-xl border border-slate-200">
                            <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
                                <thead class="bg-slate-50 text-xs font-semibold uppercase tracking-wide text-slate-500">
                                    <tr>
                                        <th scope="col" class="px-4 py-3">Month</th>
                                        <th scope="col" class="px-4 py-3">Amount</th>
                                        <th scope="col" class="px-4 py-3">Salary Deduction</th>
                                        <th scope="col" class="px-4 py-3">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 bg-white">
                                    @foreach ($recentPayments as $payment)
                                        <tr>
                                            <td class="px-4 py-3 font-semibold text-ksu-900">
                                                {{ $payment->billing_month->format('F Y') }}
                                            </td>
                                            <td class="px-4 py-3 text-slate-700">
                                                &#8369; {{ number_format($payment->amount, 2) }}
                                            </td>
                                            <td class="px-4 py-3 text-slate-600">
                                                {{ $payment->salary_deduction ? 'Yes' : 'No' }}
                                            </td>
                                            <td class="px-4 py-3">
                                                <x-ksu-badge :variant="match ($payment->status) {
                                                    EmployeePayment::STATUS_APPROVED => 'approved',
                                                    EmployeePayment::STATUS_REJECTED => 'rejected',
                                                    default => 'pending',
                                                }">
                                                    {{ Str::headline($payment->status) }}
                                                </x-ksu-badge>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </x-ksu-card>

            <x-ksu-card class="space-y-4">
                <h2 class="text-lg font-semibold text-ksu-900">Family Occupancy</h2>
                @php
                    $familyMembers = collect($tenant->family_members ?? [])->filter()->values();
                @endphp
                @if ($familyMembers->isEmpty())
                    <p class="text-sm text-slate-500">You have not listed any family members yet. Update your onboarding form to include their names.</p>
                @else
                    <ul class="space-y-3 text-sm text-slate-700">
                        @foreach ($familyMembers as $member)
                            <li class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2">{{ $member }}</li>
                        @endforeach
                    </ul>
                @endif

                <div class="rounded-xl border border-ksu-100 bg-ksu-50 px-4 py-3 text-xs text-ksu-900">
                    Keep your family roster updated so the dorm staff knows who resides with you in the employee cottages.
                </div>
            </x-ksu-card>
        </div>
    </div>
</x-ksu-layout>
