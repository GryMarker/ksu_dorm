<?php

namespace App\Services;

use App\Models\User;
use App\Notifications\PasswordResetCode;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class PasswordResetCodeService
{
    public const TTL_MINUTES = 10;

    public function send(User $user): void
    {
        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        Cache::put($this->cacheKey($user->email), [
            'user_id' => $user->id,
            'code_hash' => hash('sha256', $code),
        ], now()->addMinutes(self::TTL_MINUTES));

        $user->notify(new PasswordResetCode($code, self::TTL_MINUTES));
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
