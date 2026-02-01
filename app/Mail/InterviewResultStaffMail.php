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

class InterviewResultStaffMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public Tenant $tenant, public Interview $interview)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Interview Result Logged',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.interviews.result-staff',
            with: [
                'tenant' => $this->tenant,
                'interview' => $this->interview,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
