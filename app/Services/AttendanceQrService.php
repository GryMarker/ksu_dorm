<?php

namespace App\Services;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\URL;

class AttendanceQrService
{
    public const WINDOW_SECONDS = 30;

    public function currentWindowStart(?Carbon $time = null): int
    {
        $time ??= now();

        return (int) (floor($time->timestamp / self::WINDOW_SECONDS) * self::WINDOW_SECONDS);
    }

    public function expiresAtForWindow(int $windowStart): Carbon
    {
        return Carbon::createFromTimestamp($windowStart)->addSeconds(self::WINDOW_SECONDS);
    }

    public function signatureExpiresAtForWindow(int $windowStart): Carbon
    {
        return $this->expiresAtForWindow($windowStart)->addSeconds(self::WINDOW_SECONDS);
    }

    public function currentPayload(?Carbon $time = null): array
    {
        $windowStart = $this->currentWindowStart($time);
        $expiresAt = $this->expiresAtForWindow($windowStart);

        return [
            'window_start' => $windowStart,
            'expires_at' => $expiresAt->toIso8601String(),
            'scan_url' => URL::temporarySignedRoute(
                'tenant.attendance.scan',
                $this->signatureExpiresAtForWindow($windowStart),
                ['window' => $windowStart],
                false
            ),
        ];
    }

    public function isWindowCurrentOrPrevious(int $windowStart, ?Carbon $time = null): bool
    {
        $current = $this->currentWindowStart($time);

        return in_array($windowStart, [$current, $current - self::WINDOW_SECONDS], true);
    }
}
