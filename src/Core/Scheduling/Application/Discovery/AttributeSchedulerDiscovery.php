<?php

namespace TLabsCo\CommandScheduler\Core\Scheduling\Application\Discovery;

use Illuminate\Console\Command;
use ReflectionClass;
use TLabsCo\CommandScheduler\Attributes\Scheduled;
use TLabsCo\CommandScheduler\Attributes\ScheduledAttribute;

/**
 * Service for discovering schedulable commands via attributes.
 */
class AttributeSchedulerDiscovery extends BaseCommandDiscovery
{
    /**
     * Check if the command is discoverable by this discovery service.
     *
     * @param  ReflectionClass<Command>  $reflection
     */
    protected function isDiscoverable(ReflectionClass $reflection): bool
    {
        $attributes = $reflection->getAttributes(Scheduled::class, \ReflectionAttribute::IS_INSTANCEOF);
        if (! empty($attributes)) {
            return true;
        }

        $attributes = $reflection->getAttributes(ScheduledAttribute::class, \ReflectionAttribute::IS_INSTANCEOF);

        return ! empty($attributes);
    }

    /**
     * Extract schedule information from the command.
     *
     * @param  ReflectionClass<Command>  $reflection
     * @return array<string, mixed>
     */
    protected function extractInfo(ReflectionClass $reflection): array
    {
        $scheduledAttributes = $reflection->getAttributes(Scheduled::class, \ReflectionAttribute::IS_INSTANCEOF);
        $configuratorAttributes = $reflection->getAttributes(ScheduledAttribute::class, \ReflectionAttribute::IS_INSTANCEOF);

        if (empty($scheduledAttributes) && empty($configuratorAttributes)) {
            return [];
        }

        // Initialize with a default Scheduled object
        // If there's a #[Scheduled] attribute, use it as the base
        if (! empty($scheduledAttributes)) {
            $scheduled = $scheduledAttributes[0]->newInstance();
        } else {
            // Default values
            $scheduled = new Scheduled(expression: '*/5 * * * *');
        }

        // Apply all configurators
        foreach ($configuratorAttributes as $attribute) {
            $configurator = $attribute->newInstance();
            $configurator->configure($scheduled);
        }

        $signature = $this->extractSignature($reflection);
        $config = $scheduled->toArray();

        // Use signature as name if name is not provided
        if ($config['name'] === null) {
            $config['name'] = $signature ?? $reflection->getShortName();
        }

        return [
            'class' => $reflection->getName(),
            'signature' => $signature,
            'attribute' => $config,
        ];
    }
}
