<?php

namespace TLabsCo\CommandScheduler\Providers;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Route;
use Livewire\Component;

/**
 * Route service provider for the Command Scheduler package.
 */
class RouteServiceProvider extends ServiceProvider
{
    /**
     * Define your route model bindings, pattern filters, etc.
     */
    #[\Override]
    public function boot(): void
    {
        parent::boot();
    }

    /**
     * Define the routes for the application.
     */
    public function map(): void
    {
        $this->mapWebRoutes();
    }

    /**
     * Define the "web" routes for the application.
     */
    protected function mapWebRoutes(): void
    {
        if (! config('command-scheduler.dashboard.enabled', true)) {
            return;
        }

        if (! class_exists(Component::class)) {
            return;
        }

        Route::middleware(config('command-scheduler.dashboard.middleware', ['web', 'auth']))
            // ->prefix(config('command-scheduler.dashboard.prefix', 'admin/scheduler'))
            // ->name('scheduler.')
            ->group(__DIR__.'/../Routes/web.php');
    }
}
