<?php

namespace TLabsCo\CommandScheduler\Attributes;

use Attribute;

/**
 * Shortcut attribute for scheduling a command with a specific name.
 *
 * Usage: #[ScheduledName('Daily Stock Sync')]
 */
#[Attribute(Attribute::TARGET_CLASS)]
class ScheduledName extends ScheduledAttribute
{
    public function __construct(
        public string $name
    ) {}

    public function configure(Scheduled $scheduled): void
    {
        $scheduled->name = $this->name;
    }
}
