<?php

declare(strict_types=1);

use TLabsCo\CommandScheduler\Core\Scheduling\Application\Discovery\SchedulerDiscovery;
use TLabsCo\CommandScheduler\Core\Scheduling\Domain\Enums\RunWindow;
use TLabsCo\CommandScheduler\Tests\Fixtures\DummySchedulableCommand;

it('discovers schedule from schedulable interface reflection', function () {
    $discovery = new SchedulerDiscovery;
    $reflection = new ReflectionClass(DummySchedulableCommand::class);

    $method = new ReflectionMethod($discovery, 'extractInfo');
    $method->setAccessible(true);
    $info = $method->invoke($discovery, $reflection);

    expect($info)->toBeArray()
        ->and($info['class'])->toBe(DummySchedulableCommand::class)
        ->and($info['signature'])->toBe('test:dummy-schedulable');

    $config = $info['config'];
    expect($config->getExpression())->toBe('0 8 * * 1-5')
        ->and($config->getRunWindow())->toBe(RunWindow::WORKING_DAYS)
        ->and($config->getMetadata()['category'])->toBe('reporting')
        ->and($config->getPriority())->toBe(20)
        ->and($config->getEnvironments())->toBe(['production', 'testing'])
        ->and($config->getMetadata()['tags'])->toBe(['reports', 'weekly']);
});
