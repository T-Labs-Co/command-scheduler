<?php

declare(strict_types=1);

namespace TLabsCo\CommandScheduler\Tests\Fixtures;

use Illuminate\Console\Command;
use TLabsCo\CommandScheduler\Attributes\Scheduled;
use TLabsCo\CommandScheduler\Core\Scheduling\Domain\Enums\RunWindow;

#[Scheduled(
    expression: '0 2 * * *',
    name: 'Dummy Attribute Command',
    description: 'Test dummy attribute scheduled command',
    runWindow: RunWindow::WORKING_HOURS,
    priority: 10,
    environments: ['production', 'testing'],
    category: 'maintenance',
    tags: ['test', 'dummy']
)]
class DummyAttributeCommand extends Command
{
    protected $signature = 'test:dummy-attribute {--force : Force execution}';

    protected $description = 'Test dummy attribute scheduled command';

    public function handle(): int
    {
        $this->info('Dummy attribute command executed successfully.');

        return self::SUCCESS;
    }
}
