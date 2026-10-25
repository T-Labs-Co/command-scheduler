<?php

namespace TLabsCo\CommandScheduler\Attributes;

use Attribute;

/**
 * Shortcut attribute for scheduling a command to run every minute.
 *
 * Usage: #[ScheduledMinute]
 */
#[Attribute(Attribute::TARGET_CLASS)]
class ScheduledMinute extends ScheduledAttribute
{
    public function __construct(
        public ?string $name = null,
        public ?string $description = null,
    ) {}

    public function configure(Scheduled $scheduled): void
    {
        $scheduled->expression = '* * * * *';

        if ($this->name !== null) {
            $scheduled->name = $this->name;
        }

        if ($this->description !== null) {
            $scheduled->description = $this->description;
        }
    }
}
