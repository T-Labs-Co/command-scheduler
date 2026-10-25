<?php

namespace TLabsCo\CommandScheduler\Core\Scheduling\Application\Services;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;
use Symfony\Component\Console\Output\OutputInterface;
use TLabsCo\CommandScheduler\Core\Scheduling\Domain\Enums\SchedulerTriggerSource;
use TLabsCo\CommandScheduler\Events\ScheduledCommandExecuted;
use TLabsCo\CommandScheduler\Models\ScheduledTask;

/**
 * Service to run scheduled tasks manually and track execution.
 */
class TaskRunner
{
    /**
     * Create a singleton instance.
     */
    public static function make(): static
    {
        return app(static::class);
    }

    /**
     * Run a scheduled task.
     *
     * @param  ScheduledTask  $task  The task to run
     * @param  SchedulerTriggerSource  $source  The trigger source (default: MANUAL)
     * @param  OutputInterface|null  $outputBuffer  Optional output buffer for console commands
     * @return array{exit_code: int, output: string, duration: int, memory_usage: int, track_id: string}
     */
    public function run(ScheduledTask $task, SchedulerTriggerSource $source = SchedulerTriggerSource::MANUAL, ?OutputInterface $outputBuffer = null): array
    {
        $startTime = microtime(true);
        $memoryStart = memory_get_usage();

        try {
            $options = $task->options ?: [];
            $normalizedOptions = normalize_command_options($options);

            $artisanOptions = [];
            foreach ($normalizedOptions as $key => $value) {
                if (is_int($key) && is_string($value) && str_starts_with($value, '-')) {
                    $artisanOptions[$value] = true;
                } else {
                    $artisanOptions[$key] = $value;
                }
            }

            $exitCode = Artisan::call($task->signature, $artisanOptions, $outputBuffer);
            $output = Artisan::output();
        } catch (\Throwable $e) {
            $output = $e->getMessage();
            $exitCode = 1;
        }

        $endTime = microtime(true);
        $duration = (int) (($endTime - $startTime) * 1000);
        $memoryUsage = memory_get_usage() - $memoryStart;
        $trackId = Str::uuid7()->toString();

        event(new ScheduledCommandExecuted(
            task: $task,
            duration: $duration,
            exitCode: $exitCode,
            output: $output,
            memoryUsage: $memoryUsage,
            triggeredBy: $source,
            trackId: $trackId
        ));

        return [
            'exit_code' => $exitCode,
            'output' => $output,
            'duration' => $duration,
            'memory_usage' => $memoryUsage,
            'track_id' => $trackId,
        ];
    }
}
