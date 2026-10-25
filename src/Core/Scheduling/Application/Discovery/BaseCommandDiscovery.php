<?php

namespace TLabsCo\CommandScheduler\Core\Scheduling\Application\Discovery;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use ReflectionClass;

/**
 * Base class for command discovery services.
 */
abstract class BaseCommandDiscovery
{
    /**
     * Discover all commands.
     *
     * @return array<string, array<string, mixed>>
     */
    public function discover(): array
    {
        $discovered = [];
        $commandPaths = $this->getCommandPaths();

        foreach ($commandPaths as $path) {
            if (! File::exists($path)) {
                continue;
            }

            $discovered = array_merge($discovered, $this->scanDirectory($path));
        }

        return $discovered;
    }

    /**
     * Get the paths to scan for commands.
     *
     * @return array<string>
     */
    protected function getCommandPaths(): array
    {
        $paths = [
            // Default application command path
            app_path('Console/Commands'),
        ];

        // Additional paths from config
        $configPaths = config('command-scheduler.discovery_paths', []);
        foreach ($configPaths as $configPath) {
            if (is_string($configPath)) {
                // Support glob patterns
                if (str_contains($configPath, '*')) {
                    $globPaths = glob($configPath);
                    if ($globPaths !== false) {
                        $paths = array_merge($paths, $globPaths);
                    }
                } else {
                    $paths[] = $configPath;
                }
            }
        }

        // Add package command paths
        $packagePaths = glob(__DIR__.'/../Console/Commands');
        if ($packagePaths !== false) {
            $paths = array_merge($paths, $packagePaths);
        }

        // Add package example paths
        $examplePaths = glob(__DIR__.'/../Examples');
        if ($examplePaths !== false) {
            $paths = array_merge($paths, $examplePaths);
        }

        return $paths;
    }

    /**
     * Scan a directory for discoverable commands.
     *
     * @return array<string, array<string, mixed>>
     */
    protected function scanDirectory(string $directory): array
    {
        $discovered = [];
        $files = File::allFiles($directory);

        foreach ($files as $file) {
            if ($file->getExtension() !== 'php') {
                continue;
            }

            $className = $this->getClassNameFromFile($file->getPathname());

            if ($className === null) {
                continue;
            }

            if (! class_exists($className)) {
                continue;
            }

            try {
                $reflection = new ReflectionClass($className);
                /* @phpstan-ignore-next-line */
            } catch (\ReflectionException) {
                continue;
            }

            // Must be a Command class
            if (! $reflection->isSubclassOf(Command::class)) {
                continue;
            }

            // Cannot be abstract
            if ($reflection->isAbstract()) {
                continue;
            }

            // Check if this command is discoverable (interface, attribute, etc.)
            if (! $this->isDiscoverable($reflection)) {
                continue;
            }

            $info = $this->extractInfo($reflection);
            if (! empty($info)) {
                $discovered[$className] = $info;
            }
        }

        return $discovered;
    }

    /**
     * Extract command signature from reflection class.
     *
     * @param  ReflectionClass<Command>  $reflection
     */
    protected function extractSignature(ReflectionClass $reflection): ?string
    {
        $properties = $reflection->getDefaultProperties();
        $signature = $properties['signature'] ?? $properties['name'] ?? null;

        if ($signature === null) {
            return null;
        }

        // Extract the base command name from the signature
        // e.g., 'command:name {--option}' => 'command:name'
        if (preg_match('/^\s*([^\s{]+)/', $signature, $matches)) {
            return trim($matches[1]);
        }

        return trim($signature);
    }

    /**
     * Get class name from file path.
     */
    protected function getClassNameFromFile(string $filePath): ?string
    {
        $content = File::get($filePath);

        // Extract namespace
        if (! preg_match('/namespace\s+([^;]+);/', $content, $namespaceMatches)) {
            return null;
        }

        // Extract class name
        if (! preg_match('/class\s+(\w+)/', $content, $classMatches)) {
            return null;
        }

        return $namespaceMatches[1].'\\'.$classMatches[1];
    }

    /**
     * Check if the command is discoverable by this discovery service.
     *
     * @param  ReflectionClass<Command>  $reflection
     */
    abstract protected function isDiscoverable(ReflectionClass $reflection): bool;

    /**
     * Extract schedule information from the command.
     *
     * @param  ReflectionClass<Command>  $reflection
     * @return array<string, mixed>
     */
    abstract protected function extractInfo(ReflectionClass $reflection): array;
}
