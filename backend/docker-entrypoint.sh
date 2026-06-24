#!/bin/sh
set -e

# Ensure the uploads directory exists and is owned by the webserver user (www-data)
# This prevents permission errors when Docker volumes are mounted
echo "[Entrypoint] Configuring upload directory permissions..."
mkdir -p /var/www/html/uploads
chown -R www-data:www-data /var/www/html/uploads
chmod -R 775 /var/www/html/uploads

# 1. Run migrations and database seeding
echo "[Entrypoint] Checking database availability & running migrations..."
php database/migrate.php

# 2. Execute the container's main command (e.g. apache2-foreground)
echo "[Entrypoint] Starting web server..."
exec "$@"
