#!/bin/bash
set -e

echo "=== [start.sh] Clearing config cache ==="
php artisan config:clear

echo "=== [start.sh] Running migrations ==="
php artisan migrate --force -v

echo "=== [start.sh] Caching config ==="
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "=== [start.sh] Starting Apache ==="
exec apache2-foreground