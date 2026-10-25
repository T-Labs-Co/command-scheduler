<?php

namespace TLabsCo\CommandScheduler\Console\Commands;

use Illuminate\Console\Command;
use TLabsCo\CommandScheduler\Core\Scheduling\Application\Services\TaskRunner;
use TLabsCo\CommandScheduler\Core\Scheduling\Domain\Enums\SchedulerTriggerSource;
use TLabsCo\CommandScheduler\Models\ScheduledCommandRun;
use TLabsCo\CommandScheduler\Models\ScheduledTask;

/**
 * Command to manually run a scheduled task.
 */
class SchedulerRunCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'scheduler:run
                            {signature : The signature of the command to run}
                            {--ignore-window : Ignore run window constraints}
                            {--force : Force run even if task is disabled}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Manually run a scheduled command and track its execution';

    /**
     * Execute the console command.
     */
    public function handle(TaskRunner $taskRunner): int
    {
        $signature = (string) (is_scalar($this->argument('signature')) ? $this->argument('signature') : '');
        $task = ScheduledTask::query()
            ->where('signature', $signature)
            ->first();

        if (! $task) {
            $this->error("Task not found: {$signature}");

            return self::FAILURE;
        }

        // Check if task is enabled
        if (! $task->enabled && ! $this->option('force')) {
            $this->error('Task is disabled. Use --force to run anyway.');

            return self::FAILURE;
        }

        $this->info("Running: {$task->name}");
        $this->line("Signature: {$task->signature}");
        $this->line('Options: '.json_encode($task->options));
        $this->info('');

        $result = $taskRunner->run($task, SchedulerTriggerSource::MANUAL, $this->getOutput());

        $exitCode = $result['exit_code'];
        $success = $exitCode === 0;

        // Get the run record created by the listener (optional, for display)
        $run = ScheduledCommandRun::query()
            ->where('scheduled_task_id', $task->id)
            ->where('triggered_by', SchedulerTriggerSource::MANUAL->value)
            ->latest()
            ->first();

        $this->info('');
        if ($run) {
            $this->displayRunSummary($run, $success);
        } else {
            // Fallback if listener failed
            if ($success) {
                $this->info('✅ Command completed successfully');
            } else {
                $this->error('❌ Command failed');
            }
        }

        return $success ? self::SUCCESS : self::FAILURE;
    }

    /**
     * Display run summary.
     */
    protected function displayRunSummary(ScheduledCommandRun $run, bool $success): void
    {
        if ($success) {
            $this->info('✅ Command completed successfully');
        } else {
            $this->error('❌ Command failed');
        }

        $this->table(
            ['Metric', 'Value'],
            [
                ['Exit Code', $run->exit_code],
                ['Duration', $run->durationForHumans()],
                ['Memory Usage', $run->memoryUsageForHumans()],
                ['Started At', $run->started_at->format('Y-m-d H:i:s')],
                ['Finished At', $run->finished_at?->format('Y-m-d H:i:s') ?? '-'],
            ]
        );
    }
}
