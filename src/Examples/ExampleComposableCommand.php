<?php

namespace TLabsCo\CommandScheduler\Examples;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use TLabsCo\CommandScheduler\Attributes\ScheduledCategory;
use TLabsCo\CommandScheduler\Attributes\ScheduledDaily;
use TLabsCo\CommandScheduler\Attributes\ScheduledLocalOnly;
use TLabsCo\CommandScheduler\Attributes\ScheduledTags;
use TLabsCo\CommandScheduler\Attributes\ScheduledWithoutOverlapping;

#[ScheduledDaily(name: 'Example Composable Command', time: '11:17')]
#[ScheduledWithoutOverlapping]
#[ScheduledCategory('example')]
#[ScheduledTags(['example', 'scheduler', 'console-only'])]
#[ScheduledLocalOnly]
class ExampleComposableCommand extends Command
{
    protected $signature = 'example:composable';

    protected $description = 'Example of composable scheduled attributes';

    public function handle(): int
    {
        $this->info('[CommandScheduler] Composable command executed!');
        $this->line('[CommandScheduler] Timestamp: '.now());

        Log::info('[CommandScheduler] Composable command executed!');
        Log::info('[CommandScheduler] Timestamp: '.now());

        return self::SUCCESS;
    }
}
