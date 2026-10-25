<?php

declare(strict_types=1);

use TLabsCo\CommandScheduler\Core\Scheduling\Application\Services\SchedulerManager;
use TLabsCo\CommandScheduler\Models\ScheduledTask;

it('runs scheduler:sync command and records tasks into database', function () {
    config()->set('command-scheduler.discovery_paths', [
        __DIR__.'/../../Fixtures',
    ]);
    app(SchedulerManager::class)->clearCache();

    $this->artisan('scheduler:sync')
        ->assertSuccessful();

    $this->assertDatabaseHas('cs_scheduled_tasks', [
        'signature' => 'test:dummy-attribute',
        'expression' => '0 2 * * *',
        'priority' => 10,
    ]);

    $task = ScheduledTask::where('signature', 'test:dummy-attribute')->first();
    expect($task)->not->toBeNull()
        ->and($task->environments)->toBe(['production', 'testing'])
        ->and($task->metadata['tags'])->toBe(['test', 'dummy'])
        ->and($task->metadata['category'])->toBe('maintenance');
});
