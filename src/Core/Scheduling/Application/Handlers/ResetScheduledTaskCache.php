<?php

namespace TLabsCo\CommandScheduler\Core\Scheduling\Application\Handlers;

use TLabsCo\CommandScheduler\Core\Scheduling\Application\Services\SchedulerManager;
use TLabsCo\CommandScheduler\Events\ScheduledTaskUpdated;

/**
 * Listener to reset scheduler cache when a scheduled task is updated.
 *
 * This ensures that any changes to scheduled tasks (enabled status,
 * priority, etc.) are immediately reflected in the scheduler.
 */
class ResetScheduledTaskCache
{
    /**
     * Create the event listener.
     */
    public function __construct(
        protected SchedulerManager $schedulerManager
    ) {}

    /**
     * Handle the event.
     */
    public function handle(ScheduledTaskUpdated $event): void
    {
        // Clear all scheduler-related caches to ensure changes are reflected
        $this->schedulerManager->clearCache();
    }
}
