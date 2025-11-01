<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\RedirectResponse;
use Illuminate\Contracts\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View|RedirectResponse
    {
        $user = $request->user();

        if ($user && $user->isEmployee()) {
            $tenant = $user->tenant;

            if (!$tenant) {
                return redirect()->route('employee.apply.form');
            }

            if ($tenant->onboarding_status !== Tenant::STATUS_APPROVED) {
                return redirect()->route('employee.status');
            }

            return redirect()->route('employee.dashboard');
        }

        if ($user && $user->isTenant()) {
            $tenant = $user->tenant;

            if (!$tenant || $tenant->onboarding_status !== Tenant::STATUS_APPROVED) {
                return redirect()->route('tenant.apply.form');
            }

            return redirect()->route('tenant.dashboard');
        }

        if ($user && $user->isPresident()) {
            return redirect()->route('president.approvals.employees.index');
        }

        return app(AdminDashboardController::class)($request);
    }
}

