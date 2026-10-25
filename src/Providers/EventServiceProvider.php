<?php

namespace TLabsCo\CommandScheduler\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use TLabsCo\CommandScheduler\Core\Scheduling\Application\Handlers\LogScheduledCommandRun;
use TLabsCo\CommandScheduler\Core\Scheduling\Application\Handlers\NotifyAdminOnSchedulerFailure;
use TLabsCo\CommandScheduler\Core\Scheduling\Application\Handlers\ResetScheduledTaskCache;
use TLabsCo\CommandScheduler\Events\ScheduledCommandExecuted;
use TLabsCo\CommandScheduler\Events\ScheduledTaskUpdated;
use TLabsCo\CommandScheduler\Events\SchedulerFailureEvent;

/**
 * Event service provider for the Command Scheduler package.
 */
class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        SchedulerFailureEvent::class => [
            NotifyAdminOnSchedulerFailure::class,
        ],
        ScheduledCommandExecuted::class => [
            LogScheduledCommandRun::class,
        ],
        ScheduledTaskUpdated::class => [
            ResetScheduledTaskCache::class,
        ],
    ];

    /**
     * Register any events for your application.
     */
    #[\Override]
    public function boot(): void
    {
        //
    }
}
