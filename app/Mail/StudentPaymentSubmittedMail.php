<?php

namespace App\Mail;

use App\Models\StudentPayment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class StudentPaymentSubmittedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public StudentPayment $payment)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'New Student Payment Submitted',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.payments.student-submitted',
            with: [
                'payment' => $this->payment,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
