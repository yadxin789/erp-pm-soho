<?php

namespace App\Erp\Providers;

use Illuminate\Support\ServiceProvider;

class ErpServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(base_path('routes/erp.php'));
        $this->loadViewsFrom(resource_path('views/erp'), 'erp');
        $this->loadMigrationsFrom(database_path('migrations/erp'));
    }
}