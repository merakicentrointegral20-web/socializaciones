#!/bin/bash
# Script de deploy — Xuri Apex (Siteground GoGeek, hosting compartido).
#
# Uso:
#   - Manual por SSH: ./deploy.sh (después de git pull)
#   - Automático: en Site Tools > Git, apunta el "Deploy Script" a este archivo
#     para que corra solo después de cada pull.
#
# Los assets de Vite (public/build) ya están commiteados en el repo — no se
# necesita Node/npm en el servidor.

set -e

cd "$(dirname "$0")"

echo "==> Instalando dependencias de Composer..."
composer install --no-dev --optimize-autoloader --no-interaction

echo "==> Corriendo migraciones..."
php artisan migrate --force

echo "==> Limpiando caché previa..."
php artisan optimize:clear

echo "==> Cacheando config, rutas, vistas y eventos..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

echo "==> Deploy completo."
