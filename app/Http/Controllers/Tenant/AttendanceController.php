<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AttendanceController extends Controller
{
    public function index(Request $request): View
    {
        $tenant = $request->user()->tenant()->firstOrFail();

        $logs = $tenant->attendanceLogs()->orderByDesc('timestamp')->paginate(25);
        $latestLog = $tenant->attendanceLogs()->latest('timestamp')->first();
        $todayLogs = $tenant->attendanceLogs()
            ->whereDate('timestamp', today())
            ->orderByDesc('timestamp')
            ->get();

        return view('tenant.attendance', [
            'tenant' => $tenant,
            'logs' => $logs,
            'latestLog' => $latestLog,
            'todayLogs' => $todayLogs,
        ]);
    }
}
