<div>
    <x-mary-header title="{{ $task->name }}" subtitle="{{ $task->signature }}" separator>
        <x-slot:middle>
            @if($category = $task->categoryTag())
                <x-mary-badge :value="$category" class="badge-info badge-outline" />
            @endif
        </x-slot:middle>
        <x-slot:actions>
            @if(!$task->isConsoleOnly())
                <x-mary-button
                    label="Run"
                    icon="o-play"
                    wire:click="runTask"
                    wire:loading.attr="disabled"
                    class="btn-primary"
                />
            @endif
            <x-mary-button
                label="{{ $task->enabled ? 'Disable' : 'Enable' }}"
                icon="{{ $task->enabled ? 'o-x-circle' : 'o-check-circle' }}"
                wire:click="toggleTask"
                class="{{ $task->enabled ? 'btn-error' : 'btn-success' }}"
            />
            <x-mary-button
                label="All Tasks"
                icon="o-arrow-left"
                link="{{ route('admin.scheduler.tasks') }}"
                class="btn-ghost"
            />
        </x-slot:actions>
    </x-mary-header>

    {{-- Task Information --}}
    <div class="grid grid-cols-1 gap-6 mb-8 md:grid-cols-2">
        <x-mary-card title="Task Details">
            <div class="space-y-3">
                <div>
                    <span class="font-semibold">Status:</span>
                    @if($task->enabled)
                        <x-mary-badge value="Enabled" class="badge-success" />
                    @else
                        <x-mary-badge value="Disabled" class="badge-ghost" />
                    @endif
                </div>
                <div>
                    <span class="font-semibold">Description:</span>
                    <p class="mt-1 text-base-content/70">{{ $task->description ?: 'No description' }}</p>
                </div>
                <div>
                    <span class="font-semibold">Schedule:</span>
                    <code class="ml-2">{{ $task->expression }}</code>
                    <div class="mt-1 ml-2 text-sm text-base-content/70">{{ $task->human_cron }}</div>
                </div>
                <div>
                    <span class="font-semibold">Options:</span>
                    @if($task->options && count($task->options) > 0)
                        <div class="flex flex-wrap gap-1 mt-1 ml-2">
                            @foreach($task->options as $key => $value)
                                <x-mary-badge class="badge-ghost badge-sm" :value="is_bool($value) ? ($value ? $key : 'no-'.$key) : $key.'='.$value" />
                            @endforeach
                        </div>
                    @else
                        <span class="ml-2 text-base-content/50">None</span>
                    @endif
                </div>
                <div>
                    <span class="font-semibold">Run Window:</span>
                    <x-mary-badge :value="$task->run_window->label()" class="ml-2 badge-outline {{ run_window_badge_class($task->run_window) }}" />
                </div>
                <div>
                    <span class="font-semibold">Priority:</span>
                    <span class="ml-2">{{ $task->priority }}</span>
                </div>
                @if($task->timezone)
                <div>
                    <span class="font-semibold">Timezone:</span>
                    <span class="ml-2">{{ $task->timezone }}</span>
                </div>
                @endif
            </div>
        </x-mary-card>

        <x-mary-card title="Statistics">
            <div class="space-y-3">
                <div>
                    <span class="font-semibold">Total Runs:</span>
                    <span class="ml-2">{{ $task->run_count }}</span>
                </div>
                <div>
                    <span class="font-semibold">Failures:</span>
                    <span class="ml-2 text-error">{{ $task->failure_count }}</span>
                </div>
                <div>
                    <span class="font-semibold">Success Rate:</span>
                    <span class="ml-2">{{ number_format($task->successRate(), 2) }}%</span>
                </div>
                <div>
                    <span class="font-semibold">Avg Duration:</span>
                    <span class="ml-2">{{ $task->averageDurationForHumans() }}</span>
                </div>
                <div>
                    <span class="font-semibold">Last Run:</span>
                    <span class="ml-2">{{ $task->last_run_at?->format('H:i d/m') ?? 'Never' }}</span>
                </div>
                <div>
                    <span class="font-semibold">Next Run:</span>
                    <span class="ml-2">
                        @if($task->next_run)
                            {{ $task->next_run->format('H:i d/m') }}
                            <span class="ml-1 text-sm text-base-content/50">({{ $task->next_run->diffForHumans() }})</span>
                        @else
                            Not scheduled
                        @endif
                    </span>
                </div>
            </div>
        </x-mary-card>
    </div>

    {{-- Run History --}}
    <x-mary-header title="Run History" separator />

    <x-mary-table :headers="$this->headers" :rows="$runs" with-pagination>
        @scope('cell_status', $run)
            @if($run->wasSuccessful())
                <x-mary-badge value="Success" class="badge-success" />
            @else
                <x-mary-badge value="Failed" class="badge-error" />
            @endif
        @endscope

        @scope('cell_started_at', $run)
            <div>
                <div>{{ $run->started_at->format('Y-m-d') }}</div>
                <div class="text-sm text-base-content/70">{{ $run->started_at->format('H:i:s') }}</div>
            </div>
        @endscope

        @scope('cell_duration', $run)
            {{ $run->durationForHumans() }}
        @endscope

        @scope('cell_memory', $run)
            {{ $run->memoryUsageForHumans() ?? 'N/A' }}
        @endscope

        @scope('cell_triggered_by', $run)
            <x-mary-badge :value="$run->triggered_by->label()" class="badge-outline badge-sm" />
        @endscope

        @scope('cell_exit_code', $run)
            <code class="text-sm">{{ $run->exit_code }}</code>
        @endscope
    </x-mary-table>
</div>
