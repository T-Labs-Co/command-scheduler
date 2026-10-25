<?php

namespace TLabsCo\CommandScheduler\Attributes;

use Attribute;

/**
 * Shortcut attribute for scheduling a command with a category.
 *
 * Usage: #[ScheduledCategory('sync')]
 */
#[Attribute(Attribute::TARGET_CLASS)]
class ScheduledCategory extends ScheduledAttribute
{
    public function __construct(
        public string $category
    ) {}

    public function configure(Scheduled $scheduled): void
    {
        $scheduled->category = $this->category;
    }
}
