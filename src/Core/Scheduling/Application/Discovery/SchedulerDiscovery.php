<?php

namespace TLabsCo\CommandScheduler\Core\Scheduling\Application\Discovery;

use Illuminate\Console\Command;
use ReflectionClass;
use TLabsCo\CommandScheduler\Core\Scheduling\Domain\Contracts\SchedulableCommand;

/**
 * Service for discovering schedulable commands via interface implementation.
 */
class SchedulerDiscovery extends BaseCommandDiscovery
{
    /**
     * Check if the command is discoverable by this discovery service.
     *
     * @param  ReflectionClass<Command>  $reflection
     */
    protected function isDiscoverable(ReflectionClass $reflection): bool
    {
        return $reflection->implementsInterface(SchedulableCommand::class);
    }

    /**
     * Extract schedule information from the command.
     *
     * @param  ReflectionClass<Command>  $reflection
     * @return array<string, mixed>
     */
    protected function extractInfo(ReflectionClass $reflection): array
    {
        try {
            $instance = $reflection->newInstanceWithoutConstructor();
            if (! $instance instanceof SchedulableCommand) {
                return [];
            }

            $scheduleConfig = $instance->scheduleConfig();
            $signature = $this->extractSignature($reflection);

            return [
                'class' => $reflection->getName(),
                'signature' => $signature,
                'config' => $scheduleConfig,
            ];
        } catch (\Throwable) {
            return [];
        }
    }
}
