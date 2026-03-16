<?php

namespace App\Http\Controllers;

use App\Models\BillingTransaction;
use App\Services\StripeService;
use Illuminate\Http\RedirectResponse;
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

        $user->save();
        $request->session()->put('locale', $user->language);

        return back()->with('status', 'Profile updated successfully.');
    }
}
