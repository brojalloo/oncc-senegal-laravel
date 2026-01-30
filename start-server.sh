#!/bin/bash

echo ""
echo "================================================"
echo "        ONCC SENEGAL - SERVEUR START"
echo "================================================"
echo ""

cd "$(dirname "$0")"

echo "[1/6] Cleaning caches..."
php artisan config:clear >/dev/null 2>&1
php artisan cache:clear >/dev/null 2>&1
php artisan view:clear >/dev/null 2>&1
php artisan route:clear >/dev/null 2>&1

echo "[2/6] Stopping PHP processes..."
pkill -f "php.*serve" >/dev/null 2>&1

echo "[3/6] Checking database..."
php artisan migrate:status

echo "[4/6] Optimizing Laravel..."
php artisan config:cache >/dev/null 2>&1
php artisan route:cache >/dev/null 2>&1
php artisan view:cache >/dev/null 2>&1

echo "[5/6] Building assets..."
npm run build >/dev/null 2>&1

echo "[6/6] Starting server..."
echo ""
echo "✅ Server available at: http://localhost:8080"
echo "⚠️  Press Ctrl+C to stop"
echo ""
php -S localhost:8080 -t public