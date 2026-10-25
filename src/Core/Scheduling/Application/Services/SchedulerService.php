<?php

namespace TLabsCo\CommandScheduler\Core\Scheduling\Application\Services;

use Illuminate\Support\Collection;
use TLabsCo\CommandScheduler\Models\ScheduledCommandRun;
use TLabsCo\CommandScheduler\Models\ScheduledTask;

class SchedulerService
{
    public static function make(): self
    {
        return app(self::class);
    }

    /**
     * Get a scheduled task by signature.
     */
    public function getTaskBySignature(string $signature): ?ScheduledTask
    {
        return ScheduledTask::query()
            ->where('signature', $signature)
            ->first();
    }

    /**
     * Check if a task is enabled.
     */
    public function isTaskEnabled(string $signature): bool
    {
        $task = $this->getTaskBySignature($signature);

        return $task && $task->enabled;
    }

    /**
     * Enable a scheduled task.
     */
    public function enableTask(string $signature): bool
    {
        $task = $this->getTaskBySignature($signature);

        if (! $task) {
            return false;
        }

        $task->enable();

        return true;
    }

    /**
     * Disable a scheduled task.
     */
    public function disableTask(string $signature): bool
    {
        $task = $this->getTaskBySignature($signature);

        if (! $task) {
            return false;
        }

        $task->disable();

        return true;
    }

    /**
     * Update task priority.
     */
    public function updatePriority(string $signature, int $priority): bool
    {
        $task = $this->getTaskBySignature($signature);

        if (! $task) {
            return false;
        }

        $task->priority = $priority;
        $task->save();

        return true;
    }

    /**
     * Get statistics for the scheduler dashboard.
     *
     * @return array<string, mixed>
     */
    public function getStatistics(): array
    {
        $allTasks = ScheduledTask::query()->get();
        $enabledTasks = $allTasks->where('enabled', true);
        $disabledTasks = $allTasks->where('enabled', false);

        return [
            'total_tasks' => $allTasks->count(),
            'enabled_tasks' => $enabledTasks->count(),
            'disabled_tasks' => $disabledTasks->count(),
            'total_runs' => $allTasks->sum('run_count'),
            'total_failures' => $allTasks->sum('failure_count'),
            'average_success_rate' => $allTasks->avg(fn ($task) => $task->successRate()),
            'tasks_with_failures' => $allTasks->where('failure_count', '>', 0)->count(),
        ];
    }

    /**
     * Get top 10 tasks with highest duration.
     *
     * @return Collection<int, ScheduledCommandRun>
     */
    public function getTopDurationTasks(int $limit = 10): Collection
    {
        return ScheduledCommandRun::query()
            ->with('scheduledTask')
            ->orderByDesc('duration')
            ->limit($limit)
            ->get();
    }

    /**
     * Get top 10 tasks with highest memory usage.
     *
     * @return Collection<int, ScheduledCommandRun>
     */
    public function getTopMemoryTasks(int $limit = 10): Collection
    {
        return ScheduledCommandRun::query()
            ->with('scheduledTask')
            ->whereNotNull('memory_usage')
            ->orderByDesc('memory_usage')
            ->limit($limit)
            ->get();
    }

    /**
     * Get upcoming runs (tasks sorted by next_run attribute).
     *
     * @return Collection<int, ScheduledTask>
     */
    public function getUpcomingRuns(int $limit = 10): Collection
    {
        return ScheduledTask::query()
            ->enabled()
            ->get()
            ->sortBy('next_run')
            ->take($limit);
    }

    /**
     * Get latest runs (recent command executions).
     *
     * @return Collection<int, ScheduledCommandRun>
     */
    public function getLatestRuns(int $limit = 10): Collection
    {
        return ScheduledCommandRun::query()
            ->with('scheduledTask')
            ->orderByDesc('started_at')
            ->limit($limit)
            ->get();
    }
}
