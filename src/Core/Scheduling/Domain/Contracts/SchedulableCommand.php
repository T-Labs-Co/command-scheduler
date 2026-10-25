<?php

declare(strict_types=1);

namespace TLabsCo\CommandScheduler\Core\Scheduling\Domain\Contracts;

use TLabsCo\CommandScheduler\Core\Scheduling\Domain\ValueObjects\ScheduleDefinition;

/**
 * Interface for schedulable commands.
 *
 * Commands implementing this interface will be auto-discovered
 * and registered with the scheduler.
 */
interface SchedulableCommand
{
    /**
     * Define the schedule configuration for this command.
     */
    public function scheduleConfig(): ScheduleDefinition;
}
