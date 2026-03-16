<?php

namespace App\Http\Controllers;

use App\Mail\PublicCardRequestNotificationMail;
use App\Models\Appointment;
use App\Models\Card;
use App\Models\Lead;
use App\Models\Product;
use App\Models\User;
use App\Services\MailRuntimeConfigService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class AppointmentController extends Controller
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

    public function index(Request $request): Response
    {
        $user = $request->user();
        $query = Appointment::query()
            ->with(['card:id,name,username', 'product:id,name,duration_minutes'])
            ->latest('starts_at');

        if ($user->role !== 'admin') {
            $query->where('user_id', $user->id);
        }

        $cardId = (string) $request->query('card_id', '');
        $search = trim((string) $request->query('search', ''));
        $month = (string) $request->query('month', now()->format('Y-m'));

        if ($cardId !== '') {
            $query->where('card_id', $cardId);
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
            $monthStart = Carbon::createFromFormat('Y-m', $month)->startOfMonth();
            $monthEnd = $monthStart->copy()->endOfMonth();
            $query->whereBetween('starts_at', [$monthStart, $monthEnd]);
        }

        $appointments = $query
            ->get([
                'id',
                'user_id',
                'card_id',
                'product_id',
                'full_name',
                'phone',
                'email',
                'interest',
                'duration_minutes',
                'starts_at',
                'ends_at',
                'status',
                'notes',
                'source',
                'created_at',
            ])
            ->map(fn (Appointment $appointment): array => [
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
                'starts_at_label' => optional($appointment->starts_at)->format('Y-m-d H:i'),
                'status' => $appointment->status,
                'notes' => $appointment->notes,
                'source' => $appointment->source,
                'created_at' => optional($appointment->created_at)->toIso8601String(),
            ])
            ->values()
            ->all();

        $cardsQuery = Card::query()->orderBy('name');
        $productsQuery = Product::query()->orderBy('name');

        if ($user->role !== 'admin') {
            $cardsQuery->where('user_id', $user->id);
            $productsQuery->where('user_id', $user->id);
        }

        return Inertia::render('Modules/Appointments', [
            'appointments' => $appointments,
            'cards' => $cardsQuery->get(['id', 'name', 'username', 'schedule']),
            'services' => $productsQuery->get(['id', 'name', 'duration_minutes', 'price']),
            'filters' => [
                'card_id' => $cardId ?: null,
                'search' => $search,
                'month' => $month,
            ],
            'statusOptions' => self::STATUS_OPTIONS,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();

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
        $this->authorizeCard($user, $card);

        $product = $this->resolveProduct($validated['product_id'] ?? null, $card->user_id);
        $duration = $this->resolveDuration($validated['duration_minutes'] ?? null, $product?->duration_minutes);
        [$startsAt, $endsAt] = $this->buildDateRange($validated['appointment_date'], $validated['appointment_time'], $duration);

        $this->assertInsideSchedule($card, $startsAt, $endsAt);
        $this->assertNoOverlap($card->id, $startsAt, $endsAt);

        Appointment::query()->create([
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
            'source' => 'dashboard',
        ]);

        return back()->with('status', 'Appointment created successfully.');
    }

    public function updateStatus(Request $request, Appointment $appointment): RedirectResponse
    {
        $user = $request->user();
        if ($user->role !== 'admin' && $appointment->user_id !== $user->id) {
            abort(403);
        }

        $validated = $request->validate([
            'status' => ['required', Rule::in(self::STATUS_OPTIONS)],
        ]);

        $appointment->update([
            'status' => $validated['status'],
        ]);

        return back()->with('status', 'Appointment status updated.');
    }

    public function update(Request $request, Appointment $appointment): RedirectResponse
    {
        $user = $request->user();
        if ($user->role !== 'admin' && $appointment->user_id !== $user->id) {
            abort(403);
        }

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
        $this->authorizeCard($user, $card);

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

        return back()->with('status', 'Appointment updated successfully.');
    }

    public function destroy(Request $request, Appointment $appointment): RedirectResponse
    {
        $user = $request->user();
        if ($user->role !== 'admin' && $appointment->user_id !== $user->id) {
            abort(403);
        }

        $appointment->delete();

        return back()->with('status', 'Appointment deleted successfully.');
    }

    public function availability(string $username, Request $request): JsonResponse
    {
        $card = Card::query()
            ->where('username', $username)
            ->where('is_active', true)
            ->firstOrFail();

        $validated = $request->validate([
            'date' => ['required', 'date_format:Y-m-d'],
            'service_id' => ['nullable', 'string', Rule::exists('products', 'id')],
            'duration_minutes' => ['nullable', 'integer', 'min:5', 'max:600'],
        ]);

        $product = $this->resolveProduct($validated['service_id'] ?? null, $card->user_id);
        $duration = $this->resolveDuration($validated['duration_minutes'] ?? null, $product?->duration_minutes);
        $slots = $this->buildAvailableSlots($card, $validated['date'], $duration);

        return response()->json([
            'slots' => $slots,
            'duration_minutes' => $duration,
        ]);
    }

    public function storePublic(string $username, Request $request): RedirectResponse
    {
        $card = Card::query()
            ->where('username', $username)
            ->where('is_active', true)
            ->firstOrFail();

        $validated = $request->validate([
            'request_type' => ['required', Rule::in(['contact', 'appointment'])],
            'product_id' => ['required', 'string', Rule::exists('products', 'id')],
            'full_name' => ['required', 'string', 'max:160'],
            'phone' => ['nullable', 'string', 'max:40'],
            'email' => ['nullable', 'email', 'max:190'],
            'interest' => ['nullable', 'string', 'max:255'],
            'appointment_date' => ['required_if:request_type,appointment', 'nullable', 'date_format:Y-m-d'],
            'appointment_time' => ['required_if:request_type,appointment', 'nullable', 'date_format:H:i'],
            'duration_minutes' => ['nullable', 'integer', 'min:5', 'max:600'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $product = $this->resolveProduct($validated['product_id'] ?? null, $card->user_id);
        if (! $product) {
            throw ValidationException::withMessages([
                'product_id' => 'Selected service is not available for this card.',
            ]);
        }

        if (($validated['request_type'] ?? 'appointment') === 'contact') {
            $lead = Lead::query()->create([
                'user_id' => $card->user_id,
                'card_id' => $card->id,
                'product_id' => $product->id,
                'full_name' => trim($validated['full_name']),
                'phone' => $this->nullableTrim($validated['phone'] ?? null),
                'email' => $this->nullableTrim($validated['email'] ?? null),
                'interest' => $this->resolveInterest($validated['interest'] ?? null, $product->name),
                'notes' => $this->nullableTrim($validated['notes'] ?? null),
                'status' => 'new',
                'source' => 'public',
            ]);

            $this->sendPublicRequestEmail(
                $card,
                $product,
                'contact',
                $lead,
                null
            );

            return back()->with('status', 'Contact request sent successfully.');
        }

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
            'status' => 'scheduled',
            'notes' => $this->nullableTrim($validated['notes'] ?? null),
            'source' => 'public',
        ]);

        $this->sendPublicRequestEmail(
            $card,
            $product,
            'appointment',
            null,
            $appointment
        );

        return back()->with('status', 'Appointment request sent successfully.');
    }

    private function sendPublicRequestEmail(
        Card $card,
        Product $product,
        string $requestType,
        ?Lead $lead,
        ?Appointment $appointment
    ): void {
        $owner = User::query()->find($card->user_id);
        if (! $owner || ! $owner->email) {
            return;
        }

        $language = in_array($owner->language, ['es', 'en'], true) ? $owner->language : 'en';

        try {
            app(MailRuntimeConfigService::class)->apply();

            Mail::to($owner->email)->send(
                new PublicCardRequestNotificationMail(
                    card: $card,
                    product: $product,
                    requestType: $requestType,
                    ownerLanguage: $language,
                    lead: $lead,
                    appointment: $appointment,
                )
            );

            Log::info('Public card request email sent', [
                'type' => $requestType,
                'owner_email' => $owner->email,
                'card_id' => $card->id,
                'card_username' => $card->username,
                'lead_id' => $lead?->id,
                'appointment_id' => $appointment?->id,
            ]);
        } catch (\Throwable $exception) {
            Log::error('Public card request email failed', [
                'type' => $requestType,
                'owner_email' => $owner->email,
                'card_id' => $card->id,
                'card_username' => $card->username,
                'lead_id' => $lead?->id,
                'appointment_id' => $appointment?->id,
                'error' => $exception->getMessage(),
            ]);
            report($exception);
        }
    }

    private function authorizeCard($user, Card $card): void
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

        $hasConflict = $query->exists();

        if ($hasConflict) {
            throw ValidationException::withMessages([
                'appointment_time' => 'This time slot is already booked.',
            ]);
        }
    }

    private function buildAvailableSlots(Card $card, string $date, int $duration): array
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

            $conflict = Appointment::query()
                ->where('card_id', $card->id)
                ->where('status', '!=', 'cancelled')
                ->where('starts_at', '<', $end)
                ->where('ends_at', '>', $start)
                ->exists();

            $slots[] = [
                'time' => $start->format('H:i'),
                'available' => ! $conflict,
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
