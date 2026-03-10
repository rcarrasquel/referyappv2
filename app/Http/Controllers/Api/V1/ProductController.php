<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class ProductController extends BaseApiController
{
    public function index(Request $request): JsonResponse
    {
        $user = $this->requireBusinessUser($request);
        $search = trim((string) $request->query('search', ''));
        $perPage = max(1, min((int) $request->query('per_page', 20), 50));
        $sortBy = (string) $request->query('sort_by', 'created_at');
        $sortDir = strtolower((string) $request->query('sort_dir', 'desc')) === 'asc' ? 'asc' : 'desc';

        if (! in_array($sortBy, ['name', 'created_at', 'updated_at', 'price'], true)) {
            $sortBy = 'created_at';
        }

        $query = Product::query();
        $this->applyOwnershipScope($query, $user, $request);

        $query
            ->when($search !== '', function ($builder) use ($search): void {
                $builder->where(function ($sub) use ($search): void {
                    $sub->where('name', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%")
                        ->orWhere('price', 'like', "%{$search}%")
                        ->orWhere('link', 'like', "%{$search}%");
                });
            });

        if ($sortBy === 'name') {
            $query->orderBy('name', $sortDir);
        } elseif ($sortBy === 'price') {
            $query->orderByRaw('CASE WHEN price IS NULL OR price = "" THEN 1 ELSE 0 END')
                ->orderBy('price', $sortDir);
        } else {
            $query->orderBy($sortBy, $sortDir);
        }

        $items = $query->paginate($perPage);
        $ownerId = $this->ownerId($request);
        $plan = $this->ownerPlan($ownerId);
        $maxProducts = $plan === 'free' ? 2 : null;
        $totalOwned = Product::query()->where('user_id', $ownerId)->count();
        $canCreate = $maxProducts === null ? true : $totalOwned < $maxProducts;

        return $this->ok([
            'items' => $items->getCollection()->map(fn (Product $product) => $this->serializeProduct($product))->values()->all(),
            'meta' => [
                'current_page' => $items->currentPage(),
                'last_page' => $items->lastPage(),
                'per_page' => $items->perPage(),
                'total' => $items->total(),
            ],
            'limits' => [
                'plan' => $plan,
                'max_products' => $maxProducts,
                'total_products' => $totalOwned,
                'can_create' => $canCreate,
                'remaining_slots' => $maxProducts === null ? null : max(0, $maxProducts - $totalOwned),
            ],
        ]);
    }

    public function options(Request $request): JsonResponse
    {
        $this->requireBusinessUser($request);
        $ownerId = $this->ownerId($request);
        $plan = $this->ownerPlan($ownerId);
        $maxProducts = $plan === 'free' ? 2 : null;
        $totalOwned = Product::query()->where('user_id', $ownerId)->count();

        return $this->ok([
            'limits' => [
                'plan' => $plan,
                'max_products' => $maxProducts,
                'total_products' => $totalOwned,
                'can_create' => $maxProducts === null ? true : $totalOwned < $maxProducts,
                'remaining_slots' => $maxProducts === null ? null : max(0, $maxProducts - $totalOwned),
            ],
            'sort_options' => ['name', 'created_at', 'updated_at', 'price'],
        ]);
    }

    public function show(Request $request, Product $product): JsonResponse
    {
        $user = $this->requireBusinessUser($request);
        $this->ensureOwnership($user, $product);

        return $this->ok(['item' => $this->serializeProduct($product)]);
    }

    public function store(Request $request): JsonResponse
    {
        $this->requireBusinessUser($request);
        $ownerId = $this->ownerId($request);
        $plan = $this->ownerPlan($ownerId);

        if ($plan === 'free' && Product::query()->where('user_id', $ownerId)->count() >= 2) {
            throw ValidationException::withMessages([
                'limit' => 'Free plan allows only 2 products. Upgrade to publish more.',
            ]);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:255'],
            'price' => ['nullable', 'string', 'max:120'],
            'duration_minutes' => ['nullable', 'integer', 'min:5', 'max:600'],
            'link' => ['nullable', 'string', 'max:2048'],
            'image' => ['nullable', 'image', 'max:4096'],
        ]);

        $product = Product::query()->create([
            'user_id' => $ownerId,
            'name' => trim($validated['name']),
            'description' => trim($validated['description']),
            'price' => $this->nullableTrim($validated['price'] ?? null),
            'duration_minutes' => $validated['duration_minutes'] ?? null,
            'link' => $this->nullableTrim($validated['link'] ?? null),
        ]);

        if ($request->hasFile('image')) {
            $product->image = $request->file('image')->store("products/{$product->id}/image", 'public');
            $product->save();
        }

        return $this->ok(['item' => $this->serializeProduct($product)], 201);
    }

    public function update(Request $request, Product $product): JsonResponse
    {
        $user = $this->requireBusinessUser($request);
        $this->ensureOwnership($user, $product);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:255'],
            'price' => ['nullable', 'string', 'max:120'],
            'duration_minutes' => ['nullable', 'integer', 'min:5', 'max:600'],
            'link' => ['nullable', 'string', 'max:2048'],
            'image' => ['nullable', 'image', 'max:4096'],
            'remove_image' => ['sometimes', 'boolean'],
        ]);

        $payload = [
            'name' => trim($validated['name']),
            'description' => trim($validated['description']),
            'price' => $this->nullableTrim($validated['price'] ?? null),
            'duration_minutes' => $validated['duration_minutes'] ?? null,
            'link' => $this->nullableTrim($validated['link'] ?? null),
        ];

        if (($validated['remove_image'] ?? false) && ! $request->hasFile('image') && $product->image) {
            Storage::disk('public')->delete($product->image);
            $payload['image'] = null;
        }

        if ($request->hasFile('image')) {
            if ($product->image) {
                Storage::disk('public')->delete($product->image);
            }
            $payload['image'] = $request->file('image')->store("products/{$product->id}/image", 'public');
        }

        $product->update($payload);

        return $this->ok(['item' => $this->serializeProduct($product->fresh())]);
    }

    public function destroy(Request $request, Product $product): JsonResponse
    {
        $user = $this->requireBusinessUser($request);
        $this->ensureOwnership($user, $product);

        if ($product->image) {
            Storage::disk('public')->delete($product->image);
        }
        Storage::disk('public')->deleteDirectory("products/{$product->id}");

        $product->delete();

        return $this->message('Product deleted successfully.');
    }

    private function ensureOwnership(User $user, Product $product): void
    {
        if ($user->role !== 'admin' && $product->user_id !== $user->id) {
            abort(403);
        }
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

    private function nullableTrim(?string $value): ?string
    {
        $trimmed = trim((string) $value);
        return $trimmed === '' ? null : $trimmed;
    }

    private function serializeProduct(Product $product): array
    {
        return [
            'id' => $product->id,
            'name' => $product->name,
            'description' => $product->description,
            'price' => $product->price,
            'duration_minutes' => $product->duration_minutes,
            'link' => $product->link,
            'image' => $product->image,
            'image_url' => $product->image ? Storage::url($product->image) : null,
            'created_at' => optional($product->created_at)->toIso8601String(),
            'updated_at' => optional($product->updated_at)->toIso8601String(),
        ];
    }
}
