<?php

namespace App\Jobs;

use App\Mail\PublicCardRequestNotificationMail;
use App\Models\Appointment;
use App\Models\Card;
use App\Models\Lead;
use App\Models\Product;
use App\Models\User;
use App\Services\MailRuntimeConfigService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendPublicCardRequestNotificationJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;
    public int $timeout = 90;

    public function __construct(
        public string $cardId,
        public string $productId,
        public string $requestType,
        public ?string $leadId = null,
        public ?string $appointmentId = null,
    ) {
    }

    public function handle(): void
    {
        $card = Card::query()->find($this->cardId);
        $product = Product::query()->find($this->productId);

        if (! $card || ! $product) {
            return;
        }

        $owner = User::query()->find($card->user_id);
        if (! $owner || ! $owner->email) {
            return;
        }

        $language = in_array($owner->language, ['es', 'en'], true) ? $owner->language : 'en';

        $lead = $this->leadId ? Lead::query()->find($this->leadId) : null;
        $appointment = $this->appointmentId ? Appointment::query()->find($this->appointmentId) : null;

        app(MailRuntimeConfigService::class)->apply();

        Mail::to($owner->email)->send(
            new PublicCardRequestNotificationMail(
                card: $card,
                product: $product,
                requestType: $this->requestType,
                ownerLanguage: $language,
                lead: $lead,
                appointment: $appointment,
            )
        );

        Log::info('Public card request email queued/sent', [
            'type' => $this->requestType,
            'owner_email' => $owner->email,
            'card_id' => $card->id,
            'lead_id' => $lead?->id,
            'appointment_id' => $appointment?->id,
        ]);
    }
}

