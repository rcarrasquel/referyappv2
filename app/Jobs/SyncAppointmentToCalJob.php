<?php

namespace App\Jobs;

use App\Models\Appointment;
use App\Models\User;
use App\Services\CalComBookingService;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class SyncAppointmentToCalJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;
    public int $timeout = 90;

    public function __construct(
        public string $appointmentId,
        public string $action = 'create',
        public ?int $ownerId = null,
        public ?string $calBookingId = null,
    ) {
    }

    public function handle(CalComBookingService $calService): void
    {
        $appointment = Appointment::query()->find($this->appointmentId);
        if (! $appointment && $this->action === 'cancel' && $this->ownerId && $this->calBookingId) {
            $owner = User::query()->find($this->ownerId);
            if (! $this->syncEnabledForUser($owner)) {
                return;
            }

            $result = $calService->CancelBookingCal(
                (string) $owner->cal_api_key,
                (string) $this->calBookingId,
                'Cancelled from ReferyApp'
            );

            if (($result['ok'] ?? false) !== true) {
                $message = (string) ($result['message'] ?? 'Cal.com cancel sync failed.');
                $status = (int) ($result['status'] ?? 0);

                Log::warning('Cal.com cancel sync failed after local delete', [
                    'appointment_id' => $this->appointmentId,
                    'owner_id' => $this->ownerId,
                    'status' => $status,
                    'result' => $result,
                ]);

                if ($this->isRetryableFailure($status, $message)) {
                    throw new \RuntimeException($message);
                }
            }

            return;
        }

        if (! $appointment) {
            return;
        }

        $owner = User::query()->find($appointment->user_id);
        if (! $this->syncEnabledForUser($owner)) {
            $appointment->update([
                'cal_sync_status' => null,
                'cal_sync_error' => null,
                'cal_synced_at' => null,
            ]);
            return;
        }

        $appointment->update([
            'cal_sync_status' => 'pending',
            'cal_sync_error' => null,
        ]);

        $result = match ($this->action) {
            'cancel' => $this->cancel($calService, $owner, $appointment),
            'reschedule' => $this->reschedule($calService, $owner, $appointment),
            default => $this->create($calService, $owner, $appointment),
        };

        if (($result['ok'] ?? false) !== true) {
            $message = (string) ($result['message'] ?? 'Cal.com sync failed.');
            $status = (int) ($result['status'] ?? 0);

            $appointment->update([
                'cal_sync_status' => 'failed',
                'cal_sync_error' => mb_substr($message, 0, 1900),
                'cal_synced_at' => null,
            ]);

            Log::warning('Cal.com job sync failed', [
                'appointment_id' => $appointment->id,
                'action' => $this->action,
                'status' => $status,
                'result' => $result,
            ]);

            if ($this->isRetryableFailure($status, $message)) {
                throw new \RuntimeException($message);
            }

            return;
        }

        $bookingId = $this->extractCalBookingId((array) ($result['data'] ?? []));

        $updates = [
            'cal_sync_status' => 'synced',
            'cal_sync_error' => null,
            'cal_synced_at' => now(),
        ];

        if ($bookingId !== '') {
            $updates['cal_booking_id'] = $bookingId;
        }

        $appointment->update($updates);
    }

    private function create(CalComBookingService $calService, User $owner, Appointment $appointment): array
    {
        $ownerTimezone = $this->ownerTimezone($owner);
        $startForCal = $this->toCalStart($appointment->starts_at, $ownerTimezone);

        return $calService->BookAppointmentCal(
            (string) $owner->cal_api_key,
            (string) $owner->cal_event_type_id,
            $startForCal,
            (string) $appointment->full_name,
            (string) ($appointment->email ?: $owner->email),
            $ownerTimezone,
            (string) ($appointment->phone ?? ''),
            (string) ($appointment->notes ?? ''),
        );
    }

    private function reschedule(CalComBookingService $calService, User $owner, Appointment $appointment): array
    {
        if (! $appointment->cal_booking_id) {
            return $this->create($calService, $owner, $appointment);
        }

        $ownerTimezone = $this->ownerTimezone($owner);
        $startForCal = $this->toCalStart($appointment->starts_at, $ownerTimezone);
        $endForCal = $appointment->ends_at instanceof Carbon
            ? Carbon::parse($appointment->ends_at->format('Y-m-d H:i'), $ownerTimezone)->utc()->format('Y-m-d\TH:i:s\Z')
            : null;

        return $calService->RescheduleBookingCal(
            (string) $owner->cal_api_key,
            (string) $appointment->cal_booking_id,
            $startForCal,
            $endForCal,
            'Rescheduled from ReferyApp'
        );
    }

    private function cancel(CalComBookingService $calService, User $owner, Appointment $appointment): array
    {
        if (! $appointment->cal_booking_id) {
            return ['ok' => true, 'data' => []];
        }

        return $calService->CancelBookingCal(
            (string) $owner->cal_api_key,
            (string) $appointment->cal_booking_id,
            'Cancelled from ReferyApp'
        );
    }

    private function toCalStart(?Carbon $startsAt, string $ownerTimezone): string
    {
        if (! $startsAt instanceof Carbon) {
            return '';
        }

        return Carbon::parse($startsAt->format('Y-m-d H:i'), $ownerTimezone)
            ->utc()
            ->format('Y-m-d\TH:i:s\Z');
    }

    private function syncEnabledForUser(?User $owner): bool
    {
        return $owner
            && (bool) $owner->cal_sync_enabled
            && trim((string) $owner->cal_api_key) !== ''
            && trim((string) $owner->cal_event_type_id) !== '';
    }

    private function ownerTimezone(?User $owner): string
    {
        $tz = trim((string) ($owner?->timezone ?? ''));
        return $tz !== '' ? $tz : (string) config('app.timezone', 'UTC');
    }

    private function extractCalBookingId(array $data): string
    {
        $id = (string) ($data['uid'] ?? $data['bookingUid'] ?? $data['id'] ?? $data['bookingId'] ?? '');
        if ($id !== '') {
            return $id;
        }

        $nested = $data['booking'] ?? null;
        if (is_array($nested)) {
            return (string) ($nested['uid'] ?? $nested['bookingUid'] ?? $nested['id'] ?? $nested['bookingId'] ?? '');
        }

        return '';
    }

    private function isRetryableFailure(int $status, string $message): bool
    {
        if ($status >= 500 || $status === 429) {
            return true;
        }

        $normalized = mb_strtolower($message);
        return str_contains($normalized, 'timeout')
            || str_contains($normalized, 'timed out')
            || str_contains($normalized, 'temporar')
            || str_contains($normalized, 'connection');
    }
}
