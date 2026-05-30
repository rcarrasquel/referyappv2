<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Card;
use App\Models\Client;
use App\Models\Lead;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class ClientController extends Controller
{
    private const CLIENT_STATUS_OPTIONS = [
        'new',
        'contacted',
        'qualified',
        'proposal_sent',
        'negotiation',
        'won',
        'lost',
        'inactive',
    ];
    public function index(Request $request): Response
    {
        $user = $request->user();
        $search = trim((string) $request->query('search', ''));
        $this->syncClientsFromInteractions((int) $user->id);

        $appointments = Appointment::query()
            ->where('user_id', $user->id)
            ->get(['id', 'card_id', 'full_name', 'email', 'phone', 'interest', 'starts_at', 'created_at']);

        $leads = Lead::query()
            ->where('user_id', $user->id)
            ->get(['id', 'card_id', 'full_name', 'email', 'phone', 'interest', 'created_at']);

        $cards = Card::query()
            ->where('user_id', $user->id)
            ->pluck('name', 'id');

        $rows = collect();

        foreach ($appointments as $item) {
            $rows->push([
                'type' => 'appointment',
                'card_id' => $item->card_id,
                'full_name' => (string) $item->full_name,
                'email' => trim((string) ($item->email ?? '')),
                'phone' => trim((string) ($item->phone ?? '')),
                'interest' => trim((string) ($item->interest ?? '')),
                'happened_at' => optional($item->starts_at)->toIso8601String() ?: optional($item->created_at)->toIso8601String(),
            ]);
        }

        foreach ($leads as $item) {
            $rows->push([
                'type' => 'lead',
                'card_id' => $item->card_id,
                'full_name' => (string) $item->full_name,
                'email' => trim((string) ($item->email ?? '')),
                'phone' => trim((string) ($item->phone ?? '')),
                'interest' => trim((string) ($item->interest ?? '')),
                'happened_at' => optional($item->created_at)->toIso8601String(),
            ]);
        }

        $stats = $rows
            ->groupBy(function (array $row): string {
                [$type, $value] = $this->resolveIdentity([
                    'email' => $row['email'] ?? '',
                    'phone' => $row['phone'] ?? '',
                    'full_name' => $row['full_name'] ?? '',
                ]);
                return "{$type}:{$value}";
            })
            ->map(function ($items, string $identity) use ($cards): array {
                $items = collect($items)->sortByDesc('happened_at')->values();
                $first = (array) $items->first();

                $appointmentsCount = (int) $items->where('type', 'appointment')->count();
                $leadsCount = (int) $items->where('type', 'lead')->count();

                $cardNames = $items
                    ->pluck('card_id')
                    ->filter()
                    ->unique()
                    ->map(fn ($cardId) => (string) ($cards[$cardId] ?? ''))
                    ->filter()
                    ->values()
                    ->all();

                return [
                    'identity' => $identity,
                    'appointments_count' => $appointmentsCount,
                    'leads_count' => $leadsCount,
                    'last_interaction_at' => (string) ($first['happened_at'] ?? ''),
                    'cards' => $cardNames,
                ];
            });

        $products = Product::query()
            ->where('user_id', $user->id)
            ->orderBy('name')
            ->get(['id', 'name']);

        $clients = Client::query()
            ->with(['products:id,name'])
            ->where('user_id', $user->id)
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($sub) use ($search): void {
                    $sub->where('full_name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                });
            })
            ->orderByDesc('last_interaction_at')
            ->orderBy('full_name')
            ->get()
            ->map(function (Client $client) use ($stats): array {
                $identity = $client->identity_type . ':' . $client->identity_value;
                $meta = (array) ($stats[$identity] ?? []);

                return [
                    'id' => $client->id,
                    'identity' => $identity,
                    'first_name' => $client->first_name,
                    'last_name' => $client->last_name,
                    'full_name' => $client->full_name,
                    'email' => $client->email,
                    'phone' => $client->phone,
                    'address_1' => $client->address_1,
                    'address_2' => $client->address_2,
                    'city' => $client->city,
                    'country' => $client->country,
                    'zip' => $client->zip,
                    'state' => $client->state,
                    'status' => $client->status ?: 'new',
                    'interest' => $client->interest,
                    'notes' => $client->notes,
                    'service_ids' => $client->products->pluck('id')->values()->all(),
                    'service_names' => $client->products->pluck('name')->values()->all(),
                    'appointments_count' => (int) ($meta['appointments_count'] ?? 0),
                    'leads_count' => (int) ($meta['leads_count'] ?? 0),
                    'last_interaction_at' => (string) ($meta['last_interaction_at'] ?? optional($client->last_interaction_at)->toIso8601String()),
                    'cards' => $meta['cards'] ?? [],
                ];
            })
            ->values();

        return Inertia::render('Modules/Clients', [
            'clients' => $clients->all(),
            'filters' => [
                'search' => $search,
            ],
            'serviceOptions' => $products->map(fn (Product $product): array => [
                'id' => $product->id,
                'name' => $product->name,
            ])->values()->all(),
            'statusOptions' => self::CLIENT_STATUS_OPTIONS,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();
        $validated = $request->validate([
            'first_name' => ['nullable', 'string', 'max:120'],
            'last_name' => ['nullable', 'string', 'max:120'],
            'full_name' => ['nullable', 'string', 'max:160'],
            'email' => ['nullable', 'email', 'max:190'],
            'phone' => ['nullable', 'string', 'max:40'],
            'address_1' => ['nullable', 'string', 'max:190'],
            'address_2' => ['nullable', 'string', 'max:190'],
            'city' => ['nullable', 'string', 'max:120'],
            'country' => ['nullable', 'string', 'max:120'],
            'zip' => ['nullable', 'string', 'max:40'],
            'state' => ['nullable', 'string', 'max:120'],
            'status' => ['nullable', 'string', 'in:' . implode(',', self::CLIENT_STATUS_OPTIONS)],
            'service_ids' => ['nullable', 'array'],
            'service_ids.*' => ['string'],
            'interest' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        [$identityType, $identityValue] = $this->resolveIdentity($validated);
        $fullName = $this->buildFullName(
            $validated['first_name'] ?? null,
            $validated['last_name'] ?? null,
            $validated['full_name'] ?? null
        );

        $client = Client::query()->updateOrCreate(
            [
                'user_id' => $user->id,
                'identity_type' => $identityType,
                'identity_value' => $identityValue,
            ],
            [
                'first_name' => $this->nullableTrim($validated['first_name'] ?? null),
                'last_name' => $this->nullableTrim($validated['last_name'] ?? null),
                'full_name' => $fullName,
                'email' => $this->nullableTrim($validated['email'] ?? null),
                'phone' => $this->nullableTrim($validated['phone'] ?? null),
                'address_1' => $this->nullableTrim($validated['address_1'] ?? null),
                'address_2' => $this->nullableTrim($validated['address_2'] ?? null),
                'city' => $this->nullableTrim($validated['city'] ?? null),
                'country' => $this->nullableTrim($validated['country'] ?? null),
                'zip' => $this->nullableTrim($validated['zip'] ?? null),
                'state' => $this->nullableTrim($validated['state'] ?? null),
                'status' => in_array((string) ($validated['status'] ?? ''), self::CLIENT_STATUS_OPTIONS, true) ? (string) $validated['status'] : 'new',
                'interest' => $this->nullableTrim($validated['interest'] ?? null),
                'notes' => $this->nullableTrim($validated['notes'] ?? null),
            ]
        );

        $serviceIds = collect($validated['service_ids'] ?? [])->filter()->values()->all();
        $validServiceIds = Product::query()
            ->where('user_id', $user->id)
            ->whereIn('id', $serviceIds)
            ->pluck('id')
            ->all();
        $client->products()->sync($validServiceIds);

        return back()->with('status', 'Client saved successfully.');
    }

    public function update(Request $request, Client $client): RedirectResponse
    {
        $user = $request->user();
        if ((int) $client->user_id !== (int) $user->id) {
            abort(403);
        }

        $validated = $request->validate([
            'first_name' => ['nullable', 'string', 'max:120'],
            'last_name' => ['nullable', 'string', 'max:120'],
            'full_name' => ['nullable', 'string', 'max:160'],
            'email' => ['nullable', 'email', 'max:190'],
            'phone' => ['nullable', 'string', 'max:40'],
            'address_1' => ['nullable', 'string', 'max:190'],
            'address_2' => ['nullable', 'string', 'max:190'],
            'city' => ['nullable', 'string', 'max:120'],
            'country' => ['nullable', 'string', 'max:120'],
            'zip' => ['nullable', 'string', 'max:40'],
            'state' => ['nullable', 'string', 'max:120'],
            'status' => ['nullable', 'string', 'in:' . implode(',', self::CLIENT_STATUS_OPTIONS)],
            'service_ids' => ['nullable', 'array'],
            'service_ids.*' => ['string'],
            'interest' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        [$identityType, $identityValue] = $this->resolveIdentity($validated);
        $fullName = $this->buildFullName(
            $validated['first_name'] ?? null,
            $validated['last_name'] ?? null,
            $validated['full_name'] ?? null
        );

        $client->update([
            'identity_type' => $identityType,
            'identity_value' => $identityValue,
            'first_name' => $this->nullableTrim($validated['first_name'] ?? null),
            'last_name' => $this->nullableTrim($validated['last_name'] ?? null),
            'full_name' => $fullName,
            'email' => $this->nullableTrim($validated['email'] ?? null),
            'phone' => $this->nullableTrim($validated['phone'] ?? null),
            'address_1' => $this->nullableTrim($validated['address_1'] ?? null),
            'address_2' => $this->nullableTrim($validated['address_2'] ?? null),
            'city' => $this->nullableTrim($validated['city'] ?? null),
            'country' => $this->nullableTrim($validated['country'] ?? null),
            'zip' => $this->nullableTrim($validated['zip'] ?? null),
            'state' => $this->nullableTrim($validated['state'] ?? null),
            'status' => in_array((string) ($validated['status'] ?? ''), self::CLIENT_STATUS_OPTIONS, true) ? (string) $validated['status'] : 'new',
            'interest' => $this->nullableTrim($validated['interest'] ?? null),
            'notes' => $this->nullableTrim($validated['notes'] ?? null),
        ]);

        $serviceIds = collect($validated['service_ids'] ?? [])->filter()->values()->all();
        $validServiceIds = Product::query()
            ->where('user_id', $user->id)
            ->whereIn('id', $serviceIds)
            ->pluck('id')
            ->all();
        $client->products()->sync($validServiceIds);

        return back()->with('status', 'Client updated successfully.');
    }

    public function destroy(Request $request, Client $client): RedirectResponse
    {
        $user = $request->user();
        if ((int) $client->user_id !== (int) $user->id) {
            abort(403);
        }

        $client->delete();

        return back()->with('status', 'Client deleted successfully.');
    }

    private function syncClientsFromInteractions(int $userId): void
    {
        $appointments = Appointment::query()
            ->where('user_id', $userId)
            ->get(['full_name', 'email', 'phone', 'interest', 'starts_at', 'created_at']);

        $leads = Lead::query()
            ->where('user_id', $userId)
            ->get(['full_name', 'email', 'phone', 'interest', 'created_at']);

        $rows = collect();

        foreach ($appointments as $item) {
            $rows->push([
                'full_name' => (string) $item->full_name,
                'email' => trim((string) ($item->email ?? '')),
                'phone' => trim((string) ($item->phone ?? '')),
                'interest' => trim((string) ($item->interest ?? '')),
                'happened_at' => optional($item->starts_at)->toIso8601String() ?: optional($item->created_at)->toIso8601String(),
            ]);
        }

        foreach ($leads as $item) {
            $rows->push([
                'full_name' => (string) $item->full_name,
                'email' => trim((string) ($item->email ?? '')),
                'phone' => trim((string) ($item->phone ?? '')),
                'interest' => trim((string) ($item->interest ?? '')),
                'happened_at' => optional($item->created_at)->toIso8601String(),
            ]);
        }

        $rows
            ->groupBy(function (array $row): string {
                [$type, $value] = $this->resolveIdentity($row);
                return "{$type}:{$value}";
            })
            ->each(function ($items) use ($userId): void {
                $items = collect($items)->sortByDesc('happened_at')->values();
                $first = (array) $items->first();
                [$identityType, $identityValue] = $this->resolveIdentity($first);

                $existing = Client::query()
                    ->where('user_id', $userId)
                    ->where('identity_type', $identityType)
                    ->where('identity_value', $identityValue)
                    ->first();

                if (! $existing) {
                    Client::query()->create([
                        'user_id' => $userId,
                        'identity_type' => $identityType,
                        'identity_value' => $identityValue,
                        'first_name' => trim((string) ($first['full_name'] ?? '')),
                        'last_name' => null,
                        'full_name' => trim((string) ($first['full_name'] ?? '')),
                        'email' => $this->nullableTrim($first['email'] ?? null),
                        'phone' => $this->nullableTrim($first['phone'] ?? null),
                        'status' => 'new',
                        'interest' => $this->nullableTrim($first['interest'] ?? null),
                        'last_interaction_at' => $first['happened_at'] ?? null,
                    ]);
                    return;
                }

                $existing->update([
                    'last_interaction_at' => $first['happened_at'] ?? $existing->last_interaction_at,
                    'interest' => $existing->interest ?: $this->nullableTrim($first['interest'] ?? null),
                ]);
            });
    }

    private function resolveIdentity(array $payload): array
    {
        $email = strtolower(trim((string) ($payload['email'] ?? '')));
        if ($email !== '') {
            return ['email', $email];
        }

        $phone = preg_replace('/\s+/', '', trim((string) ($payload['phone'] ?? '')));
        if ($phone !== '') {
            return ['phone', $phone];
        }

        return ['name', strtolower(trim((string) ($payload['full_name'] ?? 'unknown')))];
    }

    private function nullableTrim(?string $value): ?string
    {
        $trimmed = trim((string) $value);
        return $trimmed === '' ? null : $trimmed;
    }

    private function buildFullName(?string $firstName, ?string $lastName, ?string $fallback): string
    {
        $first = trim((string) $firstName);
        $last = trim((string) $lastName);
        $full = trim($first . ' ' . $last);
        if ($full !== '') {
            return $full;
        }

        $fallbackName = trim((string) $fallback);
        return $fallbackName !== '' ? $fallbackName : 'Unknown Client';
    }
}
