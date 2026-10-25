<?php

declare(strict_types=1);

use TLabsCo\CommandScheduler\Attributes\Scheduled;
use TLabsCo\CommandScheduler\Attributes\ScheduledRunWindow;
use TLabsCo\CommandScheduler\Core\Scheduling\Domain\Enums\RunWindow;

it('creates scheduled attribute with default values', function () {
    $attribute = new Scheduled(expression: '0 0 * * *');

    expect($attribute->expression)->toBe('0 0 * * *')
        ->and($attribute->runWindow)->toBe(RunWindow::ALWAYS)
        ->and($attribute->priority)->toBe(100)
        ->and($attribute->enabled)->toBeTrue()
        ->and($attribute->environments)->toBe([])
        ->and($attribute->tags)->toBe([])
        ->and($attribute->options)->toBe([]);
});

it('creates scheduled attribute with custom values', function () {
    $attribute = new Scheduled(
        expression: '*/5 * * * *',
        name: 'Sync Task',
        description: 'Sync customer data',
        options: ['--force' => true],
        runWindow: RunWindow::WORKING_HOURS,
        priority: 15,
        enabled: false,
        environments: ['production'],
        category: 'sync',
        tags: ['customers', 'sync']
    );

    expect($attribute->expression)->toBe('*/5 * * * *')
        ->and($attribute->name)->toBe('Sync Task')
        ->and($attribute->runWindow)->toBe(RunWindow::WORKING_HOURS)
        ->and($attribute->priority)->toBe(15)
        ->and($attribute->category)->toBe('sync')
        ->and($attribute->environments)->toBe(['production'])
        ->and($attribute->enabled)->toBeFalse()
        ->and($attribute->description)->toBe('Sync customer data')
        ->and($attribute->tags)->toBe(['customers', 'sync'])
        ->and($attribute->options)->toBe(['--force' => true]);
});

it('creates and applies scheduled run window attribute configurator', function () {
    $scheduled = new Scheduled(expression: '0 0 * * *');
    $configurator = new ScheduledRunWindow(RunWindow::TRADING);
    $configurator->configure($scheduled);

    expect($scheduled->runWindow)->toBe(RunWindow::TRADING);
});
