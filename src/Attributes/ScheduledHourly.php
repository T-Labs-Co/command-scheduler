<?php

namespace TLabsCo\CommandScheduler\Attributes;

use Attribute;

/**
 * Shortcut attribute for scheduling a command to run hourly.
 *
 * Usage: #[ScheduledHourly]
 */
#[Attribute(Attribute::TARGET_CLASS)]
class ScheduledHourly extends ScheduledAttribute
{
    public function __construct(
        public ?string $name = null,
        public ?string $description = null,
    ) {}

    public function configure(Scheduled $scheduled): void
    {
        $scheduled->expression = '0 * * * *';

        if ($this->name !== null) {
            $scheduled->name = $this->name;
        }

        if ($this->description !== null) {
            $scheduled->description = $this->description;
        }
    }
}
