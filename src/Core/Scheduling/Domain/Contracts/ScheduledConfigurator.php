<?php

declare(strict_types=1);

namespace TLabsCo\CommandScheduler\Core\Scheduling\Domain\Contracts;

use TLabsCo\CommandScheduler\Attributes\Scheduled;

interface ScheduledConfigurator
{
    public function configure(Scheduled $scheduled): void;
}
