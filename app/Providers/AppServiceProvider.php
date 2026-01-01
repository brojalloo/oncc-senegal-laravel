<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;

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
        // Fix pour MySQL < 5.7.7 et MariaDB < 10.2.2
        // Seulement si on n'est pas en train de cacher la config
        if (!$this->app->runningInConsole() || !in_array('config:cache', $_SERVER['argv'] ?? [])) {
            Schema::defaultStringLength(191);
        }
    }
}
