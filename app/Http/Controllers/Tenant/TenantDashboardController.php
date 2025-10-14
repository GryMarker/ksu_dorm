<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class TenantDashboardController extends Controller
{
    public function index(Request $request): View
    {
        $tenant = $request->user()->tenant()->with([
            'interviews' => fn ($query) => $query->latest('scheduled_at'),
            'activeAssignment.room',
            'activeAssignment.bed',
        ])->firstOrFail();

        $recentAttendanceCount = $tenant->attendanceLogs()
            ->where('timestamp', '>=', Carbon::now()->subDays(7))
            ->count();

        return view('tenant.dashboard', [
            'tenant' => $tenant,
            'recentAttendanceCount' => $recentAttendanceCount,
        ]);
    }
}
