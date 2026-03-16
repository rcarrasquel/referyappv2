<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class MailConfigTestMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly string $recipientName,
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'ReferyApp SMTP Test - Configuration successful'
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.system.mail-config-test',
            with: [
                'recipientName' => $this->recipientName,
                'dashboardUrl' => rtrim((string) config('app.url', ''), '/') . '/dashboard',
            ],
        );
    }
}

