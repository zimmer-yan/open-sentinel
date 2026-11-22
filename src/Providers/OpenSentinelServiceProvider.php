<?php

namespace OpenSentinel\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class OpenSentinelServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Merge package configuration with application configuration.
        $this->mergeConfigFrom(
            __DIR__ . '/../../config/open-sentinel.php',
            'open-sentinel'
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {

        // Register migrations.
        $this->loadMigrationsFrom(
            __DIR__ . '/../../database/migrations'
        );

        // Register web routes.
        $this->loadRoutesFrom(
            __DIR__ . '/../../routes/web.php'
        );

        // Register Blade views.
        $this->loadViewsFrom(
            __DIR__ . '/../../resources/views',
            'open-sentinel'
        );

        $this->publishes([
            __DIR__ . '/../../public/build/assets/open-sentinel.css'
                => public_path('vendor/open-sentinel/css/open-sentinel.css'),

            __DIR__ . '/../../public/build/assets/open-sentinel-app.js'
                => public_path('vendor/open-sentinel/js/open-sentinel-app.js'),
        ], 'open-sentinel-assets');

        // Allow configuration publishing.
        $this->publishes([
            __DIR__ . '/../../config/open-sentinel.php' =>
                config_path('open-sentinel.php'),
        ], 'open-sentinel-config');

        // Allow views to be overridden.
        $this->publishes([
            __DIR__ . '/../../resources/views' =>
                resource_path('views/vendor/open-sentinel'),
        ], 'open-sentinel-views');

        Blade::anonymousComponentPath(
            __DIR__ . '/../../resources/views/components',
            'open-sentinel'
        );
    }
}
