<?php

namespace TLabsCo\CommandScheduler\Console\Commands;

use Illuminate\Console\Command;
use TLabsCo\CommandScheduler\Core\Scheduling\Application\Services\ScheduleRegistrar;
use TLabsCo\CommandScheduler\Models\ScheduledTask;

/**
 * Command to sync discovered schedulers with the database.
 */
class SchedulerSyncCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'scheduler:sync
                            {--force : Force sync even if no changes detected}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync discovered scheduled commands with the database';

    /**
     * Execute the console command.
     */
    public function handle(ScheduleRegistrar $registrar): int
    {
        $this->info('🔍 Discovering scheduled commands...');

        $force = (bool) $this->option('force');
        $synced = $registrar->syncWithDatabase($force);

        $this->displayResults($synced);

        $this->info('');
        $this->info('✅ Sync completed successfully!');

        return self::SUCCESS;
    }

    /**
     * Display sync results.
     *
     * @param  array<string, array<int, ScheduledTask>>  $synced
     */
    protected function displayResults(array $synced): void
    {
        $this->info('');

        // Display created tasks
        if (! empty($synced['created'])) {
            $this->line('<fg=green>Created Tasks:</>');
            foreach ($synced['created'] as $task) {
                $this->line("  • {$task->name} <fg=gray>({$task->signature})</>");
            }
            $this->info('');
        }

        // Display updated tasks
        if (! empty($synced['updated'])) {
            $this->line('<fg=yellow>Updated Tasks:</>');
            foreach ($synced['updated'] as $task) {
                $this->line("  • {$task->name} <fg=gray>({$task->signature})</>");
            }
            $this->info('');
        }

        // Display deleted tasks
        if (! empty($synced['deleted'])) {
            $this->line('<fg=red>Deleted Tasks:</>');
            foreach ($synced['deleted'] as $task) {
                $this->line("  • {$task->name} <fg=gray>({$task->signature})</>");
            }
            $this->info('');
        }

        // Display summary
        $totalCreated = count($synced['created']);
        $totalUpdated = count($synced['updated']);
        $totalDeleted = count($synced['deleted']);
        $totalUnchanged = count($synced['unchanged']);

        $this->table(
            ['Status', 'Count'],
            [
                ['<fg=green>Created</>', $totalCreated],
                ['<fg=yellow>Updated</>', $totalUpdated],
                ['<fg=red>Deleted</>', $totalDeleted],
                ['<fg=blue>Unchanged</>', $totalUnchanged],
            ]
        );
    }
}
