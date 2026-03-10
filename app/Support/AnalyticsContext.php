<?php

namespace App\Support;

use Illuminate\Http\Request;

class AnalyticsContext
{
    public static function fromRequest(Request $request): array
    {
        $userAgent = (string) ($request->userAgent() ?? '');
        $ip = $request->ip();
        $sessionId = $request->hasSession() ? $request->session()->getId() : null;

        return [
            'ip_address' => $ip,
            'browser' => self::detectBrowser($userAgent),
            'os' => self::detectOs($userAgent),
            'device_type' => self::detectDeviceType($userAgent),
            'session_id' => $sessionId,
            'fingerprint' => hash('sha256', implode('|', [
                $ip ?? 'unknown-ip',
                $userAgent !== '' ? $userAgent : 'unknown-agent',
                $sessionId ?? 'unknown-session',
            ])),
            'accept_language' => $request->header('Accept-Language'),
            'referer' => $request->headers->get('referer'),
            'user_agent' => $userAgent !== '' ? $userAgent : null,
        ];
    }

    private static function detectBrowser(string $ua): string
    {
        $checks = [
            'Edg/' => 'Edge',
            'OPR/' => 'Opera',
            'Firefox/' => 'Firefox',
            'Chrome/' => 'Chrome',
            'Safari/' => 'Safari',
            'MSIE ' => 'Internet Explorer',
            'Trident/' => 'Internet Explorer',
        ];

        foreach ($checks as $needle => $name) {
            if (stripos($ua, $needle) !== false) {
                if ($name === 'Safari' && stripos($ua, 'Chrome/') !== false) {
                    continue;
                }

                return $name;
            }
        }

        return 'Other';
    }

    private static function detectOs(string $ua): string
    {
        $checks = [
            'Windows NT' => 'Windows',
            'Mac OS X' => 'macOS',
            'Android' => 'Android',
            'iPhone' => 'iOS',
            'iPad' => 'iOS',
            'Linux' => 'Linux',
        ];

        foreach ($checks as $needle => $name) {
            if (stripos($ua, $needle) !== false) {
                return $name;
            }
        }

        return 'Other';
    }

    private static function detectDeviceType(string $ua): string
    {
        if (stripos($ua, 'iPad') !== false || stripos($ua, 'Tablet') !== false) {
            return 'tablet';
        }

        if (stripos($ua, 'Mobi') !== false || stripos($ua, 'Android') !== false || stripos($ua, 'iPhone') !== false) {
            return 'mobile';
        }

        return 'desktop';
    }
}
