<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    public function index(Request $request): Response
    {
        return Inertia::render('Profile/Index', [
            'user' => $request->user(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();

        if ($user->role !== 'admin' && $request->has('email')) {
            throw ValidationException::withMessages([
                'email' => 'Email updates are not allowed for this account.',
            ]);
        }

        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'language' => ['required', 'in:en,es'],
            'current_password' => ['nullable', 'required_with:password', 'current_password'],
            'password' => ['nullable', 'confirmed', Rules\Password::defaults()],
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

        return back()->with('status', 'Profile updated successfully.');
    }
}
