<?php

namespace TLabsCo\CommandScheduler\Attributes;

use Attribute;

/**
 * Shortcut attribute for scheduling a command without overlapping.
 *
 * Usage: #[ScheduledWithoutOverlapping]
 */
#[Attribute(Attribute::TARGET_CLASS)]
class ScheduledWithoutOverlapping extends ScheduledAttribute
{
    public function configure(Scheduled $scheduled): void
    {
        $scheduled->withoutOverlapping = true;
    }
}
