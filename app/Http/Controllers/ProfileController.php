<?php

namespace App\Http\Controllers;

use App\Models\BillingTransaction;
use App\Services\CalComService;
use App\Services\StripeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    public function index(Request $request, StripeService $stripe): Response
    {
        $user = $request->user();

        if ($user->role === 'business') {
            try {
                $stripe->syncLatestSubscriptionForUser($user);
            } catch (\Throwable) {
                // Keep profile loading even when Stripe is temporarily unavailable.
            }
        }

        $transactions = BillingTransaction::query()
            ->where('user_id', $user->id)
            ->latest('paid_at')
            ->latest('id')
            ->limit(20)
            ->get()
            ->map(static fn (BillingTransaction $item): array => [
                'id' => $item->id,
                'amount_cents' => (int) $item->amount_cents,
                'currency' => (string) $item->currency,
                'status' => (string) $item->status,
                'description' => (string) ($item->description ?? ''),
                'paid_at' => optional($item->paid_at)->toIso8601String(),
                'created_at' => optional($item->created_at)->toIso8601String(),
            ])
            ->values()
            ->all();

        return Inertia::render('Profile/Index', [
            'user' => $user,
            'transactions' => $transactions,
            'cal' => [
                'has_api_key' => (bool) $user->cal_api_key,
                'api_key' => (string) ($user->cal_api_key ?? ''),
                'username' => (string) ($user->cal_username ?? ''),
                'event_type_id' => (string) ($user->cal_event_type_id ?? ''),
                'event_type_slug' => (string) ($user->cal_event_type_id ?? ''),
                'connected_at' => optional($user->cal_connected_at)->toIso8601String(),
                'sync_enabled' => (bool) $user->cal_sync_enabled,
            ],
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();

        if ($user->role !== 'admin' && $request->has('email')) {
            throw ValidationException::withMessages([
                'email' => 'Email updates are not allowed for this account.',
            ]);
        }

        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'language' => ['required', 'in:en,es'],
            'current_password' => ['nullable', 'required_with:password', 'current_password'],
            'password' => ['nullable', 'confirmed', Rules\Password::defaults()],
            'cal_api_key' => ['nullable', 'string', 'max:255'],
            'cal_username' => ['nullable', 'string', 'max:120'],
            'cal_event_type_id' => ['nullable', 'string', 'max:190'],
            'cal_sync_enabled' => ['nullable', 'boolean'],
            'remove_cal_api_key' => ['nullable', 'boolean'],
        ];

        if ($user->role === 'admin') {
            $rules['email'] = [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user->id),
            ];
        }

        $validated = $request->validate($rules);

        $user->name = $validated['name'];
        $user->language = $validated['language'];

        if ($user->role === 'admin') {
            $user->email = $validated['email'];
        }

        if (! empty($validated['password'])) {
            $user->password = $validated['password'];
        }

        $removeCalApiKey = (bool) ($validated['remove_cal_api_key'] ?? false);
        if ($removeCalApiKey) {
            $user->cal_api_key = null;
            $user->cal_username = null;
            $user->cal_connected_at = null;
        } elseif (array_key_exists('cal_api_key', $validated) && trim((string) $validated['cal_api_key']) !== '') {
            $user->cal_api_key = trim((string) $validated['cal_api_key']);
        }

        $user->cal_username = trim((string) ($validated['cal_username'] ?? $user->cal_username ?? '')) ?: null;
        $eventTypeId = trim((string) ($validated['cal_event_type_id'] ?? ''));
        $user->cal_event_type_id = $eventTypeId !== '' ? $eventTypeId : null;
        $user->cal_sync_enabled = (bool) ($validated['cal_sync_enabled'] ?? false);

        $user->save();
        $request->session()->put('locale', $user->language);

        return back()->with('status', 'Profile updated successfully.');
    }

    public function testCalConnection(Request $request, CalComService $calCom): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'cal_api_key' => ['nullable', 'string', 'max:255'],
        ]);

        $apiKey = trim((string) ($validated['cal_api_key'] ?? ''));
        if ($apiKey === '') {
            $apiKey = (string) ($user->cal_api_key ?? '');
        }

        $result = $calCom->testConnection($apiKey);

        if (($result['ok'] ?? false) === true) {
            if ($apiKey !== '') {
                $user->cal_api_key = $apiKey;
            }

            $apiUsername = trim((string) ($result['username'] ?? ''));
            if ($apiUsername !== '') {
                $user->cal_username = $apiUsername;
            }

            $user->cal_connected_at = now();
            $user->save();
        }

        return response()->json($result, ($result['ok'] ?? false) ? 200 : 422);
    }
}
