#!/bin/bash
# Fix Laravel permissions in cPanel shared hosting
# Run this script via SSH: bash scripts/cpanel/fix-permissions.sh

echo "================================================"
echo "Fixing Laravel permissions for cPanel..."
echo "================================================"

# Set correct ownership (change this to your cPanel username if needed)
# chown -R $USER:$USER .

# Fix directory permissions
echo "Setting directory permissions..."
find storage bootstrap/cache -type d -exec chmod 755 {} \;

# Fix file permissions
echo "Setting file permissions..."
find storage bootstrap/cache -type f -exec chmod 644 {} \;

# Ensure critical directories exist and are writable
echo "Creating and securing storage directories..."
mkdir -p storage/framework/{sessions,views,cache}
mkdir -p storage/framework/cache/data
mkdir -p storage/logs
mkdir -p bootstrap/cache

# Set writable permissions for Laravel
chmod -R 755 storage
chmod -R 755 bootstrap/cache

# More permissive if 755 doesn't work (try this if still getting errors)
# chmod -R 775 storage
# chmod -R 775 bootstrap/cache

# Clear all caches
echo "Clearing Laravel caches..."
php artisan cache:clear || true
php artisan config:clear || true
php artisan route:clear || true
php artisan view:clear || true
php artisan optimize:clear || true

# Rebuild caches
echo "Rebuilding caches..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "================================================"
echo "Permissions fixed!"
echo "================================================"
echo ""
echo "If you still get errors, try:"
echo "  chmod -R 775 storage"
echo "  chmod -R 775 bootstrap/cache"
echo ""
