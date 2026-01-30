@echo off
echo.
echo ================================================
echo        ONCC SENEGAL - DEMARRAGE SERVEUR
echo ================================================
echo.

cd /d "%~dp0"

echo [1/6] Nettoyage des caches...
php artisan config:clear >nul 2>&1
php artisan cache:clear >nul 2>&1
php artisan view:clear >nul 2>&1
php artisan route:clear >nul 2>&1

echo [2/6] Arret des processus PHP...
taskkill /F /IM php.exe >nul 2>&1

echo [3/6] Verification base de donnees...
php artisan migrate:status

echo [4/6] Optimisation Laravel...
php artisan config:cache >nul 2>&1
php artisan route:cache >nul 2>&1
php artisan view:cache >nul 2>&1

echo [5/6] Compilation assets...
npm run build >nul 2>&1

echo [6/6] Demarrage serveur...
echo.
echo ✅ Serveur disponible sur: http://localhost:8080
echo ⚠️  Appuyez sur Ctrl+C pour arreter
echo.
php -S localhost:8080 -t public