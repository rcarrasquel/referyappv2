<!doctype html>
<html lang="{{ $isEs ? 'es' : 'en' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $isEs ? 'Notificacion de facturacion' : 'Billing notification' }}</title>
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
                            @if ($type === 'purchase_started')
                                {{ $isEs ? 'Tu compra Business esta en proceso' : 'Your Business purchase is in progress' }}
                            @elseif ($type === 'payment_confirmed')
                                {{ $isEs ? 'Pago confirmado correctamente' : 'Payment confirmed successfully' }}
                            @elseif ($type === 'plan_upgraded')
                                {{ $isEs ? 'Tu plan cambio de Free a Business' : 'Your plan changed from Free to Business' }}
                            @elseif ($type === 'plan_downgraded')
                                {{ $isEs ? 'Tu plan cambio de Business a Free' : 'Your plan changed from Business to Free' }}
                            @elseif ($type === 'payment_failed')
                                {{ $isEs ? 'No pudimos completar tu pago' : 'We could not complete your payment' }}
                            @elseif ($type === 'fraud_alert')
                                {{ $isEs ? 'Detectamos una alerta de seguridad' : 'We detected a security alert' }}
                            @else
                                {{ $isEs ? 'Actualizacion de facturacion' : 'Billing update' }}
                            @endif
                        </h1>
                    </td>
                </tr>
                <tr>
                    <td style="padding:22px 24px;">
                        <p style="margin:0 0 12px;font-size:14px;color:#475569;line-height:1.6;">
                            {{ $isEs ? 'Hola' : 'Hi' }} {{ $name !== '' ? $name : 'there' }},
                            @if ($type === 'purchase_started')
                                {{ $isEs ? 'recibimos tu solicitud para activar el plan Business mensual.' : 'we received your request to activate the monthly Business plan.' }}
                            @elseif ($type === 'payment_confirmed')
                                {{ $isEs ? 'tu pago fue confirmado exitosamente.' : 'your payment was confirmed successfully.' }}
                            @elseif ($type === 'plan_upgraded')
                                {{ $isEs ? 'tu plan se actualizo correctamente.' : 'your plan has been updated successfully.' }}
                            @elseif ($type === 'plan_downgraded')
                                {{ $isEs ? 'tu suscripcion fue cancelada y tu plan ahora es Free.' : 'your subscription was canceled and your plan is now Free.' }}
                            @elseif ($type === 'payment_failed')
                                {{ $isEs ? 'hubo un problema con el intento de pago de tu suscripcion.' : 'there was an issue with your subscription payment attempt.' }}
                            @elseif ($type === 'fraud_alert')
                                {{ $isEs ? 'Stripe reporto una revision de seguridad o posible fraude en tu suscripcion.' : 'Stripe reported a security review or possible fraud on your subscription.' }}
                            @endif
                        </p>

                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border:1px solid #e2e8f0;border-radius:10px;overflow:hidden;">
                            <tr>
                                <td style="padding:12px 14px;background:#f8fafc;font-size:12px;font-weight:700;color:#334155;width:38%;">
                                    {{ $isEs ? 'Plan' : 'Plan' }}
                                </td>
                                <td style="padding:12px 14px;background:#ffffff;font-size:13px;color:#0f172a;">
                                    @if ($type === 'plan_downgraded')
                                        Business -> Free
                                    @else
                                        Free -> Business
                                    @endif
                                </td>
                            </tr>
                            @if ($type !== 'plan_downgraded')
                                <tr>
                                    <td style="padding:12px 14px;background:#f8fafc;font-size:12px;font-weight:700;color:#334155;">
                                        {{ $isEs ? 'Monto' : 'Amount' }}
                                    </td>
                                    <td style="padding:12px 14px;background:#ffffff;font-size:13px;color:#0f172a;">
                                        {{ $currency }} {{ $amount }}
                                    </td>
                                </tr>
                            @else
                                <tr>
                                    <td style="padding:12px 14px;background:#f8fafc;font-size:12px;font-weight:700;color:#334155;">
                                        {{ $isEs ? 'Pago recurrente' : 'Recurring billing' }}
                                    </td>
                                    <td style="padding:12px 14px;background:#ffffff;font-size:13px;color:#0f172a;">
                                        {{ $isEs ? 'Desactivado' : 'Disabled' }}
                                    </td>
                                </tr>
                            @endif
                            @if (!empty($reason))
                                <tr>
                                    <td style="padding:12px 14px;background:#f8fafc;font-size:12px;font-weight:700;color:#334155;">
                                        {{ $isEs ? 'Detalle' : 'Detail' }}
                                    </td>
                                    <td style="padding:12px 14px;background:#ffffff;font-size:13px;color:#0f172a;">
                                        {{ $reason }}
                                    </td>
                                </tr>
                            @endif
                            <tr>
                                <td style="padding:12px 14px;background:#f8fafc;font-size:12px;font-weight:700;color:#334155;">
                                    {{ $isEs ? 'Fecha' : 'Date' }}
                                </td>
                                <td style="padding:12px 14px;background:#ffffff;font-size:13px;color:#0f172a;">
                                    {{ $eventDate }}
                                </td>
                            </tr>
                            @if ($reference !== '')
                                <tr>
                                    <td style="padding:12px 14px;background:#f8fafc;font-size:12px;font-weight:700;color:#334155;">
                                        {{ $isEs ? 'Referencia' : 'Reference' }}
                                    </td>
                                    <td style="padding:12px 14px;background:#ffffff;font-size:13px;color:#0f172a;">
                                        {{ $reference }}
                                    </td>
                                </tr>
                            @endif
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
