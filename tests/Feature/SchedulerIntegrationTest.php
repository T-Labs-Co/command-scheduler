<?php

declare(strict_types=1);

use TLabsCo\CommandScheduler\Core\Scheduling\Application\Services\ScheduleRegistrar;
use TLabsCo\CommandScheduler\Core\Scheduling\Application\Services\SchedulerManager;

it('discovers and registers scheduled commands with laravel schedule', function () {
    config()->set('command-scheduler.discovery_paths', [
        __DIR__.'/../Fixtures',
    ]);
    app(SchedulerManager::class)->clearCache();

    $registrar = app(ScheduleRegistrar::class);
    $synced = $registrar->syncWithDatabase();

    expect($synced)->toBeArray()
        ->and(isset($synced['created']))->toBeTrue();

    $this->assertDatabaseHas('cs_scheduled_tasks', [
        'signature' => 'test:dummy-attribute',
    ]);

    $this->assertDatabaseHas('cs_scheduled_tasks', [
        'signature' => 'test:dummy-schedulable',
    ]);

    // Test registrar execution without exceptions
    $registrar->register();
    expect(true)->toBeTrue();
});
