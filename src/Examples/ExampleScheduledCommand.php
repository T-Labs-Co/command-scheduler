<?php

namespace TLabsCo\CommandScheduler\Examples;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use TLabsCo\CommandScheduler\Core\Scheduling\Domain\Contracts\SchedulableCommand;
use TLabsCo\CommandScheduler\Core\Scheduling\Domain\ValueObjects\ScheduleDefinition;

/**
 * Example command using the Interface pattern.
 *
 * This example demonstrates how to create a schedulable command
 * by implementing the SchedulableCommand interface.
 */
class ExampleScheduledCommand extends Command implements SchedulableCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'example:scheduled';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Example scheduled command using interface pattern';

    /**
     * Define the schedule configuration for this command.
     */
    public function scheduleConfig(): ScheduleDefinition
    {
        return (new ScheduleDefinition('Example Scheduled Task'))
            ->description('This is an example task that runs every 5 minutes')
            ->everyFiveMinutes()
            ->duringWorkingHours()
            ->priority(50)
            ->category('examples')
            ->tags(['example', 'scheduler', 'console-only'])
            ->local()
            ->withoutOverlapping();
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('[CommandScheduler] Example scheduled command executed!');
        $this->line('[CommandScheduler] Timestamp: '.now());

        Log::info('[CommandScheduler] Example scheduled command executed!');
        Log::info('[CommandScheduler] Timestamp: '.now());

        return self::SUCCESS;
    }
}
