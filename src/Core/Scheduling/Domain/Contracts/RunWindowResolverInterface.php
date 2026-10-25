<?php

declare(strict_types=1);

namespace TLabsCo\CommandScheduler\Core\Scheduling\Domain\Contracts;

use TLabsCo\CommandScheduler\Core\Scheduling\Domain\Enums\RunWindow;

/**
 * Contract for resolving whether a scheduled task should run based on its run window.
 *
 * Implementations can provide different strategies:
 * - DefaultRunWindowResolver: Always allows execution (no-op)
 * - TradingCalendarRunWindowResolver: Checks trading calendar and session status
 */
interface RunWindowResolverInterface
{
    /**
     * Determine if a task should run based on its run window.
     *
     * @param  RunWindow  $window  The run window constraint for the task
     * @return bool True if the task should run, false to skip
     */
    public function shouldRun(RunWindow $window): bool;
}
