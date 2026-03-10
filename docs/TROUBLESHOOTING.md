# ReferyApp - Troubleshooting Guide

> ⚠️ **¿Sin acceso SSH?** Ver [CPANEL_NO_SSH.md](CPANEL_NO_SSH.md) para soluciones sin terminal.

## HTTP 500 - tempnam() Error en Producción

### Error Completo
```
ErrorException: tempnam(): file created in the system's temporary directory
in /vendor/laravel/framework/src/Illuminate/Filesystem/Filesystem.php
```

### Causa
Laravel no puede escribir en los directorios de caché (`storage/framework/views`, `bootstrap/cache`) debido a permisos incorrectos en el servidor cPanel.

### Solución Rápida (vía SSH)

1. **Conéctate por SSH a tu servidor cPanel**

2. **Navega al directorio de tu aplicación:**
   ```bash
   cd ~/refery.app
   # o
   cd ~/public_html/referyAppV2
   ```

3. **Ejecuta el script de corrección de permisos:**
   ```bash
   chmod +x scripts/cpanel/fix-permissions.sh
   bash scripts/cpanel/fix-permissions.sh
   ```

### Solución Manual (si no tienes SSH o el script falla)

#### Opción A: Via File Manager de cPanel

1. Ve a **cPanel → File Manager**
2. Navega a tu aplicación
3. Selecciona las carpetas `storage` y `bootstrap/cache`
4. Click derecho → **Change Permissions**
5. Establece permisos a **755** (rwxr-xr-x)
6. Marca "Recurse into subdirectories"
7. Click "Change"

#### Opción B: Comandos manuales via Terminal SSH

```bash
# Ir al directorio de la aplicación
cd ~/refery.app

# Asegurar que existen todos los directorios necesarios
mkdir -p storage/framework/{sessions,views,cache}
mkdir -p storage/framework/cache/data
mkdir -p storage/logs
mkdir -p bootstrap/cache

# Establecer permisos en directorios
find storage bootstrap/cache -type d -exec chmod 755 {} \;

# Establecer permisos en archivos
find storage bootstrap/cache -type f -exec chmod 644 {} \;

# Si 755 no funciona, prueba con 775
chmod -R 775 storage
chmod -R 775 bootstrap/cache

# Limpiar cachés
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### Verificación Post-Solución

Después de aplicar la solución, verifica:

1. **Prueba la aplicación en el navegador**
   - Visita https://refery.app
   - Intenta registrarte o iniciar sesión

2. **Verifica que los cachés se están escribiendo:**
   ```bash
   ls -la storage/framework/views/
   ls -la bootstrap/cache/
   ```

3. **Revisa los logs si aún hay errores:**
   ```bash
   tail -f storage/logs/laravel.log
   ```

## Otros Problemas Comunes

### Página en Blanco / Assets No Cargan

**Causa:** El archivo `public/hot` existe en producción.

**Solución:**
```bash
rm -f public/hot
php artisan optimize:clear
```

### Error: "Vite manifest not found"

**Causa:** No se generaron los assets de producción.

**Solución:**
```bash
# En local, regenera el paquete
./scripts/cpanel/package.sh

# Sube y extrae el nuevo ZIP
```

### Storage Link No Funciona

**Causa:** El enlace simbólico se rompió o no existe.

**Solución:**
```bash
rm -f public/storage
php artisan storage:link
```

### Database Connection Failed

**Causa:** Credenciales incorrectas en `.env`.

**Solución:**
1. Verifica las credenciales en cPanel → MySQL® Databases
2. Actualiza `.env`:
   ```env
   DB_HOST=localhost
   DB_DATABASE=oqppbydo_myreferyapp
   DB_USERNAME=oqppbydo_myreferyapp
   DB_PASSWORD=tu_password_real
   ```
3. Limpia el caché:
   ```bash
   php artisan config:clear
   php artisan config:cache
   ```

### Session/Cache Errors

**Causa:** Permisos o configuración incorrecta.

**Solución:**
```bash
# Asegúrate de que .env tenga:
# SESSION_DRIVER=database
# CACHE_STORE=database

# Regenera las tablas si es necesario
php artisan session:table
php artisan cache:table
php artisan migrate --force

# Limpia y reconstruye
php artisan optimize:clear
php artisan config:cache
```

## Herramientas de Diagnóstico

### Ver estado actual de permisos
```bash
ls -la storage/
ls -la storage/framework/
ls -la bootstrap/cache/
```

### Verificar PHP y extensiones
```bash
php -v
php -m | grep -E 'pdo|mbstring|tokenizer|xml|ctype|json|bcmath|fileinfo'
```

### Ver logs en tiempo real
```bash
tail -f storage/logs/laravel.log
```

### Testear conexión a base de datos
```bash
php artisan tinker
# Luego ejecuta:
# DB::connection()->getPdo();
```

## Contacto de Emergencia

Si ninguna solución funciona:

1. Revisa los logs: `storage/logs/laravel.log`
2. Habilita debug temporalmente (solo para diagnosticar):
   ```env
   APP_DEBUG=true
   ```
3. Limpia TODOS los cachés:
   ```bash
   php artisan optimize:clear
   rm -rf bootstrap/cache/*.php
   rm -rf storage/framework/views/*.php
   rm -rf storage/framework/cache/data/*
   ```
4. Recarga la página y revisa el error detallado

**IMPORTANTE:** Vuelve a poner `APP_DEBUG=false` después de diagnosticar.
