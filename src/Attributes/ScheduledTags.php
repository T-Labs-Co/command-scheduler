<?php

namespace TLabsCo\CommandScheduler\Attributes;

use Attribute;

/**
 * Shortcut attribute for scheduling a command with tags.
 *
 * Usage: #[ScheduledTags(['stock', 'sync'])]
 */
#[Attribute(Attribute::TARGET_CLASS | Attribute::IS_REPEATABLE)]
class ScheduledTags extends ScheduledAttribute
{
    /**
     * @param  array<string>  $tags
     */
    public function __construct(
        public array $tags
    ) {}

    public function configure(Scheduled $scheduled): void
    {
        $scheduled->tags = array_merge($scheduled->tags, $this->tags);
    }
}
