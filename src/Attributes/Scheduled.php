<?php

namespace TLabsCo\CommandScheduler\Attributes;

use Attribute;
use TLabsCo\CommandScheduler\Core\Scheduling\Domain\Enums\RunWindow;

/**
 * Attribute to mark and configure scheduled commands.
 *
 * Usage:
 * #[Scheduled(
 *     expression: '0 0 * * *',
 *     name: 'Daily Stock Sync',
 *     description: 'Synchronizes stock data daily at midnight',
 *     environments: ['production'],
 * )]
 */
#[Attribute(Attribute::TARGET_CLASS)]
class Scheduled
{
    /**
     * @param  array<string|int, mixed>  $options
     * @param  array<string>|null  $environments
     * @param  array<string>  $tags
     */
    public function __construct(
        // Default run every 5 minutes
        public string $expression = '*/5 * * * *',
        public ?string $name = null,
        public ?string $description = null,
        public array $options = [],
        public RunWindow $runWindow = RunWindow::ALWAYS,
        public int $priority = 100,
        public bool $enabled = true,
        public ?string $timezone = null,
        public bool $withoutOverlapping = false,
        public bool $onOneServer = false,
        public ?array $environments = [],
        public ?string $category = null,
        public array $tags = [],
        public ?string $outputLog = null,
    ) {}

    /**
     * Get the schedule definition metadata as an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $metadata = [];

        if ($this->category !== null) {
            $metadata['category'] = $this->category;
        }

        if (! empty($this->environments)) {
            $metadata['environments'] = $this->environments;
        }

        if (! empty($this->tags)) {
            $metadata['tags'] = $this->tags;
        }

        return [
            'expression' => $this->expression,
            'name' => $this->name,
            'description' => $this->description,
            'options' => $this->options,
            'run_window' => $this->runWindow->value,
            'priority' => $this->priority,
            'enabled' => $this->enabled,
            'timezone' => $this->timezone,
            'without_overlapping' => $this->withoutOverlapping,
            'on_one_server' => $this->onOneServer,
            'environments' => $this->environments,
            'metadata' => $metadata,
            'output_log' => $this->outputLog,
        ];
    }
}
