<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Card;
use App\Models\Lead;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class LeadController extends BaseApiController
{
    private const STATUS_OPTIONS = [
        'new',
        'contacted',
        'qualified',
        'converted',
        'lost',
    ];

    private const SOURCE_OPTIONS = [
        'public',
        'dashboard',
        'dashboard_api',
        'manual',
    ];

    public function index(Request $request): JsonResponse
    {
        $user = $this->requireBusinessOnly($request);
        $search = trim((string) $request->query('search', ''));
        $status = trim((string) $request->query('status', ''));
        $source = trim((string) $request->query('source', ''));
        $cardId = trim((string) $request->query('card_id', ''));
        $perPage = max(1, min((int) $request->query('per_page', 20), 100));
        $sortBy = (string) $request->query('sort_by', 'created_at');
        $sortDir = strtolower((string) $request->query('sort_dir', 'desc')) === 'asc' ? 'asc' : 'desc';
        $fromDate = trim((string) $request->query('from_date', ''));
        $toDate = trim((string) $request->query('to_date', ''));

        if (! in_array($sortBy, ['created_at', 'updated_at', 'full_name', 'status'], true)) {
            $sortBy = 'created_at';
        }

        $query = Lead::query()
            ->with(['card:id,name,username', 'product:id,name,user_id']);

        $this->applyOwnershipScope($query, $user, $request);

        if ($status !== '' && in_array($status, self::STATUS_OPTIONS, true)) {
            $query->where('status', $status);
        }

        if ($source !== '' && in_array($source, self::SOURCE_OPTIONS, true)) {
            $query->where('source', $source);
        }

        if ($cardId !== '') {
            $query->where('card_id', $cardId);
        }

        if ($search !== '') {
            $query->where(function (Builder $sub) use ($search): void {
                $sub->where('full_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('interest', 'like', "%{$search}%");
            });
        }

        if ($fromDate !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $fromDate)) {
            $query->whereDate('created_at', '>=', $fromDate);
        }

        if ($toDate !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $toDate)) {
            $query->whereDate('created_at', '<=', $toDate);
        }

        $items = $query->orderBy($sortBy, $sortDir)->paginate($perPage);

        return $this->ok([
            'items' => $items->getCollection()->map(fn (Lead $lead): array => $this->serializeLead($lead))->values()->all(),
            'meta' => [
                'current_page' => $items->currentPage(),
                'last_page' => $items->lastPage(),
                'per_page' => $items->perPage(),
                'total' => $items->total(),
            ],
            'status_options' => self::STATUS_OPTIONS,
            'source_options' => self::SOURCE_OPTIONS,
        ]);
    }

    public function options(Request $request): JsonResponse
    {
        $user = $this->requireBusinessOnly($request);

        $cardsQuery = Card::query()->orderBy('name');
        $productsQuery = Product::query()->orderBy('name');

        if ($user->role !== 'admin') {
            $cardsQuery->where('user_id', $user->id);
            $productsQuery->where('user_id', $user->id);
        } else {
            $ownerId = trim((string) $request->query('owner_id', ''));
            if ($ownerId !== '') {
                $cardsQuery->where('user_id', $ownerId);
                $productsQuery->where('user_id', $ownerId);
            }
        }

        return $this->ok([
            'cards' => $cardsQuery->get(['id', 'name', 'username']),
            'services' => $productsQuery->get(['id', 'name', 'duration_minutes']),
            'status_options' => self::STATUS_OPTIONS,
            'source_options' => self::SOURCE_OPTIONS,
        ]);
    }

    public function show(Request $request, Lead $lead): JsonResponse
    {
        $user = $this->requireBusinessOnly($request);
        $this->ensureOwnership($user, $lead);

        return $this->ok([
            'item' => $this->serializeLead($lead->load(['card:id,name,username', 'product:id,name,user_id'])),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $user = $this->requireBusinessOnly($request);

        $validated = $request->validate([
            'card_id' => ['required', 'string', Rule::exists('cards', 'id')],
            'product_id' => ['nullable', 'string', Rule::exists('products', 'id')],
            'full_name' => ['required', 'string', 'max:160'],
            'phone' => ['nullable', 'string', 'max:40'],
            'email' => ['nullable', 'email', 'max:190'],
            'interest' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'status' => ['nullable', Rule::in(self::STATUS_OPTIONS)],
            'source' => ['nullable', Rule::in(self::SOURCE_OPTIONS)],
        ]);

        $card = Card::query()->findOrFail($validated['card_id']);
        $this->authorizeCard($user, $card);

        $product = $this->resolveProduct($validated['product_id'] ?? null, $card->user_id);

        $lead = Lead::query()->create([
            'user_id' => $card->user_id,
            'card_id' => $card->id,
            'product_id' => $product?->id,
            'full_name' => trim($validated['full_name']),
            'phone' => $this->nullableTrim($validated['phone'] ?? null),
            'email' => $this->nullableTrim($validated['email'] ?? null),
            'interest' => $this->resolveInterest($validated['interest'] ?? null, $product?->name),
            'notes' => $this->nullableTrim($validated['notes'] ?? null),
            'status' => $validated['status'] ?? 'new',
            'source' => $validated['source'] ?? 'dashboard_api',
        ]);

        return $this->ok([
            'item' => $this->serializeLead($lead->load(['card:id,name,username', 'product:id,name,user_id'])),
        ], 201);
    }

    public function update(Request $request, Lead $lead): JsonResponse
    {
        $user = $this->requireBusinessOnly($request);
        $this->ensureOwnership($user, $lead);

        $validated = $request->validate([
            'card_id' => ['sometimes', 'required', 'string', Rule::exists('cards', 'id')],
            'product_id' => ['sometimes', 'nullable', 'string', Rule::exists('products', 'id')],
            'full_name' => ['sometimes', 'required', 'string', 'max:160'],
            'status' => ['sometimes', 'required', Rule::in(self::STATUS_OPTIONS)],
            'notes' => ['sometimes', 'nullable', 'string', 'max:1000'],
            'interest' => ['sometimes', 'nullable', 'string', 'max:255'],
            'phone' => ['sometimes', 'nullable', 'string', 'max:40'],
            'email' => ['sometimes', 'nullable', 'email', 'max:190'],
            'source' => ['sometimes', Rule::in(self::SOURCE_OPTIONS)],
        ]);

        $payload = [];

        if (array_key_exists('card_id', $validated)) {
            $card = Card::query()->findOrFail($validated['card_id']);
            $this->authorizeCard($user, $card);
            $payload['card_id'] = $card->id;
            $payload['user_id'] = $card->user_id;

            if (! array_key_exists('product_id', $validated)) {
                $validated['product_id'] = $lead->product_id;
            }
        } else {
            $card = Card::query()->findOrFail($lead->card_id);
            $this->authorizeCard($user, $card);
        }

        if (array_key_exists('product_id', $validated)) {
            $product = $this->resolveProduct($validated['product_id'], $card->user_id);
            $payload['product_id'] = $product?->id;
        } else {
            $product = $lead->product;
        }

        if (array_key_exists('full_name', $validated)) {
            $payload['full_name'] = trim($validated['full_name']);
        }

        foreach (['status', 'source'] as $field) {
            if (array_key_exists($field, $validated)) {
                $payload[$field] = $validated[$field];
            }
        }

        foreach (['interest', 'phone', 'email', 'notes'] as $field) {
            if (array_key_exists($field, $validated)) {
                $payload[$field] = $this->nullableTrim($validated[$field]);
            }
        }

        if (array_key_exists('interest', $validated) && $payload['interest'] === null) {
            $payload['interest'] = $this->resolveInterest(null, $product?->name);
        }

        if (! empty($payload)) {
            $lead->update($payload);
        }

        return $this->ok([
            'item' => $this->serializeLead($lead->fresh()->load(['card:id,name,username', 'product:id,name,user_id'])),
        ]);
    }

    public function destroy(Request $request, Lead $lead): JsonResponse
    {
        $user = $this->requireBusinessOnly($request);
        $this->ensureOwnership($user, $lead);

        $lead->delete();

        return $this->message('Lead deleted successfully.');
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

    private function ensureOwnership(User $user, Lead $lead): void
    {
        if ($user->role !== 'admin' && $lead->user_id !== $user->id) {
            abort(403);
        }
    }

    private function authorizeCard(User $user, Card $card): void
    {
        if ($user->role !== 'admin' && $card->user_id !== $user->id) {
            abort(403);
        }
    }

    private function resolveProduct(?string $productId, int $ownerId): ?Product
    {
        if (! $productId) {
            return null;
        }

        return Product::query()
            ->where('id', $productId)
            ->where('user_id', $ownerId)
            ->first();
    }

    private function nullableTrim(?string $value): ?string
    {
        $trimmed = trim((string) $value);
        return $trimmed === '' ? null : $trimmed;
    }

    private function resolveInterest(?string $interest, ?string $serviceName): ?string
    {
        $trimmed = trim((string) $interest);
        if ($trimmed !== '') {
            return $trimmed;
        }

        $service = trim((string) $serviceName);
        return $service !== '' ? $service : null;
    }

    private function serializeLead(Lead $lead): array
    {
        return [
            'id' => $lead->id,
            'user_id' => $lead->user_id,
            'card_id' => $lead->card_id,
            'card_name' => $lead->card?->name,
            'card_username' => $lead->card?->username,
            'product_id' => $lead->product_id,
            'product_name' => $lead->product?->name,
            'full_name' => $lead->full_name,
            'phone' => $lead->phone,
            'email' => $lead->email,
            'interest' => $lead->interest,
            'notes' => $lead->notes,
            'status' => $lead->status,
            'source' => $lead->source,
            'created_at' => optional($lead->created_at)->toIso8601String(),
            'updated_at' => optional($lead->updated_at)->toIso8601String(),
        ];
    }
}
