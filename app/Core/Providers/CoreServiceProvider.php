<?php

namespace App\Core\Providers;

use Illuminate\Support\ServiceProvider;

class CoreServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(base_path('routes/core.php'));
        $this->loadViewsFrom(resource_path('views/core'), 'core');
        $this->loadMigrationsFrom(database_path('migrations/core'));
    }
}