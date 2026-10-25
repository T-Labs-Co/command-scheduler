<?php

namespace TLabsCo\CommandScheduler\Attributes;

use Attribute;
use TLabsCo\CommandScheduler\Core\Scheduling\Domain\Enums\RunWindow;

/**
 * Attribute to configure the run window for a scheduled command.
 *
 * Usage: #[ScheduledRunWindow(RunWindow::TRADING)]
 */
#[Attribute(Attribute::TARGET_CLASS)]
class ScheduledRunWindow extends ScheduledAttribute
{
    /**
     * @param  RunWindow  $runWindow  The run window defining when the command can execute
     */
    public function __construct(
        public RunWindow $runWindow
    ) {}

    /**
     * Configure the scheduled command with the provided run window.
     */
    public function configure(Scheduled $scheduled): void
    {
        $scheduled->runWindow = $this->runWindow;
    }
}
