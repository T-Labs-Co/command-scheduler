<?php

namespace TLabsCo\CommandScheduler\Core\Scheduling\Application\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use TLabsCo\CommandScheduler\Core\Scheduling\Application\Discovery\AttributeSchedulerDiscovery;
use TLabsCo\CommandScheduler\Core\Scheduling\Application\Discovery\SchedulerDiscovery;
use TLabsCo\CommandScheduler\Models\ScheduledTask;

/**
 * Central manager for command scheduler operations.
 *
 * This is a singleton service that coordinates discovery and registration.
 */
class SchedulerManager
{
    public function __construct(
        protected SchedulerDiscovery $interfaceDiscovery,
        protected AttributeSchedulerDiscovery $attributeDiscovery
    ) {}

    /**
     * Create a singleton instance.
     */
    public static function make(): static
    {
        return app(static::class);
    }

    /**
     * Discover all schedulable commands (both interface and attribute-based).
     *
     * @return array<string, array<string, mixed>>
     */
    public function discoverAll(): array
    {
        $cacheKey = $this->getCacheKey('discovered_commands');
        $cacheTtl = (int) config('command-scheduler.cache_ttl', 300);

        return Cache::remember($cacheKey, $cacheTtl, function () {
            $interfaceCommands = $this->interfaceDiscovery->discover();
            $attributeCommands = $this->attributeDiscovery->discover();

            return array_merge($interfaceCommands, $attributeCommands);
        });
    }

    /**
     * Get all enabled scheduled tasks from the database.
     *
     * @return Collection<int, ScheduledTask>
     */
    public function getEnabledTasks(): Collection
    {
        $cacheKey = $this->getCacheKey('enabled_tasks');
        $cacheTtl = (int) config('command-scheduler.cache_ttl', 300);

        return Cache::remember($cacheKey, $cacheTtl, fn () => ScheduledTask::query()
            ->enabled()
            ->byPriority()
            ->get());
    }

    /**
     * Clear all scheduler-related caches.
     */
    public function clearCache(): void
    {
        Cache::forget($this->getCacheKey('discovered_commands'));
        Cache::forget($this->getCacheKey('enabled_tasks'));
    }

    /**
     * Get cache key with namespace.
     */
    protected function getCacheKey(string $key): string
    {
        return 'command_scheduler:'.$key;
    }
}
