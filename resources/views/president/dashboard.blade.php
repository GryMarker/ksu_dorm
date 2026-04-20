@php
    $kpis = [
        ['label' => 'Total Employees', 'value' => $stats['total_employees'], 'hint' => 'Employee housing profiles'],
        ['label' => 'Approved Employees', 'value' => $stats['approved_employees'], 'hint' => 'Approved for employee access'],
        ['label' => 'Pending Onboarding', 'value' => $stats['pending_onboarding'], 'hint' => 'Awaiting president review'],
        ['label' => 'Pending Payments', 'value' => $stats['pending_payments'], 'hint' => 'Monthly records for approval'],
        ['label' => 'Pending Cottages', 'value' => $stats['pending_cottages'], 'hint' => 'Cottage requests to decide'],
        ['label' => 'Occupied Cottages', 'value' => $stats['occupied_cottages'], 'hint' => 'Assigned employee cottages'],
    ];
@endphp

<x-ksu-layout page-title="President Dashboard">
    <div class="space-y-10">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-3xl font-semibold text-ksu-900 sm:text-4xl">Employee Housing Overview</h1>
                <p class="mt-1 text-sm text-slate-600">Monitor employee onboarding, cottage requests, and housing payments.</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <x-ksu-button as="a" href="{{ route('president.approvals.employees.index') }}" size="sm" variant="subtle">Review Onboarding</x-ksu-button>
                <x-ksu-button as="a" href="{{ route('president.payments.index') }}" size="sm">Review Payments</x-ksu-button>
            </div>
        </div>

        <div class="grid gap-6 md:grid-cols-2 xl:grid-cols-3">
            @foreach ($kpis as $kpi)
                <x-ksu-card>
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">{{ $kpi['label'] }}</p>
                    <p class="mt-3 text-3xl font-semibold text-ksu-900">{{ number_format($kpi['value']) }}</p>
                    <p class="text-xs text-slate-500">{{ $kpi['hint'] }}</p>
                </x-ksu-card>
            @endforeach
        </div>

        <div class="grid gap-6 lg:grid-cols-[3fr,2fr]">
            <x-ksu-card title="Recent Employees">
                @if ($recentEmployees->isEmpty())
                    <p class="text-sm text-slate-500">No employee profiles yet.</p>
                @else
                    <x-ksu-table :headers="['Employee', 'Employee ID', 'Status', 'Updated']">
                        @foreach ($recentEmployees as $employee)
                            @php
                                $badgeVariant = match ($employee->onboarding_status) {
                                    \App\Models\Tenant::STATUS_APPROVED => 'approved',
                                    \App\Models\Tenant::STATUS_REJECTED => 'rejected',
                                    default => 'pending',
                                };
                            @endphp
                            <tr>
                                <td class="px-5 py-4 text-sm text-ksu-900">
                                    <div class="font-semibold">{{ $employee->full_name ?? $employee->user->name }}</div>
                                    <div class="text-xs text-slate-500">{{ $employee->user->email }}</div>
                                </td>
                                <td class="px-5 py-4 text-sm text-slate-600">{{ $employee->employee_id_number ?? 'Pending' }}</td>
                                <td class="px-5 py-4">
                                    <x-ksu-badge :variant="$badgeVariant" size="sm">
                                        {{ \Illuminate\Support\Str::headline($employee->onboarding_status ?? 'Pending') }}
                                    </x-ksu-badge>
                                </td>
                                <td class="px-5 py-4 text-sm text-slate-600">{{ optional($employee->updated_at)->format('M d, Y') }}</td>
                            </tr>
                        @endforeach
                    </x-ksu-table>
                @endif
            </x-ksu-card>

            <x-ksu-card title="Quick Links">
                <div class="grid gap-3">
                    <x-ksu-button as="a" href="{{ route('president.employees.index') }}" full variant="subtle">Employee Masterlist</x-ksu-button>
                    <x-ksu-button as="a" href="{{ route('president.approvals.employees.index') }}" full>Onboarding Queue</x-ksu-button>
                    <x-ksu-button as="a" href="{{ route('president.payments.index') }}" full variant="outline">Payment Approvals</x-ksu-button>
                    <x-ksu-button as="a" href="{{ route('management.cottages.index') }}" full>Cottage Management</x-ksu-button>
                </div>
            </x-ksu-card>
        </div>
    </div>
</x-ksu-layout>
