<?php

namespace TLabsCo\CommandScheduler\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;
use TLabsCo\CommandScheduler\Models\ScheduledTask;

/**
 * Command to list all scheduled tasks.
 */
class SchedulerListCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'scheduler:list
                            {--enabled : Show only enabled tasks}
                            {--disabled : Show only disabled tasks}
                            {--window= : Filter by run window}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'List all scheduled tasks';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $query = ScheduledTask::query()->byPriority();

        // Apply filters
        if ($this->option('enabled')) {
            $query->enabled();
        }

        if ($this->option('disabled')) {
            $query->disabled();
        }

        $window = $this->option('window');
        if (is_string($window) && $window !== '') {
            $query->byRunWindow($window);
        }

        $tasks = $query->get();

        if ($tasks->isEmpty()) {
            $this->warn('No scheduled tasks found.');

            return self::SUCCESS;
        }

        $this->displayTasksTable($tasks);

        return self::SUCCESS;
    }

    /**
     * Display tasks in a table format.
     *
     * @param  Collection<int, ScheduledTask>  $tasks
     */
    protected function displayTasksTable(Collection $tasks): void
    {
        $rows = $tasks->map(fn (ScheduledTask $task) => [
            $this->formatStatus($task->enabled),
            $task->name,
            $task->signature,
            $task->expression,
            $this->formatOptions($task->options),
            $task->run_window->value,
            $this->formatEnvironments($task->environments),
            $task->priority,
            $task->run_count,
            $task->failure_count,
            $task->last_run_at?->format('Y-m-d H:i') ?? '-',
        ])->toArray();

        $this->table(
            [
                'Status',
                'Name',
                'Signature',
                'Expression',
                'Options',
                'Run Window',
                'Environments',
                'Priority',
                'Runs',
                'Failures',
                'Last Run',
            ],
            $rows
        );

        $this->info('');
        $this->line('Total: '.$tasks->count().' tasks');
    }

    /**
     * Format environments for display.
     *
     * @param  array<string>|null  $environments
     */
    protected function formatEnvironments(?array $environments): string
    {
        if (empty($environments)) {
            return '<fg=blue>all</>';
        }

        return implode(', ', $environments);
    }

    /**
     * Format task status for display.
     */
    protected function formatStatus(bool $enabled): string
    {
        return $enabled
            ? '<fg=green>✓ Enabled</>'
            : '<fg=red>✗ Disabled</>';
    }

    /**
     * Format options for display.
     *
     * @param  array<string|int, mixed>|null  $options
     */
    protected function formatOptions(?array $options): string
    {
        if (empty($options)) {
            return '-';
        }

        $formatted = [];
        foreach ($options as $key => $value) {
            if (is_bool($value)) {
                if ($value) {
                    $formatted[] = is_int($key) ? $value : $key;
                }
            } elseif (is_int($key)) {
                $formatted[] = $value;
            } else {
                $formatted[] = "{$key}={$value}";
            }
        }

        return implode(', ', $formatted);
    }
}
