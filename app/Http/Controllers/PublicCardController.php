<?php

namespace App\Http\Controllers;

use App\Models\Card;
use App\Models\CardLinkClick;
use App\Models\CardShareEvent;
use App\Models\CardVisit;
use App\Support\AnalyticsContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PublicCardController extends Controller
{
    private const SHARE_CHANNELS = [
        'native',
        'copy',
        'whatsapp',
        'instagram',
        'x',
        'facebook',
        'telegram',
        'email',
    ];

    public function show(Request $request, string $slug): Response
    {
        $card = $this->resolveActiveCardBySlug($slug);

        $this->recordVisit($request, $card);

        return $this->renderCard($card);
    }

    public function showByUsername(Request $request, string $username): Response
    {
        $card = $this->resolveActiveCardByUsername($username);

        $this->recordVisit($request, $card);

        return $this->renderCard($card);
    }

    public function out(Request $request, string $slug, int $index): RedirectResponse
    {
        $card = $this->resolveActiveCardBySlug($slug);

        return $this->redirectAndTrackLink($request, $card, $index);
    }

    public function outByUsername(Request $request, string $username, int $index): RedirectResponse
    {
        $card = $this->resolveActiveCardByUsername($username);

        return $this->redirectAndTrackLink($request, $card, $index);
    }

    public function trackShareByUsername(Request $request, string $username): JsonResponse
    {
        $card = $this->resolveActiveCardByUsername($username);

        $validated = $request->validate([
            'channel' => ['required', 'string', 'max:40', 'in:' . implode(',', self::SHARE_CHANNELS)],
        ]);

        CardShareEvent::query()->create(array_merge(
            AnalyticsContext::fromRequest($request),
            [
                'card_id' => $card->id,
                'channel' => $validated['channel'],
                'shared_at' => now(),
            ]
        ));

        return response()->json([
            'ok' => true,
        ]);
    }

    private function renderCard(Card $card): Response
    {
        $services = $card->user
            ? $card->user->products()
                ->orderBy('name')
                ->get(['id', 'name', 'description', 'image', 'price', 'duration_minutes'])
                ->map(fn ($product): array => [
                    'id' => $product->id,
                    'name' => $product->name,
                    'description' => $product->description,
                    'image' => $product->image,
                    'price' => $product->price,
                    'duration_minutes' => $product->duration_minutes,
                ])
                ->values()
                ->all()
            : [];

        return Inertia::render('Public/CardShow', [
            'card' => [
                'id' => $card->id,
                'slug' => $card->slug,
                'name' => $card->name,
                'username' => $card->username,
                'description' => $card->description,
                'phone' => $card->phone,
                'email' => $card->email,
                'address' => $card->address,
                'google_maps_url' => $card->google_maps_url,
                'profile_image' => $card->profile_image,
                'profile_image_style' => $card->profile_image_style,
                'header_image' => $card->header_image,
                'header_color' => $card->header_color,
                'background_color' => $card->background_color,
                'background_image' => $card->background_image,
                'button_style' => $card->button_style,
                'template_style' => $card->template_style,
                'text_color' => $card->text_color,
                'button_background_color' => $card->button_background_color,
                'button_text_color' => $card->button_text_color,
                'links' => $card->links ?? [],
                'services' => $services,
                'language' => $card->user?->language ?? 'es',
                'plan' => $card->user?->plan ?? 'free',
            ],
        ]);
    }

    private function redirectAndTrackLink(Request $request, Card $card, int $index): RedirectResponse
    {
        $slug = (string) $card->slug;

        $links = is_array($card->links) ? array_values($card->links) : [];
        $link = $links[$index] ?? null;

        if (! is_array($link)) {
            return redirect()->route('public.cards.show', ['slug' => $slug]);
        }

        $rawUrl = trim((string) ($link['url'] ?? ''));
        if ($rawUrl === '') {
            return redirect()->route('public.cards.show', ['slug' => $slug]);
        }

        $icon = strtolower(trim((string) ($link['icon'] ?? '')));
        $url = $this->normalizeOutgoingLink($rawUrl, $icon);
        if (! $url) {
            return redirect()->route('public.cards.show', ['slug' => $slug]);
        }

        CardLinkClick::query()->create(array_merge(
            AnalyticsContext::fromRequest($request),
            [
                'card_id' => $card->id,
                'link_index' => $index,
                'link_title' => trim((string) ($link['title'] ?? $link['label'] ?? '')),
                'link_url' => $url,
                'clicked_at' => now(),
            ]
        ));

        if ($this->isCustomSchemeUrl($url)) {
            return redirect()->to($url);
        }

        return redirect()->away($url);
    }

    private function normalizeOutgoingLink(string $rawUrl, string $icon): ?string
    {
        $url = trim($rawUrl);
        if ($url === '') {
            return null;
        }

        if (preg_match('/^(https?:\/\/|mailto:|tel:|sms:)/i', $url)) {
            return $url;
        }

        if ($icon === 'phone') {
            $phone = preg_replace('/\s+/', '', $url);
            return $phone !== '' ? 'tel:' . $phone : null;
        }

        if ($icon === 'email') {
            return 'mailto:' . $url;
        }

        if ($icon === 'sms') {
            $phone = preg_replace('/\s+/', '', $url);
            return $phone !== '' ? 'sms:' . $phone : null;
        }

        if ($icon === 'location') {
            if (preg_match('/^https?:\/\//i', $url)) {
                return $url;
            }

            if (preg_match('/^[a-z][a-z0-9+\-.]*:/i', $url)) {
                return $url;
            }

            if (str_contains($url, ' ') || ! str_contains($url, '.')) {
                return 'https://www.google.com/maps/search/?api=1&query=' . rawurlencode($url);
            }

            return 'https://' . $url;
        }

        return 'https://' . $url;
    }

    private function isCustomSchemeUrl(string $url): bool
    {
        return preg_match('/^(mailto:|tel:|sms:)/i', $url) === 1;
    }

    private function resolveActiveCardBySlug(string $slug): Card
    {
        return Card::query()
            ->where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();
    }

    private function resolveActiveCardByUsername(string $username): Card
    {
        return Card::query()
            ->where('username', $username)
            ->where('is_active', true)
            ->firstOrFail();
    }

    private function recordVisit(Request $request, Card $card): void
    {
        CardVisit::query()->create(array_merge(
            AnalyticsContext::fromRequest($request),
            [
                'card_id' => $card->id,
                'path' => $request->path(),
                'visited_at' => now(),
            ]
        ));
    }
}
