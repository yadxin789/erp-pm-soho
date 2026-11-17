<?php

namespace App\Soho\Providers;

use Illuminate\Support\ServiceProvider;

class SohoServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(base_path('routes/soho.php'));
        $this->loadViewsFrom(resource_path('views/soho'), 'soho');
        $this->loadMigrationsFrom(database_path('migrations/soho'));
    }
}