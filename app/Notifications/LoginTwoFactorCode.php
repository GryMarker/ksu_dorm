<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LoginTwoFactorCode extends Notification
{
    use Queueable;

    public function __construct(
        public readonly string $code,
        public readonly int $expiresInMinutes,
    ) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Your Dorm System Login Code')
            ->greeting('Login verification')
            ->line('Use this one-time code to finish signing in to the dorm system.')
            ->line('Code: '.$this->code)
            ->line('This code expires in '.$this->expiresInMinutes.' minutes and can only be used once.');
    }
}
