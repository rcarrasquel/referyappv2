# ReferyApp - Resumen General

## Descripcion General
ReferyApp es una plataforma para crear y gestionar tarjetas digitales profesionales tipo link-in-bio (estilo Linktree), con enfoque en imagen de marca, conversion de contactos, agendamiento de citas y analiticas.

Permite que cada usuario business publique su tarjeta por URL publica y reciba interacciones (clicks, formularios, citas, compartidos), mientras que el usuario admin supervisa usuarios y metricas globales del sistema.

## Modulos Principales

### 1. Autenticacion y Acceso
- Login, registro, recuperacion de clave y cierre de sesion.
- Verificacion de correo obligatoria para activar cuenta.
- Cambio de idioma ES/EN en flujo auth.
- Roles:
  - `business`: operacion completa de su negocio.
  - `admin`: supervision del sistema (solo lectura administrativa en web/API para modulos definidos).

### 2. Dashboard
- Vista principal con indicadores de rendimiento.
- En business: resumen de visitas, clicks, CTR y trafico.
- En admin: resumen global de usuarios, tarjetas, productos, citas y leads, con accesos rapidos administrativos.
- En admin: resumen financiero con ingresos:
  - ingreso del mes actual
  - comparativa contra mes anterior
  - ingreso total acumulado
  - serie mensual visual (ultimos meses)
  - cantidad de business con pagos exitosos

### 3. Cards (Tarjetas Digitales)
- Creacion y gestion de tarjetas profesionales.
- Configuracion por secciones:
  - Basic info
  - Colors
  - Images
  - Links
  - Schedule
  - Design
  - Styles
- Reglas por plan:
  - Free: solo 1 tarjeta.
  - Business: multiples tarjetas.
- Vista preview en tiempo real desde el editor.

### 4. Vista Publica de Tarjeta
- URL publica por `refery.app/{username}` y soporte de subdominio.
- Visualizacion de perfil, header, enlaces, productos, QR, compartir y descarga vCard.
- Offcanvas de contacto/agendamiento.
- Registro de visitas y eventos de interaccion.

### 5. Links (Enlaces)
- Enlaces personalizados con iconos.
- Reordenamiento drag-and-drop.
- Auto-links desde datos basicos (phone/email/maps) editables por el usuario.
- Soporte de protocolos especiales (`tel:`, `mailto:`, `sms:` y location/maps).

### 6. Images y Estilo Visual
- Carga de imagen de perfil, header y fondo.
- Recorte para imagen de perfil y header.
- Eliminacion de imagen con confirmacion modal.
- Estilos de tarjeta y estilos de botones.

### 7. Products / Services
- Gestion de productos/servicios con:
  - nombre
  - descripcion
  - imagen
  - precio (texto libre opcional)
  - enlace
  - duracion de servicio (para agenda)
- Busqueda en tiempo real y paginacion.
- Limites:
  - Free: max 2 productos.
  - Business: ilimitado.

### 8. Scheduling y Appointments (Citas)
- Agenda por tarjeta con horarios configurables.
- Bloqueo de solapamiento de citas.
- Citas desde panel interno y desde tarjeta publica.
- Gestion de estado de cita (attended, no_show, cancelled, etc.).
- Agenda diaria y listado general con filtros.

### 9. Leads (Contactos Potenciales)
- Captura de formularios de contacto desde tarjeta publica.
- Asociacion por tarjeta y por servicio/producto.
- Estados de lead para seguimiento comercial.

### 10. Analytics
- Metricas comparativas por rango de fechas.
- Filtros por tarjeta, dispositivo y navegador.
- Top cards, top links, desgloses por browser/device/OS/referrer.
- Serie temporal y comparacion contra periodo anterior.
- En admin, vision global del sistema.

### 11. Notificaciones por Correo
- Verificacion de cuenta por email.
- Notificaciones al owner cuando llega:
  - nuevo formulario de contacto
  - nueva cita agendada
- Notificaciones de billing al business:
  - compra iniciada de plan Business
  - pago confirmado
  - cambio de plan Free -> Business
  - cambio de plan Business -> Free
  - pago fallido/rechazado
  - alerta de seguridad/fraude (review/dispute/radar)
- Plantillas HTML profesionales multilenguaje (ES/EN segun idioma del owner).
- Envio sin colas.
- Configuracion SMTP manual mediante `config/mail_manual.php`.

### 12. QR y vCard
- Generacion de QR centrado con branding por plan.
- Descarga de QR.
- Descarga de vCard con datos de contacto.

### 13. Compartir y Trazabilidad
- Opciones de compartir en redes y canales directos.
- Registro de eventos de compartido para analitica.

### 14. API Movil (Business/Admin)
- API versionada (`/api/v1`) con token Sanctum.
- Modulos API implementados:
  - auth
  - dashboard
  - analytics
  - users (admin)
  - cards (business)
  - products (business)
  - appointments (business)
  - leads (business)
  - profile (business)

### 15. Billing y Planes (Stripe)
- Planes activos del sistema:
  - `free`
  - `business` (USD 9 mensual)
- No se usa configuracion de Stripe en `.env`.
- Configuracion Stripe en DB mediante tabla `stripe_settings` (columnas separadas, sin JSON):
  - publishable_key
  - secret_key
  - webhook_secret
  - currency
  - monthly_price_cents
  - is_active
- Checkout de suscripcion mensual para upgrade a Business.
- Sincronizacion de plan:
  - por webhook Stripe
  - por retorno de checkout (`session_id`) al profile
  - por lectura de suscripcion activa al cargar profile
- Cancelacion de suscripcion desde profile con confirmacion modal.
- Al cancelar (Business -> Free) se aplican limites Free de forma automatica:
  - queda solo 1 card
  - quedan solo 2 productos
  - se elimina excedente en DB y en storage (`cards/{id}`, `products/{id}`)
- Historial de transacciones en profile (tabla `billing_transactions`).
- Vistas admin de pagos:
  - tabla de usuarios con metricas de pagos por usuario
  - listado de business que si han pagado
  - detalle de usuario con historial de pagos

## Webhooks Stripe Requeridos (Produccion)
Para que el flujo de planes, pagos y alertas funcione completo, deben estar activos:

- `checkout.session.completed`
- `customer.subscription.created`
- `customer.subscription.updated`
- `customer.subscription.deleted`
- `invoice.paid`
- `invoice.payment_failed`
- `payment_intent.payment_failed`
- `review.opened`
- `radar.early_fraud_warning.created`
- `charge.dispute.created`

Webhooks opcionales pueden coexistir, pero estos son los minimos requeridos para la logica actual de ReferyApp.

## Objetivo de la Plataforma
Permitir que profesionales y negocios centralicen su presencia digital en una tarjeta moderna, configurable y orientada a conversion (contacto, agendamiento y ventas), con medicion de resultados y capacidad de escalamiento por planes.
