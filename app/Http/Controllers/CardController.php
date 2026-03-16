<?php

namespace App\Http\Controllers;

use App\Models\Card;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class CardController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();
        $cardsCount = $user->cards()->count();

        return Inertia::render('Cards/Index', [
            'cards' => $user->cards()->latest()->get(['id', 'name', 'username', 'description', 'created_at']),
            'canCreate' => $user->plan !== 'free' || $cardsCount === 0,
            'plan' => $user->plan,
        ]);
    }

    public function show(Request $request, Card $card): Response
    {
        $this->ensureOwnership($request, $card);

        return Inertia::render('Cards/Show', [
            'card' => $card,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', 'alpha_dash', Rule::unique('cards', 'username')],
            'description' => ['nullable', 'string', 'max:255'],
        ]);

        $card = DB::transaction(function () use ($user, $validated): Card {
            $lockedUser = $user->newQuery()->lockForUpdate()->findOrFail($user->id);

            if ($lockedUser->plan === 'free' && $lockedUser->cards()->exists()) {
                throw ValidationException::withMessages([
                    'limit' => 'Free plan allows only one card. Upgrade to create more.',
                ]);
            }

            return $lockedUser->cards()->create([
                'name' => $validated['name'],
                'username' => Str::lower($validated['username']),
                'slug' => Str::lower($validated['username']),
                'description' => $validated['description'] ?? null,
                'profile_image_style' => 'circle',
                'header_color' => '#6DBE45',
                'background_type' => 'color',
                'background_color' => '#F5F5F5',
                'button_style' => 'rounded',
                'template_style' => 'classic',
                'text_color' => '#111111',
                'button_background_color' => '#6DBE45',
                'button_text_color' => '#FFFFFF',
                'is_active' => true,
                'links' => [],
            ]);
        });

        return redirect()->route('cards.show', $card);
    }

    public function update(Request $request, Card $card): RedirectResponse
    {
        $this->ensureOwnership($request, $card);

        $validated = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'username' => [
                'sometimes',
                'required',
                'string',
                'max:255',
                'alpha_dash',
                Rule::unique('cards', 'username')->ignore($card->id),
            ],
            'description' => ['sometimes', 'nullable', 'string', 'max:255'],
            'phone' => ['sometimes', 'nullable', 'string', 'max:60'],
            'email' => ['sometimes', 'nullable', 'email', 'max:190'],
            'address' => ['sometimes', 'nullable', 'string', 'max:255'],
            'google_maps_url' => ['sometimes', 'nullable', 'string', 'max:2048'],
            'header_color' => ['sometimes', 'required', 'string', 'max:20'],
            'text_color' => ['sometimes', 'required', 'string', 'max:20'],
            'button_background_color' => ['sometimes', 'required', 'string', 'max:20'],
            'button_text_color' => ['sometimes', 'required', 'string', 'max:20'],
            'background_color' => ['sometimes', 'required', 'string', 'max:20'],
            'template_style' => ['sometimes', 'required', Rule::in([
                'classic',
                'classic_left',
                'classic_right',
                'wave_left',
                'wave_right',
                'split_modern',
                'soft_stack',
                'wave_center',
                'split_right',
                'layered_left',
                'layered_right',
                'minimal_center',
            ])],
            'button_style' => ['sometimes', 'required', Rule::in(['rounded', 'normal', 'square'])],
            'links' => ['sometimes', 'array', 'max:20'],
            'links.*.icon' => ['nullable', 'string', 'max:60'],
            'links.*.title' => ['nullable', 'string', 'max:100'],
            'links.*.url' => ['nullable', 'string', 'max:255'],
            'links.*.description' => ['nullable', 'string', 'max:160'],
            'schedule' => ['sometimes', 'array', 'max:7'],
            'schedule.*.day' => ['required', 'integer', 'min:0', 'max:6'],
            'schedule.*.open' => ['required', 'string', 'regex:/^([01]?[0-9]|2[0-3]):[0-5][0-9]$/'],
            'schedule.*.close' => ['required', 'string', 'regex:/^([01]?[0-9]|2[0-3]):[0-5][0-9]$/'],
            'profile_image_style' => ['sometimes', 'required', Rule::in(['circle', 'rounded', 'square'])],
            'remove_profile_image' => ['sometimes', 'boolean'],
            'remove_header_image' => ['sometimes', 'boolean'],
            'remove_background_image' => ['sometimes', 'boolean'],
            'profile_image' => ['sometimes', 'nullable', 'image', 'max:4096'],
            'header_image' => ['sometimes', 'nullable', 'image', 'max:6144'],
            'background_image' => ['sometimes', 'nullable', 'image', 'max:6144'],
        ]);

        $payload = [];

        if (array_key_exists('name', $validated)) {
            $payload['name'] = $validated['name'];
        }

        if (array_key_exists('username', $validated)) {
            $payload['username'] = Str::lower($validated['username']);
        }

        if (array_key_exists('description', $validated)) {
            $payload['description'] = $validated['description'] ?? null;
        }

        foreach (['phone', 'email', 'address', 'google_maps_url'] as $field) {
            if (array_key_exists($field, $validated)) {
                $value = trim((string) ($validated[$field] ?? ''));
                $payload[$field] = $value === '' ? null : $value;
            }
        }

        foreach (['header_color', 'text_color', 'button_background_color', 'button_text_color', 'background_color', 'template_style', 'button_style'] as $field) {
            if (array_key_exists($field, $validated)) {
                $payload[$field] = $validated[$field];
            }
        }

        if (array_key_exists('links', $validated)) {
            $payload['links'] = collect($validated['links'])
                ->map(fn (array $link) => [
                    'icon' => trim((string) ($link['icon'] ?? 'link')),
                    'title' => trim((string) ($link['title'] ?? '')),
                    'url' => trim((string) ($link['url'] ?? '')),
                    'description' => trim((string) ($link['description'] ?? '')),
                    'auto_key' => trim((string) ($link['auto_key'] ?? '')),
                ])
                ->filter(fn (array $link) => $link['title'] !== '' || $link['url'] !== '')
                ->values()
                ->all();
        }

        if (
            array_key_exists('links', $validated)
            || array_key_exists('phone', $validated)
            || array_key_exists('email', $validated)
            || array_key_exists('google_maps_url', $validated)
        ) {
            $baseLinks = is_array($payload['links'] ?? null) ? $payload['links'] : (is_array($card->links) ? $card->links : []);
            $payload['links'] = $this->synchronizeAutoLinks($baseLinks, [
                'phone' => (string) ($payload['phone'] ?? $card->phone ?? ''),
                'email' => (string) ($payload['email'] ?? $card->email ?? ''),
                'google_maps_url' => (string) ($payload['google_maps_url'] ?? $card->google_maps_url ?? ''),
            ]);
        }

        if (array_key_exists('schedule', $validated)) {
            $payload['schedule'] = collect($validated['schedule'])
                ->unique('day')
                ->sortBy('day')
                ->map(fn (array $item) => [
                    'day' => (int) $item['day'],
                    'open' => $item['open'],
                    'close' => $item['close'],
                ])
                ->values()
                ->all();
        }

        if (array_key_exists('profile_image_style', $validated)) {
            $payload['profile_image_style'] = $validated['profile_image_style'];
        }

        if (($validated['remove_profile_image'] ?? false) && ! $request->hasFile('profile_image')) {
            if (! empty($card->profile_image)) {
                Storage::disk('public')->delete($card->profile_image);
            }

            $payload['profile_image'] = null;
        }

        if (($validated['remove_header_image'] ?? false) && ! $request->hasFile('header_image')) {
            if (! empty($card->header_image)) {
                Storage::disk('public')->delete($card->header_image);
            }

            $payload['header_image'] = null;
        }

        if (($validated['remove_background_image'] ?? false) && ! $request->hasFile('background_image')) {
            if (! empty($card->background_image)) {
                Storage::disk('public')->delete($card->background_image);
            }

            $payload['background_image'] = null;
        }

        foreach (['profile_image', 'header_image', 'background_image'] as $imageField) {
            if ($request->hasFile($imageField)) {
                if (! empty($card->{$imageField})) {
                    Storage::disk('public')->delete($card->{$imageField});
                }

                $payload[$imageField] = $request->file($imageField)->store(
                    "cards/{$card->id}/{$imageField}",
                    'public'
                );
            }
        }

        if (! empty($payload)) {
            $card->update($payload);
        }

        return back()->with('status', 'Card updated successfully.');
    }

    public function destroy(Request $request, Card $card): RedirectResponse
    {
        $this->ensureOwnership($request, $card);

        Storage::disk('public')->deleteDirectory("cards/{$card->id}");
        $card->delete();

        return back()->with('status', 'Card deleted successfully.');
    }

    private function ensureOwnership(Request $request, Card $card): void
    {
        if ($card->user_id !== $request->user()->id && $request->user()->role !== 'admin') {
            abort(403);
        }
    }

    private function synchronizeAutoLinks(array $links, array $contact): array
    {
        $normalized = collect($links)
            ->map(fn ($link) => is_array($link) ? $link : [])
            ->map(fn (array $link) => [
                'icon' => trim((string) ($link['icon'] ?? 'link')),
                'title' => trim((string) ($link['title'] ?? '')),
                'url' => trim((string) ($link['url'] ?? '')),
                'description' => trim((string) ($link['description'] ?? '')),
                'auto_key' => trim((string) ($link['auto_key'] ?? '')),
            ]);

        $autoKeys = ['auto_phone', 'auto_email', 'auto_maps'];
        $existingAuto = $normalized
            ->filter(fn (array $link) => in_array($link['auto_key'], $autoKeys, true))
            ->keyBy('auto_key');

        $clean = $normalized
            ->filter(fn (array $link) => ! in_array($link['auto_key'], $autoKeys, true))
            ->values();

        $phone = preg_replace('/\s+/', '', trim((string) ($contact['phone'] ?? '')));
        if ($phone !== '') {
            $current = $existingAuto->get('auto_phone');
            $clean->push([
                'icon' => trim((string) ($current['icon'] ?? 'phone')) ?: 'phone',
                'title' => trim((string) ($current['title'] ?? 'Phone')),
                'url' => trim((string) ($current['url'] ?? '')) ?: $phone,
                'description' => trim((string) ($current['description'] ?? '')),
                'auto_key' => 'auto_phone',
            ]);
        }

        $email = trim((string) ($contact['email'] ?? ''));
        if ($email !== '') {
            $current = $existingAuto->get('auto_email');
            $clean->push([
                'icon' => trim((string) ($current['icon'] ?? 'email')) ?: 'email',
                'title' => trim((string) ($current['title'] ?? 'Email')),
                'url' => trim((string) ($current['url'] ?? '')) ?: $email,
                'description' => trim((string) ($current['description'] ?? '')),
                'auto_key' => 'auto_email',
            ]);
        }

        $maps = trim((string) ($contact['google_maps_url'] ?? ''));
        if ($maps !== '') {
            $current = $existingAuto->get('auto_maps');
            $currentIcon = trim((string) ($current['icon'] ?? ''));
            $resolvedIcon = $currentIcon === '' || $currentIcon === 'google'
                ? 'location'
                : $currentIcon;
            $clean->push([
                'icon' => $resolvedIcon,
                'title' => trim((string) ($current['title'] ?? 'Google Maps')),
                'url' => trim((string) ($current['url'] ?? '')) ?: $maps,
                'description' => trim((string) ($current['description'] ?? '')),
                'auto_key' => 'auto_maps',
            ]);
        }

        return $clean
            ->filter(fn (array $link) => $link['title'] !== '' || $link['url'] !== '')
            ->values()
            ->take(20)
            ->all();
    }
}
