<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Card;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class CardController extends BaseApiController
{
    private const TEMPLATE_STYLES = [
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
    ];

    private const BUTTON_STYLES = ['rounded', 'normal', 'square'];
    private const PROFILE_IMAGE_STYLES = ['circle', 'rounded', 'square'];
    private const BACKGROUND_TYPES = ['color', 'gradient', 'image'];

    public function index(Request $request): JsonResponse
    {
        $user = $this->requireBusinessOnly($request);
        $search = trim((string) $request->query('search', ''));
        $perPage = max(1, min((int) $request->query('per_page', 20), 50));
        $sortBy = (string) $request->query('sort_by', 'created_at');
        $sortDir = strtolower((string) $request->query('sort_dir', 'desc')) === 'asc' ? 'asc' : 'desc';
        $status = trim((string) $request->query('status', ''));

        if (! in_array($sortBy, ['name', 'username', 'created_at', 'updated_at'], true)) {
            $sortBy = 'created_at';
        }

        $query = Card::query();
        $this->applyOwnershipScope($query, $user, $request);

        if ($status === 'active') {
            $query->where('is_active', true);
        } elseif ($status === 'inactive') {
            $query->where('is_active', false);
        }

        if ($search !== '') {
            $query->where(function (Builder $sub) use ($search): void {
                $sub->where('name', 'like', "%{$search}%")
                    ->orWhere('username', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $items = $query->orderBy($sortBy, $sortDir)->paginate($perPage);

        $ownerId = $this->ownerId($request);
        $plan = $this->ownerPlan($ownerId);
        $maxCards = $plan === 'free' ? 1 : null;
        $totalOwned = Card::query()->where('user_id', $ownerId)->count();

        return $this->ok([
            'items' => $items->getCollection()->map(fn (Card $card): array => $this->serializeCard($card))->values()->all(),
            'meta' => [
                'current_page' => $items->currentPage(),
                'last_page' => $items->lastPage(),
                'per_page' => $items->perPage(),
                'total' => $items->total(),
            ],
            'limits' => [
                'plan' => $plan,
                'max_cards' => $maxCards,
                'total_cards' => $totalOwned,
                'can_create' => $maxCards === null ? true : $totalOwned < $maxCards,
                'remaining_slots' => $maxCards === null ? null : max(0, $maxCards - $totalOwned),
            ],
        ]);
    }

    public function options(Request $request): JsonResponse
    {
        $this->requireBusinessOnly($request);

        $ownerId = $this->ownerId($request);
        $plan = $this->ownerPlan($ownerId);
        $maxCards = $plan === 'free' ? 1 : null;
        $totalOwned = Card::query()->where('user_id', $ownerId)->count();

        return $this->ok([
            'styles' => [
                'template_styles' => self::TEMPLATE_STYLES,
                'button_styles' => self::BUTTON_STYLES,
                'profile_image_styles' => self::PROFILE_IMAGE_STYLES,
                'background_types' => self::BACKGROUND_TYPES,
            ],
            'limits' => [
                'plan' => $plan,
                'max_cards' => $maxCards,
                'total_cards' => $totalOwned,
                'can_create' => $maxCards === null ? true : $totalOwned < $maxCards,
                'remaining_slots' => $maxCards === null ? null : max(0, $maxCards - $totalOwned),
            ],
        ]);
    }

    public function checkUsername(Request $request): JsonResponse
    {
        $this->requireBusinessOnly($request);

        $validated = $request->validate([
            'username' => ['required', 'string', 'max:255', 'alpha_dash'],
            'ignore_id' => ['nullable', 'string', Rule::exists('cards', 'id')],
        ]);

        $username = Str::lower(trim($validated['username']));
        $query = Card::query()->whereRaw('LOWER(username) = ?', [$username]);

        if (! empty($validated['ignore_id'])) {
            $query->where('id', '!=', $validated['ignore_id']);
        }

        return $this->ok([
            'username' => $username,
            'available' => ! $query->exists(),
        ]);
    }

    public function show(Request $request, Card $card): JsonResponse
    {
        $user = $this->requireBusinessOnly($request);
        $this->ensureOwnership($user, $card);

        return $this->ok([
            'item' => $this->serializeCard($card),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $this->requireBusinessOnly($request);
        $ownerId = $this->ownerId($request);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', 'alpha_dash', Rule::unique('cards', 'username')],
            'description' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:60'],
            'email' => ['nullable', 'email', 'max:190'],
            'address' => ['nullable', 'string', 'max:255'],
            'google_maps_url' => ['nullable', 'string', 'max:2048'],
        ]);

        $card = DB::transaction(function () use ($ownerId, $validated): Card {
            $owner = User::query()->lockForUpdate()->findOrFail($ownerId);

            if ($owner->plan === 'free' && $owner->cards()->exists()) {
                throw ValidationException::withMessages([
                    'limit' => 'Free plan allows only one card. Upgrade to create more.',
                ]);
            }

            $phone = trim((string) ($validated['phone'] ?? ''));
            $email = trim((string) ($validated['email'] ?? ''));
            $maps = trim((string) ($validated['google_maps_url'] ?? ''));

            return $owner->cards()->create([
                'name' => trim($validated['name']),
                'username' => Str::lower($validated['username']),
                'slug' => Str::lower($validated['username']),
                'description' => $this->nullableTrim($validated['description'] ?? null),
                'phone' => $phone !== '' ? $phone : null,
                'email' => $email !== '' ? $email : null,
                'address' => $this->nullableTrim($validated['address'] ?? null),
                'google_maps_url' => $maps !== '' ? $maps : null,
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
                'links' => $this->synchronizeAutoLinks([], [
                    'phone' => $phone,
                    'email' => $email,
                    'google_maps_url' => $maps,
                ]),
            ]);
        });

        return $this->ok(['item' => $this->serializeCard($card)], 201);
    }

    public function update(Request $request, Card $card): JsonResponse
    {
        $user = $this->requireBusinessOnly($request);
        $this->ensureOwnership($user, $card);

        $validated = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'username' => [
                'sometimes', 'required', 'string', 'max:255', 'alpha_dash',
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
            'background_gradient' => ['sometimes', 'nullable', 'string', 'max:255'],
            'background_type' => ['sometimes', 'required', Rule::in(self::BACKGROUND_TYPES)],
            'template_style' => ['sometimes', 'required', Rule::in(self::TEMPLATE_STYLES)],
            'button_style' => ['sometimes', 'required', Rule::in(self::BUTTON_STYLES)],
            'profile_image_style' => ['sometimes', 'required', Rule::in(self::PROFILE_IMAGE_STYLES)],
            'is_active' => ['sometimes', 'boolean'],

            'links' => ['sometimes', 'array', 'max:20'],
            'links.*.icon' => ['nullable', 'string', 'max:60'],
            'links.*.title' => ['nullable', 'string', 'max:100'],
            'links.*.url' => ['nullable', 'string', 'max:255'],
            'links.*.description' => ['nullable', 'string', 'max:160'],
            'links.*.auto_key' => ['nullable', 'string', 'max:50'],

            'schedule' => ['sometimes', 'array', 'max:7'],
            'schedule.*.day' => ['required', 'integer', 'min:0', 'max:6'],
            'schedule.*.open' => ['required', 'string', 'regex:/^([01]?[0-9]|2[0-3]):[0-5][0-9]$/'],
            'schedule.*.close' => ['required', 'string', 'regex:/^([01]?[0-9]|2[0-3]):[0-5][0-9]$/'],

            'remove_profile_image' => ['sometimes', 'boolean'],
            'remove_header_image' => ['sometimes', 'boolean'],
            'remove_background_image' => ['sometimes', 'boolean'],
            'profile_image' => ['sometimes', 'nullable', 'image', 'max:4096'],
            'header_image' => ['sometimes', 'nullable', 'image', 'max:6144'],
            'background_image' => ['sometimes', 'nullable', 'image', 'max:6144'],
        ]);

        $payload = [];

        foreach ([
            'name',
            'description',
            'header_color',
            'text_color',
            'button_background_color',
            'button_text_color',
            'background_color',
            'background_gradient',
            'background_type',
            'template_style',
            'button_style',
            'profile_image_style',
            'is_active',
        ] as $field) {
            if (array_key_exists($field, $validated)) {
                $payload[$field] = is_string($validated[$field]) ? trim($validated[$field]) : $validated[$field];
            }
        }

        if (array_key_exists('username', $validated)) {
            $payload['username'] = Str::lower(trim($validated['username']));
            $payload['slug'] = $payload['username'];
        }

        foreach (['phone', 'email', 'address', 'google_maps_url'] as $field) {
            if (array_key_exists($field, $validated)) {
                $payload[$field] = $this->nullableTrim($validated[$field]);
            }
        }

        if (array_key_exists('links', $validated)) {
            $payload['links'] = collect($validated['links'])
                ->map(fn (array $link): array => [
                    'icon' => trim((string) ($link['icon'] ?? 'link')),
                    'title' => trim((string) ($link['title'] ?? '')),
                    'url' => trim((string) ($link['url'] ?? '')),
                    'description' => trim((string) ($link['description'] ?? '')),
                    'auto_key' => trim((string) ($link['auto_key'] ?? '')),
                ])
                ->filter(fn (array $link): bool => $link['title'] !== '' || $link['url'] !== '')
                ->take(20)
                ->values()
                ->all();
        }

        if (array_key_exists('schedule', $validated)) {
            $payload['schedule'] = collect($validated['schedule'])
                ->unique('day')
                ->sortBy('day')
                ->map(fn (array $item): array => [
                    'day' => (int) $item['day'],
                    'open' => $item['open'],
                    'close' => $item['close'],
                ])
                ->values()
                ->all();
        }

        if (($validated['remove_profile_image'] ?? false) && ! $request->hasFile('profile_image') && $card->profile_image) {
            Storage::disk('public')->delete($card->profile_image);
            $payload['profile_image'] = null;
        }

        if (($validated['remove_header_image'] ?? false) && ! $request->hasFile('header_image') && $card->header_image) {
            Storage::disk('public')->delete($card->header_image);
            $payload['header_image'] = null;
        }

        if (($validated['remove_background_image'] ?? false) && ! $request->hasFile('background_image') && $card->background_image) {
            Storage::disk('public')->delete($card->background_image);
            $payload['background_image'] = null;
        }

        foreach (['profile_image', 'header_image', 'background_image'] as $imageField) {
            if ($request->hasFile($imageField)) {
                if ($card->{$imageField}) {
                    Storage::disk('public')->delete($card->{$imageField});
                }
                $payload[$imageField] = $request->file($imageField)->store("cards/{$card->id}/{$imageField}", 'public');
            }
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

        if (! empty($payload)) {
            $card->update($payload);
        }

        return $this->ok(['item' => $this->serializeCard($card->fresh())]);
    }

    public function destroy(Request $request, Card $card): JsonResponse
    {
        $user = $this->requireBusinessOnly($request);
        $this->ensureOwnership($user, $card);

        Storage::disk('public')->deleteDirectory("cards/{$card->id}");
        $card->delete();

        return $this->message('Card deleted successfully.');
    }

    private function applyOwnershipScope(Builder $query, User $user, Request $request): void
    {
        if ($user->role !== 'admin') {
            $query->where('user_id', $user->id);
            return;
        }

        $ownerId = trim((string) $request->query('owner_id', ''));
        if ($ownerId !== '') {
            $query->where('user_id', $ownerId);
        }
    }

    private function ownerId(Request $request): int
    {
        /** @var User $user */
        $user = $request->user();

        if ($user->role !== 'admin') {
            return $user->id;
        }

        $ownerId = (int) $request->query('owner_id', $request->input('owner_id', $user->id));
        if ($ownerId <= 0 || ! User::query()->whereKey($ownerId)->exists()) {
            return $user->id;
        }

        return $ownerId;
    }

    private function ownerPlan(int $ownerId): string
    {
        return (string) (User::query()->whereKey($ownerId)->value('plan') ?? 'free');
    }

    private function ensureOwnership(User $user, Card $card): void
    {
        if ($user->role !== 'admin' && $card->user_id !== $user->id) {
            abort(403);
        }
    }

    private function synchronizeAutoLinks(array $links, array $contact): array
    {
        $normalized = collect($links)
            ->map(fn ($link) => is_array($link) ? $link : [])
            ->map(fn (array $link): array => [
                'icon' => trim((string) ($link['icon'] ?? 'link')),
                'title' => trim((string) ($link['title'] ?? '')),
                'url' => trim((string) ($link['url'] ?? '')),
                'description' => trim((string) ($link['description'] ?? '')),
                'auto_key' => trim((string) ($link['auto_key'] ?? '')),
            ]);

        $autoKeys = ['auto_phone', 'auto_email', 'auto_maps'];
        $existingAuto = $normalized
            ->filter(fn (array $link): bool => in_array($link['auto_key'], $autoKeys, true))
            ->keyBy('auto_key');

        $clean = $normalized
            ->filter(fn (array $link): bool => ! in_array($link['auto_key'], $autoKeys, true))
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
            ->filter(fn (array $link): bool => $link['title'] !== '' || $link['url'] !== '')
            ->take(20)
            ->values()
            ->all();
    }

    private function nullableTrim(?string $value): ?string
    {
        $trimmed = trim((string) $value);
        return $trimmed === '' ? null : $trimmed;
    }

    private function serializeCard(Card $card): array
    {
        $publicBase = rtrim((string) config('app.url', ''), '/');
        $publicUrl = $publicBase !== '' ? "{$publicBase}/{$card->username}" : "/{$card->username}";

        return [
            'id' => $card->id,
            'user_id' => $card->user_id,
            'name' => $card->name,
            'username' => $card->username,
            'slug' => $card->slug,
            'description' => $card->description,
            'phone' => $card->phone,
            'email' => $card->email,
            'address' => $card->address,
            'google_maps_url' => $card->google_maps_url,
            'profile_image' => $card->profile_image,
            'profile_image_url' => $card->profile_image ? Storage::url($card->profile_image) : null,
            'header_image' => $card->header_image,
            'header_image_url' => $card->header_image ? Storage::url($card->header_image) : null,
            'background_image' => $card->background_image,
            'background_image_url' => $card->background_image ? Storage::url($card->background_image) : null,
            'profile_image_style' => $card->profile_image_style,
            'header_color' => $card->header_color,
            'background_type' => $card->background_type,
            'background_color' => $card->background_color,
            'background_gradient' => $card->background_gradient,
            'button_style' => $card->button_style,
            'template_style' => $card->template_style,
            'text_color' => $card->text_color,
            'button_background_color' => $card->button_background_color,
            'button_text_color' => $card->button_text_color,
            'links' => is_array($card->links) ? array_values($card->links) : [],
            'links_count' => is_array($card->links) ? count($card->links) : 0,
            'schedule' => is_array($card->schedule) ? array_values($card->schedule) : [],
            'is_active' => (bool) $card->is_active,
            'public_url' => $publicUrl,
            'created_at' => optional($card->created_at)->toIso8601String(),
            'updated_at' => optional($card->updated_at)->toIso8601String(),
        ];
    }
}
