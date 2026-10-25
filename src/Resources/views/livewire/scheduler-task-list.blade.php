<div>
    <x-mary-header title="Scheduled Tasks" subtitle="Manage and monitor all scheduled commands" separator />

    {{-- Filters --}}
    <div class="grid grid-cols-2 gap-4 mb-6 md:grid-cols-3 lg:grid-cols-6">
        <x-mary-input
            wire:model.live.debounce="search"
            placeholder="Search tasks..."
            icon="o-magnifying-glass"
            clearable
        />

        <x-mary-select
            wire:model.live="filterCategory"
            :options="$this->categoryOptions"
            icon="o-hashtag"
        />

        <x-mary-select
            wire:model.live="filterEnvironment"
            :options="$this->environmentOptions"
            icon="o-computer-desktop"
        />

        <x-mary-choices-offline
            wire:model.live="filterTag"
            :options="$this->tagOptions"
            placeholder="Filter Tags..."
            icon="o-tag"
        />

        <x-mary-select
            wire:model.live="filterStatus"
            :options="$this->statusOptions"
            icon="o-adjustments-horizontal"
        />

        <x-mary-select
            wire:model.live="filterWindow"
            :options="$this->runWindowOptions"
            icon="o-clock"
        />
    </div>

    {{-- Tasks Table --}}
    <x-mary-table :headers="$this->headers" :rows="$tasks" with-pagination per-page="perPage">
        @scope('cell_status', $task)
            @if($task->enabled)
                <x-mary-badge value="ON" class="badge-success badge-xs" />
            @else
                <x-mary-badge value="OFF" class="badge-neutral badge-xs" />
            @endif
        <div class="text-xs">
            <div class="text-nowrap">Runs: {{ $task->run_count }}</div>
            <div class="text-error text-nowrap">Fail: {{ $task->failure_count }}</div>
            <div class="text-nowrap">Success: {{ number_format($task->successRate(), 1) }}%</div>
        </div>
        @endscope

        @scope('cell_category', $task)
            @if($category = $task->categoryTag())
                <x-mary-button
                    wire:click="$set('filterCategory', '{{ $category }}')"
                    class="p-0 h-auto min-h-0 bg-transparent border-none hover:bg-transparent"
                >
                    <x-mary-badge :value="$category" class="badge-info badge-outline badge-xs text-nowrap cursor-pointer hover:badge-solid" />
                </x-mary-button>
            @else
                <span class="text-xs text-base-content/50">-</span>
            @endif
        @endscope

        @scope('cell_name', $task)
            <div>
                <div class="font-semibold text-sm">{{ $task->name }}</div>
                @if($task->description)
                    <div class="text-xs text-base-content/70" title="{{ $task->description }}">{{ Str::limit($task->description, 35) }}</div>
                @endif
                <div class="flex flex-wrap gap-1 mt-1">
                    @if($task->environments)
                        @foreach($task->environments as $env)
                            <x-mary-button
                                wire:click="$set('filterEnvironment', '{{ $env }}')"
                                class="p-0 h-auto min-h-0 bg-transparent border-none hover:bg-transparent"
                            >
                                <x-mary-badge :value="$env" class="badge-warning badge-outline badge-xs cursor-pointer hover:badge-solid" />
                            </x-mary-button>
                        @endforeach
                    @endif
                    @if($category = $task->categoryTag())
                        <x-mary-button
                            wire:click="$set('filterCategory', '{{ $category }}')"
                            class="p-0 h-auto min-h-0 bg-transparent border-none hover:bg-transparent"
                        >
                            <x-mary-badge :value="$category" class="badge-info badge-outline badge-xs text-nowrap cursor-pointer hover:badge-solid" />
                        </x-mary-button>
                    @endif
                    @if(isset($task->metadata['tags']))
                        @foreach($task->metadata['tags'] as $tag)
                            <x-mary-button
                                wire:click="$wire.set('filterTag', ['{{ $tag }}'])"
                                class="p-0 h-auto min-h-0 bg-transparent border-none hover:bg-transparent"
                            >
                                <x-mary-badge :value="$tag" class="badge-ghost badge-xs cursor-pointer hover:badge-outline" />
                            </x-mary-button>
                        @endforeach
                    @endif
                </div>
            </div>
        @endscope

        @scope('cell_signature', $task)
            <code class="text-sm">{{ $task->signature }}</code>
        @endscope

        @scope('cell_expression', $task)
            <div class="text-sm font-mono text-nowrap">{{ $task->expression }}</div>
            <div class="text-xs">{{ $task->human_cron }}</div>
        @endscope

        @scope('cell_human_cron', $task)
            <span class="text-xs">{{ $task->human_cron }}</span>
        @endscope

        @scope('cell_next_run', $task)
            <span class="text-xs text-nowrap">
                @if($task->next_run)
                    {{ $task->next_run->format('H:i d/m') }}
                    <div class="text-[10px] text-base-content/50">{{ $task->next_run->diffForHumans() }}</div>
                @else
                    -
                @endif
            </span>
        @endscope

        @scope('cell_options', $task)
            @if($task->options && count($task->options) > 0)
                <div class="flex flex-wrap gap-1">
                    @foreach($task->options as $key => $value)
                        <x-mary-badge class="badge-ghost badge-sm" :value="is_bool($value) ? ($value ? $key : 'no-'.$key) : $key.'='.$value" />
                    @endforeach
                </div>
            @else
                <span class="text-xs text-base-content/50">None</span>
            @endif
        @endscope

        @scope('cell_run_window', $task)
            <x-mary-badge :value="$task->run_window->label()" class="badge-outline {{ run_window_badge_class($task->run_window) }} badge-xs text-nowrap" />
        @endscope

        @scope('cell_stats', $task)
            <div class="text-xs">
                <div class="text-nowrap">Runs: {{ $task->run_count }}</div>
                <div class="text-error text-nowrap">Fail: {{ $task->failure_count }}</div>
                <div class="text-nowrap">Success: {{ number_format($task->successRate(), 1) }}%</div>
            </div>
        @endscope

        @scope('cell_actions', $task)
            <div class="flex gap-2">
                <x-mary-button
                    icon="o-eye"
                    link="{{ route('admin.scheduler.tasks.show', $task) }}"
                    class="btn-sm btn-ghost"
                    tooltip="View Details"
                />
                @if(!$task->isConsoleOnly())
                    <x-mary-button
                        icon="o-play"
                        wire:click="runTask({{ $task->id }})"
                        wire:loading.attr="disabled"
                        class="btn-sm btn-primary"
                        tooltip="Run Manual"
                    />
                @endif
                <x-mary-button
                    icon="{{ $task->enabled ? 'o-x-circle' : 'o-check-circle' }}"
                    wire:click="toggleTask({{ $task->id }})"
                    class="btn-sm {{ $task->enabled ? 'btn-error' : 'btn-success' }}"
                    tooltip="{{ $task->enabled ? 'Disable' : 'Enable' }}"
                />
            </div>
        @endscope
    </x-mary-table>
</div>
