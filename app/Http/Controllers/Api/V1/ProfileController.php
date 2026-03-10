<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;

class ProfileController extends BaseApiController
{
    public function show(Request $request): JsonResponse
    {
        $user = $this->requireBusinessUser($request);

        return $this->ok([
            'user' => $this->serializeUser($user),
        ]);
    }

    public function update(Request $request): JsonResponse
    {
        $user = $this->requireBusinessUser($request);

        if ($user->role !== 'admin' && $request->has('email')) {
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
        ];

        if ($user->role === 'admin') {
            $rules['email'] = [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user->id),
            ];
        }

        $validated = $request->validate($rules);

        $user->name = $validated['name'];
        $user->language = $validated['language'];

        if ($user->role === 'admin') {
            $user->email = $validated['email'];
        }

        if (! empty($validated['password'])) {
            $user->password = $validated['password'];
        }

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
            'created_at' => optional($user->created_at)->toIso8601String(),
            'updated_at' => optional($user->updated_at)->toIso8601String(),
        ];
    }
}
