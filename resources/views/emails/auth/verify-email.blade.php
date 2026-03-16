<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Verify Your ReferyApp Account</title>
</head>
<body style="margin:0;padding:0;background:#f4f7f2;font-family:Arial,sans-serif;color:#111111;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="padding:24px 12px;background:#f4f7f2;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:620px;background:#ffffff;border:1px solid #e5e7eb;border-radius:14px;overflow:hidden;">
                    <tr>
                        <td style="padding:20px 24px;background:linear-gradient(135deg,#111111 0%,#1a2a12 70%,#6DBE45 100%);">
                            <p style="margin:0;font-size:12px;letter-spacing:1.8px;color:#d1d5db;text-transform:uppercase;">ReferyApp</p>
                            <h1 style="margin:8px 0 0;font-size:24px;line-height:1.2;color:#ffffff;font-weight:700;">Verify your email</h1>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:24px;">
                            <p style="margin:0 0 12px;font-size:15px;line-height:1.6;color:#334155;">
                                Hi {{ $name !== '' ? $name : 'there' }}, thanks for creating your ReferyApp account.
                            </p>
                            <p style="margin:0 0 18px;font-size:15px;line-height:1.6;color:#334155;">
                                Please confirm your email address to activate your account and access the platform.
                            </p>
                            <table role="presentation" cellpadding="0" cellspacing="0" style="margin:0 0 18px;">
                                <tr>
                                    <td>
                                        <a href="{{ $verificationUrl }}" style="display:inline-block;padding:12px 20px;border-radius:10px;background:#6DBE45;color:#111111;text-decoration:none;font-size:14px;font-weight:700;">
                                            Verify My Account
                                        </a>
                                    </td>
                                </tr>
                            </table>
                            <p style="margin:0 0 8px;font-size:13px;line-height:1.6;color:#64748b;">
                                If you did not create this account, you can safely ignore this email.
                            </p>
                            <p style="margin:0;font-size:13px;line-height:1.6;color:#64748b;word-break:break-word;">
                                If the button doesn't work, use this URL:
                                <a href="{{ $verificationUrl }}" style="color:#111111;">{{ $verificationUrl }}</a>
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:14px 24px;background:#f8fafc;border-top:1px solid #e5e7eb;">
                            <p style="margin:0;font-size:12px;color:#64748b;">
                                A product of
                                <a href="{{ $supportUrl }}" style="color:#111111;text-decoration:none;font-weight:600;">Xperteam LLC</a>
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>

