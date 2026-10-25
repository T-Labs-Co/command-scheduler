<?php

namespace TLabsCo\CommandScheduler\Attributes;

use Attribute;

/**
 * Shortcut attribute for scheduling a command to run only in the production environment.
 *
 * Usage: #[ScheduledProduction]
 */
#[Attribute(Attribute::TARGET_CLASS)]
class ScheduledProductionOnly extends ScheduledAttribute
{
    public function configure(Scheduled $scheduled): void
    {
        $scheduled->environments = ['production'];
    }
}
