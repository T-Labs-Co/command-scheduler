<?php

declare(strict_types=1);

use TLabsCo\CommandScheduler\Core\Scheduling\Domain\Enums\RunWindow;
use TLabsCo\CommandScheduler\Core\Scheduling\Domain\ValueObjects\ScheduleDefinition;

it('builds schedule definition fluently', function () {
    $definition = (new ScheduleDefinition('Daily sync task'))
        ->cron('0 8 * * *')
        ->runWindow(RunWindow::WORKING_HOURS)
        ->priority(10)
        ->category('sync')
        ->description('Daily sync task')
        ->environments(['production', 'staging'])
        ->tags(['sync', 'daily'])
        ->options(['--force' => true])
        ->disabled();

    expect($definition->getName())->toBe('Daily sync task')
        ->and($definition->getExpression())->toBe('0 8 * * *')
        ->and($definition->getRunWindow())->toBe(RunWindow::WORKING_HOURS)
        ->and($definition->getPriority())->toBe(10)
        ->and($definition->getDescription())->toBe('Daily sync task')
        ->and($definition->getEnvironments())->toBe(['production', 'staging'])
        ->and($definition->getMetadata()['tags'])->toBe(['sync', 'daily'])
        ->and($definition->getOptions())->toBe(['--force' => true])
        ->and($definition->isEnabled())->toBeFalse();
});
