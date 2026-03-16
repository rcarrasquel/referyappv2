<?php

/*
|--------------------------------------------------------------------------
| Manual SMTP Settings Loader
|--------------------------------------------------------------------------
|
| This file is preserved intentionally. It now loads SMTP credentials from
| database table `mail_settings` and falls back to safe empty defaults.
|
*/

$defaults = [
    'host' => '',
    'port' => 465,
    'encryption' => 'ssl', // tls, ssl, or '' for none
    'username' => '',
    'password' => '',
    'timeout' => 30,
    'from_address' => '',
    'from_name' => '',
];

try {
    if (
        class_exists(\Illuminate\Support\Facades\Schema::class)
        && class_exists(\Illuminate\Support\Facades\DB::class)
        && \Illuminate\Support\Facades\Schema::hasTable('mail_settings')
    ) {
        $row = \Illuminate\Support\Facades\DB::table('mail_settings')
            ->where('is_active', true)
            ->orderByDesc('id')
            ->first([
                'host',
                'port',
                'encryption',
                'username',
                'password',
                'timeout',
                'from_address',
                'from_name',
            ]);

        if ($row) {
            return [
                'host' => (string) ($row->host ?? ''),
                'port' => (int) ($row->port ?? 465),
                'encryption' => (string) ($row->encryption ?? 'ssl'),
                'username' => (string) ($row->username ?? ''),
                'password' => (string) ($row->password ?? ''),
                'timeout' => (int) ($row->timeout ?? 30),
                'from_address' => (string) ($row->from_address ?? ''),
                'from_name' => (string) ($row->from_name ?? ''),
            ];
        }
    }
} catch (\Throwable) {
    // Fallback to defaults if DB is not ready.
}

return $defaults;
