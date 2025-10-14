<?php

namespace App\Http\Middleware;

use App\Models\Tenant;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTenantApproved
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user || !$user->isTenant()) {
            return $next($request);
        }

        $tenant = $user->tenant;

        if (!$tenant || $tenant->admission_status !== Tenant::STATUS_APPROVED) {
            return redirect()->route('tenant.apply.form')->with('status', 'Finish your application and interview to proceed.');
        }

        return $next($request);
    }
}
