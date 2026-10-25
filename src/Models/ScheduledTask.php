<?php

namespace TLabsCo\CommandScheduler\Models;

use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use TLabsCo\CommandScheduler\Core\Scheduling\Domain\Enums\RunWindow;
use TLabsCo\CommandScheduler\Events\ScheduledTaskUpdated;
use TLabsCo\CommandScheduler\Models\Traits\WithCronExpressionDescriptor;

/**
 * ScheduledTask Model
 *
 * @property int $id
 * @property string $name
 * @property string $signature
 * @property string $class_name
 * @property string|null $description
 * @property string $expression
 * @property array<string|int, mixed>|null $options
 * @property int $priority
 * @property RunWindow $run_window
 * @property bool $enabled
 * @property array<string>|null $environments
 * @property string|null $timezone
 * @property bool $without_overlapping
 * @property bool $on_one_server
 * @property array<string, mixed>|null $metadata
 * @property string|null $output_log
 * @property Carbon|CarbonInterface|null $last_run_at
 * @property Carbon|CarbonInterface|null $next_run_at
 * @property int $run_count
 * @property int $failure_count
 * @property int $average_duration
 * @property Carbon|CarbonInterface $created_at
 * @property Carbon|CarbonInterface $updated_at
 * @property-read ScheduledCommandRun|null $latestRun
 * @property-read Collection<int, ScheduledCommandRun> $runs
 */
class ScheduledTask extends Model
{
    use WithCronExpressionDescriptor;

    /**
     * The event map for the model.
     */
    protected $dispatchesEvents = [
        'saved' => ScheduledTaskUpdated::class,
    ];

    /**
     * Get the table name from configuration.
     */
    #[\Override]
    public function getTable(): string
    {
        return config('command-scheduler.tables.scheduled_tasks', 'cs_scheduled_tasks');
    }

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'signature',
        'class_name',
        'description',
        'expression',
        'options',
        'priority',
        'run_window',
        'enabled',
        'environments',
        'timezone',
        'without_overlapping',
        'on_one_server',
        'metadata',
        'output_log',
        'last_run_at',
        'next_run_at',
        'run_count',
        'failure_count',
        'average_duration',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'enabled' => 'boolean',
        'without_overlapping' => 'boolean',
        'on_one_server' => 'boolean',
        'options' => 'array',
        'metadata' => 'array',
        'environments' => 'array',
        'last_run_at' => 'datetime',
        'next_run_at' => 'datetime',
        'run_window' => RunWindow::class,
    ];

    /**
     * Get all runs for this scheduled task.
     *
     * @return HasMany<ScheduledCommandRun, $this>
     */
    public function runs(): HasMany
    {
        return $this->hasMany(ScheduledCommandRun::class, 'scheduled_task_id');
    }

    /**
     * Get the latest run for this scheduled task.
     *
     * @return HasOne<ScheduledCommandRun, $this>
     */
    public function latestRun(): HasOne
    {
        return $this->hasOne(ScheduledCommandRun::class, 'scheduled_task_id')
            ->latestOfMany();
    }

    /**
     * Scope a query to only include enabled tasks.
     *
     * @param  Builder<ScheduledTask>  $query
     * @return Builder<ScheduledTask>
     */
    public function scopeEnabled(Builder $query): Builder
    {
        return $query->where('enabled', true);
    }

    /**
     * Scope a query to only include disabled tasks.
     *
     * @param  Builder<ScheduledTask>  $query
     * @return Builder<ScheduledTask>
     */
    public function scopeDisabled(Builder $query): Builder
    {
        return $query->where('enabled', false);
    }

    /**
     * Scope a query to order by priority (lower = higher priority).
     *
     * @param  Builder<ScheduledTask>  $query
     * @return Builder<ScheduledTask>
     */
    public function scopeByPriority(Builder $query): Builder
    {
        return $query->orderBy('priority', 'asc');
    }

    /**
     * Scope a query to filter by run window.
     *
     * @param  Builder<ScheduledTask>  $query
     * @return Builder<ScheduledTask>
     */
    public function scopeByRunWindow(Builder $query, string $window): Builder
    {
        return $query->where('run_window', $window);
    }

    public function disable(): void
    {
        $this->enabled = false;
        $this->save();
    }

    public function enable(): void
    {
        $this->enabled = true;
        $this->save();
    }

    public function toggleStatus(): void
    {
        $this->enabled = ! $this->enabled;
        $this->save();
    }

    /**
     * Get the average duration in seconds.
     */
    public function averageDurationInSeconds(): float
    {
        return $this->average_duration / 1000;
    }

    /**
     * Calculate the success rate based on run history.
     */
    public function successRate(): float
    {
        if ($this->run_count === 0) {
            return 0.0;
        }

        $successCount = $this->run_count - $this->failure_count;

        return ($successCount / $this->run_count) * 100;
    }

    /**
     * Get a human-readable average duration.
     */
    public function averageDurationForHumans(): string
    {
        return duration_to_human($this->average_duration);
    }

    /**
     * Get status color for UI badges.
     */
    public function statusColor(): string
    {
        if (! $this->enabled) {
            return 'gray';
        }

        if ($this->failure_count >= 3) {
            return 'red';
        }

        if ($this->failure_count > 0) {
            return 'yellow';
        }

        return 'green';
    }

    /**
     * Get category tag from metadata.
     */
    public function categoryTag(): ?string
    {
        return $this->metadata['category'] ?? null;
    }

    /**
     * Check if the task is console-only.
     */
    public function isConsoleOnly(): bool
    {
        $tags = $this->metadata['tags'] ?? [];

        return in_array('console-only', $tags, true);
    }
}
