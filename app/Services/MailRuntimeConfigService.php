<?php

namespace App\Services;

class MailRuntimeConfigService
{
    /**
     * Apply manual SMTP configuration for transactional emails.
     */
    public function apply(): void
    {
        $settings = $this->manualSettings();
        $username = (string) ($settings['username'] ?? '');
        $password = (string) ($settings['password'] ?? '');
        $host = (string) ($settings['host'] ?? '');
        $port = (int) ($settings['port'] ?? 587);
        $encryption = (string) ($settings['encryption'] ?? 'tls');
        $timeout = (int) ($settings['timeout'] ?? 30);
        $fromAddress = (string) ($settings['from_address'] ?? '');
        $fromName = (string) ($settings['from_name'] ?? 'ReferyApp');

        if ($host === '') {
            return;
        }

        config([
            'mail.default' => 'smtp',
            'mail.mailers.smtp.transport' => 'smtp',
            'mail.mailers.smtp.host' => $host,
            'mail.mailers.smtp.port' => $port,
            'mail.mailers.smtp.encryption' => $encryption !== '' ? $encryption : null,
            'mail.mailers.smtp.username' => $username,
            'mail.mailers.smtp.password' => $password,
            'mail.mailers.smtp.timeout' => $timeout,
            'mail.from.address' => $fromAddress !== '' ? $fromAddress : $username,
            'mail.from.name' => $fromName,
        ]);
    }

    private function manualSettings(): array
    {
        $path = base_path('config/mail_manual.php');
        if (! is_file($path)) {
            return [];
        }

        $settings = require $path;
        return is_array($settings) ? $settings : [];
    }
}
