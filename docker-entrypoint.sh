#!/bin/bash
set -e

# Adapt Apache port to Render dynamic $PORT
PORT=${PORT:-80}
sed -i "s/Listen 80/Listen ${PORT}/g" /etc/apache2/ports.conf
sed -i "s/<VirtualHost \*:80>/<VirtualHost \*:${PORT}>/g" /etc/apache2/sites-available/000-default.conf

# Tell Apache that HTTPS is active when behind Render proxy
if ! grep -q "SetEnvIf X-Forwarded-Proto" /etc/apache2/sites-available/000-default.conf; then
    sed -i '/<VirtualHost/a \    SetEnvIf X-Forwarded-Proto https HTTPS=on' /etc/apache2/sites-available/000-default.conf
fi

# Detect persistent database (PostgreSQL on Render)
if [ -n "$DATABASE_URL" ]; then
    export DB_CONNECTION=pgsql
fi

# Setup SQLite fallback database file only if using sqlite
if [ "${DB_CONNECTION:-sqlite}" = "sqlite" ] && [ -z "$DATABASE_URL" ]; then
    mkdir -p /var/www/html/database
    if [ ! -f /var/www/html/database/database.sqlite ]; then
        touch /var/www/html/database/database.sqlite
    fi
fi

# Ensure all storage directories exist for uploads, livewire temporary files, and cache
mkdir -p /var/www/html/storage/app/public \
         /var/www/html/storage/app/private \
         /var/www/html/storage/app/livewire-tmp \
         /var/www/html/storage/app/private/livewire-tmp \
         /var/www/html/storage/framework/cache \
         /var/www/html/storage/framework/sessions \
         /var/www/html/storage/framework/views \
         /var/www/html/storage/logs

# Set full permissions so Apache (www-data) can read/write files and uploads
chown -R www-data:www-data /var/www/html/database /var/www/html/storage /var/www/html/bootstrap/cache
chmod -R 777 /var/www/html/storage /var/www/html/bootstrap/cache /var/www/html/database

# Ensure public storage symlink exists
php artisan storage:link --force || true

# Set fallback APP_URL if not provided
if [ -z "$APP_URL" ]; then
    export APP_URL="${RENDER_EXTERNAL_URL:-https://socializaciones.onrender.com}"
fi

# Ensure APP_KEY exists
if [ -z "$APP_KEY" ]; then
    php artisan key:generate --force
fi

# Run database migrations and seeders automatically
php artisan migrate --force --seed || php artisan migrate --force

# Start Apache web server in foreground
exec apache2-foreground
