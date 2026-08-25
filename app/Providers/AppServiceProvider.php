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
        // Force les URLs générées par Laravel en HTTPS en production.
        // Nécessaire car Render fait du SSL termination : le trafic externe
        // est en HTTPS, mais Laravel reçoit la requête en HTTP en interne,
        // ce qui causait des erreurs "Mixed Content" sur les assets (CSS/JS).
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }
    }
}