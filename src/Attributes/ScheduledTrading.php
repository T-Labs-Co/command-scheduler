<?php

namespace TLabsCo\CommandScheduler\Attributes;

use Attribute;
use TLabsCo\CommandScheduler\Core\Scheduling\Domain\Enums\RunWindow;

/**
 * Shortcut attribute for scheduling a command to run during trading hours.
 *
 * Usage: #[ScheduledTrading]
 */
#[Attribute(Attribute::TARGET_CLASS)]
class ScheduledTrading extends ScheduledAttribute
{
    public function __construct(
        public ?string $expression = null,
    ) {}

    public function configure(Scheduled $scheduled): void
    {
        if ($this->expression !== null) {
            $scheduled->expression = $this->expression;
        }
        $scheduled->runWindow = RunWindow::TRADING;
    }
}
