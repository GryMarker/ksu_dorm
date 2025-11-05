<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\EmployeeCottage;
use App\Models\Tenant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class EmployeeCottageController extends Controller
{
    public function index(Request $request): View
    {
        $tenant = $request->user()->tenant()->with(['cottage', 'cottageRequest'])->firstOrFail();

        abort_if($tenant->type !== Tenant::TYPE_EMPLOYEE, 403);

        $cottages = EmployeeCottage::with(['tenant.user', 'requestedTenant.user'])
            ->orderBy('code')
            ->get();

        return view('employee.cottages.index', [
            'tenant' => $tenant,
            'cottages' => $cottages,
        ]);
    }

    public function request(Request $request, EmployeeCottage $cottage): RedirectResponse
    {
        $tenant = $request->user()->tenant()->firstOrFail();

        abort_if($tenant->type !== Tenant::TYPE_EMPLOYEE, 403);

        if ($tenant->cottage || $tenant->cottageRequest) {
            return redirect()
                ->route('employee.cottages.index')
                ->withErrors(['cottage' => 'You already have an active cottage assignment or pending request.']);
        }

        if ($cottage->status !== EmployeeCottage::STATUS_AVAILABLE) {
            return redirect()
                ->route('employee.cottages.index')
                ->withErrors(['cottage' => 'This cottage is no longer available. Please choose another one.']);
        }

        $cottage->forceFill([
            'status' => EmployeeCottage::STATUS_REQUESTED,
            'requested_tenant_id' => $tenant->id,
            'requested_at' => Carbon::now(),
            'family_members' => $tenant->family_members ?: [],
        ])->save();

        return redirect()
            ->route('employee.cottages.index')
            ->with('status', "{$cottage->code} requested successfully. Awaiting approval.");
    }
}

