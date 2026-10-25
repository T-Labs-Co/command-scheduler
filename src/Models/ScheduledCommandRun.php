<?php

namespace TLabsCo\CommandScheduler\Models;

use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use TLabsCo\CommandScheduler\Core\Scheduling\Domain\Enums\SchedulerTriggerSource;

/**
 * ScheduledCommandRun Model
 *
 * @property int $id
 * @property string|null $track_id
 * @property int $scheduled_task_id
 * @property Carbon|CarbonInterface $started_at
 * @property Carbon|CarbonInterface|null $finished_at
 * @property int $duration
 * @property int $exit_code
 * @property string|null $output
 * @property int|null $memory_usage
 * @property SchedulerTriggerSource $triggered_by
 * @property Carbon|CarbonInterface $created_at
 * @property Carbon|CarbonInterface $updated_at
 * @property-read ScheduledTask $scheduledTask
 */
class ScheduledCommandRun extends Model
{
    /**
     * Get the table name from configuration.
     */
    #[\Override]
    public function getTable(): string
    {
        return config('command-scheduler.tables.scheduled_command_runs', 'cs_scheduled_command_runs');
    }

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'track_id',
        'scheduled_task_id',
        'started_at',
        'finished_at',
        'duration',
        'exit_code',
        'output',
        'memory_usage',
        'triggered_by',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
        'triggered_by' => SchedulerTriggerSource::class,
    ];

    /**
     * Get the scheduled task that owns this run.
     *
     * @return BelongsTo<ScheduledTask, $this>
     */
    public function scheduledTask(): BelongsTo
    {
        return $this->belongsTo(ScheduledTask::class, 'scheduled_task_id');
    }

    /**
     * Scope a query to only include outdated runs.
     *
     * @param  \Illuminate\Database\Eloquent\Builder<ScheduledCommandRun>  $query
     * @return \Illuminate\Database\Eloquent\Builder<ScheduledCommandRun>
     */
    public function scopeOutdated(\Illuminate\Database\Eloquent\Builder $query, ?int $days = null): \Illuminate\Database\Eloquent\Builder
    {
        $days ??= config('command-scheduler.prune_logs_days', 30);

        return $query->where('started_at', '<', now()->subDays($days));
    }

    /**
     * Get the duration in milliseconds (raw value).
     */
    public function durationInMilliseconds(): int
    {
        return $this->duration;
    }

    /**
     * Get the duration in seconds.
     */
    public function durationInSeconds(): float
    {
        return $this->duration / 1000;
    }

    /**
     * Get a human-readable duration.
     */
    public function durationForHumans(): string
    {
        return duration_to_human($this->duration);
    }

    /**
     * Get memory usage in human-readable format.
     */
    public function memoryUsageForHumans(): ?string
    {
        if ($this->memory_usage === null) {
            return null;
        }

        return memory_to_human($this->memory_usage);
    }

    /**
     * Check if the run was successful.
     */
    public function wasSuccessful(): bool
    {
        return $this->exit_code === 0;
    }

    /**
     * Check if the run failed.
     */
    public function failed(): bool
    {
        return $this->exit_code !== 0;
    }

    /**
     * Get status color for UI badges.
     */
    public function statusColor(): string
    {
        return $this->wasSuccessful() ? 'green' : 'red';
    }
}
