<?php

namespace TLabsCo\CommandScheduler\Livewire;

use Illuminate\Console\Command;
use Illuminate\Contracts\View\View;
use Livewire\Component;
use Livewire\WithPagination;
use Mary\Traits\Toast;
use TLabsCo\CommandScheduler\Models\ScheduledCommandRun;

/**
 * Component for listing recent scheduled command runs.
 */
class SchedulerRunList extends Component
{
    use Toast;
    use WithPagination;

    public int $perPage = 15;

    public string $search = '';

    public string $filterStatus = 'all';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingFilterStatus(): void
    {
        $this->resetPage();
    }

    public function render(): View
    {
        $query = ScheduledCommandRun::query()
            ->with('scheduledTask')
            ->orderByDesc('started_at');

        // Apply search filter (by task name or signature)
        if ($this->search) {
            $query->whereHas('scheduledTask', function ($q): void {
                $q->where('name', 'like', "%{$this->search}%")
                    ->orWhere('signature', 'like', "%{$this->search}%");
            });
        }

        // Apply status filter
        if ($this->filterStatus === 'success') {
            $query->where('exit_code', Command::SUCCESS);
        } elseif ($this->filterStatus === 'failed') {
            $query->where('exit_code', '!=', Command::SUCCESS);
        }

        $runs = $query->paginate($this->perPage);

        $layout = config('command-scheduler.dashboard.layout', 'scheduler');

        /** @var view-string $view */
        $view = 'command-scheduler::livewire.scheduler-run-list';

        /** @var view-string $layoutView */
        $layoutView = "command-scheduler::layouts.{$layout}";

        return view($view, [
            'runs' => $runs,
        ])->layout($layoutView, ['title' => 'Recent Command Runs']);
    }

    /**
     * @return array<int, array<string, string>>
     */
    public function getStatusOptionsProperty(): array
    {
        return [
            ['id' => 'all', 'name' => 'All Status', 'icon' => 'o-adjustments-horizontal'],
            ['id' => 'success', 'name' => 'Success Only', 'icon' => 'o-check-circle'],
            ['id' => 'failed', 'name' => 'Failed Only', 'icon' => 'o-x-circle'],
        ];
    }

    /**
     * @return array<int, array<string, string>>
     */
    public function getHeadersProperty(): array
    {
        return [
            ['key' => 'id', 'label' => 'ID'],
            ['key' => 'scheduledTask.name', 'label' => 'Task'],
            ['key' => 'started_at', 'label' => 'Started At'],
            ['key' => 'finished_at', 'label' => 'Finished At'],
            ['key' => 'duration', 'label' => 'Duration'],
            ['key' => 'memory_usage', 'label' => 'Memory'],
            ['key' => 'exit_code', 'label' => 'Status'],
            ['key' => 'triggered_by', 'label' => 'Triggered By'],
            ['key' => 'actions', 'label' => 'Actions'],
        ];
    }
}
