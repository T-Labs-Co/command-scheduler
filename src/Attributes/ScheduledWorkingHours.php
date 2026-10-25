<?php

namespace TLabsCo\CommandScheduler\Attributes;

use Attribute;
use TLabsCo\CommandScheduler\Core\Scheduling\Domain\Enums\RunWindow;

/**
 * Shortcut attribute for scheduling a command to run only during working hours.
 *
 * Usage: #[ScheduledWorkingHours]
 */
#[Attribute(Attribute::TARGET_CLASS)]
class ScheduledWorkingHours extends ScheduledAttribute
{
    public function __construct(
        public ?string $expression = null,
    ) {}

    public function configure(Scheduled $scheduled): void
    {
        if ($this->expression !== null) {
            $scheduled->expression = $this->expression;
        }
        $scheduled->runWindow = RunWindow::WORKING_HOURS;
    }
}
