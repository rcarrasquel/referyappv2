<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class CalComService
{
    private const BASE_URL = 'https://api.cal.com';

    public function testConnection(string $apiKey): array
    {
        $apiKey = trim($apiKey);
        if ($apiKey === '') {
            return [
                'ok' => false,
                'message' => 'API key is required.',
            ];
        }

        $client = Http::acceptJson()
            ->withToken($apiKey)
            ->timeout(20);

        $attempts = [
            ['/v2/me', ['cal-api-version' => '2024-08-13']],
            ['/v1/me', []],
        ];

        foreach ($attempts as [$path, $headers]) {
            $response = $client->withHeaders($headers)->get(self::BASE_URL . $path);

            if ($response->successful()) {
                $payload = $response->json();
                $data = is_array($payload['data'] ?? null) ? $payload['data'] : (is_array($payload) ? $payload : []);

                return [
                    'ok' => true,
                    'message' => 'Cal.com connection successful.',
                    'username' => (string) ($data['username'] ?? ''),
                    'name' => (string) ($data['name'] ?? ''),
                    'email' => (string) ($data['email'] ?? ''),
                ];
            }

            if (in_array($response->status(), [401, 403], true)) {
                return [
                    'ok' => false,
                    'message' => 'Cal.com rejected the API key.',
                    'status' => $response->status(),
                ];
            }
        }

        return [
            'ok' => false,
            'message' => 'Unable to connect to Cal.com with the provided API key.',
        ];
    }
}
