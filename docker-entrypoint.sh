#!/bin/bash
set -e

echo "=== Starting ONCC Senegal Application ==="

# Create .env file from environment variables if it doesn't exist
if [ ! -f .env ]; then
    echo "Creating .env file from environment variables..."
    cat > .env << EOF
APP_NAME="${APP_NAME:-ONCC Sénégal}"
APP_ENV="${APP_ENV:-production}"
APP_KEY="${APP_KEY}"
APP_DEBUG="${APP_DEBUG:-false}"
APP_URL="${APP_URL:-http://localhost}"

LOG_CHANNEL=stack
LOG_LEVEL=debug

DB_CONNECTION=sqlite
DB_DATABASE=/app/database/database.sqlite

SESSION_DRIVER=cookie
SESSION_LIFETIME=120

CACHE_STORE=file
QUEUE_CONNECTION=sync
EOF
fi

echo "=== Environment configured ==="
cat .env

# Ensure storage directories exist and are writable
echo "=== Setting up storage directories ==="
mkdir -p storage/framework/cache/data
mkdir -p storage/framework/sessions
mkdir -p storage/framework/views
mkdir -p storage/logs
mkdir -p bootstrap/cache
chmod -R 775 storage bootstrap/cache

# Create SQLite database if not exists
echo "=== Setting up database ==="
touch database/database.sqlite
chmod 664 database/database.sqlite

# Clear caches
echo "=== Clearing caches ==="
php artisan config:clear || true
php artisan cache:clear || true
php artisan view:clear || true

# Run migrations
echo "=== Running migrations ==="
php artisan migrate --force || echo "Migration failed, continuing..."

# Seed database
echo "=== Seeding database ==="
php artisan db:seed --force || echo "Seeding failed, continuing..."

# Start server
echo "=== Starting PHP server on port ${PORT:-8080} ==="
exec php -S 0.0.0.0:${PORT:-8080} -t public public/index.php
