<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\AttendanceLogRequest;
use App\Models\AttendanceLog;
use App\Models\Tenant;
use Illuminate\Support\Carbon;
use Symfony\Component\HttpFoundation\Response;

class AttendanceScanController extends Controller
{
    public function __invoke(AttendanceLogRequest $request)
    {
        $validated = $request->validated();

        $tenant = Tenant::findOrFail($validated['tenant_id']);

        $latestLog = $tenant->attendanceLogs()->orderByDesc('timestamp')->first();
        if ($latestLog && $latestLog->type === $validated['type']) {
            return response()->json([
                'message' => 'Duplicate consecutive scan is not allowed.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $timestamp = isset($validated['timestamp']) ? Carbon::parse($validated['timestamp']) : Carbon::now();

        $log = $tenant->attendanceLogs()->create([
            'type' => $validated['type'],
            'timestamp' => $timestamp,
            'mode' => $validated['mode'] ?? 'manual',
            'device_id' => $validated['device_id'] ?? null,
            'ip' => $validated['ip'] ?? $request->ip(),
            'remarks' => $validated['remarks'] ?? null,
        ]);

        return response()->json([
            'message' => 'Attendance logged successfully.',
            'data' => $log,
        ], Response::HTTP_CREATED);
    }
}
