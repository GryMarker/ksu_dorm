<?php

namespace App\Mail;

use App\Models\Tenant;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ApplicationRejectedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public Tenant $tenant, public ?string $notes)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Dorm Application Update',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.applications.rejected',
            with: [
                'tenant' => $this->tenant,
                'notes' => $this->notes,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
