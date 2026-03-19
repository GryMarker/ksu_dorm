<?php

namespace App\Services;

use App\Models\User;
use App\Notifications\LoginTwoFactorCode;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

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
        $this->sendCode($user, $attemptId);

        return [
            'attempt_id' => $attemptId,
            'email' => $user->email,
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
    ): void {
        $this->storeChallenge($attemptId, $user->id, $remember, $redirectRoute, $markEmailAsVerified);
        $this->sendCode($user, $attemptId);
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

    private function sendCode(User $user, string $attemptId): void
    {
        $payload = $this->getPayload($attemptId);

        if (! $payload || empty($payload['plain_code'])) {
            return;
        }

        $user->notify(new LoginTwoFactorCode($payload['plain_code'], self::TTL_MINUTES));

        unset($payload['plain_code']);

        Cache::put($this->cacheKey($attemptId), $payload, now()->addMinutes(self::TTL_MINUTES));
    }
}
