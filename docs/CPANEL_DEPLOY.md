# ReferyApp cPanel Deployment Guide

This guide prepares and deploys the Laravel + Inertia/Vue app on cPanel safely.

## 1) Server Requirements

- PHP `8.2+`
- MySQL `8+` or MariaDB `10.5+`
- Composer available on server (recommended)
- SSH access (recommended)
- Domain/subdomain already configured in cPanel

## 2) Domain Document Root

Set the domain document root to the Laravel `public` folder.

Example:

- App path: `/home/<cpanel-user>/referyAppV2`
- Document root: `/home/<cpanel-user>/referyAppV2/public`

Do not point the domain to project root.

## 3) Prepare a Production Package (local machine)

From project root:

```bash
chmod +x scripts/cpanel/package.sh
./scripts/cpanel/package.sh
```

This script:

- removes `public/hot` (critical for avoiding blank screen in production)
- installs prod dependencies
- builds Vite assets
- caches Laravel config/routes/views
- creates a zip file in `deploy/`

## 4) Upload to cPanel

1. Upload the generated zip (`deploy/referyapp-cpanel-*.zip`) to your app folder.
2. Extract it in `/home/<cpanel-user>/referyAppV2`.
3. Ensure these writable paths:
   - `storage/`
   - `bootstrap/cache/`

## 5) Configure `.env`

Use production values. Minimum:

```env
APP_NAME=ReferyApp
APP_ENV=production
APP_DEBUG=false
APP_URL=https://refery.app

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=YOUR_DB
DB_USERNAME=YOUR_USER
DB_PASSWORD=YOUR_PASSWORD

SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=database

FILESYSTEM_DISK=public
```

## 6) Fix Permissions (CRITICAL!)

Laravel needs write permissions on specific directories. Run this FIRST:

```bash
# Run the automated fix script
chmod +x scripts/cpanel/fix-permissions.sh
bash scripts/cpanel/fix-permissions.sh
```

Or manually:

```bash
# Ensure all required directories exist
mkdir -p storage/framework/{sessions,views,cache}
mkdir -p storage/framework/cache/data
mkdir -p bootstrap/cache

# Set correct permissions
find storage bootstrap/cache -type d -exec chmod 755 {} \;
find storage bootstrap/cache -type f -exec chmod 644 {} \;

# If you get "tempnam()" errors, use 775 instead:
chmod -R 775 storage
chmod -R 775 bootstrap/cache
```

## 7) Run Final Commands on Server

From project root (SSH terminal):

```bash
php artisan key:generate --force
php artisan migrate --force
php artisan storage:link || true
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

If your app uses queues:

```bash
php artisan queue:table
php artisan migrate --force
```

## 8) Important Production Checks

- `public/hot` must not exist.
- `public/build/manifest.json` must exist.
- `storage/app/public` must be linked to `public/storage`.
- Permissions are correct: `ls -la storage/framework/`
- Verify app with:
  - login/register page
  - Check for any HTTP 500 errors

## Troubleshooting

If you encounter errors (especially HTTP 500 or tempnam() errors):

- **With SSH access:** See [TROUBLESHOOTING.md](TROUBLESHOOTING.md)
- **Without SSH (cPanel only):** See [CPANEL_NO_SSH.md](CPANEL_NO_SSH.md) ⭐
  - dashboard
  - cards public URL (`https://refery.app/{username}`)
  - image uploads

## 8) Optional: Build without local packaging script

You can also run:

```bash
composer run deploy:cpanel
```

Then upload the whole prepared project (excluding local-only files).
