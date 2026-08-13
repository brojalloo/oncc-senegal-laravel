<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * Define your route model bindings, pattern filters, and other route configuration.
     */
    public function boot(): void
    {
        $this->registerHealthCheckRoute();

        Route::middleware('web')
            ->group(base_path('routes/web.php'));
    }

    /**
     * Sonde d'état, hors du groupe « web ».
     *
     * Le conteneur l'interroge toutes les 30 secondes. Passée par le groupe
     * « web », chaque appel démarrerait une session — soit, avec
     * SESSION_DRIVER=database, près de trois mille lignes ajoutées par jour
     * dans la table `sessions`, sans que personne ne s'y connecte jamais.
     *
     * Sans session, sans CSRF et sans accès à la base : la sonde répond même
     * lorsque la base est injoignable, ce qui distingue « le serveur web est
     * mort » de « la base est en panne ».
     */
    private function registerHealthCheckRoute(): void
    {
        Route::get('/up', fn () => response('OK', 200)
            ->header('Content-Type', 'text/plain'))
            ->name('health');
    }
}
