<?php

namespace App\Services;

use App\Models\User;
use App\Notifications\PasswordResetCode;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class PasswordResetCodeService
{
    public const TTL_MINUTES = 10;

    public function send(User $user): bool
    {
        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        Cache::put($this->cacheKey($user->email), [
            'user_id' => $user->id,
            'code_hash' => hash('sha256', $code),
        ], now()->addMinutes(self::TTL_MINUTES));

        try {
            $user->notify(new PasswordResetCode($code, self::TTL_MINUTES));

            return true;
        } catch (Throwable $exception) {
            Log::error('Unable to send password reset code.', [
                'user_id' => $user->id,
                'email' => $user->email,
                'exception' => $exception,
            ]);

            if (config('auth.log_codes')) {
                Log::info('Password reset code fallback.', [
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'code' => $code,
                    'expires_in_minutes' => self::TTL_MINUTES,
                ]);
            } else {
                Cache::forget($this->cacheKey($user->email));
            }

            return false;
        }
    }

    public function verify(string $email, string $code): ?User
    {
        $payload = Cache::get($this->cacheKey($email));

        if (! is_array($payload) || empty($payload['code_hash']) || empty($payload['user_id'])) {
            return null;
        }

        if (! hash_equals($payload['code_hash'], hash('sha256', $code))) {
            return null;
        }

        return User::query()->find($payload['user_id']);
    }

    public function consume(string $email): void
    {
        Cache::forget($this->cacheKey($email));
    }

    private function cacheKey(string $email): string
    {
        return 'password_reset_code:'.Str::lower($email);
    }
}
