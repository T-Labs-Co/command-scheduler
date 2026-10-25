<?php

namespace TLabsCo\CommandScheduler\Livewire;

use Illuminate\Contracts\View\View;
use Livewire\Component;
use Livewire\WithPagination;
use Mary\Traits\Toast;
use TLabsCo\CommandScheduler\Core\Scheduling\Application\Services\TaskRunner;
use TLabsCo\CommandScheduler\Models\ScheduledTask;

/**
 * Task detail and run history component.
 */
class SchedulerTaskDetail extends Component
{
    use Toast;
    use WithPagination;

    public ScheduledTask $task;

    public function mount(ScheduledTask $task): void
    {
        $this->task = $task;
    }

    public function toggleTask(): void
    {
        check_scheduler_permission();

        $this->task->toggleStatus();

        $this->dispatch('task-updated');

        $status = $this->task->enabled ? 'enabled' : 'disabled';
        $this->success("Task {$this->task->name} has been {$status}.");
    }

    public function runTask(TaskRunner $taskRunner): void
    {
        check_scheduler_permission();

        if ($this->task->isConsoleOnly()) {
            $this->error("Task {$this->task->name} is marked as console-only and cannot be run manually from the UI.");

            return;
        }

        $result = $taskRunner->run($this->task);

        if ($result['exit_code'] === 0) {
            $this->success("Task {$this->task->name} executed successfully.");
        } else {
            $this->error("Task {$this->task->name} failed with exit code {$result['exit_code']}.");
        }

        $this->dispatch('task-updated');
    }

    public function render(): View
    {
        $runs = $this->task
            ->runs()
            ->with('scheduledTask')
            ->latest('started_at')
            ->paginate(20);

        $layout = config('command-scheduler.dashboard.layout', 'scheduler');

        /** @var view-string $view */
        $view = 'command-scheduler::livewire.scheduler-task-detail';

        /** @var view-string $layoutView */
        $layoutView = "command-scheduler::layouts.{$layout}";

        return view($view, [
            'runs' => $runs,
        ])->layout($layoutView, ['title' => 'Command Scheduler - '.$this->task->name]);
    }

    /**
     * @return array<int, array<string, string>>
     */
    public function getHeadersProperty(): array
    {
        return [
            ['key' => 'status', 'label' => 'Status'],
            ['key' => 'started_at', 'label' => 'Started'],
            ['key' => 'duration', 'label' => 'Duration'],
            ['key' => 'memory', 'label' => 'Memory'],
            ['key' => 'triggered_by', 'label' => 'Triggered By'],
            ['key' => 'exit_code', 'label' => 'Exit Code'],
        ];
    }
}
