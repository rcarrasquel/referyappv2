<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class ProductController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();
        $search = trim((string) $request->string('search'));

        $productsQuery = $user->products()
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($subQuery) use ($search): void {
                    $subQuery->where('name', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%")
                        ->orWhere('price', 'like', "%{$search}%")
                        ->orWhere('link', 'like', "%{$search}%");
                });
            })
            ->latest();

        $products = $productsQuery
            ->paginate(8, ['id', 'name', 'description', 'image', 'price', 'duration_minutes', 'link', 'created_at'])
            ->withQueryString();

        $isFree = $user->plan === 'free';
        $totalProducts = $user->products()->count();

        return Inertia::render('Products/Index', [
            'products' => $products,
            'plan' => $user->plan,
            'canCreate' => ! $isFree || $totalProducts < 2,
            'limit' => $isFree ? 2 : null,
            'filters' => [
                'search' => $search,
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();

        if ($user->plan === 'free' && $user->products()->count() >= 2) {
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

        $product = $user->products()->create([
            'name' => $validated['name'],
            'description' => $validated['description'],
            'price' => $validated['price'] ?: null,
            'duration_minutes' => $validated['duration_minutes'] ?? null,
            'link' => $validated['link'] ?: null,
        ]);

        if ($request->hasFile('image')) {
            $product->image = $request->file('image')->store("products/{$product->id}/image", 'public');
            $product->save();
        }

        return back()->with('status', 'Product created successfully.');
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $this->ensureOwnership($request, $product);

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
            'name' => $validated['name'],
            'description' => $validated['description'],
            'price' => $validated['price'] ?: null,
            'duration_minutes' => $validated['duration_minutes'] ?? null,
            'link' => $validated['link'] ?: null,
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

        return back()->with('status', 'Product updated successfully.');
    }

    public function destroy(Request $request, Product $product): RedirectResponse
    {
        $this->ensureOwnership($request, $product);

        if ($product->image) {
            Storage::disk('public')->delete($product->image);
            Storage::disk('public')->deleteDirectory("products/{$product->id}");
        }

        $product->delete();

        return back()->with('status', 'Product deleted successfully.');
    }

    private function ensureOwnership(Request $request, Product $product): void
    {
        if ($request->user()->role !== 'admin' && $product->user_id !== $request->user()->id) {
            abort(403);
        }
    }
}
