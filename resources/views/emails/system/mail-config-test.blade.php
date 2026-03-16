<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>ReferyApp SMTP Test</title>
</head>
<body style="margin:0;padding:0;background:#f3f7ef;font-family:Arial,sans-serif;color:#111111;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="padding:24px 12px;background:#f3f7ef;">
    <tr>
        <td align="center">
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:640px;background:#ffffff;border:1px solid #e5e7eb;border-radius:14px;overflow:hidden;">
                <tr>
                    <td style="padding:20px 24px;background:linear-gradient(135deg,#111111 0%,#1a2a12 70%,#6DBE45 100%);">
                        <p style="margin:0;font-size:12px;letter-spacing:1.8px;color:#d1d5db;text-transform:uppercase;">ReferyApp</p>
                        <h1 style="margin:8px 0 0;font-size:23px;line-height:1.2;color:#ffffff;font-weight:700;">SMTP configuration test successful</h1>
                    </td>
                </tr>
                <tr>
                    <td style="padding:22px 24px;">
                        <p style="margin:0 0 10px;font-size:14px;color:#475569;line-height:1.6;">
                            Hello {{ $recipientName !== '' ? $recipientName : 'Admin' }}, your mail configuration is working correctly.
                        </p>
                        <table role="presentation" cellpadding="0" cellspacing="0" style="margin:14px 0 0;">
                            <tr>
                                <td>
                                    <a href="{{ $dashboardUrl }}" style="display:inline-block;padding:12px 20px;border-radius:10px;background:#6DBE45;color:#111111;text-decoration:none;font-size:14px;font-weight:700;">
                                        Open dashboard
                                    </a>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>
</html>

