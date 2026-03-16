<?php

namespace App\Http\Controllers;

use App\Mail\BillingPlanNotificationMail;
use App\Models\BillingTransaction;
use App\Models\StripeSetting;
use App\Models\User;
use App\Models\Card;
use App\Models\Product;
use App\Services\MailRuntimeConfigService;
use App\Services\StripeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class BillingController extends Controller
{
    public function checkout(Request $request, StripeService $stripe): RedirectResponse|SymfonyResponse
    {
        $user = $request->user();
        abort_unless($user && $user->role === 'business', 403);

        if ($user->plan === 'business') {
            return back()->with('status', $this->statusMessage($user, 'already_business'));
        }

        $successUrl = url('/profile?billing=success&session_id={CHECKOUT_SESSION_ID}');
        $cancelUrl = url('/profile?billing=cancel');

        $session = $stripe->createCheckoutSession($user, $successUrl, $cancelUrl);
        $this->sendBillingEmail($user, 'purchase_started', [
            'amount_cents' => 900,
            'currency' => 'usd',
            'event_date' => now()->format($user->language === 'es' ? 'd/m/Y H:i' : 'Y-m-d H:i'),
            'reference' => (string) ($session['id'] ?? ''),
        ]);

        if ($request->header('X-Inertia')) {
            return Inertia::location((string) $session['url']);
        }

        return redirect()->away((string) $session['url']);
    }

    public function syncSession(Request $request, StripeService $stripe): RedirectResponse
    {
        $validated = $request->validate([
            'session_id' => ['required', 'string', 'max:120'],
        ]);

        $user = $request->user();
        abort_unless($user && $user->role === 'business', 403);

        $previousPlan = (string) $user->plan;
        $stripe->syncCheckoutSessionForUser($user, (string) $validated['session_id']);
        $refreshedUser = $user->fresh() ?? $user;

        $latestPaidTransaction = BillingTransaction::query()
            ->where('user_id', $refreshedUser->id)
            ->where(function ($query): void {
                $query
                    ->whereNotNull('paid_at')
                    ->orWhereIn('status', ['paid', 'succeeded']);
            })
            ->latest('paid_at')
            ->latest('id')
            ->first();

        $context = [
            'amount_cents' => (int) ($latestPaidTransaction?->amount_cents ?? 900),
            'currency' => (string) ($latestPaidTransaction?->currency ?? 'usd'),
            'event_date' => optional($latestPaidTransaction?->paid_at)->format(
                $refreshedUser->language === 'es' ? 'd/m/Y H:i' : 'Y-m-d H:i'
            ) ?: now()->format($refreshedUser->language === 'es' ? 'd/m/Y H:i' : 'Y-m-d H:i'),
            'reference' => (string) ($latestPaidTransaction?->stripe_invoice_id ?? $validated['session_id']),
        ];

        $this->sendBillingEmail($refreshedUser, 'payment_confirmed', $context);

        if ($previousPlan !== 'business' && $refreshedUser->plan === 'business') {
            $this->sendBillingEmail($refreshedUser, 'plan_upgraded', $context);
        }

        return back()->with('status', $this->statusMessage($user, 'updated'));
    }

    public function cancelSubscription(Request $request, StripeService $stripe): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user && $user->role === 'business', 403);

        if ($user->plan !== 'business') {
            return back()->with('status', $this->statusMessage($user, 'already_free'));
        }

        DB::transaction(function () use ($stripe, $user): void {
            $lockedUser = User::query()->lockForUpdate()->findOrFail($user->id);
            $stripe->cancelActiveSubscription($lockedUser);
            $this->enforceFreeLimits($lockedUser->fresh());
        });

        $freshUser = $user->fresh() ?? $user;
        $this->sendBillingEmail($freshUser, 'plan_downgraded', [
            'event_date' => now()->format($freshUser->language === 'es' ? 'd/m/Y H:i' : 'Y-m-d H:i'),
            'reference' => (string) ($freshUser->stripe_subscription_id ?? ''),
        ]);

        return back()->with('status', $this->statusMessage($user, 'canceled'));
    }

    public function webhook(Request $request, StripeService $stripe): Response
    {
        $payload = (string) $request->getContent();
        $signature = (string) $request->header('Stripe-Signature', '');

        $settings = StripeSetting::query()->latest('id')->first();
        if (! $settings || ! $settings->webhook_secret) {
            return response('Webhook not configured.', 400);
        }

        if (! $stripe->verifyWebhookSignature($payload, $signature, (string) $settings->webhook_secret)) {
            return response('Invalid signature.', 400);
        }

        $event = json_decode($payload, true);
        $type = (string) ($event['type'] ?? '');
        $object = (array) ($event['data']['object'] ?? []);

        if ($type === 'checkout.session.completed') {
            $sessionId = (string) ($object['id'] ?? '');
            if ($sessionId !== '') {
                $checkoutSession = $stripe->fetchCheckoutSessionById($sessionId);
                if (! empty($checkoutSession)) {
                    $customerId = (string) ($checkoutSession['customer'] ?? '');
                    $user = $customerId !== ''
                        ? User::query()->where('stripe_customer_id', $customerId)->first()
                        : null;

                    if ($user) {
                        $stripe->syncCheckoutSessionForUser($user, $sessionId);
                    }
                }
            }
        }

        if (in_array($type, ['customer.subscription.created', 'customer.subscription.updated'], true)) {
            $stripe->syncUserFromSubscriptionPayload($object);
        }

        if ($type === 'customer.subscription.deleted') {
            $downgradedUser = $stripe->downgradeUserFromSubscriptionCancelled($object);
            if ($downgradedUser) {
                $this->enforceFreeLimits($downgradedUser);
            }
        }

        if (in_array($type, ['invoice.paid', 'invoice.payment_succeeded'], true)) {
            $stripe->recordTransactionFromInvoice($object);
        }

        if ($type === 'invoice.payment_failed') {
            $stripe->recordTransactionFromInvoice($object);
            $user = $stripe->findUserFromStripeObject($object);
            if ($user) {
                $this->sendBillingEmail($user, 'payment_failed', [
                    'amount_cents' => (int) ($object['amount_due'] ?? 900),
                    'currency' => (string) ($object['currency'] ?? 'usd'),
                    'event_date' => now()->format($user->language === 'es' ? 'd/m/Y H:i' : 'Y-m-d H:i'),
                    'reference' => (string) ($object['id'] ?? ''),
                    'reason' => (string) ($object['last_finalization_error']['message'] ?? ''),
                ]);
            }
        }

        if ($type === 'payment_intent.payment_failed') {
            $stripe->recordTransactionFromPaymentIntent($object);
            if (! empty($object['invoice'])) {
                return response('ok', 200);
            }
            $user = $stripe->findUserFromStripeObject($object);
            if ($user) {
                $this->sendBillingEmail($user, 'payment_failed', [
                    'amount_cents' => (int) ($object['amount'] ?? 900),
                    'currency' => (string) ($object['currency'] ?? 'usd'),
                    'event_date' => now()->format($user->language === 'es' ? 'd/m/Y H:i' : 'Y-m-d H:i'),
                    'reference' => (string) ($object['id'] ?? ''),
                    'reason' => (string) ($object['last_payment_error']['message'] ?? ''),
                ]);
            }
        }

        if (in_array($type, ['review.opened', 'radar.early_fraud_warning.created', 'charge.dispute.created'], true)) {
            $user = $stripe->findUserFromStripeObject($object);
            if ($user) {
                $reason = (string) ($object['reason'] ?? $object['type'] ?? '');
                if ($reason === '' && isset($object['fraud_type'])) {
                    $reason = (string) $object['fraud_type'];
                }

                $this->sendBillingEmail($user, 'fraud_alert', [
                    'currency' => 'usd',
                    'event_date' => now()->format($user->language === 'es' ? 'd/m/Y H:i' : 'Y-m-d H:i'),
                    'reference' => (string) ($object['id'] ?? ''),
                    'reason' => $reason !== '' ? $reason : $type,
                ]);
            }
        }

        return response('ok', 200);
    }

    private function enforceFreeLimits(User $user): void
    {
        $keepCardIds = Card::query()
            ->where('user_id', $user->id)
            ->orderBy('created_at')
            ->limit(1)
            ->pluck('id')
            ->all();

        $cardsToDelete = Card::query()
            ->where('user_id', $user->id)
            ->when(! empty($keepCardIds), fn ($query) => $query->whereNotIn('id', $keepCardIds))
            ->get(['id']);

        foreach ($cardsToDelete as $card) {
            Storage::disk('public')->deleteDirectory("cards/{$card->id}");
            $card->delete();
        }

        $keepProductIds = Product::query()
            ->where('user_id', $user->id)
            ->orderBy('created_at')
            ->limit(2)
            ->pluck('id')
            ->all();

        $productsToDelete = Product::query()
            ->where('user_id', $user->id)
            ->when(! empty($keepProductIds), fn ($query) => $query->whereNotIn('id', $keepProductIds))
            ->get(['id', 'image']);

        foreach ($productsToDelete as $product) {
            if ($product->image) {
                Storage::disk('public')->delete($product->image);
            }
            Storage::disk('public')->deleteDirectory("products/{$product->id}");
            $product->delete();
        }
    }

    private function statusMessage(User $user, string $key): string
    {
        $messages = [
            'already_business' => [
                'en' => 'Your account is already on the business plan.',
                'es' => 'Tu cuenta ya se encuentra en el plan business.',
            ],
            'updated' => [
                'en' => 'Your plan has been updated successfully.',
                'es' => 'Tu plan se actualizo correctamente.',
            ],
            'already_free' => [
                'en' => 'Your account is already on free plan.',
                'es' => 'Tu cuenta ya se encuentra en plan free.',
            ],
            'canceled' => [
                'en' => 'Subscription canceled. Your account is now on free plan.',
                'es' => 'Suscripcion cancelada. Tu cuenta ahora esta en plan free.',
            ],
        ];

        $lang = in_array($user->language, ['en', 'es'], true) ? $user->language : 'en';
        return $messages[$key][$lang] ?? $messages[$key]['en'] ?? 'OK';
    }

    private function sendBillingEmail(User $user, string $type, array $context = []): void
    {
        if (! $user->email) {
            return;
        }

        try {
            app(MailRuntimeConfigService::class)->apply();

            Mail::to($user->email)->send(
                new BillingPlanNotificationMail(
                    user: $user,
                    type: $type,
                    context: $context
                )
            );
        } catch (\Throwable $exception) {
            Log::error('Billing email failed', [
                'user_id' => $user->id,
                'email' => $user->email,
                'type' => $type,
                'error' => $exception->getMessage(),
            ]);
            report($exception);
        }
    }
}
