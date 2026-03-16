<?php

namespace App\Notifications;

use App\Services\MailRuntimeConfigService;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;

class VerifyEmailNotification extends VerifyEmail
{
    public function toMail($notifiable): MailMessage
    {
        app(MailRuntimeConfigService::class)->apply();

        $verificationUrl = $this->verificationUrl($notifiable);

        return (new MailMessage)
            ->subject('Verify your ReferyApp account')
            ->view('emails.auth.verify-email', [
                'name' => (string) ($notifiable->name ?? ''),
                'verificationUrl' => $verificationUrl,
                'supportUrl' => 'https://xper.team',
            ]);
    }
}

