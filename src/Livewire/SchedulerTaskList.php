<?php

namespace TLabsCo\CommandScheduler\Livewire;

use Illuminate\Contracts\View\View;
use Livewire\Component;
use Livewire\WithPagination;
use Mary\Traits\Toast;
use TLabsCo\CommandScheduler\Core\Scheduling\Application\Services\TaskRunner;
use TLabsCo\CommandScheduler\Models\ScheduledTask;

/**
 * Task list management component.
 */
class SchedulerTaskList extends Component
{
    use Toast;
    use WithPagination;

    public int $perPage = 15;

    public string $search = '';

    public string $filterStatus = 'all';

    public string $filterWindow = 'all';

    public string $filterCategory = 'all';

    public string $filterEnvironment = 'all';

    /**
     * @var array<string>
     */
    public array $filterTag = [];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingFilterStatus(): void
    {
        $this->resetPage();
    }

    public function updatingFilterWindow(): void
    {
        $this->resetPage();
    }

    public function updatingFilterCategory(): void
    {
        $this->resetPage();
    }

    public function updatingFilterEnvironment(): void
    {
        $this->resetPage();
    }

    public function updatingFilterTag(): void
    {
        $this->resetPage();
    }

    public function toggleTask(int $taskId): void
    {
        check_scheduler_permission();

        $task = ScheduledTask::query()->findOrFail($taskId);
        $task->toggleStatus();

        $this->dispatch('task-updated');

        $status = $task->enabled ? 'enabled' : 'disabled';
        $this->success("Task {$task->name} has been {$status}.");
    }

    public function runTask(int $taskId, TaskRunner $taskRunner): void
    {
        check_scheduler_permission();

        $task = ScheduledTask::query()->findOrFail($taskId);

        if ($task->isConsoleOnly()) {
            $this->error("Task {$task->name} is marked as console-only and cannot be run manually from the UI.");

            return;
        }

        $result = $taskRunner->run($task);

        if ($result['exit_code'] === 0) {
            $this->success("Task {$task->name} executed successfully.");
        } else {
            $this->error("Task {$task->name} failed with exit code {$result['exit_code']}.");
        }

        $this->dispatch('task-updated');
    }

    public function render(): View
    {
        $query = ScheduledTask::query()->byPriority();

        // Apply search filter
        if ($this->search) {
            $query->where(function ($q): void {
                $q->where('name', 'like', "%{$this->search}%")
                    ->orWhere('signature', 'like', "%{$this->search}%")
                    ->orWhere('description', 'like', "%{$this->search}%");
            });
        }

        // Apply status filter
        if ($this->filterStatus === 'enabled') {
            $query->enabled();
        } elseif ($this->filterStatus === 'disabled') {
            $query->disabled();
        }

        // Apply run window filter
        if ($this->filterWindow !== 'all') {
            $query->byRunWindow($this->filterWindow);
        }

        // Apply category filter
        if ($this->filterCategory !== 'all') {
            $query->where('metadata->category', $this->filterCategory);
        }

        // Apply environment filter
        if ($this->filterEnvironment !== 'all') {
            $query->whereJsonContains('environments', $this->filterEnvironment);
        }

        // Apply tag filter
        if (! empty($this->filterTag)) {
            $query->where(function ($q): void {
                foreach ($this->filterTag as $tag) {
                    $q->orWhereJsonContains('metadata->tags', $tag);
                }
            });
        }

        $tasks = $query->paginate($this->perPage);

        $layout = config('command-scheduler.dashboard.layout', 'scheduler');

        /** @var view-string $view */
        $view = 'command-scheduler::livewire.scheduler-task-list';

        /** @var view-string $layoutView */
        $layoutView = "command-scheduler::layouts.{$layout}";

        return view($view, [
            'tasks' => $tasks,
        ])->layout($layoutView, ['title' => 'Command Scheduled Tasks']);
    }

    /**
     * @return array<int, array<string, string>>
     */
    public function getRunWindowOptionsProperty(): array
    {
        return [
            ['id' => 'all', 'name' => 'All Windows', 'icon' => 'o-globe-alt'],
            ['id' => 'always', 'name' => 'Always', 'icon' => 'o-clock'],
            ['id' => 'trading', 'name' => 'Trading Hours', 'icon' => 'o-banknotes'],
            ['id' => 'closed', 'name' => 'Market Closed', 'icon' => 'o-moon'],
            ['id' => 'premarket', 'name' => 'Pre-Market', 'icon' => 'o-sun'],
            ['id' => 'afterhours', 'name' => 'After Hours', 'icon' => 'o-sparkles'],
        ];
    }

    /**
     * @return array<int, array<string, string>>
     */
    public function getStatusOptionsProperty(): array
    {
        return [
            ['id' => 'all', 'name' => 'All Status', 'icon' => 'o-adjustments-horizontal'],
            ['id' => 'enabled', 'name' => 'Enabled Only', 'icon' => 'o-check-circle'],
            ['id' => 'disabled', 'name' => 'Disabled Only', 'icon' => 'o-x-circle'],
        ];
    }

    /**
     * @return array<int, array<string, string>>
     */
    public function getCategoryOptionsProperty(): array
    {
        $categories = ScheduledTask::query()
            ->whereNotNull('metadata->category')
            ->distinct()
            ->get(['metadata'])
            ->map(fn ($task) => $task->metadata['category'] ?? null)
            ->filter()
            ->unique()
            ->sort()
            ->values();

        $options = [
            ['id' => 'all', 'name' => 'All Categories', 'icon' => 'o-hashtag'],
        ];

        foreach ($categories as $category) {
            $options[] = [
                'id' => $category,
                'name' => $category,
                'icon' => 'o-tag',
            ];
        }

        return $options;
    }

    /**
     * @return array<int, array<string, string>>
     */
    public function getEnvironmentOptionsProperty(): array
    {
        $tasks = ScheduledTask::query()
            ->whereNotNull('environments')
            ->get(['environments']);

        $environments = $tasks->flatMap(fn (ScheduledTask $task): array => $task->environments ?? [])
            ->filter()
            ->unique()
            ->sort()
            ->values();

        $options = [
            ['id' => 'all', 'name' => 'All Environments', 'icon' => 'o-computer-desktop'],
        ];

        foreach ($environments as $env) {
            $options[] = [
                'id' => $env,
                'name' => ucfirst((string) $env),
                'icon' => 'o-server',
            ];
        }

        return $options;
    }

    /**
     * @return array<int, array<string, string>>
     */
    public function getTagOptionsProperty(): array
    {
        $tasks = ScheduledTask::query()
            ->whereNotNull('metadata->tags')
            ->get(['metadata']);

        $tags = $tasks->flatMap(function (ScheduledTask $task): array {
            $metadata = $task->metadata;

            return (is_array($metadata) && isset($metadata['tags']) && is_array($metadata['tags']))
                ? $metadata['tags']
                : [];
        })
            ->filter()
            ->unique()
            ->sort()
            ->values();

        $options = [];

        foreach ($tags as $tag) {
            $options[] = [
                'id' => $tag,
                'name' => $tag,
            ];
        }

        return $options;
    }

    /**
     * @return array<int, array<string, string>>
     */
    public function getHeadersProperty(): array
    {
        return [
            ['key' => 'name', 'label' => 'Name'],
            //            ['key' => 'category', 'label' => 'Category'],
            ['key' => 'signature', 'label' => 'Signature'],
            ['key' => 'expression', 'label' => 'Schedule'],
            ['key' => 'next_run', 'label' => 'Next Run'],
            ['key' => 'options', 'label' => 'Options'],
            ['key' => 'run_window', 'label' => 'Window'],
            ['key' => 'status', 'label' => 'Status/Stats'],
            //            ['key' => 'stats', 'label' => 'Stats'],
            ['key' => 'actions', 'label' => 'Actions'],
        ];
    }
}
