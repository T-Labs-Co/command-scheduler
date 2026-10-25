<?php

namespace TLabsCo\CommandScheduler\Attributes;

use Attribute;

/**
 * Shortcut attribute for scheduling a command with a specific timezone.
 *
 * Usage: #[ScheduledTimezone('UTC')]
 */
#[Attribute(Attribute::TARGET_CLASS)]
class ScheduledTimezone extends ScheduledAttribute
{
    public function __construct(
        public string $timezone
    ) {}

    public function configure(Scheduled $scheduled): void
    {
        $scheduled->timezone = $this->timezone;
    }
}
