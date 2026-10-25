<?php

namespace TLabsCo\CommandScheduler\Attributes;

use Attribute;

/**
 * Shortcut attribute for scheduling a command with UTC timezone.
 *
 * Usage: #[ScheduledTimezoneUTC]
 */
#[Attribute(Attribute::TARGET_CLASS)]
class ScheduledTimezoneUTC extends ScheduledAttribute
{
    public function configure(Scheduled $scheduled): void
    {
        $scheduled->timezone = 'UTC';
    }
}
