<?php

namespace App\Http\Controllers\President;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EmployeeApprovalController extends Controller
{
    public function index(Request $request): View
    {
        $tenants = Tenant::with('user')
            ->where('type', Tenant::TYPE_EMPLOYEE)
            ->whereIn('onboarding_status', [
                Tenant::STATUS_FOR_APPROVAL,
                Tenant::STATUS_RECHECK,
            ])
            ->orderByDesc('created_at')
            ->paginate(15);

        $approvedTenants = Tenant::with('user')
            ->where('type', Tenant::TYPE_EMPLOYEE)
            ->where('onboarding_status', Tenant::STATUS_APPROVED)
            ->orderByDesc('updated_at')
            ->take(10)
            ->get();

        return view('president.approvals.employees', [
            'pendingTenants' => $tenants,
            'recentTenants' => $approvedTenants,
        ]);
    }

    public function approve(Request $request, Tenant $tenant): RedirectResponse
    {
        abort_unless($tenant->type === Tenant::TYPE_EMPLOYEE, 404);

        $tenant->forceFill([
            'onboarding_status' => Tenant::STATUS_APPROVED,
        ])->save();

        return redirect()
            ->route('president.approvals.employees.index')
            ->with('status', "{$tenant->full_name} has been approved.");
    }

    public function reject(Request $request, Tenant $tenant): RedirectResponse
    {
        abort_unless($tenant->type === Tenant::TYPE_EMPLOYEE, 404);

        $tenant->forceFill([
            'onboarding_status' => Tenant::STATUS_REJECTED,
        ])->save();

        return redirect()
            ->route('president.approvals.employees.index')
            ->with('status', "{$tenant->full_name} has been rejected.");
    }
}
