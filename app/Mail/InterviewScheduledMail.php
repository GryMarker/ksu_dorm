<?php

namespace App\Mail;

use App\Models\Interview;
use App\Models\Tenant;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;

class InterviewScheduledMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public Tenant $tenant, public Interview $interview)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your Dorm Interview Schedule',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.interviews.scheduled',
            with: [
                'tenant' => $this->tenant,
                'interview' => $this->interview,
                'scheduledAt' => Carbon::parse($this->interview->scheduled_at)->timezone(config('app.timezone')),
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
