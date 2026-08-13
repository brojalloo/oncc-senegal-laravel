<?php

namespace App\Providers;

use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Le protocole d'origine est normalement rétabli par TrustProxies, à
        // partir de X-Forwarded-Proto : les URL générées sont alors correctes
        // sans rien forcer, et un déploiement en HTTP simple continue de
        // fonctionner.
        //
        // FORCE_HTTPS reste disponible pour les rares plateformes qui ne
        // transmettent pas cet en-tête. Ne l'activez pas si le conteneur est
        // servi en clair : toutes les URL deviendraient injoignables.
        if (config('app.force_https')) {
            URL::forceScheme('https');
        }
    }
}
