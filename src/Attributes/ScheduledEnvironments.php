<?php

namespace TLabsCo\CommandScheduler\Attributes;

use Attribute;

/**
 * Shortcut attribute for scheduling a command with specific environments.
 *
 * Usage: #[ScheduledEnvironments(['production', 'staging'])]
 */
#[Attribute(Attribute::TARGET_CLASS | Attribute::IS_REPEATABLE)]
class ScheduledEnvironments extends ScheduledAttribute
{
    /**
     * @var array<string>
     */
    public array $environments;

    /**
     * @param  string|array<string>  $environments
     */
    public function __construct(
        string|array $environments
    ) {
        $this->environments = is_array($environments)
            ? $environments
            /* @phpstan-ignore-next-line */
            : array_values(array_filter(explode(',', $environments), trim(...)));
    }

    public function configure(Scheduled $scheduled): void
    {
        $scheduled->environments = array_unique(array_merge($scheduled->environments ?? [], $this->environments));
    }
}
