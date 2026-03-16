<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends BaseApiController
{
    public function login(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email', 'max:190'],
            'password' => ['required', 'string', 'min:6'],
            'device_name' => ['nullable', 'string', 'max:120'],
        ]);

        $user = User::query()->where('email', $validated['email'])->first();
        if (! $user || ! Hash::check($validated['password'], $user->password)) {
            return response()->json([
                'message' => 'Invalid credentials.',
                'errors' => ['email' => ['Invalid credentials.']],
            ], 422);
        }

        if (! in_array($user->role, ['business', 'admin'], true)) {
            return response()->json([
                'message' => 'Only business or admin users can access the mobile API.',
            ], 403);
        }

        $token = $user->createToken($validated['device_name'] ?: 'mobile-app')->plainTextToken;

        return $this->ok([
            'token' => $token,
            'token_type' => 'Bearer',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'plan' => $user->plan,
                'language' => $user->language,
            ],
        ]);
    }

    public function me(Request $request): JsonResponse
    {
        $user = $this->requireBusinessUser($request);

        return $this->ok([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'plan' => $user->plan,
                'language' => $user->language,
                'created_at' => optional($user->created_at)->toIso8601String(),
            ],
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $this->requireBusinessUser($request);
        $request->user()?->currentAccessToken()?->delete();

        return $this->message('Logged out successfully.');
    }

    public function logoutAll(Request $request): JsonResponse
    {
        $user = $this->requireBusinessUser($request);
        $user->tokens()->delete();

        return $this->message('All sessions closed successfully.');
    }
}
