<?php

namespace TLabsCo\CommandScheduler\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use TLabsCo\CommandScheduler\Models\ScheduledTask;

/**
 * Event fired when a scheduled command fails.
 */
class SchedulerFailureEvent
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public ScheduledTask $task,
        public ?string $output = null,
        public ?int $exitCode = null
    ) {}

    /**
     * Check if consecutive failure threshold is reached.
     */
    public function shouldNotifyAdmin(): bool
    {
        $threshold = config('command-scheduler.notifications.notify_after_consecutive_failures', 3);

        return $this->task->failure_count >= $threshold;
    }
}
