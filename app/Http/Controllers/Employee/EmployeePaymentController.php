<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Http\Requests\EmployeePaymentRequest;
use App\Models\EmployeePayment;
use App\Models\Tenant;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class EmployeePaymentController extends Controller
{
    public function index(Request $request): View
    {
        $tenant = $request->user()->tenant()->firstOrFail();

        abort_if($tenant->type !== Tenant::TYPE_EMPLOYEE, 403);

        $payments = $tenant->employeePayments()
            ->latest('billing_month')
            ->paginate(12);

        $defaultMonth = Carbon::now()->startOfMonth()->format('Y-m');

        return view('employee.payments.index', [
            'tenant' => $tenant,
            'payments' => $payments,
            'defaultMonth' => $defaultMonth,
        ]);
    }

    public function store(EmployeePaymentRequest $request): RedirectResponse
    {
        $tenant = $request->user()->tenant()->firstOrFail();

        abort_if($tenant->type !== Tenant::TYPE_EMPLOYEE, 403);

        $validated = $request->validated();

        $billingMonth = Carbon::createFromFormat('Y-m', $validated['billing_month'])->startOfMonth();
        $salaryDeduction = array_key_exists('salary_deduction', $validated)
            ? $request->boolean('salary_deduction')
            : (bool) $tenant->salary_deduction;

        $amount = $validated['amount'] ?? $tenant->monthly_rate ?? Tenant::DEFAULT_EMPLOYEE_MONTHLY_RATE;

        try {
            $tenant->employeePayments()->create([
                'billing_month' => $billingMonth,
                'amount' => $amount,
                'salary_deduction' => $salaryDeduction,
                'status' => EmployeePayment::STATUS_PENDING,
                'employee_note' => $validated['employee_note'] ?? null,
            ]);
        } catch (QueryException $exception) {
            if ($exception->getCode() === '23000') {
                return redirect()
                    ->route('employee.payments.index')
                    ->withErrors(['billing_month' => 'A payment record for this month already exists.']);
            }

            throw $exception;
        }

        return redirect()
            ->route('employee.payments.index')
            ->with('status', 'Payment record submitted for approval.');
    }
}
