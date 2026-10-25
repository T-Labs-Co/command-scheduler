<?php

namespace TLabsCo\CommandScheduler\Core\Scheduling\Infrastructure\RunWindow;

use TLabsCo\CommandScheduler\Core\Scheduling\Domain\Contracts\RunWindowResolverInterface;
use TLabsCo\CommandScheduler\Core\Scheduling\Domain\Enums\RunWindow;

/**
 * Default run window resolver that delegates working-hours windows and passes trading windows.
 *
 * This is the fallback implementation when no trading calendar is available.
 * Working-hours windows are resolved using config-based logic via WorkingHoursRunWindowResolver.
 * Trading windows always pass since there's no trading calendar in the default setup.
 */
class DefaultRunWindowResolver implements RunWindowResolverInterface
{
    public function __construct(
        protected WorkingHoursRunWindowResolver $workingHoursResolver
    ) {}

    /**
     * Determine if a task should run based on the window type.
     */
    public function shouldRun(RunWindow $window): bool
    {
        if ($window->isWorkingHoursWindow()) {
            return $this->workingHoursResolver->shouldRun($window);
        }

        // Trading windows and ALWAYS always pass in the default resolver
        return true;
    }
}
