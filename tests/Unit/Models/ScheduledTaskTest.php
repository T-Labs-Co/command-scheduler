<?php

declare(strict_types=1);

use TLabsCo\CommandScheduler\Core\Scheduling\Domain\Enums\RunWindow;
use TLabsCo\CommandScheduler\Models\ScheduledTask;

it('creates and manages scheduled task model', function () {
    $task = ScheduledTask::create([
        'name' => 'Test Task',
        'signature' => 'test:sample',
        'class_name' => 'App\\Console\\Commands\\SampleCommand',
        'expression' => '0 0 * * *',
        'run_window' => RunWindow::WORKING_HOURS,
        'enabled' => true,
        'priority' => 5,
        'description' => 'A test task',
        'environments' => ['production', 'testing'],
        'metadata' => [
            'category' => 'general',
            'tags' => ['sample'],
        ],
    ]);

    expect($task->exists)->toBeTrue()
        ->and($task->name)->toBe('Test Task')
        ->and($task->signature)->toBe('test:sample')
        ->and($task->run_window)->toBe(RunWindow::WORKING_HOURS)
        ->and($task->enabled)->toBeTrue()
        ->and($task->environments)->toBe(['production', 'testing'])
        ->and($task->metadata['tags'])->toBe(['sample']);

    $task->toggleStatus();
    expect($task->fresh()->enabled)->toBeFalse();

    $task->toggleStatus();
    expect($task->fresh()->enabled)->toBeTrue();
});
