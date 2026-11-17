<?php

namespace App\Pm\Providers;

use Illuminate\Support\ServiceProvider;

class PmServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(base_path('routes/pm.php'));
        $this->loadViewsFrom(resource_path('views/pm'), 'pm');
        $this->loadMigrationsFrom(database_path('migrations/pm'));
    }
}