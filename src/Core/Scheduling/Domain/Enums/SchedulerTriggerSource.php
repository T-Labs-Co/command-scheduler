<?php

declare(strict_types=1);

namespace TLabsCo\CommandScheduler\Core\Scheduling\Domain\Enums;

/**
 * Scheduler trigger source defines how a command was triggered.
 */
enum SchedulerTriggerSource: string
{
    /**
     * Command was triggered manually via CLI or UI.
     */
    case MANUAL = 'manual';

    /**
     * Command was triggered by the scheduler (cron).
     */
    case SCHEDULE = 'schedule';

    /**
     * Get human-readable label for the trigger source.
     */
    public function label(): string
    {
        return match ($this) {
            self::MANUAL => 'Manual',
            self::SCHEDULE => 'Schedule',
        };
    }

    /**
     * Get icon for the trigger source.
     */
    public function icon(): string
    {
        return match ($this) {
            self::MANUAL => '👤',
            self::SCHEDULE => '⏰',
        };
    }
}
