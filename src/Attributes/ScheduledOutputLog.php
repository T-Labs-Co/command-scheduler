<?php

namespace TLabsCo\CommandScheduler\Attributes;

use Attribute;

/**
 * Shortcut attribute for setting output log file for a scheduled command.
 *
 * Usage: #[ScheduledOutputLog('/path/to/log.log')]
 */
#[Attribute(Attribute::TARGET_CLASS)]
class ScheduledOutputLog extends ScheduledAttribute
{
    public function __construct(
        public string $path
    ) {}

    public function configure(Scheduled $scheduled): void
    {
        $scheduled->outputLog = $this->path;
    }
}
