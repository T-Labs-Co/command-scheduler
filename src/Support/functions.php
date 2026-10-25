<?php

use Illuminate\Support\Facades\Gate;
use TLabsCo\CommandScheduler\Core\Scheduling\Domain\Enums\RunWindow;

if (! function_exists('normalize_command_options')) {
    /**
     * Normalize command options: convert numeric-keyed strings like "--force" to ["--force" => true]
     * and ensure all string keys start with "--" if they are options.
     * This is required because Artisan::call and Schedule::command interpret numeric-keyed arrays as positional arguments
     * and string keys without "--" as arguments.
     *
     * @param  array<string|int, mixed>  $options  Command options and arguments
     * @return array<string|int, mixed> Normalized options
     */
    function normalize_command_options(array $options): array
    {
        $normalizedOptions = [];
        foreach ($options as $key => $value) {
            // Convert boolean-like values to actual booleans
            if ($value === 'true' || $value === '1') {
                $value = true;
            } elseif ($value === 'false' || $value === '0') {
                $value = false;
            }

            if (is_int($key)) {
                if (is_string($value) && str_starts_with($value, '-')) {
                    $normalizedOptions[] = $value;
                } else {
                    $normalizedOptions[$key] = $value;
                }
            } else {
                // Handle string keys
                $fullKey = str_starts_with($key, '-') ? $key : '--'.$key;

                if (is_bool($value)) {
                    if ($value) {
                        $normalizedOptions[] = $fullKey;
                    }
                    // If false, we skip it entirely
                } else {
                    $normalizedOptions[$fullKey] = $value;
                }
            }
        }

        return $normalizedOptions;
    }
}

if (! function_exists('duration_to_human')) {
    /**
     * Convert duration in milliseconds to human readable format.
     */
    function duration_to_human(int|float $durationMs): string
    {
        $seconds = $durationMs / 1000;

        if ($seconds < 0.1) {
            return $durationMs.'ms';
        }

        if ($seconds < 1) {
            return round($seconds * 1000).'ms';
        }

        if ($seconds < 60) {
            return round($seconds, 2).'s';
        }

        $minutes = floor($seconds / 60);
        $remainingSeconds = $seconds % 60;

        if ($minutes < 60) {
            return "{$minutes}m ".round($remainingSeconds).'s';
        }

        $hours = floor($minutes / 60);
        $remainingMinutes = $minutes % 60;

        return "{$hours}h {$remainingMinutes}m";
    }
}

if (! function_exists('memory_to_human')) {
    /**
     * Convert memory in bytes to human readable format.
     */
    function memory_to_human(int|float $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $index = 0;

        while ($bytes >= 1024 && $index < count($units) - 1) {
            $bytes /= 1024;
            $index++;
        }

        return round($bytes, 2).' '.$units[$index];
    }
}

if (! function_exists('check_scheduler_permission')) {
    /**
     * Check if the current user has permission to access the scheduler dashboard.
     *
     * Uses a configurable Laravel Gate defined in the host application.
     *
     * @return bool True if authorized, throws AuthorizationException if not
     */
    function check_scheduler_permission(): bool
    {
        Gate::authorize(
            config('command-scheduler.dashboard.sync_gate', 'viewSchedulerDashboard')
        );

        return true;
    }
}

if (! function_exists('run_window_badge_class')) {
    /**
     * Get the DaisyUI badge class for a run window.
     *
     * Presentation-layer helper that keeps UI concerns out of the Domain enum.
     */
    function run_window_badge_class(RunWindow $window): string
    {
        return match ($window) {
            RunWindow::ALWAYS => 'badge-info',
            RunWindow::TRADING => 'badge-success',
            RunWindow::CLOSED => 'badge-neutral',
            RunWindow::PREMARKET => 'badge-warning',
            RunWindow::AFTERHOURS => 'badge-primary',
            RunWindow::TRADING_DAYS => 'badge-accent',
            RunWindow::NON_TRADING_DAYS => 'badge-ghost',
            RunWindow::WORKING_HOURS => 'badge-info',
            RunWindow::BEFORE_WORKING_HOURS => 'badge-warning',
            RunWindow::AFTER_WORKING_HOURS => 'badge-primary',
            RunWindow::WORKING_DAYS => 'badge-success',
            RunWindow::REST_TIME => 'badge-neutral',
            RunWindow::HOLIDAY => 'badge-ghost',
            RunWindow::WEEKEND => 'badge-secondary',
        };
    }
}
