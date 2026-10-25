<?php

namespace TLabsCo\CommandScheduler\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use TLabsCo\CommandScheduler\Models\ScheduledTask;

/**
 * Event dispatched when a scheduled task is updated.
 *
 * This event is fired whenever a ScheduledTask model is saved,
 * allowing listeners to react to changes (e.g., clearing caches).
 */
class ScheduledTaskUpdated
{
    use Dispatchable, SerializesModels;

    /**
     * Create a new event instance.
     *
     * @param  ScheduledTask  $task  The scheduled task that was updated
     */
    public function __construct(
        public ScheduledTask $task
    ) {}
}
