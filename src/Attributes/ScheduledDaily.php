<?php

namespace TLabsCo\CommandScheduler\Attributes;

use Attribute;

/**
 * Shortcut attribute for scheduling a command to run daily.
 *
 * Usage:
 *  #[ScheduledDaily]
 *  #[ScheduledDaily(time: '10:00')]
 */
#[Attribute(Attribute::TARGET_CLASS)]
class ScheduledDaily extends ScheduledAttribute
{
    public function __construct(
        public ?string $time = null,
        public ?string $name = null,
        public ?string $description = null,
    ) {}

    public function configure(Scheduled $scheduled): void
    {
        if ($this->time !== null) {
            [$hour, $minute] = explode(':', $this->time);
            $scheduled->expression = "{$minute} {$hour} * * *";
        } else {
            $scheduled->expression = '0 0 * * *';
        }

        if ($this->name !== null) {
            $scheduled->name = $this->name;
        }

        if ($this->description !== null) {
            $scheduled->description = $this->description;
        }
    }
}
