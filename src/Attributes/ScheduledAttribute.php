<?php

namespace TLabsCo\CommandScheduler\Attributes;

use TLabsCo\CommandScheduler\Core\Scheduling\Domain\Contracts\ScheduledConfigurator;

abstract class ScheduledAttribute implements ScheduledConfigurator
{
    abstract public function configure(Scheduled $scheduled): void;
}
