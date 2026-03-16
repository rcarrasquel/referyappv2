<?php

namespace App\Http\Controllers;

use App\Models\StripeSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class StripeSettingsController extends Controller
{
    public function index(): Response
    {
        $settings = StripeSetting::query()->latest('id')->first();

        return Inertia::render('Modules/StripeSettings', [
            'settings' => [
                'publishable_key' => (string) ($settings?->publishable_key ?? ''),
                'secret_key' => (string) ($settings?->secret_key ?? ''),
                'webhook_secret' => (string) ($settings?->webhook_secret ?? ''),
                'currency' => (string) ($settings?->currency ?? 'usd'),
                'monthly_price_cents' => (int) ($settings?->monthly_price_cents ?? 900),
                'is_active' => (bool) ($settings?->is_active ?? false),
            ],
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'publishable_key' => ['nullable', 'string', 'max:255'],
            'secret_key' => ['nullable', 'string', 'max:255'],
            'webhook_secret' => ['nullable', 'string', 'max:255'],
            'currency' => ['required', 'string', 'size:3'],
            'monthly_price_cents' => ['required', 'integer', 'min:100', 'max:1000000'],
            'is_active' => ['required', 'boolean'],
        ]);

        $settings = StripeSetting::query()->latest('id')->first();
        if (! $settings) {
            $settings = new StripeSetting();
        }

        $settings->fill([
            'publishable_key' => trim((string) ($validated['publishable_key'] ?? '')),
            'secret_key' => trim((string) ($validated['secret_key'] ?? '')),
            'webhook_secret' => trim((string) ($validated['webhook_secret'] ?? '')),
            'currency' => strtolower(trim((string) $validated['currency'])),
            'monthly_price_cents' => (int) $validated['monthly_price_cents'],
            'is_active' => (bool) $validated['is_active'],
        ]);
        $settings->save();

        return back()->with('status', 'Stripe settings updated successfully.');
    }
}

