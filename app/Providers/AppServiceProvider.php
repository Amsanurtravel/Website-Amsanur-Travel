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
        // Canonical links, the sitemap and JSON-LD always use APP_URL in production,
        // even when the site is reached through www, http, the server IP or a proxy.
        if ($this->app->isProduction()) {
            URL::forceRootUrl(config('app.url'));
            URL::forceScheme(parse_url(config('app.url'), PHP_URL_SCHEME) ?: 'https');
        }
    }
}
