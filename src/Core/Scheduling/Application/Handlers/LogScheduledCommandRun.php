<?php

namespace TLabsCo\CommandScheduler\Core\Scheduling\Application\Handlers;

use TLabsCo\CommandScheduler\Events\ScheduledCommandExecuted;
use TLabsCo\CommandScheduler\Models\ScheduledCommandRun;
use TLabsCo\CommandScheduler\Models\ScheduledTask;

class LogScheduledCommandRun
{
    /**
     * Handle the event.
     */
    public function handle(ScheduledCommandExecuted $event): void
    {
        $task = $event->task;
        $success = $event->exitCode === 0;

        // Create run record
        $scheduledCommandRun = ScheduledCommandRun::firstOrCreate(
            ['track_id' => $event->trackId],
            [
                'scheduled_task_id' => $task->id,
                'started_at' => now()->subMilliseconds($event->duration),
                'finished_at' => now(),
                'duration' => $event->duration,
                'exit_code' => $event->exitCode,
                'output' => $event->output,
                'memory_usage' => $event->memoryUsage,
                'triggered_by' => $event->triggeredBy,
            ]);

        // Update task statistics - if created
        if ($scheduledCommandRun->wasRecentlyCreated) {
            $this->updateTaskStatistics($task, $success, $event->duration);
        }
    }

    /**
     * Update task statistics.
     */
    protected function updateTaskStatistics(ScheduledTask $task, bool $success, int $duration): void
    {
        $task->run_count++;
        $task->last_run_at = now();

        if (! $success) {
            $task->failure_count++;
        }

        // Update average duration
        if ($task->average_duration === 0) {
            $task->average_duration = $duration;
        } else {
            // Weighted moving average
            $task->average_duration = (int) (
                ($task->average_duration * 0.8) + ($duration * 0.2)
            );
        }

        $task->save();
    }
}
