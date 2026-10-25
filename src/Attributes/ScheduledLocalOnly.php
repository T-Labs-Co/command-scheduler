<?php

namespace TLabsCo\CommandScheduler\Attributes;

use Attribute;

/**
 * Shortcut attribute for scheduling a command to run only in the local environment.
 *
 * Usage: #[ScheduledLocal]
 */
#[Attribute(Attribute::TARGET_CLASS)]
class ScheduledLocalOnly extends ScheduledAttribute
{
    public function configure(Scheduled $scheduled): void
    {
        $scheduled->environments = ['local'];
    }
}
