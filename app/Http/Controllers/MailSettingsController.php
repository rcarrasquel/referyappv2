<?php

namespace App\Http\Controllers;

use App\Mail\MailConfigTestMail;
use App\Models\MailSetting;
use App\Services\MailRuntimeConfigService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;
use Inertia\Response;

class MailSettingsController extends Controller
{
    public function index(): Response
    {
        $settings = MailSetting::query()->latest('id')->first();

        return Inertia::render('Modules/MailSettings', [
            'settings' => [
                'host' => (string) ($settings?->host ?? ''),
                'port' => (int) ($settings?->port ?? 465),
                'encryption' => (string) ($settings?->encryption ?? 'ssl'),
                'username' => (string) ($settings?->username ?? ''),
                'password' => (string) ($settings?->password ?? ''),
                'timeout' => (int) ($settings?->timeout ?? 30),
                'from_address' => (string) ($settings?->from_address ?? ''),
                'from_name' => (string) ($settings?->from_name ?? ''),
                'is_active' => (bool) ($settings?->is_active ?? true),
            ],
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'host' => ['required', 'string', 'max:255'],
            'port' => ['required', 'integer', 'min:1', 'max:65535'],
            'encryption' => ['nullable', 'string', 'max:20'],
            'username' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string', 'max:255'],
            'timeout' => ['required', 'integer', 'min:1', 'max:120'],
            'from_address' => ['required', 'email', 'max:255'],
            'from_name' => ['required', 'string', 'max:255'],
            'is_active' => ['required', 'boolean'],
        ]);

        $settings = MailSetting::query()->latest('id')->first();
        if (! $settings) {
            $settings = new MailSetting();
        }

        $settings->fill([
            'host' => trim((string) $validated['host']),
            'port' => (int) $validated['port'],
            'encryption' => trim((string) ($validated['encryption'] ?? '')),
            'username' => trim((string) $validated['username']),
            'password' => trim((string) $validated['password']),
            'timeout' => (int) $validated['timeout'],
            'from_address' => trim((string) $validated['from_address']),
            'from_name' => trim((string) $validated['from_name']),
            'is_active' => (bool) $validated['is_active'],
        ]);
        $settings->save();

        return back()->with('status', 'Mail settings updated successfully.');
    }

    public function test(Request $request, MailRuntimeConfigService $mailRuntimeConfigService): RedirectResponse
    {
        $validated = $request->validate([
            'test_email' => ['nullable', 'email', 'max:255'],
        ]);

        $recipient = trim((string) ($validated['test_email'] ?? ''));
        if ($recipient === '') {
            $recipient = (string) ($request->user()?->email ?? '');
        }

        if ($recipient === '') {
            return back()->withErrors([
                'test_email' => 'A test email address is required.',
            ]);
        }

        $mailRuntimeConfigService->apply();
        Mail::to($recipient)->send(
            new MailConfigTestMail(
                recipientName: (string) ($request->user()?->name ?? 'Admin')
            )
        );

        return back()->with('status', 'Test email sent successfully.');
    }
}

