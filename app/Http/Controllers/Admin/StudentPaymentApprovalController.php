<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StudentPayment;
use App\Models\Tenant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class StudentPaymentApprovalController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless($request->user()?->isDormMaster(), 403);

        $pendingPayments = StudentPayment::with(['tenant.user'])
            ->whereHas('tenant', fn ($query) => $query->where('type', Tenant::TYPE_STUDENT))
            ->where('status', StudentPayment::STATUS_PENDING)
            ->orderByDesc('billing_month')
            ->paginate(15);

        $recentPayments = StudentPayment::with(['tenant.user'])
            ->whereHas('tenant', fn ($query) => $query->where('type', Tenant::TYPE_STUDENT))
            ->where('status', StudentPayment::STATUS_APPROVED)
            ->orderByDesc('reviewed_at')
            ->take(10)
            ->get();

        return view('admin.payments.index', [
            'pendingPayments' => $pendingPayments,
            'recentPayments' => $recentPayments,
        ]);
    }

    public function approve(Request $request, StudentPayment $payment): RedirectResponse
    {
        abort_unless($request->user()?->isDormMaster(), 403);

        if ($payment->status !== StudentPayment::STATUS_PENDING) {
            return redirect()
                ->route('admin.payments.index')
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
            'status' => StudentPayment::STATUS_APPROVED,
            'review_note' => $data['review_note'] ?? null,
            'reviewed_by' => $request->user()->id,
            'reviewed_at' => Carbon::now(),
        ])->save();

        return redirect()
            ->route('admin.payments.index')
            ->with('status', 'Student payment record approved.');
    }

    public function reject(Request $request, StudentPayment $payment): RedirectResponse
    {
        abort_unless($request->user()?->isDormMaster(), 403);

        if ($payment->status !== StudentPayment::STATUS_PENDING) {
            return redirect()
                ->route('admin.payments.index')
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
            'status' => StudentPayment::STATUS_REJECTED,
            'review_note' => $data['review_note'],
            'reviewed_by' => $request->user()->id,
            'reviewed_at' => Carbon::now(),
        ])->save();

        return redirect()
            ->route('admin.payments.index')
            ->with('status', 'Student payment record rejected.');
    }
}
