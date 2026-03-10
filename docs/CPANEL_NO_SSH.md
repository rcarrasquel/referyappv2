# ReferyApp - Solución de Errores SIN SSH (Solo cPanel)

Si tienes el error `tempnam()` y **NO tienes acceso SSH**, sigue estos pasos:

## Método 1: Script PHP Automático (Recomendado) ⭐

### Paso 1: Subir el script

1. Ve a **cPanel → File Manager**
2. Navega a tu carpeta `public/`
3. Sube el archivo `fix-production.php` desde tu proyecto local

### Paso 2: Ejecutar el script

1. Abre tu navegador
2. Visita: `https://refery.app/fix-production.php`

3. El script automáticamente:
   - ✅ Creará directorios necesarios
   - ✅ Limpiará todos los cachés
   - ✅ Reconstruirá los cachés
   - ✅ Verificará el storage link
   - ✅ Mostrará estado de permisos
   - ✅ **Se auto-eliminará por seguridad**

### Paso 3: Verificar permisos (si es necesario)

Si el script muestra "✗ NOT writable", continúa al **Método 2** abajo para cambiar permisos manualmente

---

## Método 2: Cambiar Permisos Manualmente en cPanel

### Opción A: File Manager (Interfaz Gráfica)

1. **Abrir File Manager**
   - Ve a tu **cPanel**
   - Busca y abre **File Manager**
   - Navega a tu aplicación (ejemplo: `/home/oqppbydo/refery.app`)

2. **Cambiar permisos de `storage`**
   - Click derecho en la carpeta `storage`
   - Selecciona **Change Permissions**
   - Configura así:
     ```
     User:  [✓] Read  [✓] Write  [✓] Execute
     Group: [✓] Read  [ ] Write  [✓] Execute
     World: [✓] Read  [ ] Write  [✓] Execute
     ```
     (Esto equivale a 755)
   - **IMPORTANTE:** Marca la casilla **"Recurse into subdirectories"**
   - Click **Change**

3. **Cambiar permisos de `bootstrap/cache`**
   - Click derecho en `bootstrap/cache`
   - Repite el mismo proceso del paso 2
   - Permisos: 755 con recursión

4. **Verificar estructura de directorios**
   
   Asegúrate de que existan estas carpetas dentro de `storage/framework/`:
   - `cache/`
   - `cache/data/`
   - `sessions/`
   - `views/`
   
   Si no existen, créalas:
   - Click en **+ Folder**
   - Crea cada carpeta
   - Establece permisos 755 en cada una

### Opción B: Terminal de cPanel (si está disponible)

Algunos cPanel tienen una opción **Terminal** en el menú:

1. Ve a cPanel → busca **Terminal** o **Shell Access**
2. Si existe, ábrelo y ejecuta:

```bash
cd ~/refery.app

# Crear directorios
mkdir -p storage/framework/{sessions,views,cache}
mkdir -p storage/framework/cache/data
mkdir -p bootstrap/cache

# Cambiar permisos
chmod -R 755 storage
chmod -R 755 bootstrap/cache

# Limpiar cachés
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

---

## Método 3: Usar Cron Job para Ejecutar Comandos

Si cPanel Terminal no está disponible, usa **Cron Jobs**:

1. Ve a **cPanel → Cron Jobs**

2. En **Add New Cron Job**, configura:
   - **Minute:** `*`
   - **Hour:** `*`
   - **Day:** `*`
   - **Month:** `*`
   - **Weekday:** `*`
   - **Command:**
     ```bash
     cd /home/oqppbydo/refery.app && php artisan optimize:clear > /dev/null 2>&1
     ```

3. Click **Add New Cron Job**

4. **Espera 1 minuto** (el cron se ejecutará)

5. **ELIMINA el cron job** inmediatamente después (no lo dejes corriendo)

6. Repite para estos comandos (uno a la vez):
   ```bash
   cd /home/oqppbydo/refery.app && php artisan config:cache
   cd /home/oqppbydo/refery.app && php artisan route:cache
   cd /home/oqppbydo/refery.app && php artisan view:cache
   ```

---

## Verificación Final

1. **Visita tu aplicación:** https://refery.app

2. **Si aún ves errores:**
   - Ve a cPanel → File Manager
   - Navega a `storage/logs/laravel.log`
   - Abre el archivo y busca el error más reciente
   - Copia el error y busca solución en `docs/TROUBLESHOOTING.md`

3. **Verifica estos puntos:**
   - [ ] `public/hot` NO existe
   - [ ] `public/build/manifest.json` SÍ existe
   - [ ] `storage/` tiene permisos 755
   - [ ] `bootstrap/cache/` tiene permisos 755
   - [ ] Existen: `storage/framework/sessions/`, `views/`, `cache/`

---

## Problemas Comunes y Soluciones Rápidas

### ❌ Problema: Sigo viendo el error tempnam()

**Solución:** Los permisos necesitan ser 775 en lugar de 755

1. File Manager → `storage` → Change Permissions
2. User y Group: Read, Write, Execute
3. World: Read, Execute
4. Recurse into subdirectories
5. Repite para `bootstrap/cache`

### ❌ Problema: Error "No application encryption key"

**Solución:** Falta el APP_KEY en `.env`

1. File Manager → edita `.env`
2. Verifica que exista: `APP_KEY=base64:...`
3. Si no existe o está vacío:
   - Usa el script `fix-production.php` (Método 1)
   - O genera uno en: https://generate-random.org/laravel-key-generator
   - Copia y pega en `.env`: `APP_KEY=base64:tu_key_generada`

### ❌ Problema: Página en blanco (sin errores)

**Soluciones:**

1. **Habilita debug temporalmente:**
   - Edita `.env` en cPanel File Manager
   - Cambia: `APP_DEBUG=true`
   - Recarga la página para ver el error real
   - **NO OLVIDES volver a poner: `APP_DEBUG=false`**

2. **Revisa los logs:**
   - File Manager → `storage/logs/laravel.log`
   - Busca el error más reciente (al final del archivo)

3. **Verifica que Vite assets existan:**
   - Debe existir: `public/build/manifest.json`
   - Si no existe, regenera el build localmente y sube todo `public/build/`

---

## Contacto de Soporte

Si después de todos estos pasos sigues con problemas:

1. **Revisa el log completo:**
   ```
   cPanel → File Manager → storage/logs/laravel.log
   ```

2. **Toma screenshot del error**

3. **Verifica tu plan de hosting:**
   - PHP versión: debe ser 8.2 o superior
   - Extensiones PHP requeridas: PDO, mbstring, tokenizer, xml, ctype, json

4. **Comparte estos detalles:**
   - Error exacto del log
   - Versión de PHP (cPanel → PHP Selector)
   - Permisos actuales de `storage/` y `bootstrap/cache/`

---

## Checklist Final ✅

Antes de contactar soporte, verifica:

- [ ] Ejecuté el script `fix-production.php` o cambié permisos manualmente
- [ ] `storage/` tiene permisos 755 o 775
- [ ] `bootstrap/cache/` tiene permisos 755 o 775
- [ ] Existen las carpetas: `storage/framework/{sessions,views,cache}`
- [ ] `.env` tiene configuración correcta de base de datos
- [ ] `APP_KEY` está definida en `.env`
- [ ] `public/hot` NO existe
- [ ] `public/build/manifest.json` SÍ existe
- [ ] Limpié todos los cachés (usando script o cron)
- [ ] Revisé `storage/logs/laravel.log` para más detalles

---

**¿Funcionó?** 🎉 ¡No olvides eliminar `public/fix-production.php`!

---

## Solución: Imágenes No Se Cargan en Cards Públicas

### Caso 1: Error 404 (No encontradas)

El problema es que **falta el enlace simbólico de storage**. Ver abajo "Crear el Enlace Simbólico".

### Caso 2: Error 403 (Acceso Denegado) ⚠️

Si las imágenes existen pero dan "Acceso Denegado":

**Solución Rápida:**

1. Sube `public/fix-storage-permissions.php` a tu servidor
2. Visita: `https://refery.app/fix-storage-permissions.php`
3. El script corregirá todos los permisos automáticamente
4. Prueba tu card - ¡debería funcionar!

**Ver guía detallada:** [FIX_IMAGE_PERMISSIONS.md](FIX_IMAGE_PERMISSIONS.md)

---

### Diagnóstico General

Si las imágenes aparecen en el editor pero NO en las cards públicas, el problema es que **falta el enlace simbólico de storage**.

### Diagnóstico Rápido

1. **Sube el archivo de diagnóstico:**
   - Sube `public/check-storage.php` a tu servidor (cPanel → File Manager → `public/`)

2. **Visita en tu navegador:**
   - `https://refery.app/check-storage.php`

3. **El script te mostrará:**
   - ✅ Si el enlace simbólico existe
   - ✅ Si apunta al lugar correcto
   - ❌ Qué está fallando

### Solución: Crear el Enlace Simbólico

#### Método A: Script Automático (Más fácil)

1. **Sube el archivo:**
   - Sube `public/create-storage-link.php` a tu servidor

2. **Visita una vez:**
   - `https://refery.app/create-storage-link.php`

3. **El script:**
   - Creará el enlace simbólico automáticamente
   - Se auto-eliminará
   - Tus imágenes deberían cargar inmediatamente ✅

#### Método B: Si el Script Automático Falla

Si tu hosting no permite symlinks via PHP:

1. **Contacta al soporte de tu hosting** (cPanel/Hostinger/GoDaddy/etc.)

2. **Solicita que creen un enlace simbólico:**
   ```
   Desde: /home/oqppbydo/refery.app/public/storage
   Hacia: /home/oqppbydo/refery.app/storage/app/public
   ```

3. **Alternativa - Copiado manual (NO recomendado):**
   Si tu hosting NO soporta enlaces simbólicos:
   - Ve a cPanel → File Manager
   - Copia todo el contenido de `storage/app/public/` 
   - Pégalo en `public/storage/`
   - **PROBLEMA:** Tendrás que hacer esto cada vez que subas una imagen nueva

#### Verificación Final

Después de crear el enlace:

1. Visita tu card pública: `https://refery.app/tu-username`
2. Las imágenes deberían cargar correctamente
3. Abre el inspector del navegador (F12) → pestaña Network
4. Busca peticiones a `/storage/cards/...`
5. Deben retornar `200 OK` en lugar de `404`

---

**¿Funcionó?** 🎉 ¡No olvides eliminar los scripts de diagnóstico!
