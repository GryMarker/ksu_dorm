<?php

namespace App\Mail;

use Illuminate\Support\Facades\Http;
use Symfony\Component\Mailer\Envelope;
use Symfony\Component\Mailer\Exception\TransportException;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Transport\AbstractTransport;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;

class BrevoApiTransport extends AbstractTransport
{
    public function __construct(
        private readonly string $apiKey,
        private readonly int $timeout = 10,
    ) {
        parent::__construct();
    }

    public function __toString(): string
    {
        return 'brevo+api';
    }

    protected function doSend(SentMessage $message): void
    {
        $email = $message->getOriginalMessage();

        if (! $email instanceof Email) {
            throw new TransportException('Brevo API transport only supports Symfony Email messages.');
        }

        $payload = $this->payload($email, $message->getEnvelope());

        $response = Http::timeout($this->timeout)
            ->withHeaders([
                'accept' => 'application/json',
                'api-key' => $this->apiKey,
            ])
            ->post('https://api.brevo.com/v3/smtp/email', $payload);

        if ($response->failed()) {
            throw new TransportException(
                sprintf('Brevo API mail send failed with HTTP %s: %s', $response->status(), $response->body())
            );
        }
    }

    private function payload(Email $email, Envelope $envelope): array
    {
        $from = $email->getFrom()[0] ?? $envelope->getSender();

        $payload = [
            'sender' => $this->address($from),
            'to' => $this->addresses($email->getTo() ?: $envelope->getRecipients()),
            'subject' => $email->getSubject() ?? '',
        ];

        if ($email->getHtmlBody()) {
            $payload['htmlContent'] = $email->getHtmlBody();
        }

        if ($email->getTextBody()) {
            $payload['textContent'] = $email->getTextBody();
        }

        if (! isset($payload['htmlContent'], $payload['textContent'])) {
            $payload['textContent'] ??= $email->getBody()->toString();
        }

        if ($email->getCc()) {
            $payload['cc'] = $this->addresses($email->getCc());
        }

        if ($email->getBcc()) {
            $payload['bcc'] = $this->addresses($email->getBcc());
        }

        if ($email->getReplyTo()) {
            $payload['replyTo'] = $this->address($email->getReplyTo()[0]);
        }

        return $payload;
    }

    /**
     * @param  array<int, Address>  $addresses
     * @return array<int, array{email: string, name?: string}>
     */
    private function addresses(array $addresses): array
    {
        return array_map(fn (Address $address): array => $this->address($address), $addresses);
    }

    /**
     * @return array{email: string, name?: string}
     */
    private function address(Address $address): array
    {
        $payload = ['email' => $address->getAddress()];

        if ($address->getName() !== '') {
            $payload['name'] = $address->getName();
        }

        return $payload;
    }
}
