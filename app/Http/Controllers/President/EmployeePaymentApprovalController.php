<?php

namespace App\Http\Controllers\President;

use App\Http\Controllers\Controller;
use App\Models\EmployeePayment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class EmployeePaymentApprovalController extends Controller
{
    public function index(Request $request): View
    {
        $pendingPayments = EmployeePayment::with(['tenant.user'])
            ->where('status', EmployeePayment::STATUS_PENDING)
            ->orderByDesc('billing_month')
            ->paginate(15);

        $recentPayments = EmployeePayment::with(['tenant.user'])
            ->where('status', EmployeePayment::STATUS_APPROVED)
            ->orderByDesc('reviewed_at')
            ->take(10)
            ->get();

        return view('president.approvals.payments', [
            'pendingPayments' => $pendingPayments,
            'recentPayments' => $recentPayments,
        ]);
    }

    public function approve(Request $request, EmployeePayment $payment): RedirectResponse
    {
        if ($payment->status !== EmployeePayment::STATUS_PENDING) {
            return redirect()
                ->route('president.payments.index')
                ->withErrors(['payment' => 'Only pending payments can be approved.']);
        }

        $data = $request->validate([
            'amount' => ['nullable', 'numeric', 'min:0'],
            'review_note' => ['nullable', 'string', 'max:500'],
        ]);

        if (array_key_exists('amount', $data) && $data['amount'] !== null) {
            $payment->amount = $data['amount'];
        }

        $payment->forceFill([
            'status' => EmployeePayment::STATUS_APPROVED,
            'review_note' => $data['review_note'] ?? null,
            'reviewed_by' => $request->user()->id,
            'reviewed_at' => Carbon::now(),
        ])->save();

        return redirect()
            ->route('president.payments.index')
            ->with('status', 'Payment record approved.');
    }

    public function reject(Request $request, EmployeePayment $payment): RedirectResponse
    {
        if ($payment->status !== EmployeePayment::STATUS_PENDING) {
            return redirect()
                ->route('president.payments.index')
                ->withErrors(['payment' => 'Only pending payments can be rejected.']);
        }

        $data = $request->validate([
            'amount' => ['nullable', 'numeric', 'min:0'],
            'review_note' => ['required', 'string', 'max:500'],
        ]);

        if (array_key_exists('amount', $data) && $data['amount'] !== null) {
            $payment->amount = $data['amount'];
        }

        $payment->forceFill([
            'status' => EmployeePayment::STATUS_REJECTED,
            'review_note' => $data['review_note'],
            'reviewed_by' => $request->user()->id,
            'reviewed_at' => Carbon::now(),
        ])->save();

        return redirect()
            ->route('president.payments.index')
            ->with('status', 'Payment record rejected.');
    }
}
