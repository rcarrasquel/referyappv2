<?php

namespace App\Mail;

use App\Models\Appointment;
use App\Models\Card;
use App\Models\Lead;
use App\Models\Product;
use App\Services\MailRuntimeConfigService;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PublicCardRequestNotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly Card $card,
        public readonly Product $product,
        public readonly string $requestType,
        public readonly string $ownerLanguage,
        public readonly ?Lead $lead = null,
        public readonly ?Appointment $appointment = null,
    ) {
    }

    public function envelope(): Envelope
    {
        $isEs = $this->ownerLanguage === 'es';
        $subject = $this->requestType === 'appointment'
            ? ($isEs ? 'Nueva cita agendada en tu tarjeta' : 'New appointment booked on your card')
            : ($isEs ? 'Nuevo formulario de contacto en tu tarjeta' : 'New contact form submission on your card');

        return new Envelope(subject: $subject);
    }

    public function content(): Content
    {
        app(MailRuntimeConfigService::class)->apply();

        $isEs = $this->ownerLanguage === 'es';
        $model = $this->requestType === 'appointment' ? $this->appointment : $this->lead;
        $startsAt = $this->appointment?->starts_at;
        $endsAt = $this->appointment?->ends_at;

        return new Content(
            view: 'emails.public.request-notification',
            with: [
                'isEs' => $isEs,
                'cardName' => (string) ($this->card->name ?: '@' . $this->card->username),
                'cardUsername' => (string) $this->card->username,
                'requestType' => $this->requestType,
                'fullName' => (string) ($model?->full_name ?? ''),
                'email' => (string) ($model?->email ?? ''),
                'phone' => (string) ($model?->phone ?? ''),
                'interest' => (string) ($model?->interest ?? ''),
                'notes' => (string) ($model?->notes ?? ''),
                'serviceName' => (string) ($this->product->name ?? ''),
                'duration' => (int) ($this->appointment?->duration_minutes ?? $this->product->duration_minutes ?? 0),
                'startsAt' => $startsAt ? ($isEs ? $startsAt->format('d/m/Y H:i') : $startsAt->format('Y-m-d H:i')) : '',
                'endsAt' => $endsAt ? ($isEs ? $endsAt->format('d/m/Y H:i') : $endsAt->format('Y-m-d H:i')) : '',
                'dashboardUrl' => rtrim((string) config('app.url', ''), '/') . '/dashboard',
            ],
        );
    }
}

