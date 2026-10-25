<?php

namespace TLabsCo\CommandScheduler\Livewire;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Livewire\Component;
use Mary\Traits\Toast;
use TLabsCo\CommandScheduler\Core\Scheduling\Application\Services\ScheduleRegistrar;
use TLabsCo\CommandScheduler\Core\Scheduling\Application\Services\SchedulerService;
use TLabsCo\CommandScheduler\Models\ScheduledCommandRun;
use TLabsCo\CommandScheduler\Models\ScheduledTask;

/**
 * Dashboard for scheduler overview and statistics.
 */
class SchedulerDashboard extends Component
{
    use Toast;

    public bool $showSyncModal = false;

    /**
     * @return array<string, mixed>
     */
    public function getStatisticsProperty(): array
    {
        return SchedulerService::make()->getStatistics();
    }

    /**
     * @return Collection<int, ScheduledCommandRun>
     */
    public function getTopDurationTasksProperty(): Collection
    {
        return SchedulerService::make()->getTopDurationTasks();
    }

    /**
     * @return Collection<int, ScheduledCommandRun>
     */
    public function getTopMemoryTasksProperty(): Collection
    {
        return SchedulerService::make()->getTopMemoryTasks();
    }

    /**
     * @return Collection<int, ScheduledTask>
     */
    public function getUpcomingRunsProperty(): Collection
    {
        return SchedulerService::make()->getUpcomingRuns();
    }

    /**
     * @return Collection<int, ScheduledCommandRun>
     */
    public function getLatestRunsProperty(): Collection
    {
        return SchedulerService::make()->getLatestRuns();
    }

    /**
     * Sync discovered commands with the database.
     */
    public function syncWithDatabase(): void
    {
        check_scheduler_permission();

        $registrar = ScheduleRegistrar::make();
        $synced = $registrar->syncWithDatabase();

        $this->showSyncModal = false;

        $created = count($synced['created']);
        $updated = count($synced['updated']);
        $deleted = count($synced['deleted']);

        $message = "Sync completed! Created: {$created}, Updated: {$updated}, Deleted: {$deleted}";
        $this->success($message);
    }

    public function render(): View
    {
        $layout = config('command-scheduler.dashboard.layout', 'scheduler');

        /** @var view-string $view */
        $view = 'command-scheduler::livewire.scheduler-dashboard';

        /** @var view-string $layoutView */
        $layoutView = "command-scheduler::layouts.{$layout}";

        return view($view)
            ->layout($layoutView, ['title' => 'Command Scheduler Dashboard']);
    }
}
