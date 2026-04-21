<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\StudentPaymentRequest;
use App\Mail\StudentPaymentSubmittedMail;
use App\Models\StudentPayment;
use App\Models\Tenant;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class StudentPaymentController extends Controller
{
    public function index(Request $request): View
    {
        $tenant = $request->user()->tenant()->firstOrFail();

        abort_if($tenant->type !== Tenant::TYPE_STUDENT, 403);

        $payments = $tenant->studentPayments()
            ->latest('billing_month')
            ->paginate(12);

        return view('tenant.payments.index', [
            'tenant' => $tenant,
            'payments' => $payments,
            'defaultMonth' => Carbon::now()->startOfMonth()->format('Y-m'),
        ]);
    }

    public function store(StudentPaymentRequest $request): RedirectResponse
    {
        $tenant = $request->user()->tenant()->firstOrFail();

        abort_if($tenant->type !== Tenant::TYPE_STUDENT, 403);

        $validated = $request->validated();
        $billingMonth = Carbon::createFromFormat('Y-m', $validated['billing_month'])->startOfMonth();

        try {
            $payment = $tenant->studentPayments()->create([
                'billing_month' => $billingMonth,
                'amount' => $validated['amount'],
                'status' => StudentPayment::STATUS_PENDING,
                'student_note' => $validated['student_note'] ?? null,
            ]);
        } catch (QueryException $exception) {
            if ($exception->getCode() === '23000') {
                return redirect()
                    ->route('tenant.payments.index')
                    ->withErrors(['billing_month' => 'A payment record for this month already exists.']);
            }

            throw $exception;
        }

        $payment->load('tenant.user');

        User::where('role', User::ROLE_DORM_MASTER)->get()
            ->each(fn (User $dormMaster) => NotificationService::queueMail(
                $dormMaster,
                new StudentPaymentSubmittedMail($payment),
                'student.payment.submitted',
                [
                    'payment_id' => $payment->id,
                    'tenant_id' => $tenant->id,
                    'billing_month' => $billingMonth->toDateString(),
                    'amount' => $payment->amount,
                ]
            ));

        return redirect()
            ->route('tenant.payments.index')
            ->with('status', 'Payment record submitted for Dorm Master review.');
    }
}
