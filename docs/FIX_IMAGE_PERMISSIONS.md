# ReferyApp - Quick Fix for "Access Denied" on Images

## Problema: "Acceso Denegado" en Imágenes

Si ves este error al intentar acceder a las imágenes:
```
https://refery.app/storage/cards/.../image.jpg
Acceso denegado
```

Significa que las imágenes existen pero tienen **permisos incorrectos**.

---

## Solución Rápida

### 1. Sube y ejecuta el script de permisos

1. **Sube:** `public/fix-storage-permissions.php` a tu servidor (cPanel → File Manager → `public/`)

2. **Visita:** `https://refery.app/fix-storage-permissions.php`

3. **El script:**
   - Verificará todos los archivos en `storage/app/public/`
   - Corregirá permisos de directorios a `755`
   - Corregirá permisos de archivos a `644`
   - Verificará tu imagen específica
   - Se auto-eliminará

4. **Prueba tu card** → Las imágenes deberían cargar correctamente ✅

---

## Si el Script No Soluciona el Problema

### Opción A: Via cPanel File Manager

1. **Ve a cPanel → File Manager**

2. **Navega a:** `storage/app/public/cards/`

3. **Selecciona la carpeta `cards`**

4. **Click derecho → Change Permissions**

5. **Configura así:**
   - User: `[✓] Read [✓] Write [✓] Execute`
   - Group: `[✓] Read [ ] Write [✓] Execute`
   - World: `[✓] Read [ ] Write [✓] Execute`
   - **IMPORTANTE:** Marca `"Recurse into subdirectories"`
   - Esto es equivalente a `755`

6. **Click "Change"** y espera que procese todos los subdirectorios

7. **Repite para los ARCHIVOS dentro:**
   - Selecciona archivos de imagen individuales
   - Permisos: `644`
   - User: `[✓] Read [✓] Write [ ] Execute`
   - Group: `[✓] Read [ ] Write [ ] Execute`
   - World: `[✓] Read [ ] Write [ ] Execute`

---

### Opción B: Verificar .htaccess

A veces el problema es que Apache está bloqueando el acceso. Verifica:

1. **Abre:** `public/.htaccess`

2. **Busca esta sección y asegúrate que NO bloquee storage:**

```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    
    # Allow access to storage
    RewriteCond %{REQUEST_URI} ^/storage/
    RewriteRule ^ - [L]
    
    # Redirect to index.php
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^ index.php [L]
</IfModule>
```

3. **Si no existe la regla de storage, agrégala** (después de `RewriteEngine On`)

---

### Opción C: Verificar el enlace simbólico

El enlace simbólico podría tener permisos incorrectos:

1. **Sube y ejecuta:** `public/check-storage.php`

2. **Visita:** `https://refery.app/check-storage.php`

3. **Si el enlace no existe o está mal:**
   - Ejecuta `public/create-storage-link.php`
   - O contacta a soporte del hosting

---

## Verificación Final

Después de aplicar la solución:

1. **Abre tu navegador en modo incógnito** (Ctrl+Shift+N)

2. **Visita tu card:** `https://refery.app/tu-username`

3. **Abre DevTools** (F12) → pestaña **Network**

4. **Recarga la página**

5. **Busca peticiones a `/storage/cards/...`**
   - ✅ Deben mostrar: `200 OK`
   - ❌ Si muestran: `403 Forbidden` → problema de permisos
   - ❌ Si muestran: `404 Not Found` → problema de enlace simbólico

---

## Checklist de Diagnóstico

- [ ] El enlace simbólico `public/storage` existe y apunta a `storage/app/public`
- [ ] La carpeta `storage/app/public/cards/` tiene permisos `755`
- [ ] Los archivos de imagen tienen permisos `644`
- [ ] El archivo `.htaccess` no bloquea el acceso a `/storage/`
- [ ] Las imágenes existen físicamente en el servidor
- [ ] La ruta en la base de datos es correcta (formato: `cards/uuid/profile_image/filename.jpg`)

---

## Contactar Soporte del Hosting

Si nada funciona, contacta al soporte de tu hosting y proporciona:

```
Tengo imágenes en: /home/oqppbydo/refery.app/storage/app/public/cards/
Con un enlace simbólico en: /home/oqppbydo/refery.app/public/storage

Las imágenes devuelven "403 Forbidden" al accederlas via:
https://refery.app/storage/cards/.../image.jpg

He configurado permisos 755 en directorios y 644 en archivos.
¿Pueden verificar la configuración de Apache/LiteSpeed?
```

---

## Prevención Futura

Para evitar este problema en el futuro:

1. **Después de cada deploy, ejecuta:**
   ```bash
   chmod -R 755 storage/app/public
   find storage/app/public -type f -exec chmod 644 {} \;
   ```

2. **O incluye en tu script de deploy:**
   - El script `fix-storage-permissions.php`
   - Ejecútalo automáticamente después de subir archivos

3. **Verifica que el proceso de subida de PHP tenga permisos correctos**
   - En `config/filesystems.php`, el disco `public` debe tener:
   ```php
   'visibility' => 'public',
   ```

---

**¿Funcionó?** Elimina los scripts de diagnóstico por seguridad.
