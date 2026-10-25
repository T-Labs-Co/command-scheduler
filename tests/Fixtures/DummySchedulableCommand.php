<?php

declare(strict_types=1);

namespace TLabsCo\CommandScheduler\Tests\Fixtures;

use Illuminate\Console\Command;
use TLabsCo\CommandScheduler\Core\Scheduling\Domain\Contracts\SchedulableCommand;
use TLabsCo\CommandScheduler\Core\Scheduling\Domain\Enums\RunWindow;
use TLabsCo\CommandScheduler\Core\Scheduling\Domain\ValueObjects\ScheduleDefinition;

class DummySchedulableCommand extends Command implements SchedulableCommand
{
    protected $signature = 'test:dummy-schedulable';

    protected $description = 'Test dummy schedulable interface command';

    public function scheduleConfig(): ScheduleDefinition
    {
        return (new ScheduleDefinition('Test dummy schedulable interface command'))
            ->cron('0 8 * * 1-5')
            ->runWindow(RunWindow::WORKING_DAYS)
            ->category('reporting')
            ->priority(20)
            ->description('Test dummy schedulable interface command')
            ->environments(['production', 'testing'])
            ->tags(['reports', 'weekly']);
    }

    public function handle(): int
    {
        $this->info('Dummy schedulable command executed successfully.');

        return self::SUCCESS;
    }
}
