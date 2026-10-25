<?php

declare(strict_types=1);

namespace TLabsCo\CommandScheduler\Tests;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use TLabsCo\CommandScheduler\Models\ScheduledCommandRun;
use TLabsCo\CommandScheduler\Models\ScheduledTask;
use TLabsCo\CommandScheduler\Providers\CommandSchedulerServiceProvider;
use TLabsCo\CommandScheduler\Providers\EventServiceProvider;
use TLabsCo\CommandScheduler\Providers\RouteServiceProvider;

if (class_exists(\Orchestra\Testbench\TestCase::class)) {
    abstract class BaseTestCase extends \Orchestra\Testbench\TestCase {}
} else {
    abstract class BaseTestCase extends \Tests\TestCase {}
}

abstract class TestCase extends BaseTestCase
{
    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();

        if (! $this->app->providerIsLoaded(CommandSchedulerServiceProvider::class)) {
            $this->app->register(CommandSchedulerServiceProvider::class);
            $this->app->register(RouteServiceProvider::class);
            $this->app->register(EventServiceProvider::class);
        }

        if (! Schema::hasTable('cs_scheduled_tasks')) {
            foreach (File::allFiles(__DIR__.'/../src/Database/Migrations') as $migration) {
                (include $migration->getRealPath())->up();
            }
        } else {
            ScheduledCommandRun::query()->delete();
            ScheduledTask::query()->delete();
        }
    }

    protected function getPackageProviders($app): array
    {
        return [
            CommandSchedulerServiceProvider::class,
            RouteServiceProvider::class,
            EventServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app): void
    {
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);

        $app['config']->set('command-scheduler.enabled', true);
        $app['config']->set('command-scheduler.load_examples', false);
    }
}
