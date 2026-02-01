<?php

namespace App\Mail;

use App\Models\Tenant;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ApplicationSubmittedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public Tenant $tenant)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'New Student Application Submitted',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.applications.submitted',
            with: [
                'tenant' => $this->tenant,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
