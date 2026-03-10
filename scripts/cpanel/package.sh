#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
OUTPUT_DIR="${ROOT_DIR}/deploy"
PACKAGE_NAME="referyapp-cpanel-$(date +%Y%m%d-%H%M%S).zip"
PACKAGE_PATH="${OUTPUT_DIR}/${PACKAGE_NAME}"

cd "${ROOT_DIR}"

echo "[1/6] Cleaning production-only artifacts..."
rm -f public/hot
php artisan optimize:clear >/dev/null

echo "[2/6] Installing PHP dependencies (no-dev)..."
composer install --no-dev --prefer-dist --optimize-autoloader

echo "[3/6] Installing Node dependencies..."
npm ci

echo "[4/6] Building frontend assets..."
npm run build

echo "[5/6] Caching Laravel bootstrap files..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "[6/6] Creating deployment package..."
mkdir -p "${OUTPUT_DIR}"
rm -f "${PACKAGE_PATH}"

zip -r "${PACKAGE_PATH}" . \
    -x ".git/*" \
    -x "**/.DS_Store" \
    -x "node_modules/*" \
    -x "tests/*" \
    -x "database/database.sqlite" \
    -x "storage/logs/*" \
    -x "storage/framework/cache/*" \
    -x "storage/framework/sessions/*" \
    -x "storage/framework/testing/*" \
    -x "storage/framework/views/*" \
    -x ".env" \
    -x ".env.*" \
    -x "phpunit.xml" \
    -x ".phpunit.result.cache" \
    -x "deploy/*"

echo "Package ready: ${PACKAGE_PATH}"
