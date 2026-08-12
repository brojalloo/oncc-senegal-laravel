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
        // Derrière le répartiteur de charge d'un hébergeur, l'application voit
        // souvent la requête en clair et génère alors des URL http://, ce qui
        // provoque des avertissements de contenu mixte et des redirections
        // vers HTTP. Hors développement local, on force le schéma.
        if (! $this->app->environment('local', 'testing')) {
            URL::forceScheme('https');
        }
    }
}
