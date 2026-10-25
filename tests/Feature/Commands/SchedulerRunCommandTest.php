<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Artisan;
use TLabsCo\CommandScheduler\Core\Scheduling\Domain\Enums\RunWindow;
use TLabsCo\CommandScheduler\Models\ScheduledTask;
use TLabsCo\CommandScheduler\Tests\Fixtures\DummyAttributeCommand;

it('runs scheduler:run command and records run execution history', function () {
    Artisan::registerCommand(new DummyAttributeCommand);

    $task = ScheduledTask::create([
        'name' => 'Dummy Attribute Command',
        'signature' => 'test:dummy-attribute',
        'class_name' => DummyAttributeCommand::class,
        'expression' => '0 2 * * *',
        'run_window' => RunWindow::ALWAYS,
        'enabled' => true,
        'priority' => 10,
        'metadata' => [
            'category' => 'maintenance',
        ],
    ]);

    $this->artisan('scheduler:run', ['signature' => 'test:dummy-attribute', '--force' => true])
        ->assertSuccessful();

    $this->assertDatabaseHas('cs_scheduled_command_runs', [
        'scheduled_task_id' => $task->id,
        'exit_code' => 0,
    ]);
});
