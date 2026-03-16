<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Appointment;
use App\Models\Card;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class AppointmentController extends BaseApiController
{
    private const STATUS_OPTIONS = [
        'scheduled',
        'confirmed',
        'attended',
        'no_show',
        'cancelled',
        'rescheduled',
        'completed',
    ];

    public function index(Request $request): JsonResponse
    {
        $user = $this->requireBusinessOnly($request);
        $search = trim((string) $request->query('search', ''));
        $cardId = trim((string) $request->query('card_id', ''));
        $month = trim((string) $request->query('month', now()->format('Y-m')));
        $status = trim((string) $request->query('status', ''));
        $perPage = max(1, min((int) $request->query('per_page', 20), 100));

        $query = Appointment::query()
            ->with(['card:id,name,username', 'product:id,name,duration_minutes'])
            ->latest('starts_at');

        if (! $this->isAdmin($user->role)) {
            $query->where('user_id', $user->id);
        }

        if ($cardId !== '') {
            $query->where('card_id', $cardId);
        }

        if ($status !== '' && in_array($status, self::STATUS_OPTIONS, true)) {
            $query->where('status', $status);
        }

        if ($search !== '') {
            $query->where(function ($sub) use ($search): void {
                $sub->where('full_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('interest', 'like', "%{$search}%");
            });
        }

        if (preg_match('/^\d{4}-\d{2}$/', $month)) {
            $start = Carbon::createFromFormat('Y-m', $month)->startOfMonth();
            $end = $start->copy()->endOfMonth();
            $query->whereBetween('starts_at', [$start, $end]);
        }

        $items = $query->paginate($perPage);

        return $this->ok([
            'items' => $items->getCollection()->map(fn (Appointment $appointment) => $this->serializeAppointment($appointment))->values()->all(),
            'meta' => [
                'current_page' => $items->currentPage(),
                'last_page' => $items->lastPage(),
                'per_page' => $items->perPage(),
                'total' => $items->total(),
            ],
            'status_options' => self::STATUS_OPTIONS,
        ]);
    }

    public function options(Request $request): JsonResponse
    {
        $user = $this->requireBusinessOnly($request);

        $cardsQuery = Card::query()->orderBy('name');
        $servicesQuery = Product::query()->orderBy('name');

        if (! $this->isAdmin($user->role)) {
            $cardsQuery->where('user_id', $user->id);
            $servicesQuery->where('user_id', $user->id);
        }

        return $this->ok([
            'cards' => $cardsQuery->get(['id', 'name', 'username', 'schedule']),
            'services' => $servicesQuery->get(['id', 'user_id', 'name', 'duration_minutes', 'price']),
            'status_options' => self::STATUS_OPTIONS,
        ]);
    }

    public function show(Request $request, Appointment $appointment): JsonResponse
    {
        $user = $this->requireBusinessOnly($request);
        $this->ensureAppointmentOwnership($user->id, $appointment, $user->role);

        return $this->ok([
            'item' => $this->serializeAppointment($appointment->load(['card:id,name,username', 'product:id,name,duration_minutes'])),
        ]);
    }

    public function calendar(Request $request): JsonResponse
    {
        $user = $this->requireBusinessOnly($request);
        $month = trim((string) $request->query('month', now()->format('Y-m')));
        $cardId = trim((string) $request->query('card_id', ''));
        $status = trim((string) $request->query('status', ''));

        if (! preg_match('/^\d{4}-\d{2}$/', $month)) {
            throw ValidationException::withMessages([
                'month' => 'Invalid month format. Expected YYYY-MM.',
            ]);
        }

        $start = Carbon::createFromFormat('Y-m', $month)->startOfMonth();
        $end = $start->copy()->endOfMonth();

        $query = Appointment::query();
        $this->applyOwnershipScope($query, $user->id, $user->role);

        if ($cardId !== '') {
            $card = Card::query()->findOrFail($cardId);
            $this->authorizeCard($user->id, $card, $user->role);
            $query->where('card_id', $cardId);
        }

        if ($status !== '' && in_array($status, self::STATUS_OPTIONS, true)) {
            $query->where('status', $status);
        }

        $rows = $query
            ->whereBetween('starts_at', [$start, $end])
            ->selectRaw('DATE(starts_at) as day, count(*) as total')
            ->groupBy('day')
            ->orderBy('day')
            ->get();

        $map = $rows->pluck('total', 'day');

        $days = [];
        $cursor = $start->copy();
        while ($cursor->lte($end)) {
            $key = $cursor->toDateString();
            $days[] = [
                'date' => $key,
                'count' => (int) ($map[$key] ?? 0),
            ];
            $cursor->addDay();
        }

        return $this->ok([
            'month' => $month,
            'range' => [
                'start' => $start->toDateString(),
                'end' => $end->toDateString(),
            ],
            'days' => $days,
        ]);
    }

    public function dayAgenda(Request $request): JsonResponse
    {
        $user = $this->requireBusinessOnly($request);
        $date = trim((string) $request->query('date', now()->toDateString()));
        $cardId = trim((string) $request->query('card_id', ''));
        $status = trim((string) $request->query('status', ''));

        if (! preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            throw ValidationException::withMessages([
                'date' => 'Invalid date format. Expected YYYY-MM-DD.',
            ]);
        }

        $start = Carbon::createFromFormat('Y-m-d', $date)->startOfDay();
        $end = $start->copy()->endOfDay();

        $query = Appointment::query()
            ->with(['card:id,name,username', 'product:id,name,duration_minutes'])
            ->orderBy('starts_at');

        $this->applyOwnershipScope($query, $user->id, $user->role);

        if ($cardId !== '') {
            $card = Card::query()->findOrFail($cardId);
            $this->authorizeCard($user->id, $card, $user->role);
            $query->where('card_id', $cardId);
        }

        if ($status !== '' && in_array($status, self::STATUS_OPTIONS, true)) {
            $query->where('status', $status);
        }

        $items = $query
            ->whereBetween('starts_at', [$start, $end])
            ->get()
            ->map(fn (Appointment $appointment): array => $this->serializeAppointment($appointment))
            ->values()
            ->all();

        return $this->ok([
            'date' => $date,
            'items' => $items,
            'total' => count($items),
        ]);
    }

    public function availability(Request $request): JsonResponse
    {
        $user = $this->requireBusinessOnly($request);

        $validated = $request->validate([
            'card_id' => ['required', 'string', Rule::exists('cards', 'id')],
            'date' => ['required', 'date_format:Y-m-d'],
            'product_id' => ['nullable', 'string', Rule::exists('products', 'id')],
            'duration_minutes' => ['nullable', 'integer', 'min:5', 'max:600'],
            'ignore_appointment_id' => ['nullable', 'string', Rule::exists('appointments', 'id')],
        ]);

        $card = Card::query()->findOrFail($validated['card_id']);
        $this->authorizeCard($user->id, $card, $user->role);

        $product = $this->resolveProduct($validated['product_id'] ?? null, $card->user_id);
        $duration = $this->resolveDuration($validated['duration_minutes'] ?? null, $product?->duration_minutes);
        $slots = $this->buildAvailableSlots($card, $validated['date'], $duration, $validated['ignore_appointment_id'] ?? null);

        return $this->ok([
            'date' => $validated['date'],
            'duration_minutes' => $duration,
            'slots' => $slots,
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
            'appointment_date' => ['required', 'date_format:Y-m-d'],
            'appointment_time' => ['required', 'date_format:H:i'],
            'duration_minutes' => ['nullable', 'integer', 'min:5', 'max:600'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'status' => ['nullable', Rule::in(self::STATUS_OPTIONS)],
        ]);

        $card = Card::query()->findOrFail($validated['card_id']);
        $this->authorizeCard($user->id, $card, $user->role);

        $product = $this->resolveProduct($validated['product_id'] ?? null, $card->user_id);
        $duration = $this->resolveDuration($validated['duration_minutes'] ?? null, $product?->duration_minutes);
        [$startsAt, $endsAt] = $this->buildDateRange($validated['appointment_date'], $validated['appointment_time'], $duration);

        $this->assertInsideSchedule($card, $startsAt, $endsAt);
        $this->assertNoOverlap($card->id, $startsAt, $endsAt);

        $appointment = Appointment::query()->create([
            'user_id' => $card->user_id,
            'card_id' => $card->id,
            'product_id' => $product?->id,
            'full_name' => trim($validated['full_name']),
            'phone' => $this->nullableTrim($validated['phone'] ?? null),
            'email' => $this->nullableTrim($validated['email'] ?? null),
            'interest' => $this->resolveInterest($validated['interest'] ?? null, $product?->name),
            'duration_minutes' => $duration,
            'starts_at' => $startsAt,
            'ends_at' => $endsAt,
            'status' => $validated['status'] ?? 'scheduled',
            'notes' => $this->nullableTrim($validated['notes'] ?? null),
            'source' => 'dashboard_api',
        ]);

        return $this->ok([
            'item' => $this->serializeAppointment($appointment->load(['card:id,name,username', 'product:id,name,duration_minutes'])),
        ], 201);
    }

    public function update(Request $request, Appointment $appointment): JsonResponse
    {
        $user = $this->requireBusinessOnly($request);
        $this->ensureAppointmentOwnership($user->id, $appointment, $user->role);

        $validated = $request->validate([
            'card_id' => ['required', 'string', Rule::exists('cards', 'id')],
            'product_id' => ['nullable', 'string', Rule::exists('products', 'id')],
            'full_name' => ['required', 'string', 'max:160'],
            'phone' => ['nullable', 'string', 'max:40'],
            'email' => ['nullable', 'email', 'max:190'],
            'interest' => ['nullable', 'string', 'max:255'],
            'appointment_date' => ['required', 'date_format:Y-m-d'],
            'appointment_time' => ['required', 'date_format:H:i'],
            'duration_minutes' => ['nullable', 'integer', 'min:5', 'max:600'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'status' => ['required', Rule::in(self::STATUS_OPTIONS)],
        ]);

        $card = Card::query()->findOrFail($validated['card_id']);
        $this->authorizeCard($user->id, $card, $user->role);

        $product = $this->resolveProduct($validated['product_id'] ?? null, $card->user_id);
        $duration = $this->resolveDuration($validated['duration_minutes'] ?? null, $product?->duration_minutes);
        [$startsAt, $endsAt] = $this->buildDateRange($validated['appointment_date'], $validated['appointment_time'], $duration);

        $this->assertInsideSchedule($card, $startsAt, $endsAt);
        $this->assertNoOverlap($card->id, $startsAt, $endsAt, $appointment->id);

        $appointment->update([
            'user_id' => $card->user_id,
            'card_id' => $card->id,
            'product_id' => $product?->id,
            'full_name' => trim($validated['full_name']),
            'phone' => $this->nullableTrim($validated['phone'] ?? null),
            'email' => $this->nullableTrim($validated['email'] ?? null),
            'interest' => $this->resolveInterest($validated['interest'] ?? null, $product?->name),
            'duration_minutes' => $duration,
            'starts_at' => $startsAt,
            'ends_at' => $endsAt,
            'status' => $validated['status'],
            'notes' => $this->nullableTrim($validated['notes'] ?? null),
        ]);

        return $this->ok([
            'item' => $this->serializeAppointment($appointment->fresh()->load(['card:id,name,username', 'product:id,name,duration_minutes'])),
        ]);
    }

    public function updateStatus(Request $request, Appointment $appointment): JsonResponse
    {
        $user = $this->requireBusinessOnly($request);
        $this->ensureAppointmentOwnership($user->id, $appointment, $user->role);

        $validated = $request->validate([
            'status' => ['required', Rule::in(self::STATUS_OPTIONS)],
        ]);

        $appointment->update([
            'status' => $validated['status'],
        ]);

        return $this->ok([
            'item' => $this->serializeAppointment($appointment->fresh()->load(['card:id,name,username', 'product:id,name,duration_minutes'])),
        ]);
    }

    public function destroy(Request $request, Appointment $appointment): JsonResponse
    {
        $user = $this->requireBusinessOnly($request);
        $this->ensureAppointmentOwnership($user->id, $appointment, $user->role);
        $appointment->delete();

        return $this->message('Appointment deleted successfully.');
    }

    private function serializeAppointment(Appointment $appointment): array
    {
        return [
            'id' => $appointment->id,
            'card_id' => $appointment->card_id,
            'card_name' => $appointment->card?->name,
            'card_username' => $appointment->card?->username,
            'product_id' => $appointment->product_id,
            'product_name' => $appointment->product?->name,
            'full_name' => $appointment->full_name,
            'phone' => $appointment->phone,
            'email' => $appointment->email,
            'interest' => $appointment->interest,
            'duration_minutes' => (int) $appointment->duration_minutes,
            'starts_at' => optional($appointment->starts_at)->toIso8601String(),
            'ends_at' => optional($appointment->ends_at)->toIso8601String(),
            'date_key' => optional($appointment->starts_at)->format('Y-m-d'),
            'start_time' => optional($appointment->starts_at)->format('H:i'),
            'end_time' => optional($appointment->ends_at)->format('H:i'),
            'status' => $appointment->status,
            'notes' => $appointment->notes,
            'source' => $appointment->source,
            'created_at' => optional($appointment->created_at)->toIso8601String(),
            'updated_at' => optional($appointment->updated_at)->toIso8601String(),
        ];
    }

    private function ensureAppointmentOwnership(int $userId, Appointment $appointment, string $role): void
    {
        if (! $this->isAdmin($role) && $appointment->user_id !== $userId) {
            abort(403);
        }
    }

    private function authorizeCard(int $userId, Card $card, string $role): void
    {
        if (! $this->isAdmin($role) && $card->user_id !== $userId) {
            abort(403);
        }
    }

    private function applyOwnershipScope(Builder $query, int $userId, string $role): void
    {
        if (! $this->isAdmin($role)) {
            $query->where('user_id', $userId);
        }
    }

    private function isAdmin(string $role): bool
    {
        return $role === 'admin';
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

    private function resolveDuration(?int $requestedDuration, ?int $serviceDuration): int
    {
        $duration = $requestedDuration ?: ($serviceDuration ?: 30);
        return max(5, min(600, (int) $duration));
    }

    private function buildDateRange(string $date, string $time, int $duration): array
    {
        $startsAt = Carbon::createFromFormat('Y-m-d H:i', "{$date} {$time}");
        $endsAt = $startsAt->copy()->addMinutes($duration);

        return [$startsAt, $endsAt];
    }

    private function assertInsideSchedule(Card $card, Carbon $startsAt, Carbon $endsAt): void
    {
        $schedule = collect($card->schedule ?? []);
        $dayConfig = $schedule->firstWhere('day', $startsAt->dayOfWeek);

        if (! is_array($dayConfig)) {
            throw ValidationException::withMessages([
                'appointment_date' => 'No availability for the selected day.',
            ]);
        }

        $open = Carbon::createFromFormat('Y-m-d H:i', $startsAt->toDateString() . ' ' . ($dayConfig['open'] ?? '00:00'));
        $close = Carbon::createFromFormat('Y-m-d H:i', $startsAt->toDateString() . ' ' . ($dayConfig['close'] ?? '00:00'));

        if ($startsAt->lt($open) || $endsAt->gt($close) || $endsAt->lte($startsAt)) {
            throw ValidationException::withMessages([
                'appointment_time' => 'Selected time is outside the configured schedule.',
            ]);
        }
    }

    private function assertNoOverlap(string $cardId, Carbon $startsAt, Carbon $endsAt, ?string $ignoreId = null): void
    {
        $query = Appointment::query()
            ->where('card_id', $cardId)
            ->where('status', '!=', 'cancelled')
            ->where('starts_at', '<', $endsAt)
            ->where('ends_at', '>', $startsAt);

        if ($ignoreId) {
            $query->where('id', '!=', $ignoreId);
        }

        if ($query->exists()) {
            throw ValidationException::withMessages([
                'appointment_time' => 'This time slot is already booked.',
            ]);
        }
    }

    private function buildAvailableSlots(Card $card, string $date, int $duration, ?string $ignoreAppointmentId = null): array
    {
        $target = Carbon::createFromFormat('Y-m-d', $date);
        $schedule = collect($card->schedule ?? []);
        $dayConfig = $schedule->firstWhere('day', $target->dayOfWeek);

        if (! is_array($dayConfig)) {
            return [];
        }

        $open = Carbon::createFromFormat('Y-m-d H:i', $date . ' ' . ($dayConfig['open'] ?? '00:00'));
        $close = Carbon::createFromFormat('Y-m-d H:i', $date . ' ' . ($dayConfig['close'] ?? '00:00'));

        if ($close->lte($open)) {
            return [];
        }

        $slots = [];
        $cursor = $open->copy();
        while ($cursor->copy()->addMinutes($duration)->lte($close)) {
            $start = $cursor->copy();
            $end = $cursor->copy()->addMinutes($duration);

            $conflictQuery = Appointment::query()
                ->where('card_id', $card->id)
                ->where('status', '!=', 'cancelled')
                ->where('starts_at', '<', $end)
                ->where('ends_at', '>', $start);

            if ($ignoreAppointmentId) {
                $conflictQuery->where('id', '!=', $ignoreAppointmentId);
            }

            $slots[] = [
                'time' => $start->format('H:i'),
                'available' => ! $conflictQuery->exists(),
            ];

            $cursor->addMinutes(15);
        }

        return $slots;
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
}
