#!/bin/bash

echo ""
echo "================================================"
echo "        ONCC SENEGAL - SERVEUR START"
echo "================================================"
echo ""

cd "$(dirname "$0")"

echo "[1/5] Cleaning caches..."
php artisan config:clear >/dev/null 2>&1
php artisan cache:clear >/dev/null 2>&1
php artisan view:clear >/dev/null 2>&1
php artisan route:clear >/dev/null 2>&1

echo "[2/5] Stopping PHP processes..."
pkill -f "php.*serve" >/dev/null 2>&1

echo "[3/5] Checking database..."
php artisan migrate:status

echo "[4/5] Building assets..."
npm run build >/dev/null 2>&1

# Pas de config:cache ici : en développement, la configuration mise en cache
# fige le .env, les modifications n'ont plus d'effet et env() renvoie null.
# La mise en cache appartient au démarrage du conteneur (docker/entrypoint.sh).

echo "[5/5] Starting server..."
echo ""
echo "✅ Server available at: http://localhost:8080"
echo "⚠️  Press Ctrl+C to stop"
echo ""
php -S localhost:8080 -t public