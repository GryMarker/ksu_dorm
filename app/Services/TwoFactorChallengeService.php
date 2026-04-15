<?php

namespace App\Services;

use App\Models\User;
use App\Notifications\LoginTwoFactorCode;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class TwoFactorChallengeService
{
    public const SESSION_KEY = 'auth.two_factor_login';

    public const TTL_MINUTES = 10;

    public function begin(
        User $user,
        bool $remember = false,
        ?string $redirectRoute = null,
        bool $markEmailAsVerified = false,
    ): array {
        $attemptId = (string) Str::uuid();

        $this->storeChallenge($attemptId, $user->id, $remember, $redirectRoute, $markEmailAsVerified);
        $mailSent = $this->sendCode($user, $attemptId);

        return [
            'attempt_id' => $attemptId,
            'email' => $user->email,
            'mail_sent' => $mailSent,
        ];
    }

    public function hasActiveChallenge(string $attemptId): bool
    {
        return Cache::has($this->cacheKey($attemptId));
    }

    public function getPayload(string $attemptId): ?array
    {
        $payload = Cache::get($this->cacheKey($attemptId));

        return is_array($payload) ? $payload : null;
    }

    public function consume(string $attemptId): void
    {
        Cache::forget($this->cacheKey($attemptId));
    }

    public function resend(
        User $user,
        string $attemptId,
        bool $remember = false,
        ?string $redirectRoute = null,
        bool $markEmailAsVerified = false,
    ): bool {
        $this->storeChallenge($attemptId, $user->id, $remember, $redirectRoute, $markEmailAsVerified);

        return $this->sendCode($user, $attemptId);
    }

    public function cacheKey(string $attemptId): string
    {
        return 'login_2fa:'.$attemptId;
    }

    private function storeChallenge(
        string $attemptId,
        int $userId,
        bool $remember,
        ?string $redirectRoute,
        bool $markEmailAsVerified,
    ): void {
        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        Cache::put($this->cacheKey($attemptId), [
            'user_id' => $userId,
            'remember' => $remember,
            'redirect_route' => $redirectRoute,
            'mark_email_as_verified' => $markEmailAsVerified,
            'code_hash' => hash('sha256', $code),
            'plain_code' => $code,
        ], now()->addMinutes(self::TTL_MINUTES));
    }

    private function sendCode(User $user, string $attemptId): bool
    {
        $payload = $this->getPayload($attemptId);

        if (! $payload || empty($payload['plain_code'])) {
            return false;
        }

        try {
            $user->notify(new LoginTwoFactorCode($payload['plain_code'], self::TTL_MINUTES));
            $mailSent = true;
        } catch (Throwable $exception) {
            Log::error('Unable to send two-factor verification code.', [
                'user_id' => $user->id,
                'email' => $user->email,
                'exception' => $exception,
            ]);

            $mailSent = false;
        }

        if (! $mailSent && config('auth.log_codes')) {
            Log::info('Two-factor verification code fallback.', [
                'user_id' => $user->id,
                'email' => $user->email,
                'code' => $payload['plain_code'],
                'expires_in_minutes' => self::TTL_MINUTES,
            ]);
        }

        unset($payload['plain_code']);

        Cache::put($this->cacheKey($attemptId), $payload, now()->addMinutes(self::TTL_MINUTES));

        return $mailSent;
    }
}
