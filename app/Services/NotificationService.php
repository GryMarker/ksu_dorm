<?php

namespace App\Services;

use App\Models\NotificationLog;
use App\Models\User;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Mail;

class NotificationService
{
    public static function queueMail(User $user, Mailable $mailable, string $templateKey, array $payload = []): void
    {
        $log = NotificationLog::create([
            'user_id' => $user->id,
            'channel' => 'email',
            'template_key' => $templateKey,
            'payload_json' => $payload,
            'status' => 'queued',
        ]);

        try {
            Mail::to($user)->queue($mailable);

            $log->update([
                'status' => 'sent',
                'sent_at' => now(),
            ]);
        } catch (\Throwable $throwable) {
            $log->update([
                'status' => 'failed',
                'payload_json' => array_merge($payload, [
                    'error' => $throwable->getMessage(),
                ]),
            ]);

            report($throwable);
        }
    }
}
