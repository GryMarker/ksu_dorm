@php
    use App\Models\EmployeePayment;
    use Illuminate\Support\Str;
@endphp

<x-ksu-layout page-title="Employee Payment Approvals">
    <div class="space-y-8">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-3xl font-semibold text-ksu-900 sm:text-4xl">Employee Payment Approvals</h1>
                <p class="mt-1 text-sm text-slate-600">
                    Review monthly housing payment records submitted by employees and confirm whether to approve or reject them.
                </p>
            </div>
            <x-ksu-button :href="route('president.approvals.employees.index')" size="sm" variant="outline">
                View Onboarding Requests
            </x-ksu-button>
        </div>

        @if (session('status'))
            <x-ksu-alert type="success">
                {{ session('status') }}
            </x-ksu-alert>
        @endif

        @if ($errors->any())
            <x-ksu-alert type="error">
                {{ $errors->first() }}
            </x-ksu-alert>
        @endif

        <x-ksu-card title="Pending Payment Records">
            @if ($pendingPayments->isEmpty())
                <p class="text-sm text-slate-500">No pending payment records at the moment.</p>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
                        <thead class="bg-slate-50 text-xs font-semibold uppercase tracking-wide text-slate-500">
                            <tr>
                                <th scope="col" class="px-4 py-3">Employee</th>
                                <th scope="col" class="px-4 py-3">Month</th>
                                <th scope="col" class="px-4 py-3">Amount</th>
                                <th scope="col" class="px-4 py-3">Salary Deduction</th>
                                <th scope="col" class="px-4 py-3">Employee Note</th>
                                <th scope="col" class="px-4 py-3">Submitted</th>
                                <th scope="col" class="px-4 py-3">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white">
                            @foreach ($pendingPayments as $payment)
                                <tr>
                                    <td class="px-4 py-4">
                                        <div class="space-y-1">
                                            <p class="text-sm font-semibold text-ksu-900">{{ $payment->tenant->full_name }}</p>
                                            <p class="text-xs text-slate-500">{{ $payment->tenant->user->email }}</p>
                                        </div>
                                    </td>
                                    <td class="px-4 py-4 text-sm text-slate-600">
                                        {{ $payment->billing_month->format('F Y') }}
                                    </td>
                                    <td class="px-4 py-4 text-sm font-semibold text-ksu-900">
                                        &#8369; {{ number_format($payment->amount, 2) }}
                                    </td>
                                    <td class="px-4 py-4 text-sm text-slate-600">
                                        {{ $payment->salary_deduction ? 'Yes' : 'No' }}
                                    </td>
                                    <td class="px-4 py-4 text-xs text-slate-500">
                                        {{ $payment->employee_note ?: '—' }}
                                    </td>
                                    <td class="px-4 py-4 text-xs text-slate-500">
                                        {{ $payment->created_at->diffForHumans() }}
                                    </td>
                                    <td class="px-4 py-4">
                                        <div class="space-y-4">
                                            <form method="POST" action="{{ route('president.payments.approve', $payment) }}" class="space-y-2">
                                                @csrf
                                                @method('PATCH')
                                                <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500" for="approve-amount-{{ $payment->id }}">Amount</label>
                                                <input
                                                    id="approve-amount-{{ $payment->id }}"
                                                    type="number"
                                                    name="amount"
                                                    step="0.01"
                                                    min="0"
                                                    value="{{ number_format($payment->amount, 2, '.', '') }}"
                                                    class="w-full rounded-lg border border-slate-300 px-2 py-1 text-xs text-slate-700 focus:border-ksu-600 focus:outline-none focus:ring-ksu-400"
                                                >
                                                <input
                                                    type="text"
                                                    name="review_note"
                                                    placeholder="Optional note"
                                                    class="w-full rounded-lg border border-slate-300 px-2 py-1 text-xs text-slate-700 focus:border-ksu-600 focus:outline-none focus:ring-ksu-400"
                                                >
                                                <x-ksu-button type="submit" size="sm">Approve</x-ksu-button>
                                            </form>
                                            <form method="POST" action="{{ route('president.payments.reject', $payment) }}" class="space-y-2">
                                                @csrf
                                                @method('PATCH')
                                                <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500" for="reject-amount-{{ $payment->id }}">Amount</label>
                                                <input
                                                    id="reject-amount-{{ $payment->id }}"
                                                    type="number"
                                                    name="amount"
                                                    step="0.01"
                                                    min="0"
                                                    value="{{ number_format($payment->amount, 2, '.', '') }}"
                                                    class="w-full rounded-lg border border-slate-300 px-2 py-1 text-xs text-slate-700 focus:border-ksu-600 focus:outline-none focus:ring-ksu-400"
                                                >
                                                <input
                                                    type="text"
                                                    name="review_note"
                                                    placeholder="Reason for rejection"
                                                    required
                                                    class="w-full rounded-lg border border-slate-300 px-2 py-1 text-xs text-slate-700 focus:border-ksu-600 focus:outline-none focus:ring-ksu-400"
                                                >
                                                <x-ksu-button type="submit" size="sm" variant="outline">Reject</x-ksu-button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    {{ $pendingPayments->links() }}
                </div>
            @endif
        </x-ksu-card>

        <x-ksu-card title="Recently Approved">
            @if ($recentPayments->isEmpty())
                <p class="text-sm text-slate-500">No approved payment records yet.</p>
            @else
                <ul class="divide-y divide-slate-100 text-sm text-slate-600">
                    @foreach ($recentPayments as $payment)
                        <li class="flex flex-wrap items-center justify-between gap-3 px-1 py-3">
                            <div>
                                <p class="font-semibold text-ksu-900">{{ $payment->tenant->full_name }}</p>
                                <p class="text-xs text-slate-500">
                                    {{ $payment->billing_month->format('F Y') }} • &#8369; {{ number_format($payment->amount, 2) }}
                                </p>
                            </div>
                            <div class="text-xs text-slate-500">
                                Approved {{ optional($payment->reviewed_at)->diffForHumans() ?? '—' }}
                            </div>
                        </li>
                    @endforeach
                </ul>
            @endif
        </x-ksu-card>
    </div>
</x-ksu-layout>
