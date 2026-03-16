<?php

namespace App\Services;

use App\Models\BillingTransaction;
use App\Models\StripeSetting;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;

class StripeService
{
    public function activeSettings(): StripeSetting
    {
        $settings = StripeSetting::query()->latest('id')->first();
        if (! $settings || ! $settings->is_active) {
            throw ValidationException::withMessages([
                'stripe' => 'Stripe is not configured or active.',
            ]);
        }

        if (! $settings->publishable_key || ! $settings->secret_key) {
            throw ValidationException::withMessages([
                'stripe' => 'Stripe keys are missing in configuration.',
            ]);
        }

        return $settings;
    }

    public function createOrGetCustomer(User $user, StripeSetting $settings): string
    {
        if ($user->stripe_customer_id) {
            return $user->stripe_customer_id;
        }

        $response = $this->request($settings, 'post', '/v1/customers', [
            'email' => $user->email,
            'name' => $user->name,
            'metadata[user_id]' => (string) $user->id,
        ]);

        $customerId = (string) ($response['id'] ?? '');
        if ($customerId === '') {
            throw ValidationException::withMessages([
                'stripe' => 'Unable to create Stripe customer.',
            ]);
        }

        $user->stripe_customer_id = $customerId;
        $user->save();

        return $customerId;
    }

    public function createCheckoutSession(User $user, string $successUrl, string $cancelUrl): array
    {
        $settings = $this->activeSettings();
        $customerId = $this->createOrGetCustomer($user, $settings);

        $payload = [
            'mode' => 'subscription',
            'customer' => $customerId,
            'success_url' => $successUrl,
            'cancel_url' => $cancelUrl,
            'client_reference_id' => (string) $user->id,
            'subscription_data[metadata][user_id]' => (string) $user->id,
            'line_items[0][quantity]' => 1,
            'line_items[0][price_data][currency]' => strtolower((string) $settings->currency),
            'line_items[0][price_data][unit_amount]' => (string) $settings->monthly_price_cents,
            'line_items[0][price_data][recurring][interval]' => 'month',
            'line_items[0][price_data][product_data][name]' => 'ReferyApp Business Plan',
            'line_items[0][price_data][product_data][description]' => 'Monthly subscription for business features',
        ];

        $response = $this->request($settings, 'post', '/v1/checkout/sessions', $payload);
        if (empty($response['url'])) {
            throw ValidationException::withMessages([
                'stripe' => 'Unable to create checkout session.',
            ]);
        }

        return $response;
    }

    public function fetchSubscriptionById(string $subscriptionId): array
    {
        if ($subscriptionId === '') {
            return [];
        }

        $settings = $this->activeSettings();

        return $this->request($settings, 'get', '/v1/subscriptions/' . $subscriptionId, [
            'expand[0]' => 'items.data.price',
        ]);
    }

    public function fetchCheckoutSessionById(string $sessionId): array
    {
        if ($sessionId === '') {
            return [];
        }

        $settings = $this->activeSettings();

        return $this->request($settings, 'get', '/v1/checkout/sessions/' . $sessionId, [
            'expand[0]' => 'subscription',
            'expand[1]' => 'invoice',
            'expand[2]' => 'invoice.payment_intent',
        ]);
    }

    public function fetchPaymentIntentById(string $paymentIntentId): array
    {
        if ($paymentIntentId === '') {
            return [];
        }

        $settings = $this->activeSettings();

        return $this->request($settings, 'get', '/v1/payment_intents/' . $paymentIntentId);
    }

    public function fetchChargeById(string $chargeId): array
    {
        if ($chargeId === '') {
            return [];
        }

        $settings = $this->activeSettings();

        return $this->request($settings, 'get', '/v1/charges/' . $chargeId);
    }

    public function syncLatestSubscriptionForUser(User $user): bool
    {
        if (! $user->stripe_customer_id) {
            return false;
        }

        $settings = $this->activeSettings();
        $response = $this->request($settings, 'get', '/v1/subscriptions', [
            'customer' => $user->stripe_customer_id,
            'status' => 'all',
            'limit' => 1,
            'expand[0]' => 'data.items.data.price',
            'expand[1]' => 'data.latest_invoice.payment_intent',
        ]);

        $subscription = $response['data'][0] ?? null;
        if (! is_array($subscription) || empty($subscription)) {
            return false;
        }

        $this->syncUserFromSubscriptionPayload($subscription);

        $latestInvoice = $subscription['latest_invoice'] ?? null;
        if (is_array($latestInvoice) && ! empty($latestInvoice)) {
            $this->recordTransactionFromInvoice($latestInvoice);
        }

        return true;
    }

    public function cancelActiveSubscription(User $user): void
    {
        $settings = $this->activeSettings();

        $subscriptionId = (string) ($user->stripe_subscription_id ?? '');
        if ($subscriptionId === '' && $user->stripe_customer_id) {
            $response = $this->request($settings, 'get', '/v1/subscriptions', [
                'customer' => $user->stripe_customer_id,
                'status' => 'all',
                'limit' => 1,
            ]);
            $subscriptionId = (string) ($response['data'][0]['id'] ?? '');
        }

        if ($subscriptionId === '') {
            throw ValidationException::withMessages([
                'stripe' => 'No Stripe subscription found for this account.',
            ]);
        }

        $response = $this->request($settings, 'delete', '/v1/subscriptions/' . $subscriptionId);
        $this->downgradeUserFromSubscriptionCancelled([
            'id' => $subscriptionId,
            'customer' => (string) ($response['customer'] ?? $user->stripe_customer_id ?? ''),
        ]);
    }

    public function verifyWebhookSignature(string $payload, string $signature, string $secret): bool
    {
        if ($secret === '' || $signature === '') {
            return false;
        }

        $parts = [];
        foreach (explode(',', $signature) as $segment) {
            [$key, $value] = array_pad(explode('=', trim($segment), 2), 2, null);
            if ($key && $value) {
                $parts[$key] = $value;
            }
        }

        $timestamp = $parts['t'] ?? null;
        $v1 = $parts['v1'] ?? null;
        if (! $timestamp || ! $v1) {
            return false;
        }

        if (abs(time() - (int) $timestamp) > 300) {
            return false;
        }

        $computed = hash_hmac('sha256', $timestamp . '.' . $payload, $secret);
        return hash_equals($computed, $v1);
    }

    public function syncUserFromSubscriptionPayload(array $subscription): void
    {
        $subscriptionId = (string) ($subscription['id'] ?? '');
        if ($subscriptionId === '') {
            return;
        }

        $customerId = (string) ($subscription['customer'] ?? '');
        $status = (string) ($subscription['status'] ?? '');
        $priceId = (string) ($subscription['items']['data'][0]['price']['id'] ?? '');
        $periodEnd = (int) ($subscription['current_period_end'] ?? 0);
        $userId = (string) ($subscription['metadata']['user_id'] ?? '');

        $userQuery = User::query();
        if ($userId !== '') {
            $userQuery->where('id', $userId);
        } elseif ($customerId !== '') {
            $userQuery->where('stripe_customer_id', $customerId);
        } else {
            return;
        }

        $user = $userQuery->first();
        if (! $user) {
            return;
        }

        $active = in_array($status, ['active', 'trialing', 'past_due'], true);

        $user->stripe_customer_id = $customerId !== '' ? $customerId : $user->stripe_customer_id;
        $user->stripe_subscription_id = $subscriptionId;
        $user->stripe_subscription_status = $status;
        $user->stripe_price_id = $priceId !== '' ? $priceId : null;
        $user->stripe_current_period_end = $periodEnd > 0 ? Carbon::createFromTimestamp($periodEnd) : null;
        $user->plan = $active ? 'business' : 'free';
        $user->save();
    }

    public function downgradeUserFromSubscriptionCancelled(array $subscription): ?User
    {
        $subscriptionId = (string) ($subscription['id'] ?? '');
        $customerId = (string) ($subscription['customer'] ?? '');

        $query = User::query();
        if ($subscriptionId !== '') {
            $query->where('stripe_subscription_id', $subscriptionId);
            if ($customerId !== '') {
                $query->orWhere('stripe_customer_id', $customerId);
            }
        } elseif ($customerId !== '') {
            $query->where('stripe_customer_id', $customerId);
        } else {
            return null;
        }

        $user = $query->first();

        if (! $user) {
            return null;
        }

        $user->plan = 'free';
        $user->stripe_subscription_status = 'canceled';
        $user->stripe_current_period_end = null;
        $user->save();
        return $user;
    }

    public function syncCheckoutSessionForUser(User $user, string $sessionId): void
    {
        $session = $this->fetchCheckoutSessionById($sessionId);
        if (empty($session)) {
            return;
        }

        $sessionCustomerId = (string) ($session['customer'] ?? '');
        if ($sessionCustomerId !== '' && $user->stripe_customer_id && $user->stripe_customer_id !== $sessionCustomerId) {
            throw ValidationException::withMessages([
                'stripe' => 'Checkout session does not belong to this account.',
            ]);
        }

        $subscription = $session['subscription'] ?? null;
        if (is_array($subscription) && ! empty($subscription)) {
            $this->syncUserFromSubscriptionPayload($subscription);
        } elseif (is_string($subscription) && $subscription !== '') {
            $subscriptionPayload = $this->fetchSubscriptionById($subscription);
            if (! empty($subscriptionPayload)) {
                $this->syncUserFromSubscriptionPayload($subscriptionPayload);
            }
        }

        $this->recordTransactionFromCheckoutSession($user, $session);
    }

    public function recordTransactionFromInvoice(array $invoice): void
    {
        $invoiceId = (string) ($invoice['id'] ?? '');
        if ($invoiceId === '') {
            return;
        }

        $customerId = (string) ($invoice['customer'] ?? '');
        if ($customerId === '') {
            return;
        }

        $user = User::query()->where('stripe_customer_id', $customerId)->first();
        if (! $user) {
            return;
        }

        BillingTransaction::query()->updateOrCreate(
            ['stripe_invoice_id' => $invoiceId],
            [
                'user_id' => $user->id,
                'stripe_subscription_id' => (string) ($invoice['subscription'] ?? '') ?: null,
                'stripe_payment_intent_id' => $this->stringValue($invoice['payment_intent'] ?? null),
                'amount_cents' => (int) ($invoice['amount_paid'] ?? $invoice['amount_due'] ?? 0),
                'currency' => strtolower((string) ($invoice['currency'] ?? 'usd')),
                'status' => (string) ($invoice['status'] ?? 'paid'),
                'description' => (string) ($invoice['description'] ?? 'ReferyApp Business Plan - Monthly'),
                'paid_at' => $this->timestampToCarbon((int) ($invoice['status_transitions']['paid_at'] ?? 0)),
            ]
        );
    }

    public function recordTransactionFromPaymentIntent(array $paymentIntent): void
    {
        $paymentIntentId = (string) ($paymentIntent['id'] ?? '');
        if ($paymentIntentId === '') {
            return;
        }

        $customerId = (string) ($paymentIntent['customer'] ?? '');
        $user = $customerId !== ''
            ? User::query()->where('stripe_customer_id', $customerId)->first()
            : null;

        if (! $user) {
            return;
        }

        BillingTransaction::query()->updateOrCreate(
            ['stripe_payment_intent_id' => $paymentIntentId],
            [
                'user_id' => $user->id,
                'stripe_subscription_id' => (string) ($paymentIntent['metadata']['subscription_id'] ?? '') ?: null,
                'amount_cents' => (int) ($paymentIntent['amount_received'] ?? $paymentIntent['amount'] ?? 0),
                'currency' => strtolower((string) ($paymentIntent['currency'] ?? 'usd')),
                'status' => (string) ($paymentIntent['status'] ?? 'failed'),
                'description' => (string) ($paymentIntent['description'] ?? 'ReferyApp Business Plan - Payment attempt'),
                'paid_at' => null,
            ]
        );
    }

    public function findUserFromStripeObject(array $object): ?User
    {
        $customerId = (string) ($object['customer'] ?? '');
        if ($customerId !== '') {
            $user = User::query()->where('stripe_customer_id', $customerId)->first();
            if ($user) {
                return $user;
            }
        }

        $subscriptionId = $this->stringValue($object['subscription'] ?? null);
        if ($subscriptionId) {
            $user = User::query()->where('stripe_subscription_id', $subscriptionId)->first();
            if ($user) {
                return $user;
            }
        }

        $paymentIntentId = $this->stringValue($object['payment_intent'] ?? null);
        if (! $paymentIntentId && str_starts_with((string) ($object['id'] ?? ''), 'pi_')) {
            $paymentIntentId = (string) $object['id'];
        }

        if ($paymentIntentId) {
            $userByTx = BillingTransaction::query()
                ->where('stripe_payment_intent_id', $paymentIntentId)
                ->with('user')
                ->first()?->user;
            if ($userByTx) {
                return $userByTx;
            }

            $intent = $this->fetchPaymentIntentById($paymentIntentId);
            $intentCustomer = (string) ($intent['customer'] ?? '');
            if ($intentCustomer !== '') {
                $user = User::query()->where('stripe_customer_id', $intentCustomer)->first();
                if ($user) {
                    return $user;
                }
            }
        }

        $chargeId = $this->stringValue($object['charge'] ?? null);
        if (! $chargeId && str_starts_with((string) ($object['id'] ?? ''), 'ch_')) {
            $chargeId = (string) $object['id'];
        }

        if ($chargeId) {
            $charge = $this->fetchChargeById($chargeId);
            $chargeCustomer = (string) ($charge['customer'] ?? '');
            if ($chargeCustomer !== '') {
                $user = User::query()->where('stripe_customer_id', $chargeCustomer)->first();
                if ($user) {
                    return $user;
                }
            }

            $chargePaymentIntent = $this->stringValue($charge['payment_intent'] ?? null);
            if ($chargePaymentIntent) {
                $userByTx = BillingTransaction::query()
                    ->where('stripe_payment_intent_id', $chargePaymentIntent)
                    ->with('user')
                    ->first()?->user;
                if ($userByTx) {
                    return $userByTx;
                }
            }
        }

        return null;
    }

    private function recordTransactionFromCheckoutSession(User $user, array $session): void
    {
        $sessionId = (string) ($session['id'] ?? '');
        if ($sessionId === '') {
            return;
        }

        $invoice = $session['invoice'] ?? null;
        $invoiceId = is_array($invoice) ? (string) ($invoice['id'] ?? '') : $this->stringValue($invoice);
        $paymentIntentId = is_array($invoice)
            ? $this->stringValue($invoice['payment_intent'] ?? null)
            : null;

        $paidAt = is_array($invoice)
            ? $this->timestampToCarbon((int) ($invoice['status_transitions']['paid_at'] ?? 0))
            : null;

        $status = (string) ($session['payment_status'] ?? 'unpaid');
        $description = (string) ($session['metadata']['description'] ?? 'ReferyApp Business Plan - Monthly');

        $payload = [
            'user_id' => $user->id,
            'stripe_invoice_id' => $invoiceId !== '' ? $invoiceId : null,
            'stripe_payment_intent_id' => $paymentIntentId,
            'stripe_subscription_id' => $this->stringValue($session['subscription'] ?? null),
            'amount_cents' => (int) ($session['amount_total'] ?? 0),
            'currency' => strtolower((string) ($session['currency'] ?? 'usd')),
            'status' => $status,
            'description' => $description,
            'paid_at' => $paidAt,
        ];

        if ($invoiceId !== '') {
            BillingTransaction::query()->updateOrCreate(
                ['stripe_invoice_id' => $invoiceId],
                array_merge($payload, ['stripe_checkout_session_id' => $sessionId])
            );

            return;
        }

        BillingTransaction::query()->updateOrCreate(
            ['stripe_checkout_session_id' => $sessionId],
            $payload
        );
    }

    private function timestampToCarbon(int $timestamp): ?Carbon
    {
        if ($timestamp <= 0) {
            return null;
        }

        return Carbon::createFromTimestamp($timestamp);
    }

    private function stringValue(mixed $value): ?string
    {
        if (is_string($value) && $value !== '') {
            return $value;
        }

        if (is_array($value)) {
            $id = (string) ($value['id'] ?? '');
            return $id !== '' ? $id : null;
        }

        return null;
    }

    private function request(StripeSetting $settings, string $method, string $path, array $payload = []): array
    {
        $url = 'https://api.stripe.com' . $path;

        $client = Http::asForm()
            ->withBasicAuth((string) $settings->secret_key, '')
            ->timeout(20);

        /** @var Response $response */
        if ($method === 'get') {
            $response = $client->get($url, $payload);
        } elseif ($method === 'delete') {
            $response = $client->delete($url, $payload);
        } else {
            $response = $client->post($url, $payload);
        }

        if ($response->failed()) {
            $error = $response->json('error.message') ?: 'Stripe request failed.';
            throw ValidationException::withMessages([
                'stripe' => (string) $error,
            ]);
        }

        return (array) $response->json();
    }
}
