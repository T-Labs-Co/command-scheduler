<?php

namespace TLabsCo\CommandScheduler\Attributes;

use Attribute;
use TLabsCo\CommandScheduler\Core\Scheduling\Domain\Enums\RunWindow;

/**
 * Shortcut attribute for scheduling a command to run only before working hours.
 *
 * Usage: #[ScheduledBeforeWorkingHours]
 */
#[Attribute(Attribute::TARGET_CLASS)]
class ScheduledBeforeWorkingHours extends ScheduledAttribute
{
    public function __construct(
        public ?string $expression = null,
    ) {}

    public function configure(Scheduled $scheduled): void
    {
        if ($this->expression !== null) {
            $scheduled->expression = $this->expression;
        }
        $scheduled->runWindow = RunWindow::BEFORE_WORKING_HOURS;
    }
}
