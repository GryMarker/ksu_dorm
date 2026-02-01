<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AttendanceLog;
use App\Models\Tenant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
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

    public function monthly(Request $request): View
    {
        $validated = $request->validate([
            'month' => ['nullable', 'date_format:Y-m'],
            'tenant_id' => ['nullable', 'exists:tenants,id'],
        ]);

        $monthString = $validated['month'] ?? now()->format('Y-m');
        $start = Carbon::createFromFormat('Y-m', $monthString)->startOfMonth();
        $end = (clone $start)->endOfMonth();

        $logsQuery = AttendanceLog::with('tenant.user')
            ->whereBetween('timestamp', [$start, $end])
            ->orderBy('timestamp');

        if (!empty($validated['tenant_id'])) {
            $logsQuery->where('tenant_id', $validated['tenant_id']);
        }

        $logs = $logsQuery->get();
        $dailyGrouped = $logs->groupBy(fn (AttendanceLog $log) => $log->tenant_id)
            ->map(fn ($tenantLogs) => $tenantLogs->groupBy(fn (AttendanceLog $log) => $log->timestamp->toDateString()));

        $tenants = Tenant::with('user')->orderBy('full_name')->get();

        return view('admin.attendance.monthly', [
            'logs' => $logs,
            'dailyGrouped' => $dailyGrouped,
            'tenants' => $tenants,
            'filters' => [
                'month' => $monthString,
                'tenant_id' => $validated['tenant_id'] ?? null,
            ],
            'range' => [$start, $end],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'tenant_id' => ['required', 'exists:tenants,id'],
            'type' => ['required', Rule::in(['in', 'out'])],
            'timestamp' => ['nullable', 'date'],
            'remarks' => ['nullable', 'string', 'max:255'],
        ]);

        $timestamp = $validated['timestamp']
            ? Carbon::parse($validated['timestamp'])
            : Carbon::now();

        AttendanceLog::create([
            'tenant_id' => $validated['tenant_id'],
            'type' => $validated['type'],
            'timestamp' => $timestamp,
            'mode' => 'manual',
            'device_id' => 'Manual Entry',
            'ip' => $request->ip(),
            'remarks' => $validated['remarks'] ?? null,
        ]);

        return redirect()
            ->route('admin.attendance.index')
            ->with('status', 'Attendance recorded successfully.');
    }
}
