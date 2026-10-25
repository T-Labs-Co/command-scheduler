<?php

declare(strict_types=1);

namespace TLabsCo\CommandScheduler\Core\Scheduling\Domain\ValueObjects;

use TLabsCo\CommandScheduler\Core\Scheduling\Domain\Enums\RunWindow;

/**
 * Fluent schedule definition builder.
 *
 * Provides a Laravel-like chainable interface for defining
 * command schedules with trading hours support.
 */
class ScheduleDefinition
{
    protected string $description = '';

    protected string $expression = '*/5 * * * *';

    /**
     * @var array<string|int, mixed>
     */
    protected array $options = [];

    protected RunWindow $runWindow = RunWindow::ALWAYS;

    protected int $priority = 100;

    protected bool $enabled = true;

    protected ?string $timezone = null;

    protected bool $withoutOverlapping = false;

    protected bool $onOneServer = false;

    /**
     * @var array<string>
     */
    protected array $environments = [];

    /**
     * @var array<string, mixed>
     */
    protected array $metadata = [];

    protected ?string $outputLog = null;

    /**
     * Create a new schedule definition instance.
     */
    public function __construct(protected string $name) {}

    /**
     * Set the cron expression directly.
     */
    public function cron(string $expression): static
    {
        $this->expression = $expression;

        return $this;
    }

    /**
     * Set the command options/arguments.
     *
     * @param  array<string|int, mixed>  $options
     */
    public function options(array $options): static
    {
        $this->options = $options;

        return $this;
    }

    /**
     * Schedule the command to run every minute.
     */
    public function everyMinute(): static
    {
        return $this->cron('* * * * *');
    }

    /**
     * Schedule the command to run every N minutes.
     */
    public function everyNMinutes(int $minutes): static
    {
        return $this->cron("*/{$minutes} * * * *");
    }

    /**
     * Schedule the command to run every five minutes.
     */
    public function everyFiveMinutes(): static
    {
        return $this->everyNMinutes(5);
    }

    /**
     * Schedule the command to run every ten minutes.
     */
    public function everyTenMinutes(): static
    {
        return $this->everyNMinutes(10);
    }

    /**
     * Schedule the command to run every fifteen minutes.
     */
    public function everyFifteenMinutes(): static
    {
        return $this->everyNMinutes(15);
    }

    /**
     * Schedule the command to run every thirty minutes.
     */
    public function everyThirtyMinutes(): static
    {
        return $this->everyNMinutes(30);
    }

    /**
     * Schedule the command to run hourly.
     */
    public function hourly(): static
    {
        return $this->cron('0 * * * *');
    }

    /**
     * Schedule the command to run hourly at a specific minute.
     */
    public function hourlyAt(int $minute): static
    {
        return $this->cron("{$minute} * * * *");
    }

    /**
     * Schedule the command to run every N hours.
     */
    public function everyNHours(int $hours): static
    {
        return $this->cron("0 */{$hours} * * *");
    }

    /**
     * Schedule the command to run daily.
     */
    public function daily(): static
    {
        return $this->cron('0 0 * * *');
    }

    /**
     * Schedule the command to run daily at a specific time.
     */
    public function dailyAt(string $time): static
    {
        [$hour, $minute] = explode(':', $time);

        return $this->cron("{$minute} {$hour} * * *");
    }

    /**
     * Schedule the command to run twice daily.
     */
    public function twiceDaily(int $first = 1, int $second = 13): static
    {
        return $this->cron("0 {$first},{$second} * * *");
    }

    /**
     * Schedule the command to run weekly.
     */
    public function weekly(): static
    {
        return $this->cron('0 0 * * 0');
    }

    /**
     * Schedule the command to run weekly on a specific day and time.
     */
    public function weeklyOn(int $day, string $time = '0:0'): static
    {
        [$hour, $minute] = explode(':', $time);

        return $this->cron("{$minute} {$hour} * * {$day}");
    }

    /**
     * Schedule the command to run monthly.
     */
    public function monthly(): static
    {
        return $this->cron('0 0 1 * *');
    }

    /**
     * Schedule the command to run monthly on a specific day and time.
     */
    public function monthlyOn(int $day = 1, string $time = '0:0'): static
    {
        [$hour, $minute] = explode(':', $time);

        return $this->cron("{$minute} {$hour} {$day} * *");
    }

    /**
     * Schedule the command to run on weekdays only.
     */
    public function weekdays(): static
    {
        return $this->cron('0 0 * * 1-5');
    }

    /**
     * Schedule the command to run on weekends only.
     */
    public function weekends(): static
    {
        return $this->cron('0 0 * * 0,6');
    }

    /**
     * Schedule the command to run on Mondays.
     */
    public function mondays(): static
    {
        return $this->cron('0 0 * * 1');
    }

    /**
     * Schedule the command to run on Tuesdays.
     */
    public function tuesdays(): static
    {
        return $this->cron('0 0 * * 2');
    }

    /**
     * Schedule the command to run on Wednesdays.
     */
    public function wednesdays(): static
    {
        return $this->cron('0 0 * * 3');
    }

    /**
     * Schedule the command to run on Thursdays.
     */
    public function thursdays(): static
    {
        return $this->cron('0 0 * * 4');
    }

    /**
     * Schedule the command to run on Fridays.
     */
    public function fridays(): static
    {
        return $this->cron('0 0 * * 5');
    }

    /**
     * Schedule the command to run on Saturdays.
     */
    public function saturdays(): static
    {
        return $this->cron('0 0 * * 6');
    }

    /**
     * Schedule the command to run on Sundays.
     */
    public function sundays(): static
    {
        return $this->cron('0 0 * * 0');
    }

    /**
     * Set the run window to trading hours.
     */
    public function tradingHours(): static
    {
        $this->runWindow = RunWindow::TRADING;

        return $this;
    }

    /**
     * Set the run window to market closed.
     */
    public function whenMarketClosed(): static
    {
        $this->runWindow = RunWindow::CLOSED;

        return $this;
    }

    /**
     * Set the run window to pre-market hours.
     */
    public function duringPreMarket(): static
    {
        $this->runWindow = RunWindow::PREMARKET;

        return $this;
    }

    /**
     * Set the run window to after-hours.
     */
    public function duringAfterHours(): static
    {
        $this->runWindow = RunWindow::AFTERHOURS;

        return $this;
    }

    /**
     * Set the run window to trading days only.
     */
    public function onTradingDays(): static
    {
        $this->runWindow = RunWindow::TRADING_DAYS;

        return $this;
    }

    /**
     * Set the run window to non-trading days only.
     */
    public function onNonTradingDays(): static
    {
        $this->runWindow = RunWindow::NON_TRADING_DAYS;

        return $this;
    }

    /**
     * Set the run window for the schedule.
     */
    public function runWindow(RunWindow $window): static
    {
        $this->runWindow = $window;

        return $this;
    }

    /**
     * Set the run window to working hours only.
     */
    public function duringWorkingHours(): static
    {
        $this->runWindow = RunWindow::WORKING_HOURS;

        return $this;
    }

    /**
     * Set the run window to before working hours.
     */
    public function beforeWorkingHours(): static
    {
        $this->runWindow = RunWindow::BEFORE_WORKING_HOURS;

        return $this;
    }

    /**
     * Set the run window to after working hours.
     */
    public function afterWorkingHours(): static
    {
        $this->runWindow = RunWindow::AFTER_WORKING_HOURS;

        return $this;
    }

    /**
     * Set the run window to working days only.
     */
    public function onWorkingDays(): static
    {
        $this->runWindow = RunWindow::WORKING_DAYS;

        return $this;
    }

    /**
     * Set the run window to rest time only.
     */
    public function duringRestTime(): static
    {
        $this->runWindow = RunWindow::REST_TIME;

        return $this;
    }

    /**
     * Set the run window to holidays only.
     */
    public function onHolidays(): static
    {
        $this->runWindow = RunWindow::HOLIDAY;

        return $this;
    }

    /**
     * Set the run window to weekends only.
     */
    public function onWeekends(): static
    {
        $this->runWindow = RunWindow::WEEKEND;

        return $this;
    }

    /**
     * Set the description for the scheduled command.
     */
    public function description(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Set the priority (lower number = higher priority).
     */
    public function priority(int $priority): static
    {
        $this->priority = $priority;

        return $this;
    }

    /**
     * Disable the scheduled task by default.
     */
    public function disabled(): static
    {
        $this->enabled = false;

        return $this;
    }

    /**
     * Set the timezone for this task.
     */
    public function timezone(string $timezone): static
    {
        $this->timezone = $timezone;

        return $this;
    }

    /**
     * Do not allow the command to overlap itself.
     */
    public function withoutOverlapping(): static
    {
        $this->withoutOverlapping = true;

        return $this;
    }

    /**
     * Run the command on one server only.
     */
    public function onOneServer(): static
    {
        $this->onOneServer = true;

        return $this;
    }

    /**
     * Set the environments the command should run in.
     *
     * @param  string|array<string>  $environments
     */
    public function environments(string|array $environments): static
    {
        $this->environments = is_array($environments) ? $environments : func_get_args();

        return $this;
    }

    /**
     * Limit the command to only run in the production environment.
     */
    public function production(): static
    {
        return $this->environments('production');
    }

    /**
     * Limit the command to only run in the local environment.
     */
    public function local(): static
    {
        return $this->environments('local');
    }

    /**
     * Set the output log file path.
     */
    public function outputLog(string $path): static
    {
        $this->outputLog = $path;

        return $this;
    }

    /**
     * Add metadata to the scheduled task.
     *
     * @param  array<string, mixed>  $metadata
     */
    public function metadata(array $metadata): static
    {
        $this->metadata = array_merge($this->metadata, $metadata);

        return $this;
    }

    /**
     * Set the category tag for the scheduled task.
     */
    public function category(string $category): static
    {
        return $this->metadata(['category' => $category]);
    }

    /**
     * Set the tags for the scheduled task.
     *
     * @param  array<string>  $tags
     */
    public function tags(array $tags): static
    {
        return $this->metadata(['tags' => $tags]);
    }

    /**
     * Get the name of the schedule.
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Get the description of the schedule.
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * Get the cron expression.
     */
    public function getExpression(): string
    {
        return $this->expression;
    }

    /**
     * Get the command options.
     *
     * @return array<string|int, mixed>
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * Get the run window.
     */
    public function getRunWindow(): RunWindow
    {
        return $this->runWindow;
    }

    /**
     * Get the priority.
     */
    public function getPriority(): int
    {
        return $this->priority;
    }

    /**
     * Check if the task is enabled.
     */
    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    /**
     * Get the timezone.
     */
    public function getTimezone(): ?string
    {
        return $this->timezone;
    }

    /**
     * Check if the task should not overlap.
     */
    public function shouldNotOverlap(): bool
    {
        return $this->withoutOverlapping;
    }

    /**
     * Check if the task should run on one server only.
     */
    public function shouldRunOnOneServer(): bool
    {
        return $this->onOneServer;
    }

    /**
     * Get the environments.
     *
     * @return array<string>
     */
    public function getEnvironments(): array
    {
        return $this->environments;
    }

    /**
     * Get the metadata.
     *
     * @return array<string, mixed>
     */
    public function getMetadata(): array
    {
        return $this->metadata;
    }

    /**
     * Get the output log path.
     */
    public function getOutputLog(): ?string
    {
        return $this->outputLog;
    }

    /**
     * Convert the definition to an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'description' => $this->description,
            'expression' => $this->expression,
            'options' => $this->options,
            'run_window' => $this->runWindow->value,
            'priority' => $this->priority,
            'enabled' => $this->enabled,
            'timezone' => $this->timezone,
            'without_overlapping' => $this->withoutOverlapping,
            'on_one_server' => $this->onOneServer,
            'environments' => $this->environments,
            'metadata' => $this->metadata,
            'output_log' => $this->outputLog,
        ];
    }
}
