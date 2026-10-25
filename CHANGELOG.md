# Changelog

All notable changes to `t-labs-co/command-scheduler` will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.0.0] - 2026-10-25

### Added
- **Code-First Task Discovery**:
  - `#[Scheduled]` PHP 8 attribute with named arguments (`cron`, `window`, `priority`, `category`, `environments`, `enabled`, `consoleOnly`, `description`, `tags`, `options`, `parameters`)
  - `#[RunInWindow]` PHP 8 attribute
  - `SchedulableCommand` interface and `IsSchedulable` trait for composable scheduling definitions
  - `AttributeSchedulerDiscovery` and `SchedulerDiscovery` engine for automatic command discovery
- **Database Runtime Controls**:
  - `cs_scheduled_tasks` and `cs_scheduled_command_runs` migrations
  - `ScheduledTask` Eloquent model with runtime override and toggle capabilities
  - `ScheduledCommandRun` execution tracking with durations, memory usage, and exit codes
- **Execution & Run Windows**:
  - `RunWindow` domain enum (`ALWAYS`, `WORKING_HOURS`, `BEFORE_WORKING_HOURS`, `AFTER_WORKING_HOURS`, `WORKING_DAYS`, `REST_TIME`, `HOLIDAY`, `WEEKEND`, `TRADING`, `PREMARKET`, `AFTERHOURS`, `TRADING_DAYS`, `NON_TRADING_DAYS`, `CLOSED`)
  - `RunWindowResolverInterface` and `WorkingHoursRunWindowResolver`
  - Integration with Laravel's native Console Scheduler via `ScheduleRegistrar`
- **Artisan Commands**:
  - `scheduler:sync` — discover and sync schedulable commands with the database
  - `scheduler:list` — display discovered and configured scheduled tasks
  - `scheduler:run` — manually trigger a task with run window bypass and argument overrides
  - `scheduler:prune-logs` — prune old command run history
- **Interactive Livewire & MaryUI Dashboard**:
  - `SchedulerDashboard` — statistics overview, duration and memory metrics, upcoming runs
  - `SchedulerTaskList` — task management with toggle, manual trigger, filtering by category and window
  - `SchedulerTaskDetail` — deep dive task metrics and run history
  - `SchedulerRunList` — paginated run history with exit status filter
- **Developer Tools**:
  - Package auto-discovery for Laravel 12 and 13
  - PHP 8.4 support and static analysis Level 8
  - Comprehensive Pest and PHPUnit test suite
