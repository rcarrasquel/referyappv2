<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BillingPlanNotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly User $user,
        public readonly string $type,
        public readonly array $context = [],
    ) {
    }

    public function envelope(): Envelope
    {
        $isEs = $this->language() === 'es';

        $subject = match ($this->type) {
            'purchase_started' => $isEs
                ? 'Recibimos tu solicitud de compra Business'
                : 'We received your Business purchase request',
            'payment_confirmed' => $isEs
                ? 'Pago confirmado en ReferyApp'
                : 'Payment confirmed on ReferyApp',
            'plan_upgraded' => $isEs
                ? 'Tu plan fue actualizado: Free a Business'
                : 'Your plan was updated: Free to Business',
            'plan_downgraded' => $isEs
                ? 'Tu plan fue actualizado: Business a Free'
                : 'Your plan was updated: Business to Free',
            'payment_failed' => $isEs
                ? 'No se pudo procesar tu pago'
                : 'Your payment could not be processed',
            'fraud_alert' => $isEs
                ? 'Alerta de seguridad en tu suscripcion'
                : 'Security alert on your subscription',
            default => $isEs ? 'Notificacion de facturacion' : 'Billing notification',
        };

        return new Envelope(subject: $subject);
    }

    public function content(): Content
    {
        $isEs = $this->language() === 'es';
        $amountCents = (int) ($this->context['amount_cents'] ?? 900);
        $currency = strtoupper((string) ($this->context['currency'] ?? 'USD'));
        $formattedAmount = number_format($amountCents / 100, 2);
        $eventDate = (string) ($this->context['event_date'] ?? now()->format($isEs ? 'd/m/Y H:i' : 'Y-m-d H:i'));
        $reference = (string) ($this->context['reference'] ?? '');
        $reason = (string) ($this->context['reason'] ?? '');

        return new Content(
            view: 'emails.billing.plan-notification',
            with: [
                'isEs' => $isEs,
                'name' => (string) ($this->user->name ?? ''),
                'type' => $this->type,
                'amount' => $formattedAmount,
                'currency' => $currency,
                'eventDate' => $eventDate,
                'reference' => $reference,
                'reason' => $reason,
                'dashboardUrl' => rtrim((string) config('app.url', ''), '/') . '/dashboard',
            ],
        );
    }

    private function language(): string
    {
        return in_array($this->user->language, ['en', 'es'], true) ? $this->user->language : 'en';
    }
}
