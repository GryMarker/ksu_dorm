<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AttendanceLog;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class AttendanceController extends Controller
{
    public function index(Request $request): View
    {
        $query = AttendanceLog::with('tenant.user')->orderByDesc('timestamp');

        if ($request->filled('tenant_id')) {
            $query->where('tenant_id', $request->input('tenant_id'));
        }

        if ($request->filled('date')) {
            $date = Carbon::parse($request->input('date'));
            $query->whereDate('timestamp', $date->toDateString());
        }

        $logs = $query->paginate(50);

        $tenants = Tenant::with('user')->get()->sortBy(fn ($tenant) => $tenant->user?->name)->values();

        return view('admin.attendance.index', [
            'logs' => $logs,
            'tenants' => $tenants,
            'filters' => $request->only(['tenant_id', 'date']),
        ]);
    }
}
