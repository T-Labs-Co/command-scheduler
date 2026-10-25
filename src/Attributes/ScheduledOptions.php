<?php

namespace TLabsCo\CommandScheduler\Attributes;

use Attribute;

/**
 * Attribute to configure scheduled command options.
 *
 * Usage: #[ScheduledOptions(['--force' => true, 'name' => 'John'])]
 */
#[Attribute(Attribute::TARGET_CLASS | Attribute::IS_REPEATABLE)]
class ScheduledOptions extends ScheduledAttribute
{
    /**
     * @param  array<string|int, mixed>  $options  Command options and arguments
     */
    public function __construct(
        public array $options = []
    ) {}

    /**
     * Configure the scheduled command with the provided options.
     */
    public function configure(Scheduled $scheduled): void
    {
        $scheduled->options = array_merge($scheduled->options, $this->options);
    }
}
