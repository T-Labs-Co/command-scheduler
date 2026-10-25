<?php

namespace TLabsCo\CommandScheduler\Providers;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\ServiceProvider;
use TLabsCo\CommandScheduler\Console\Commands\SchedulerListCommand;
use TLabsCo\CommandScheduler\Console\Commands\SchedulerPruneLogCommand;
use TLabsCo\CommandScheduler\Console\Commands\SchedulerRunCommand;
use TLabsCo\CommandScheduler\Console\Commands\SchedulerSyncCommand;
use TLabsCo\CommandScheduler\Core\Scheduling\Application\Discovery\AttributeSchedulerDiscovery;
use TLabsCo\CommandScheduler\Core\Scheduling\Application\Discovery\SchedulerDiscovery;
use TLabsCo\CommandScheduler\Core\Scheduling\Application\Services\ScheduleRegistrar;
use TLabsCo\CommandScheduler\Core\Scheduling\Application\Services\SchedulerManager;
use TLabsCo\CommandScheduler\Core\Scheduling\Domain\Contracts\RunWindowResolverInterface;
use TLabsCo\CommandScheduler\Core\Scheduling\Infrastructure\RunWindow\DefaultRunWindowResolver;
use TLabsCo\CommandScheduler\Core\Scheduling\Infrastructure\RunWindow\WorkingHoursRunWindowResolver;
use TLabsCo\CommandScheduler\Examples\ExampleAttributeCommand;
use TLabsCo\CommandScheduler\Examples\ExampleComposableCommand;
use TLabsCo\CommandScheduler\Examples\ExampleScheduledCommand;

/**
 * Main service provider for the Command Scheduler package.
 */
class CommandSchedulerServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    #[\Override]
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../../config/command-scheduler.php',
            'command-scheduler'
        );

        $this->registerServices();
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->registerLogChannel();

        $this->publishes([
            __DIR__.'/../../config/command-scheduler.php' => config_path('command-scheduler.php'),
        ], 'command-scheduler-config');

        $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations');

        $this->loadViewsFrom(__DIR__.'/../Resources/views', 'command-scheduler');

        $this->publishes([
            __DIR__.'/../Resources/views' => resource_path('views/vendor/command-scheduler'),
        ], 'command-scheduler-views');

        $this->publishes([
            __DIR__.'/../../resources/assets/css/scheduler.css' => resource_path('css/scheduler.css'),
        ], 'command-scheduler-assets');

        $this->publishes([
            __DIR__.'/../../resources/assets/js/scheduler.js' => resource_path('js/scheduler.js'),
        ], 'command-scheduler-assets');

        if ($this->app->runningInConsole()) {
            $this->commands([
                SchedulerSyncCommand::class,
                SchedulerListCommand::class,
                SchedulerRunCommand::class,
                SchedulerPruneLogCommand::class,
            ]);

            // Register example commands (for demonstration)
            if (config('command-scheduler.load_examples', false)) {
                $this->commands([
                    ExampleScheduledCommand::class,
                    ExampleAttributeCommand::class,
                    ExampleComposableCommand::class,
                ]);
            }
        }

        // Register scheduled commands with Laravel's scheduler
        $this->registerScheduledCommands();
    }

    /**
     * Register package services as singletons.
     */
    protected function registerServices(): void
    {
        $this->app->singleton(SchedulerDiscovery::class, fn ($app) => new SchedulerDiscovery);

        $this->app->singleton(AttributeSchedulerDiscovery::class, fn ($app) => new AttributeSchedulerDiscovery);

        $this->app->singleton(SchedulerManager::class, fn ($app) => new SchedulerManager(
            $app->make(SchedulerDiscovery::class),
            $app->make(AttributeSchedulerDiscovery::class)
        ));

        // Bind working hours resolver first
        $this->app->singleton(WorkingHoursRunWindowResolver::class);

        // Bind run window resolver - can be overridden by host app
        $this->app->bindIf(RunWindowResolverInterface::class, DefaultRunWindowResolver::class, true);

        $this->app->singleton(ScheduleRegistrar::class, fn ($app) => new ScheduleRegistrar(
            $app->make(SchedulerManager::class),
            $app->make(Schedule::class),
            $app->make(RunWindowResolverInterface::class)
        ));
    }

    /**
     * Register scheduled commands with Laravel's scheduler.
     */
    protected function registerScheduledCommands(): void
    {
        if (! config('command-scheduler.enabled', true)) {
            return;
        }

        // Check if tables exist before registering
        try {
            $registrar = $this->app->make(ScheduleRegistrar::class);
            $registrar->register();
        } catch (\Throwable $e) {
            // Tables don't exist yet (migrations not run)
            // Silently fail during installation/migration
            if (! str_contains($e->getMessage(), "doesn't exist") && ! str_contains($e->getMessage(), 'no such table')) {
                throw $e;
            }
        }
    }

    /**
     * Register a custom log channel for notifications.
     */
    protected function registerLogChannel(): void
    {
        config(['logging.channels.command_scheduler' => config('command-scheduler.logging')]);
    }
}
