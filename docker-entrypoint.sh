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

# Setup SQLite database file if DB_CONNECTION is sqlite (or fallback default)
mkdir -p /var/www/html/database
if [ ! -f /var/www/html/database/database.sqlite ]; then
    touch /var/www/html/database/database.sqlite
fi

# Ensure storage directories exist
mkdir -p /var/www/html/storage/framework/cache
mkdir -p /var/www/html/storage/framework/sessions
mkdir -p /var/www/html/storage/framework/views
mkdir -p /var/www/html/storage/logs

chown -R www-data:www-data /var/www/html/database /var/www/html/storage /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache /var/www/html/database

# Ensure APP_KEY exists
if [ -z "$APP_KEY" ]; then
    php artisan key:generate --force
fi

# Run database migrations and seeders automatically
php artisan migrate --force --seed || php artisan migrate --force

# Start Apache web server in foreground
exec apache2-foreground
