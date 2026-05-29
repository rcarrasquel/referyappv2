<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Http;

class CalComBookingService
{
    private const BASE_URL = 'https://api.cal.com';
    private const BOOKING_API_VERSION = '2026-02-25';
    private const SLOT_API_VERSION = '2024-09-04';

    /**
     * Check slots for a given event type and date.
     */
    public function CheckAvailabilityCal(
        string $apiKey,
        string $eventTypeId,
        string $date,
        string $timezone = 'UTC'
    ): array {
        $apiKey = trim($apiKey);
        $eventTypeId = trim($eventTypeId);

        if ($apiKey === '' || $eventTypeId === '' || $date === '') {
            return $this->error('Missing required parameters for availability check.');
        }

        $start = Carbon::parse($date, $timezone)->startOfDay()->utc()->toIso8601String();
        $end = Carbon::parse($date, $timezone)->endOfDay()->utc()->toIso8601String();

        $basePayload = [
            'eventTypeId' => $eventTypeId,
            'start' => $start,
            'end' => $end,
            'timeZone' => $timezone,
        ];

        $attempts = [
            ['GET', '/v2/slots', $basePayload, ['cal-api-version' => self::SLOT_API_VERSION]],
            ['GET', '/v2/slots', $basePayload, ['cal-api-version' => self::BOOKING_API_VERSION]],
            ['GET', '/v2/availability', $basePayload, ['cal-api-version' => self::SLOT_API_VERSION]],
            ['GET', '/v2/event-types/' . $eventTypeId . '/slots', [
                'start' => $start,
                'end' => $end,
                'timeZone' => $timezone,
            ], ['cal-api-version' => self::SLOT_API_VERSION]],
            ['GET', '/v2/event-types/' . $eventTypeId . '/availability', [
                'start' => $start,
                'end' => $end,
                'timeZone' => $timezone,
            ], ['cal-api-version' => self::SLOT_API_VERSION]],
        ];

        return $this->tryEndpoints($apiKey, $attempts, 'Availability retrieved successfully.');
    }

    /**
     * Create a new booking.
     */
    public function BookAppointmentCal(
        string $apiKey,
        string $eventTypeId,
        string $startIso,
        string $name,
        string $email,
        ?string $timezone = null,
        ?string $phone = null,
        ?string $notes = null
    ): array {
        $apiKey = trim($apiKey);
        $eventTypeId = trim($eventTypeId);

        if ($apiKey === '' || $eventTypeId === '' || trim($startIso) === '' || trim($name) === '' || trim($email) === '') {
            return $this->error('Missing required parameters for booking.');
        }

        $payload = [
            'eventTypeId' => (int) $eventTypeId,
            'start' => $startIso,
            'attendee' => [
                'name' => trim($name),
                'email' => trim($email),
                'timeZone' => $timezone ?: 'UTC',
            ],
        ];

        $metadata = [];
        $phoneValue = trim((string) ($phone ?? ''));
        $notesValue = trim((string) ($notes ?? ''));

        if ($phoneValue !== '') {
            $metadata['phone'] = mb_substr($phoneValue, 0, 500);
        }

        if ($notesValue !== '') {
            $metadata['notes'] = mb_substr($notesValue, 0, 500);
        }

        if ($metadata !== []) {
            $payload['metadata'] = $metadata;
        }

        $attempts = [
            ['POST', '/v2/bookings', $payload, ['cal-api-version' => self::BOOKING_API_VERSION]],
        ];

        return $this->tryEndpoints($apiKey, $attempts, 'Appointment booked successfully.');
    }

    /**
     * Cancel an existing booking.
     */
    public function CancelBookingCal(
        string $apiKey,
        string $bookingId,
        ?string $reason = null
    ): array {
        $apiKey = trim($apiKey);
        $bookingId = trim($bookingId);

        if ($apiKey === '' || $bookingId === '') {
            return $this->error('Missing required parameters for cancellation.');
        }

        $payload = [
            'cancellationReason' => $reason ? trim($reason) : null,
            'cancelSubsequentBookings' => false,
        ];

        $attempts = [
            ['POST', '/v2/bookings/' . $bookingId . '/cancel', $payload, ['cal-api-version' => self::BOOKING_API_VERSION]],
        ];

        return $this->tryEndpoints($apiKey, $attempts, 'Booking cancelled successfully.');
    }

    /**
     * Reschedule an existing booking.
     */
    public function RescheduleBookingCal(
        string $apiKey,
        string $bookingId,
        string $newStartIso,
        ?string $newEndIso = null,
        ?string $reason = null
    ): array {
        $apiKey = trim($apiKey);
        $bookingId = trim($bookingId);

        if ($apiKey === '' || $bookingId === '' || trim($newStartIso) === '') {
            return $this->error('Missing required parameters for reschedule.');
        }

        $payload = [
            'start' => $newStartIso,
            'reschedulingReason' => $reason ? trim($reason) : null,
        ];

        $attempts = [
            ['POST', '/v2/bookings/' . $bookingId . '/reschedule', $payload, ['cal-api-version' => self::BOOKING_API_VERSION]],
        ];

        return $this->tryEndpoints($apiKey, $attempts, 'Booking rescheduled successfully.');
    }

    private function tryEndpoints(string $apiKey, array $attempts, string $successMessage): array
    {
        $lastError = null;

        foreach ($attempts as [$method, $path, $payload, $headers]) {
            $response = $this->sendRequest($apiKey, $method, $path, (array) $payload, (array) $headers);

            if (($response['ok'] ?? false) === true) {
                return [
                    'ok' => true,
                    'message' => $successMessage,
                    'status' => $response['status'] ?? 200,
                    'data' => $response['data'] ?? [],
                    'raw' => $response['raw'] ?? [],
                ];
            }

            if (in_array((int) ($response['status'] ?? 0), [401, 403], true)) {
                return $response;
            }

            $lastError = $response;
        }

        return $lastError ?: $this->error('Unable to process request with Cal.com.');
    }

    private function sendRequest(string $apiKey, string $method, string $path, array $payload = [], array $headers = []): array
    {
        try {
            $request = Http::acceptJson()
                ->withToken($apiKey)
                ->timeout(25)
                ->withHeaders($headers);

            $url = self::BASE_URL . $path;
            $method = strtoupper($method);

            if ($method === 'GET') {
                $http = $request->get($url, $payload);
            } elseif ($method === 'POST') {
                $http = $request->post($url, $payload);
            } elseif ($method === 'PATCH') {
                $http = $request->patch($url, $payload);
            } elseif ($method === 'DELETE') {
                $http = $request->delete($url, $payload);
            } else {
                return $this->error('Unsupported HTTP method.', 422);
            }

            $json = $http->json();
            $data = is_array($json['data'] ?? null) ? $json['data'] : (is_array($json) ? $json : []);

            if ($http->successful()) {
                return [
                    'ok' => true,
                    'status' => $http->status(),
                    'data' => $data,
                    'raw' => is_array($json) ? $json : [],
                ];
            }

            $message = (string) ($json['message'] ?? $json['error']['message'] ?? 'Cal.com request failed.');

            return $this->error($message, $http->status(), is_array($json) ? $json : []);
        } catch (\Throwable $e) {
            return $this->error($e->getMessage(), 500);
        }
    }

    private function error(string $message, int $status = 422, array $raw = []): array
    {
        return [
            'ok' => false,
            'message' => $message,
            'status' => $status,
            'data' => [],
            'raw' => $raw,
        ];
    }
}
