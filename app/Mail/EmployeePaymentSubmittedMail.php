<?php

namespace App\Mail;

use App\Models\EmployeePayment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class EmployeePaymentSubmittedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public EmployeePayment $payment)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'New Employee Payment Submitted',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.payments.employee-submitted',
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
