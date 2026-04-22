<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\AttendanceLog;
use App\Services\AttendanceQrService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class QrAttendanceController extends Controller
{
    public function __construct(
        private readonly AttendanceQrService $attendanceQrService,
    ) {}

    public function show(Request $request): View|RedirectResponse
    {
        $tenant = $request->user()->tenant()->firstOrFail();
        $windowStart = (int) $request->integer('window');

        if (! $request->hasValidSignature(false) || ! $this->attendanceQrService->isWindowCurrentOrPrevious($windowStart)) {
            return redirect()->route('tenant.attendance.index')->withErrors('That QR code is no longer valid. Please scan the latest code.');
        }

        $latestLog = $tenant->attendanceLogs()->latest('timestamp')->first();

        return view('tenant.attendance-scan', [
            'tenant' => $tenant,
            'windowStart' => $windowStart,
            'expiresAt' => $this->attendanceQrService->expiresAtForWindow($windowStart),
            'nextType' => $latestLog?->type === 'in' ? 'out' : 'in',
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $tenant = $request->user()->tenant()->firstOrFail();
        $windowStart = (int) $request->integer('window');

        if (! $request->hasValidSignature(false) || ! $this->attendanceQrService->isWindowCurrentOrPrevious($windowStart)) {
            return redirect()->route('tenant.attendance.index')->withErrors('That QR code is no longer valid. Please scan the latest code.');
        }

        $submissionKey = 'attendance_qr_submission:'.$tenant->id.':'.$windowStart;

        if (Cache::has($submissionKey)) {
            return redirect()->route('tenant.attendance.index')->withErrors('You already used this QR code. Please wait for the next one.');
        }

        $latestLog = $tenant->attendanceLogs()->latest('timestamp')->first();
        $nextType = $latestLog?->type === 'in' ? 'out' : 'in';

        AttendanceLog::create([
            'tenant_id' => $tenant->id,
            'type' => $nextType,
            'timestamp' => now(),
            'mode' => 'qr',
            'device_id' => 'QR-'.substr(hash('sha256', ($request->userAgent() ?? 'unknown').'|'.$request->session()->getId()), 0, 12),
            'ip' => $request->ip(),
            'remarks' => 'QR attendance scan',
        ]);

        Cache::put($submissionKey, true, now()->addMinute());

        return redirect()
            ->route('tenant.attendance.index')
            ->with('status', 'Attendance '.($nextType === 'in' ? 'check-in' : 'check-out').' recorded successfully.');
    }
}
