<?php

namespace TLabsCo\CommandScheduler\Core\Scheduling\Application\Services;

use Illuminate\Console\Scheduling\Event;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Str;
use TLabsCo\CommandScheduler\Core\Scheduling\Domain\Contracts\RunWindowResolverInterface;
use TLabsCo\CommandScheduler\Core\Scheduling\Domain\Enums\SchedulerTriggerSource;
use TLabsCo\CommandScheduler\Core\Scheduling\Domain\ValueObjects\ScheduleDefinition;
use TLabsCo\CommandScheduler\Events\ScheduledCommandExecuted;
use TLabsCo\CommandScheduler\Events\SchedulerFailureEvent;
use TLabsCo\CommandScheduler\Models\ScheduledTask;

/**
 * Service for registering scheduled commands with Laravel's scheduler.
 */
class ScheduleRegistrar
{
    public function __construct(
        protected SchedulerManager $manager,
        protected Schedule $schedule,
        protected RunWindowResolverInterface $runWindowResolver
    ) {}

    /**
     * Create a singleton instance.
     */
    public static function make(): static
    {
        return app(static::class);
    }

    /**
     * Register all enabled scheduled commands with Laravel's scheduler.
     */
    public function register(): void
    {
        $enabledTasks = $this->manager->getEnabledTasks();

        foreach ($enabledTasks as $task) {
            $this->registerTask($task);
        }
    }

    /**
     * Register a single scheduled task.
     */
    protected function registerTask(ScheduledTask $task): void
    {
        // Skip if command should not run in current environment
        if (! empty($task->environments) && ! in_array(app()->environment(), $task->environments)) {
            return;
        }

        $options = $task->options ?: [];
        $normalizedOptions = normalize_command_options($options);

        $event = $this->schedule
            ->command($task->signature, $normalizedOptions)
            ->cron($task->expression);

        // Apply timezone if specified
        if ($task->timezone) {
            $event->timezone($task->timezone);
        }

        // Apply without overlapping constraint
        if ($task->without_overlapping) {
            $event->withoutOverlapping();
        }

        // Apply one server constraint
        if ($task->on_one_server) {
            $event->onOneServer();
        }

        // Apply output logging
        $outputLog = $task->output_log ?? 'logs/cron.log';
        $event->appendOutputTo(storage_path($outputLog));

        // Apply run window constraints
        $this->applyRunWindowConstraints($event, $task);

        // Track execution
        $this->trackExecution($event, $task);
    }

    /**
     * Apply run window constraints to the scheduled event.
     */
    protected function applyRunWindowConstraints(Event $event, ScheduledTask $task): void
    {
        $event->when(fn () => $this->runWindowResolver->shouldRun($task->run_window));
    }

    /**
     * Track command execution and update task statistics.
     */
    protected function trackExecution(Event $event, ScheduledTask $task): void
    {
        $startTime = 0;
        $memoryStart = 0;
        // Generate a unique ID for this execution instance (optional, can be used for logging/tracing) - avoid duplicate logging
        $uuid = Str::uuid7()->toString();

        $event->before(function () use ($task, &$startTime, &$memoryStart): void {
            $startTime = microtime(true);
            $memoryStart = memory_get_usage();
            $task->last_run_at = now();
            $task->save();
        });

        $event->after(function () use ($task, &$startTime, &$memoryStart, $uuid): void {
            $duration = (int) ((microtime(true) - $startTime) * 1000);
            $memoryUsage = max(0, memory_get_usage() - $memoryStart);

            event(new ScheduledCommandExecuted(
                task: $task,
                duration: $duration,
                exitCode: 0, // Laravel doesn't easily provide exit code in 'after' hook without more complex setup
                memoryUsage: $memoryUsage,
                triggeredBy: SchedulerTriggerSource::SCHEDULE,
                trackId: $uuid
            ));
        });

        $event->onFailure(function () use ($task, &$startTime, &$memoryStart, $uuid): void {
            $duration = (int) ((microtime(true) - $startTime) * 1000);
            $memoryUsage = max(0, memory_get_usage() - $memoryStart);

            $task->failure_count++;
            $task->save();

            // Dispatch execution event with failure code
            event(new ScheduledCommandExecuted(
                task: $task,
                duration: $duration,
                exitCode: 1,
                memoryUsage: $memoryUsage,
                triggeredBy: SchedulerTriggerSource::SCHEDULE,
                trackId: $uuid
            ));

            // Dispatch failure event for notifications
            event(new SchedulerFailureEvent($task));
        });
    }

    /**
     * Sync discovered commands with the database.
     *
     * This ensures the database reflects the current state of the codebase.
     *
     * @return array{created: array<int, ScheduledTask>, updated: array<int, ScheduledTask>, unchanged: array<int, ScheduledTask>, deleted: array<int, ScheduledTask>}
     */
    public function syncWithDatabase(bool $force = false): array
    {
        // Always clear stale cache before a fresh discovery to ensure newly added commands are found.
        $this->manager->clearCache();
        $discovered = $this->manager->discoverAll();
        $synced = [
            'created' => [],
            'updated' => [],
            'unchanged' => [],
            'deleted' => [],
        ];

        $discoveredSignatures = [];

        foreach ($discovered as $className => $info) {
            $result = $this->syncCommand($className, $info);

            if ($result['task'] !== null) {
                if (isset($synced[$result['action']])) {
                    $synced[$result['action']][] = $result['task'];
                }
                $discoveredSignatures[] = $result['task']->signature;
            }
        }

        // Remove tasks from database that are no longer discovered in code
        $orphanedTasks = ScheduledTask::query()
            ->whereNotIn('signature', $discoveredSignatures)
            ->get();

        /** @var ScheduledTask $task */
        foreach ($orphanedTasks as $task) {
            $synced['deleted'][] = $task;
            $task->delete();
        }

        // Clear cache after syncing
        $this->manager->clearCache();

        return $synced;
    }

    /**
     * Sync a single command with the database.
     *
     * @param  array<string, mixed>  $info
     * @return array{action: string, task: ScheduledTask|null}
     */
    protected function syncCommand(string $className, array $info): array
    {
        $signature = $info['signature'];

        // Get configuration from interface or attribute
        $config = $info['config'] ?? null;
        $attributeData = $info['attribute'] ?? null;

        if ($config instanceof ScheduleDefinition) {
            $data = $this->prepareDataFromDefinition($className, $signature, $config);
        } elseif ($attributeData !== null) {
            $data = $this->prepareDataFromAttribute($className, $signature, $attributeData);
        } else {
            return ['action' => 'unchanged', 'task' => null];
        }

        // Ensure expression is set to default if not provided
        if (empty($data['expression'])) {
            $data['expression'] = '*/5 * * * *';
        }

        // Find or create task
        $task = ScheduledTask::query()->firstOrNew(['signature' => $signature]);
        $isNew = ! $task->exists;

        // Only update code-driven fields, preserve DB runtime fields
        $codeFields = [
            'name',
            'class_name',
            'description',
            'expression',
            'options',
            'priority',
            'run_window',
            'environments',
            'timezone',
            'without_overlapping',
            'on_one_server',
            'output_log',
            'metadata',
        ];

        $hasChanges = false;
        foreach ($codeFields as $field) {
            if (isset($data[$field]) && $task->{$field} !== $data[$field]) {
                $task->{$field} = $data[$field];
                $hasChanges = true;
            }
        }

        // Set enabled=true for new tasks only
        if ($isNew) {
            $task->enabled = $data['enabled'] ?? true;
        }

        if ($isNew || $hasChanges) {
            $task->save();

            return [
                'action' => $isNew ? 'created' : 'updated',
                'task' => $task,
            ];
        }

        return ['action' => 'unchanged', 'task' => $task];
    }

    /**
     * Prepare task data from ScheduleDefinition.
     *
     * @return array<string, mixed>
     */
    protected function prepareDataFromDefinition(
        string $className,
        string $signature,
        ScheduleDefinition $definition
    ): array {
        return array_merge($definition->toArray(), [
            'signature' => $signature,
            'class_name' => $className,
        ]);
    }

    /**
     * Prepare task data from attribute.
     *
     * @param  array<string, mixed>  $attributeData
     * @return array<string, mixed>
     */
    protected function prepareDataFromAttribute(
        string $className,
        string $signature,
        array $attributeData
    ): array {
        return array_merge($attributeData, [
            'signature' => $signature,
            'class_name' => $className,
        ]);
    }
}
