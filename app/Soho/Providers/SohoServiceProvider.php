<?php

namespace App\Soho\Providers;

use Illuminate\Support\ServiceProvider;

class SohoServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->extend('view', function ($factory) {
            return new \App\Soho\Support\SohoViewFactory(
                $factory->getEngineResolver(),
                $factory->getFinder(),
                $factory->getDispatcher()
            );
        });
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(base_path('routes/soho.php'));
        $this->loadRoutesFrom(base_path('routes/soho-api.php'));
        $this->loadViewsFrom(resource_path('views/soho'), 'soho');
        $this->loadMigrationsFrom(database_path('migrations/soho'));
    }
}