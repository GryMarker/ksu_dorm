<?php

namespace App\Http\Middleware;

use App\Models\Tenant;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureEmployeeApproved
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user || !$user->isEmployee()) {
            return $next($request);
        }

        $tenant = $user->tenant;

        if (!$tenant) {
            return redirect()->route('employee.apply.form');
        }

        if ($tenant->onboarding_status !== Tenant::STATUS_APPROVED) {
            return redirect()->route('employee.status')
                ->with('status', 'Your onboarding is still pending approval.');
        }

        return $next($request);
    }
}
