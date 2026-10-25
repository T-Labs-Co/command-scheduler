<div>
    <x-mary-header title="Scheduler Dashboard" subtitle="Overview of scheduled command statistics" separator />

    <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-4">
        {{-- Total Tasks --}}
        <x-mary-stat
            title="Total Tasks"
            :value="$this->statistics['total_tasks']"
            icon="o-queue-list"
            color="text-primary"
        />

        {{-- Enabled Tasks --}}
        <x-mary-stat
            title="Enabled Tasks"
            :value="$this->statistics['enabled_tasks']"
            icon="o-check-circle"
            color="text-success"
        />

        {{-- Disabled Tasks --}}
        <x-mary-stat
            title="Disabled Tasks"
            :value="$this->statistics['disabled_tasks']"
            icon="o-x-circle"
            color="text-neutral"
        />

        {{-- Total Runs --}}
        <x-mary-stat
            title="Total Runs"
            :value="$this->statistics['total_runs']"
            icon="o-arrow-path"
            color="text-secondary"
        />
    </div>

    <div class="grid grid-cols-1 gap-4 mt-6 md:grid-cols-3">
        {{-- Total Failures --}}
        <x-mary-stat
            title="Total Failures"
            :value="$this->statistics['total_failures']"
            icon="o-exclamation-triangle"
            color="text-error"
        />

        {{-- Tasks with Failures --}}
        <x-mary-stat
            title="Tasks with Failures"
            :value="$this->statistics['tasks_with_failures']"
            icon="o-shield-exclamation"
            color="text-warning"
        />

        {{-- Average Success Rate --}}
        <x-mary-stat
            title="Avg Success Rate"
            :value="number_format($this->statistics['average_success_rate'], 1) . '%'"
            icon="o-chart-bar"
            color="text-info"
        />
    </div>

    <div class="mt-6 flex flex-col sm:flex-row gap-2">
        <x-mary-button
            label="View All Tasks"
            icon="o-queue-list"
            link="{{ route('admin.scheduler.tasks') }}"
            class="btn-primary"
        />

        <x-mary-button
            label="View Recent Runs"
            icon="o-clock"
            link="{{ route('admin.scheduler.runs') }}"
            class="btn-outline btn-primary"
        />

        <x-mary-button
            label="Sync with Database"
            icon="o-arrow-path"
            @click="$wire.showSyncModal = true"
            class="btn-outline btn-secondary"
        />
    </div>

    <div class="grid grid-cols-1 gap-6 mt-8 lg:grid-cols-2">
        {{-- Next Run --}}
        <x-mary-card title="Next Run" separator shadow>
            <x-mary-table :rows="$this->upcomingRuns" :headers="[
                ['key' => 'name', 'label' => 'Task'],
                ['key' => 'expression', 'label' => 'Schedule'],
                ['key' => 'run_window', 'label' => 'Window'],
                ['key' => 'next_run', 'label' => 'Next Run'],
                ['key' => 'actions', 'label' => '', 'sortable' => false],
            ]">
                @scope('cell_name', $task)
                    <div class="font-semibold">{{ $task->name }}</div>
                    <div class="text-xs text-gray-500">{{ $task->signature }}</div>
                @endscope
                @scope('cell_expression', $task)
                    <div class="font-mono text-xs text-nowrap">{{ $task->expression }}</div>
                    <div class="text-[10px] text-gray-500">{{ $task->human_cron }}</div>
                @endscope
                @scope('cell_run_window', $task)
                    <x-mary-badge :value="$task->run_window->label()" class="{{ run_window_badge_class($task->run_window) }} badge-xs text-nowrap" />
                @endscope
                @scope('cell_next_run', $task)
                    @if($task->next_run)
                        <div>{{ $task->next_run->format('H:i d/m') }}</div>
                        <div class="text-xs text-info">{{ $task->next_run->diffForHumans() }}</div>
                    @else
                        <span class="text-gray-400">-</span>
                    @endif
                @endscope
                @scope('cell_actions', $task)
                    <x-mary-button
                        icon="o-eye"
                        link="{{ route('admin.scheduler.tasks.show', $task->id) }}"
                        class="btn-ghost btn-sm text-info"
                        tooltip="View Task Detail"
                    />
                @endscope
            </x-mary-table>
        </x-mary-card>

        {{-- Latest Run --}}
        <x-mary-card title="Latest Run" separator shadow>
            <x-mary-table :rows="$this->latestRuns" :headers="[
                ['key' => 'scheduledTask.name', 'label' => 'Task'],
                ['key' => 'status', 'label' => 'Status'],
                ['key' => 'started_at', 'label' => 'Started At'],
                ['key' => 'actions', 'label' => '', 'sortable' => false],
            ]">
                @scope('cell_scheduledTask.name', $run)
                    <div class="font-semibold">{{ $run->scheduledTask->name }}</div>
                    <div class="text-xs text-gray-500">{{ $run->scheduledTask->signature }}</div>
                @endscope
                @scope('cell_status', $run)
                    @if($run->wasSuccessful())
                        <x-mary-badge value="Success" class="badge-success badge-sm" />
                    @else
                        <x-mary-badge value="Failed" class="badge-error badge-sm" />
                    @endif
                @endscope
                @scope('cell_started_at', $run)
                    <div>{{ $run->started_at->format('Y-m-d H:i:s') }}</div>
                    <div class="text-xs text-gray-500">{{ $run->started_at->diffForHumans() }}</div>
                @endscope
                @scope('cell_actions', $run)
                    <x-mary-button
                        icon="o-eye"
                        link="{{ route('admin.scheduler.tasks.show', $run->scheduledTask->id) }}"
                        class="btn-ghost btn-sm text-info"
                        tooltip="View Task Detail"
                    />
                @endscope
            </x-mary-table>
        </x-mary-card>
    </div>

    <div class="grid grid-cols-1 gap-6 mt-8 lg:grid-cols-2">
        {{-- Top 10 Duration Tasks --}}
        <x-mary-card title="Top 10 Duration" separator shadow>
            <x-mary-table :rows="$this->topDurationTasks" :headers="[
                ['key' => 'scheduledTask.name', 'label' => 'Task'],
                ['key' => 'duration', 'label' => 'Duration'],
                ['key' => 'started_at', 'label' => 'Started At'],
                ['key' => 'actions', 'label' => '', 'sortable' => false],
            ]">
                @scope('cell_scheduledTask.name', $run)
                    <div class="font-semibold">{{ $run->scheduledTask->name }}</div>
                    <div class="text-xs text-gray-500">{{ $run->scheduledTask->signature }}</div>
                @endscope
                @scope('cell_duration', $run)
                    {{ $run->durationForHumans() }}
                @endscope
                @scope('cell_started_at', $run)
                    {{ $run->started_at->format('Y-m-d H:i:s') }}
                @endscope
                @scope('cell_actions', $run)
                    <x-mary-button
                        icon="o-eye"
                        link="{{ route('admin.scheduler.tasks.show', $run->scheduledTask->id) }}"
                        class="btn-ghost btn-sm text-info"
                        tooltip="View Task Detail"
                    />
                @endscope
            </x-mary-table>
        </x-mary-card>

        {{-- Top 10 Memory Tasks --}}
        <x-mary-card title="Top 10 Memory Usage" separator shadow>
            <x-mary-table :rows="$this->topMemoryTasks" :headers="[
                ['key' => 'scheduledTask.name', 'label' => 'Task'],
                ['key' => 'memory_usage', 'label' => 'Memory'],
                ['key' => 'started_at', 'label' => 'Started At'],
                ['key' => 'actions', 'label' => '', 'sortable' => false],
            ]">
                @scope('cell_scheduledTask.name', $run)
                    <div class="font-semibold">{{ $run->scheduledTask->name }}</div>
                    <div class="text-xs text-gray-500">{{ $run->scheduledTask->signature }}</div>
                @endscope
                @scope('cell_memory_usage', $run)
                    {{ $run->memoryUsageForHumans() }}
                @endscope
                @scope('cell_started_at', $run)
                    {{ $run->started_at->format('Y-m-d H:i:s') }}
                @endscope
                @scope('cell_actions', $run)
                    <x-mary-button
                        icon="o-eye"
                        link="{{ route('admin.scheduler.tasks.show', $run->scheduledTask->id) }}"
                        class="btn-ghost btn-sm text-info"
                        tooltip="View Task Detail"
                    />
                @endscope
            </x-mary-table>
        </x-mary-card>
    </div>

    <x-mary-modal wire:model="showSyncModal" title="Sync with Database" separator>
        <div>
            Are you sure you want to sync discovered scheduled commands with the database?
            This will create new tasks, update existing ones, and remove tasks that are no longer discovered.
        </div>

        <x-slot:actions>
            <x-mary-button label="Cancel" @click="$wire.showSyncModal = false" />
            <x-mary-button label="Sync Now" icon="o-check" class="btn-primary" wire:click="syncWithDatabase" spinner />
        </x-slot:actions>
    </x-mary-modal>
</div>
