<?php

namespace TLabsCo\CommandScheduler\Examples;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use TLabsCo\CommandScheduler\Attributes\Scheduled;
use TLabsCo\CommandScheduler\Attributes\ScheduledLocalOnly;
use TLabsCo\CommandScheduler\Core\Scheduling\Domain\Enums\RunWindow;

/**
 * Example command using the Attribute pattern.
 *
 * This example demonstrates how to create a schedulable command
 * using PHP 8.4 attributes for cleaner syntax.
 */
#[Scheduled(
    expression: '0 * * * *',
    name: 'Hourly Data Sync',
    description: 'Syncs data every hour during market closed',
    runWindow: RunWindow::WORKING_HOURS,
    priority: 100,
    category: 'examples',
    tags: ['examples', 'scheduler', 'console-only'],
    withoutOverlapping: true
)]
#[ScheduledLocalOnly]
class ExampleAttributeCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'example:attribute';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Example scheduled command using attribute pattern';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('[CommandScheduler] Example attribute command executed!');
        $this->line('[CommandScheduler] Timestamp: '.now());

        Log::info('[CommandScheduler] Example attribute command executed!');
        Log::info('[CommandScheduler] Timestamp: '.now());

        return self::SUCCESS;
    }
}
