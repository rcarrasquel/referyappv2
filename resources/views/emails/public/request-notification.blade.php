<!doctype html>
<html lang="{{ $isEs ? 'es' : 'en' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $isEs ? 'Nueva solicitud en tu tarjeta' : 'New request on your card' }}</title>
</head>
<body style="margin:0;padding:0;background:#f3f7ef;font-family:Arial,sans-serif;color:#111111;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="padding:24px 12px;background:#f3f7ef;">
    <tr>
        <td align="center">
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:660px;background:#ffffff;border:1px solid #e5e7eb;border-radius:14px;overflow:hidden;">
                <tr>
                    <td style="padding:20px 24px;background:linear-gradient(135deg,#111111 0%,#1a2a12 70%,#6DBE45 100%);">
                        <p style="margin:0;font-size:12px;letter-spacing:1.8px;color:#d1d5db;text-transform:uppercase;">ReferyApp</p>
                        <h1 style="margin:8px 0 0;font-size:23px;line-height:1.2;color:#ffffff;font-weight:700;">
                            {{ $requestType === 'appointment'
                                ? ($isEs ? 'Tienes una nueva cita agendada' : 'You have a new booked appointment')
                                : ($isEs ? 'Tienes un nuevo formulario de contacto' : 'You have a new contact form submission') }}
                        </h1>
                    </td>
                </tr>
                <tr>
                    <td style="padding:22px 24px;">
                        <p style="margin:0 0 12px;font-size:14px;color:#475569;line-height:1.6;">
                            {{ $isEs
                                ? 'Recibiste una nueva solicitud desde tu tarjeta pública. Aquí están los detalles:'
                                : 'You received a new request from your public card. Here are the details:' }}
                        </p>

                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border:1px solid #e2e8f0;border-radius:10px;overflow:hidden;">
                            <tr>
                                <td style="padding:12px 14px;background:#f8fafc;font-size:12px;font-weight:700;color:#334155;width:38%;">
                                    {{ $isEs ? 'Tarjeta' : 'Card' }}
                                </td>
                                <td style="padding:12px 14px;background:#ffffff;font-size:13px;color:#0f172a;">
                                    {{ $cardName }} ({{ '@' . $cardUsername }})
                                </td>
                            </tr>
                            <tr>
                                <td style="padding:12px 14px;background:#f8fafc;font-size:12px;font-weight:700;color:#334155;">
                                    {{ $isEs ? 'Tipo de solicitud' : 'Request type' }}
                                </td>
                                <td style="padding:12px 14px;background:#ffffff;font-size:13px;color:#0f172a;">
                                    {{ $requestType === 'appointment'
                                        ? ($isEs ? 'Cita agendada' : 'Appointment')
                                        : ($isEs ? 'Formulario de contacto' : 'Contact form') }}
                                </td>
                            </tr>
                            <tr>
                                <td style="padding:12px 14px;background:#f8fafc;font-size:12px;font-weight:700;color:#334155;">
                                    {{ $isEs ? 'Nombre completo' : 'Full name' }}
                                </td>
                                <td style="padding:12px 14px;background:#ffffff;font-size:13px;color:#0f172a;">
                                    {{ $fullName !== '' ? $fullName : '-' }}
                                </td>
                            </tr>
                            <tr>
                                <td style="padding:12px 14px;background:#f8fafc;font-size:12px;font-weight:700;color:#334155;">Email</td>
                                <td style="padding:12px 14px;background:#ffffff;font-size:13px;color:#0f172a;">
                                    {{ $email !== '' ? $email : '-' }}
                                </td>
                            </tr>
                            <tr>
                                <td style="padding:12px 14px;background:#f8fafc;font-size:12px;font-weight:700;color:#334155;">
                                    {{ $isEs ? 'Teléfono' : 'Phone' }}
                                </td>
                                <td style="padding:12px 14px;background:#ffffff;font-size:13px;color:#0f172a;">
                                    {{ $phone !== '' ? $phone : '-' }}
                                </td>
                            </tr>
                            <tr>
                                <td style="padding:12px 14px;background:#f8fafc;font-size:12px;font-weight:700;color:#334155;">
                                    {{ $isEs ? 'Servicio / Producto' : 'Service / Product' }}
                                </td>
                                <td style="padding:12px 14px;background:#ffffff;font-size:13px;color:#0f172a;">
                                    {{ $serviceName !== '' ? $serviceName : '-' }}
                                </td>
                            </tr>
                            <tr>
                                <td style="padding:12px 14px;background:#f8fafc;font-size:12px;font-weight:700;color:#334155;">
                                    {{ $isEs ? 'Interés' : 'Interest' }}
                                </td>
                                <td style="padding:12px 14px;background:#ffffff;font-size:13px;color:#0f172a;">
                                    {{ $interest !== '' ? $interest : '-' }}
                                </td>
                            </tr>
                            @if ($requestType === 'appointment')
                                <tr>
                                    <td style="padding:12px 14px;background:#f8fafc;font-size:12px;font-weight:700;color:#334155;">
                                        {{ $isEs ? 'Inicio' : 'Starts at' }}
                                    </td>
                                    <td style="padding:12px 14px;background:#ffffff;font-size:13px;color:#0f172a;">
                                        {{ $startsAt !== '' ? $startsAt : '-' }}
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding:12px 14px;background:#f8fafc;font-size:12px;font-weight:700;color:#334155;">
                                        {{ $isEs ? 'Fin' : 'Ends at' }}
                                    </td>
                                    <td style="padding:12px 14px;background:#ffffff;font-size:13px;color:#0f172a;">
                                        {{ $endsAt !== '' ? $endsAt : '-' }}
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding:12px 14px;background:#f8fafc;font-size:12px;font-weight:700;color:#334155;">
                                        {{ $isEs ? 'Duración' : 'Duration' }}
                                    </td>
                                    <td style="padding:12px 14px;background:#ffffff;font-size:13px;color:#0f172a;">
                                        {{ $duration > 0 ? $duration . ' min' : '-' }}
                                    </td>
                                </tr>
                            @endif
                            <tr>
                                <td style="padding:12px 14px;background:#f8fafc;font-size:12px;font-weight:700;color:#334155;">
                                    {{ $isEs ? 'Mensaje' : 'Message' }}
                                </td>
                                <td style="padding:12px 14px;background:#ffffff;font-size:13px;color:#0f172a;line-height:1.5;">
                                    {{ $notes !== '' ? $notes : '-' }}
                                </td>
                            </tr>
                        </table>

                        <table role="presentation" cellpadding="0" cellspacing="0" style="margin:18px 0 0;">
                            <tr>
                                <td>
                                    <a href="{{ $dashboardUrl }}" style="display:inline-block;padding:12px 20px;border-radius:10px;background:#6DBE45;color:#111111;text-decoration:none;font-size:14px;font-weight:700;">
                                        {{ $isEs ? 'Abrir dashboard' : 'Open dashboard' }}
                                    </a>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <tr>
                    <td style="padding:14px 24px;background:#f8fafc;border-top:1px solid #e5e7eb;">
                        <p style="margin:0;font-size:12px;color:#64748b;">
                            A product of
                            <a href="https://xper.team" style="color:#111111;text-decoration:none;font-weight:600;">Xperteam LLC</a>
                        </p>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>
</html>
