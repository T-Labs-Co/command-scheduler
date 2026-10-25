<?php

declare(strict_types=1);

use Carbon\Carbon;
use TLabsCo\CommandScheduler\Core\Scheduling\Domain\Enums\RunWindow;
use TLabsCo\CommandScheduler\Models\ScheduledCommandRun;
use TLabsCo\CommandScheduler\Models\ScheduledTask;

it('prunes old command runs beyond configured retention days', function () {
    $task = ScheduledTask::create([
        'name' => 'Prunable Task',
        'signature' => 'test:prunable',
        'class_name' => 'App\\TestCommand',
        'expression' => '0 0 * * *',
        'run_window' => RunWindow::ALWAYS,
        'enabled' => true,
    ]);

    // Old run (40 days ago)
    ScheduledCommandRun::create([
        'scheduled_task_id' => $task->id,
        'started_at' => Carbon::now()->subDays(40),
        'finished_at' => Carbon::now()->subDays(40)->addSeconds(10),
        'duration' => 10000,
        'memory_usage' => 1024,
        'exit_code' => 0,
        'triggered_by' => 'schedule',
    ]);

    // Recent run (5 days ago)
    $recentRun = ScheduledCommandRun::create([
        'scheduled_task_id' => $task->id,
        'started_at' => Carbon::now()->subDays(5),
        'finished_at' => Carbon::now()->subDays(5)->addSeconds(5),
        'duration' => 5000,
        'memory_usage' => 1024,
        'exit_code' => 0,
        'triggered_by' => 'schedule',
    ]);

    $this->artisan('scheduler:prune-logs', ['--days' => 30])
        ->assertSuccessful();

    expect(ScheduledCommandRun::count())->toBe(1)
        ->and(ScheduledCommandRun::first()->id)->toBe($recentRun->id);
});
