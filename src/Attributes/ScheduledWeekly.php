<?php

namespace TLabsCo\CommandScheduler\Attributes;

use Attribute;

/**
 * Shortcut attribute for scheduling a command to run weekly.
 *
 * Usage:
 *  #[ScheduledWeekly(day: 'Sat', time: '01:00')]
 */
#[Attribute(Attribute::TARGET_CLASS)]
class ScheduledWeekly extends ScheduledAttribute
{
    public function __construct(
        public ?string $name = null,
        public ?string $description = null,
        public string $day = 'Sun',
        public string $time = '00:00',
    ) {}

    public function configure(Scheduled $scheduled): void
    {
        [$hour, $minute] = explode(':', $this->time);

        $dayMap = [
            'Sun' => 0,
            'Mon' => 1,
            'Tue' => 2,
            'Wed' => 3,
            'Thu' => 4,
            'Fri' => 5,
            'Sat' => 6,
        ];

        $dayOfWeek = $dayMap[$this->day] ?? $this->day;

        $scheduled->expression = "{$minute} {$hour} * * {$dayOfWeek}";

        if ($this->name !== null) {
            $scheduled->name = $this->name;
        }

        if ($this->description !== null) {
            $scheduled->description = $this->description;
        }
    }
}
