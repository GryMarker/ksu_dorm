<?php

namespace App\Mail;

use App\Models\Reservation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TransferStatusMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public Reservation $reservation, public string $status)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Transfer ' . ucfirst($this->status),
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.transfers.status',
            with: [
                'reservation' => $this->reservation,
                'status' => $this->status,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
