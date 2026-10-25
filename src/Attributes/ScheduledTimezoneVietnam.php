<?php

namespace TLabsCo\CommandScheduler\Attributes;

use Attribute;

/**
 * Shortcut attribute for scheduling a command with Vietnam timezone (Asia/Ho_Chi_Minh).
 *
 * Usage: #[ScheduledTimezoneVietnam]
 */
#[Attribute(Attribute::TARGET_CLASS)]
class ScheduledTimezoneVietnam extends ScheduledAttribute
{
    public function configure(Scheduled $scheduled): void
    {
        $scheduled->timezone = 'Asia/Ho_Chi_Minh';
    }
}
