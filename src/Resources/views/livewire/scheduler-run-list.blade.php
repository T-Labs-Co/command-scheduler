<div>
    <x-mary-header title="Recent Command Runs" subtitle="Monitor execution history of scheduled commands" separator />

    {{-- Filters --}}
    <div class="grid grid-cols-1 gap-4 mb-6 md:grid-cols-2">
        <x-mary-input
            wire:model.live.debounce="search"
            placeholder="Search by task name or signature..."
            icon="o-magnifying-glass"
            clearable
        />

        <x-mary-select
            wire:model.live="filterStatus"
            :options="$this->statusOptions"
            icon="o-tag"
        />
    </div>

    {{-- Runs Table --}}
    <x-mary-table :headers="$this->headers" :rows="$runs" with-pagination :per-page="$perPage">
        @scope('cell_id', $run)
            <span class="text-xs text-base-content/50 font-mono">#{{ $run->id }}</span>
        @endscope

        @scope('cell_scheduledTask.name', $run)
            <div>
                <div class="font-semibold">{{ $run->scheduledTask->name }}</div>
                <div class="text-xs text-base-content/70 font-mono">{{ $run->scheduledTask->signature }}</div>
            </div>
        @endscope

        @scope('cell_started_at', $run)
            <div class="text-sm">
                {{ $run->started_at->format('Y-m-d H:i:s') }}
                <div class="text-xs text-base-content/50">{{ $run->started_at->diffForHumans() }}</div>
            </div>
        @endscope

        @scope('cell_finished_at', $run)
            <div class="text-sm">
                @if($run->finished_at)
                    {{ $run->finished_at->format('Y-m-d H:i:s') }}
                @else
                    <span class="text-warning">Running...</span>
                @endif
            </div>
        @endscope

        @scope('cell_duration', $run)
            <span class="text-sm">{{ $run->durationForHumans() }}</span>
        @endscope

        @scope('cell_memory_usage', $run)
            <span class="text-sm">{{ $run->memoryUsageForHumans() ?: '-' }}</span>
        @endscope

        @scope('cell_exit_code', $run)
            @if($run->wasSuccessful())
                <x-mary-badge value="Success" class="badge-success badge-sm" />
            @else
                <x-mary-badge value="Failed ({{ $run->exit_code }})" class="badge-error badge-sm" />
            @endif
        @endscope

        @scope('cell_triggered_by', $run)
            <x-mary-badge :value="$run->triggered_by->value" class="badge-ghost badge-sm" />
        @endscope

        @scope('cell_actions', $run)
            <div class="flex gap-2">
                <x-mary-button
                    icon="o-eye"
                    link="{{ route('admin.scheduler.tasks.show', $run->scheduled_task_id) }}"
                    class="btn-sm btn-ghost"
                    tooltip="View Task Details"
                />
            </div>
        @endscope
    </x-mary-table>
</div>
