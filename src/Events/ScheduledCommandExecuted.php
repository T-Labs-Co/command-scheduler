<?php

namespace TLabsCo\CommandScheduler\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use TLabsCo\CommandScheduler\Core\Scheduling\Domain\Enums\SchedulerTriggerSource;
use TLabsCo\CommandScheduler\Models\ScheduledTask;

class ScheduledCommandExecuted
{
    use Dispatchable, SerializesModels;

    /**
     * Create a new event instance.
     *
     * @param  ScheduledTask  $task  The scheduled task being executed
     * @param  int  $duration  Execution duration in milliseconds
     * @param  int  $exitCode  Command exit code
     * @param  string|null  $output  Command output
     * @param  int|null  $memoryUsage  Memory usage in bytes
     * @param  SchedulerTriggerSource  $triggeredBy  How the command was triggered
     * @param  string|null  $trackId  UUID for tracking execution instance
     */
    public function __construct(
        public ScheduledTask $task,
        public int $duration,
        public int $exitCode,
        public ?string $output = null,
        public ?int $memoryUsage = null,
        public SchedulerTriggerSource $triggeredBy = SchedulerTriggerSource::SCHEDULE,
        public ?string $trackId = null
    ) {}
}
