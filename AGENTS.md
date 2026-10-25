# AI Assistant Guide — Command Scheduler

This document provides essential context and guidelines for AI assistants working on the `t-labs-co/command-scheduler` Laravel package.

## Project Overview

**Command Scheduler** is a code-first, database-backed command scheduling framework for Laravel (`^12.0` | `^13.0`) and PHP `^8.4`. It enables developers to define scheduled tasks using PHP 8 attributes or interfaces, persist them to a database, enforce time-based run windows, and manage tasks through a Livewire/MaryUI dashboard or CLI.

### Key Features
- **Code-First Scheduling**: Define schedules directly on Artisan commands using `#[Scheduled]` attributes or `SchedulableCommand` interface
- **Run Windows**: Restrict execution to business hours, trading sessions, weekends, holidays, etc.
- **Database Persistence**: Sync discovered tasks to `cs_scheduled_tasks` table for runtime control
- **Execution Tracking**: Log all runs to `cs_scheduled_command_runs` with timing, memory, and exit codes
- **Livewire Dashboard**: Built-in UI for monitoring, toggling, and manually triggering tasks
- **CLI Management**: Artisan commands for sync, list, run, and log pruning

## Architecture

### Directory Structure
```
src/
├── Attributes/                    # PHP 8 scheduling attributes
│   ├── Scheduled.php              # Main scheduling attribute
│   ├── ScheduledAttribute.php     # Base attribute class
│   ├── ScheduledEnvironments.php  # Environment constraint attribute
│   ├── ScheduledOptions.php       # Command options attribute
│   └── ScheduledTags.php          # Tag attribute
├── Console/Commands/              # Artisan CLI commands
│   ├── SchedulerSyncCommand.php   # Sync discovered commands to DB
│   ├── SchedulerListCommand.php   # List all scheduled tasks
│   ├── SchedulerRunCommand.php    # Run a task manually
│   └── SchedulerPruneLogCommand.php # Prune old execution logs
├── Core/Scheduling/               # Core scheduling engine
│   ├── Application/               # Application layer services
│   │   ├── Discovery/             # Command discovery services
│   │   │   ├── BaseCommandDiscovery.php
│   │   │   ├── AttributeSchedulerDiscovery.php
│   │   │   └── SchedulerDiscovery.php
│   │   ├── Handlers/              # Event handlers
│   │   │   └── LogScheduledCommandRun.php
│   │   └── Services/              # Business logic services
│   │       ├── ScheduleRegistrar.php
│   │       ├── SchedulerManager.php
│   │       └── SchedulerService.php
│   ├── Domain/                    # Domain layer
│   │   ├── Contracts/             # Interfaces
│   │   │   └── SchedulableCommand.php
│   │   ├── Enums/                 # Value enumerations
│   │   │   └── RunWindow.php
│   │   └── ValueObjects/          # Immutable value objects
│   │       ├── CronExpressionData.php
│   │       └── ScheduleDefinition.php
│   └── Infrastructure/            # Infrastructure adapters
│       └── WorkingHoursRunWindowResolver.php
├── Database/Migrations/           # Database migrations
├── Events/                        # Domain events
├── Livewire/                      # Livewire dashboard components
│   ├── SchedulerDashboard.php
│   ├── SchedulerTaskList.php
│   ├── SchedulerTaskDetail.php
│   └── SchedulerRunList.php
├── Models/                        # Eloquent models
│   ├── ScheduledTask.php
│   └── ScheduledCommandRun.php
├── Providers/                     # Service providers
│   ├── CommandSchedulerServiceProvider.php
│   ├── RouteServiceProvider.php
│   └── EventServiceProvider.php
├── Resources/views/               # Blade templates
└── Support/                       # Helper functions
    └── functions.php
```

### Core Concepts

1. **Discovery**: `AttributeSchedulerDiscovery` and `SchedulerDiscovery` scan configured paths for commands with `#[Scheduled]` attributes or `SchedulableCommand` interface implementations
2. **Registration**: `ScheduleRegistrar` syncs discovered commands to the database, creating/updating `ScheduledTask` records
3. **Execution**: `TaskRunner` executes scheduled commands with run window validation and logs results
4. **Run Windows**: `RunWindow` enum defines time constraints (working hours, trading sessions, etc.) evaluated by resolvers before execution

## Coding Standards

### PHP Standards
- **PHP Version**: `^8.4` with strict types (`declare(strict_types=1)`)
- **Code Style**: Laravel Pint configuration (see `pint.json`)
- **Static Analysis**: PHPStan Level 8 via Larastan (`composer analyse`)
- **Type Safety**: Full type coverage required — all parameters, properties, and return types must be explicitly typed

### Naming Conventions
- **Classes**: PascalCase (e.g., `SchedulerManager`, `ScheduledTask`)
- **Methods/Functions**: camelCase (e.g., `discoverAll`, `syncWithDatabase`)
- **Properties**: camelCase with visibility modifiers
- **Constants**: UPPER_SNAKE_CASE
- **Files**: PascalCase.php matching class name

### Type Annotations
Use PHPDoc `@param`, `@return`, and `@var` annotations for:
- Complex array types: `@param array<string, mixed> $data`
- Collection generics: `@return Collection<int, ScheduledTask>`
- Reflection types: `@param ReflectionClass<Command> $reflection`
- Nullable types: `@param array<string>|null $environments`

### Code Organization
- Follow Laravel's service provider pattern for package registration
- Use dependency injection via constructor
- Keep business logic in `Services/`, data access in `Models/`, and presentation in `Livewire/`
- Domain contracts in `Domain/Contracts/`, implementations in `Application/` or `Infrastructure/`

## Testing Guidelines

### Test Framework
- **Pest PHP** with Laravel plugin for feature/unit tests
- Run tests with: `composer test` (includes parallel execution)

### Test Coverage Requirements
- **Unit Tests**: Value objects, enums, and pure functions
- **Feature Tests**: Command discovery, scheduling logic, database operations
- **Integration Tests**: End-to-end command execution flow

### Test Organization
```
tests/
├── Unit/                          # Unit tests for value objects, enums
├── Feature/                       # Feature tests for commands, services
└── Integration/                   # Integration tests for full workflows
```

### Test Writing Guidelines
- Use descriptive test names: `it_can_discover_scheduled_commands()`
- Test both success and failure paths
- Mock external dependencies where appropriate
- Use Orchestra Testbench for Laravel package testing
- Cover edge cases: null values, empty arrays, boundary conditions

## Development Workflow

### Quality Gates
Before submitting changes, ensure:
1. **Static Analysis Passes**: `composer analyse` (PHPStan Level 8, 0 errors)
2. **Tests Pass**: `composer test` (all tests passing)
3. **Code Style**: `composer format` (Laravel Pint auto-fixes applied)

### Common Commands
```bash
# Run tests
composer test

# Run static analysis
composer analyse

# Format code
composer format

# Full quality check
composer analyse && composer test
```

### Package Configuration
Key configuration in `config/command-scheduler.php`:
- `enabled`: Master toggle for scheduler
- `dashboard.*`: Dashboard route and middleware settings
- `discovery_paths`: Additional paths to scan for commands
- `cache_ttl`: Cache duration for task lookups (default: 300s)
- `tables.*`: Database table names
- `working_hours.*`: Business hours configuration
- `trading_hours.*`: Stock market hours configuration
- `notifications.*`: Failure notification settings

## Change Guidelines

### Adding New Features
1. Follow existing architecture patterns (Attributes, Services, Models)
2. Add corresponding tests for new functionality
3. Update documentation if user-facing
4. Ensure PHPStan Level 8 passes with full type coverage

### Modifying Existing Code
1. Preserve backward compatibility where possible
2. Update tests to reflect new behavior
3. Maintain type safety — no untyped parameters or returns
4. Follow existing naming and organization patterns

### Database Changes
1. Add migrations in `src/Database/Migrations/`
2. Update models with new relationships/accessors
3. Consider migration rollback scenarios

### UI Changes
1. Use MaryUI components for consistency
2. Follow existing Livewire component patterns
3. Ensure responsive design

## Important Notes

- **Package Namespace**: `TLabsCo\CommandScheduler\`
- **Table Prefix**: `cs_` for all database tables
- **Event System**: Uses Laravel events for lifecycle hooks
- **Cache Strategy**: In-memory/cache store for task lookups to reduce DB queries
- **Dashboard Auth**: Protected by `viewSchedulerDashboard` gate
- **Logging**: Optional dedicated log channel configurable via `logging.*`

## Documentation

For detailed user-facing guides, refer to the following external documentation:

- **[Usage Guide & Examples](USAGE.md)** — All patterns for defining scheduled commands (attributes, interfaces, composable attributes), run windows reference, working hours configuration, custom resolvers, database controls, Artisan commands, events, failure notifications, and migration from native Laravel scheduler.
- **[Dashboard Setup Guide](DASHBOARD-SETUP.md)** — Step-by-step guide for installing, configuring, and customizing the Livewire/MaryUI dashboard, including Vite asset building, layout options, Gate authorization, middleware, and troubleshooting.

## References

- **README.md**: User-facing documentation and quick-start examples
- **USAGE.md**: Comprehensive usage guide with all patterns and examples
- **DASHBOARD-SETUP.md**: Complete dashboard setup and customization guide
- **config/command-scheduler.php**: All configuration options
- **phpstan.neon**: Static analysis configuration
- **pint.json**: Code style configuration
- **composer.json**: Dependencies and scripts
