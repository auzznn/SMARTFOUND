#!/bin/sh
set -e

# 1. Run migrations and database seeding
echo "[Entrypoint] Checking database availability & running migrations..."
php database/migrate.php

# 2. Execute the container's main command (e.g. apache2-foreground)
echo "[Entrypoint] Starting web server..."
exec "$@"
