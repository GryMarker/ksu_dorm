@php
    use App\Models\EmployeePayment;
    use Illuminate\Support\Str;

    $defaultMonth = $defaultMonth ?? now()->format('Y-m');
@endphp

<x-ksu-layout page-title="Employee Payments">
    <div class="space-y-8">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-ksu-900 sm:text-3xl">Monthly Payment Records</h1>
                <p class="mt-1 text-sm text-slate-600">
                    Submit or track monthly housing payments. These records are reviewed and approved by the University President.
                </p>
            </div>
            <x-ksu-button :href="route('employee.dashboard')" size="sm" variant="outline">
                Back to Dashboard
            </x-ksu-button>
        </div>

        @if (session('status'))
            <x-ksu-alert type="success">
                {{ session('status') }}
            </x-ksu-alert>
        @endif

        @if ($errors->any())
            <x-ksu-alert type="error">
                Please review the highlighted fields below.
            </x-ksu-alert>
        @endif

        <x-ksu-card>
            <form method="POST" action="{{ route('employee.payments.store') }}" class="space-y-6">
                @csrf

                <div class="grid gap-6 md:grid-cols-2">
                    <div class="space-y-2">
                        <x-input-label for="billing_month" value="Billing Month" />
                        <x-text-input
                            id="billing_month"
                            name="billing_month"
                            type="month"
                            class="mt-1 block w-full"
                            value="{{ old('billing_month', $defaultMonth) }}"
                            required
                        />
                        <x-input-error :messages="$errors->get('billing_month')" />
                    </div>
                    <div class="space-y-2">
                        <x-input-label for="amount" value="Amount" />
                        <div class="mt-1 flex items-center rounded-xl border border-slate-200 bg-slate-50 px-3 py-2">
                            <span class="text-slate-500">&#8369;</span>
                            <input
                                id="amount"
                                name="amount"
                                type="number"
                                step="0.01"
                                min="0"
                                value="{{ old('amount', $tenant->monthly_rate ?? \App\Models\Tenant::DEFAULT_EMPLOYEE_MONTHLY_RATE) }}"
                                class="ml-2 w-full border-0 bg-transparent text-sm font-semibold text-ksu-900 focus:ring-0"
                            />
                        </div>
                        <x-input-error :messages="$errors->get('amount')" />
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
                            <span>Request salary deduction for this payment</span>
                        </label>
                        <x-input-error :messages="$errors->get('salary_deduction')" />
                    </div>
                    <div class="space-y-2 md:col-span-2">
                        <x-input-label for="employee_note" value="Employee Note (optional)" />
                        <textarea
                            id="employee_note"
                            name="employee_note"
                            rows="3"
                            class="mt-1 block w-full rounded-xl border border-slate-300 px-3 py-2 text-sm text-slate-700 shadow-sm focus:border-ksu-600 focus:outline-none focus:ring-ksu-400"
                        >{{ old('employee_note') }}</textarea>
                        <x-input-error :messages="$errors->get('employee_note')" />
                    </div>
                </div>

                <div class="flex items-center justify-end">
                    <x-ksu-button type="submit">
                        Submit for Approval
                    </x-ksu-button>
                </div>
            </form>
        </x-ksu-card>

        <x-ksu-card class="space-y-4" title="Payment History">
            @if ($payments->isEmpty())
                <p class="text-sm text-slate-500">No payment records found. Submit your first record using the form above.</p>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
                        <thead class="bg-slate-50 text-xs font-semibold uppercase tracking-wide text-slate-500">
                            <tr>
                                <th scope="col" class="px-4 py-3">Month</th>
                                <th scope="col" class="px-4 py-3">Amount</th>
                                <th scope="col" class="px-4 py-3">Salary Deduction</th>
                                <th scope="col" class="px-4 py-3">Status</th>
                                <th scope="col" class="px-4 py-3">Reviewer Note</th>
                                <th scope="col" class="px-4 py-3">Submitted</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white">
                            @foreach ($payments as $payment)
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
                                    <td class="px-4 py-3 text-xs text-slate-500">
                                        @if ($payment->review_note)
                                            {{ $payment->review_note }}
                                        @elseif ($payment->employee_note)
                                            <span class="text-slate-600">Employee note: {{ $payment->employee_note }}</span>
                                        @else
                                            &mdash;
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-xs text-slate-500">
                                        {{ $payment->created_at->format('M d, Y g:i A') }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    {{ $payments->links() }}
                </div>
            @endif
        </x-ksu-card>
    </div>
</x-ksu-layout>
