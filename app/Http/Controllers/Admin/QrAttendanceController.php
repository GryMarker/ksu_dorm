<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AttendanceLog;
use App\Services\AttendanceQrService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class QrAttendanceController extends Controller
{
    public function __construct(
        private readonly AttendanceQrService $attendanceQrService,
    ) {}

    public function show(Request $request): View
    {
        abort_unless($request->user()?->isDormMaster(), 403);

        return view('admin.attendance.qr', [
            'qrPayload' => $this->attendanceQrService->currentPayload(),
            'recentLogs' => $this->recentLogs(),
        ]);
    }

    public function current(Request $request): JsonResponse
    {
        abort_unless($request->user()?->isDormMaster(), 403);

        return response()->json([
            'qr' => $this->attendanceQrService->currentPayload(),
            'recent_logs' => $this->recentLogs(),
        ]);
    }

    private function recentLogs()
    {
        return AttendanceLog::query()
            ->with('tenant.user')
            ->orderByDesc('timestamp')
            ->limit(10)
            ->get()
            ->map(fn (AttendanceLog $log) => [
                'tenant' => $log->tenant?->user?->name,
                'type' => $log->type,
                'timestamp' => $log->timestamp?->timezone(config('app.timezone'))->format('M d, Y h:i:s A'),
                'mode' => strtoupper($log->mode),
            ])
            ->values();
    }
}
