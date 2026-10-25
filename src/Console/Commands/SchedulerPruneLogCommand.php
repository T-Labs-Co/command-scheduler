<?php

namespace TLabsCo\CommandScheduler\Console\Commands;

use Illuminate\Console\Command;
use TLabsCo\CommandScheduler\Models\ScheduledCommandRun;

/**
 * Command to prune logs of scheduled command runs.
 */
class SchedulerPruneLogCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'scheduler:prune-logs {--days= : Number of days of logs to retain}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Prune outdated logs of scheduled command runs.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $days = $this->option('days') ? (int) $this->option('days') : null;

        $count = ScheduledCommandRun::query()
            ->outdated($days)
            ->delete();

        $this->info("Pruned {$count} outdated scheduled command run logs.");

        return self::SUCCESS;
    }
}
