<?php

namespace App\Http\Controllers\Api\V1;

use App\Services\CalComService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;

class ProfileController extends BaseApiController
{
    public function show(Request $request): JsonResponse
    {
        $user = $this->requireBusinessOnly($request);

        return $this->ok([
            'user' => $this->serializeUser($user),
        ]);
    }

    public function update(Request $request): JsonResponse
    {
        $user = $this->requireBusinessOnly($request);

        if ($request->has('email')) {
            throw ValidationException::withMessages([
                'email' => 'Email updates are not allowed for this account.',
            ]);
        }

        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'language' => ['required', Rule::in(['en', 'es'])],
            'current_password' => ['nullable', 'required_with:password', 'current_password'],
            'password' => ['nullable', 'confirmed', Rules\Password::defaults()],
            'password_confirmation' => ['nullable', 'string'],
            'cal_api_key' => ['nullable', 'string', 'max:255'],
            'cal_username' => ['nullable', 'string', 'max:120'],
            'cal_event_type_id' => ['nullable', 'string', 'max:190'],
            'cal_sync_enabled' => ['nullable', 'boolean'],
            'remove_cal_api_key' => ['nullable', 'boolean'],
        ];

        $validated = $request->validate($rules);

        $user->name = $validated['name'];
        $user->language = $validated['language'];

        if (! empty($validated['password'])) {
            $user->password = $validated['password'];
        }

        $removeCalApiKey = (bool) ($validated['remove_cal_api_key'] ?? false);
        if ($removeCalApiKey) {
            $user->cal_api_key = null;
            $user->cal_username = null;
            $user->cal_connected_at = null;
        } elseif (array_key_exists('cal_api_key', $validated) && trim((string) $validated['cal_api_key']) !== '') {
            $user->cal_api_key = trim((string) $validated['cal_api_key']);
        }

        $user->cal_username = trim((string) ($validated['cal_username'] ?? $user->cal_username ?? '')) ?: null;
        $eventTypeId = trim((string) ($validated['cal_event_type_id'] ?? ''));
        $user->cal_event_type_id = $eventTypeId !== '' ? $eventTypeId : null;
        $user->cal_sync_enabled = (bool) ($validated['cal_sync_enabled'] ?? false);

        $user->save();

        return $this->ok([
            'message' => 'Profile updated successfully.',
            'user' => $this->serializeUser($user),
        ]);
    }

    private function serializeUser($user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
            'plan' => $user->plan,
            'language' => $user->language,
            'cal' => [
                'has_api_key' => (bool) $user->cal_api_key,
                'api_key' => (string) ($user->cal_api_key ?? ''),
                'username' => (string) ($user->cal_username ?? ''),
                'event_type_id' => (string) ($user->cal_event_type_id ?? ''),
                'event_type_slug' => (string) ($user->cal_event_type_id ?? ''),
                'connected_at' => optional($user->cal_connected_at)->toIso8601String(),
                'sync_enabled' => (bool) $user->cal_sync_enabled,
            ],
            'created_at' => optional($user->created_at)->toIso8601String(),
            'updated_at' => optional($user->updated_at)->toIso8601String(),
        ];
    }

    public function testCalConnection(Request $request, CalComService $calCom): JsonResponse
    {
        $user = $this->requireBusinessOnly($request);

        $validated = $request->validate([
            'cal_api_key' => ['nullable', 'string', 'max:255'],
        ]);

        $apiKey = trim((string) ($validated['cal_api_key'] ?? ''));
        if ($apiKey === '') {
            $apiKey = (string) ($user->cal_api_key ?? '');
        }

        $result = $calCom->testConnection($apiKey);

        if (($result['ok'] ?? false) === true) {
            if ($apiKey !== '') {
                $user->cal_api_key = $apiKey;
            }

            $apiUsername = trim((string) ($result['username'] ?? ''));
            if ($apiUsername !== '') {
                $user->cal_username = $apiUsername;
            }

            $user->cal_connected_at = now();
            $user->save();
        }

        if (($result['ok'] ?? false) !== true) {
            return $this->validationError([
                'cal_api_key' => [(string) ($result['message'] ?? 'Unable to connect to Cal.com.')],
            ]);
        }

        return $this->ok([
            'message' => (string) ($result['message'] ?? 'Cal.com connection successful.'),
            'cal' => [
                'has_api_key' => true,
                'api_key' => (string) ($user->cal_api_key ?? ''),
                'username' => (string) ($user->cal_username ?? ''),
                'event_type_id' => (string) ($user->cal_event_type_id ?? ''),
                'event_type_slug' => (string) ($user->cal_event_type_id ?? ''),
                'connected_at' => optional($user->cal_connected_at)->toIso8601String(),
                'sync_enabled' => (bool) $user->cal_sync_enabled,
            ],
        ]);
    }
}
