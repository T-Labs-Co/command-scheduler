# Laravel Command Scheduler

[![Tests](https://github.com/t-labs-co/command-scheduler/actions/workflows/tests.yml/badge.svg)](https://github.com/t-labs-co/command-scheduler/actions/workflows/tests.yml)
[![Static Analysis](https://github.com/t-labs-co/command-scheduler/actions/workflows/static-analysis.yml/badge.svg)](https://github.com/t-labs-co/command-scheduler/actions/workflows/static-analysis.yml)
[![License: MIT](https://img.shields.io/badge/License-MIT-blue.svg)](LICENSE)
[![PHP Version](https://img.shields.io/badge/php-%5E8.4-8892BF.svg)](https://php.net)
[![Laravel Version](https://img.shields.io/badge/laravel-%5E12.0%20%7C%20%5E13.0-FF2D20.svg)](https://laravel.com)

Code-first command scheduling framework for Laravel — discover schedulable commands via PHP 8 attributes or interfaces, persist schedule definitions to database, enforce run windows, and control tasks via a Livewire/MaryUI dashboard or CLI.

---

## Features

- **Code-First Scheduling**: Define schedules right on Artisan command classes using `#[Scheduled]` PHP 8 attributes or `SchedulableCommand` interface.
- **Run Windows**: Restrict command execution to specific business windows (`WORKING_HOURS`, `WEEKEND`, `HOLIDAY`, `TRADING`, `PREMARKET`, etc.) without writing complex cron expressions.
- **Database Runtime Controls**: Sync discovered tasks to database (`cs_scheduled_tasks`), allowing administrators to toggle tasks or adjust parameters at runtime without redeploying.
- **Execution Tracking**: Automatically log start time, completion time, duration, peak memory usage, and exit codes to `cs_scheduled_command_runs`.
- **Livewire & MaryUI Dashboard**: Built-in dashboard to monitor upcoming runs, inspect recent execution logs, toggle tasks, and trigger manual runs.
- **Artisan Management**: CLI commands for syncing (`scheduler:sync`), listing (`scheduler:list`), running (`scheduler:run`), and pruning logs (`scheduler:prune-logs`).

---

## Documentation

- **[Usage Guide & Examples](USAGE.md)** — All patterns for defining scheduled commands, run windows, composable attributes, events, failure notifications, and migration from native Laravel scheduler.
- **[Dashboard Setup Guide](DASHBOARD-SETUP.md)** — Step-by-step guide for installing, configuring, and customizing the Livewire/MaryUI dashboard, including Vite asset building and layout options.

---

## Requirements

- PHP `^8.4`
- Laravel `^12.0` or `^13.0`
- Livewire `^3.0` or `^4.0` (for dashboard)
- MaryUI `^2.1` (for dashboard)

---

## Installation

You can install the package via Composer:

```bash
composer require t-labs-co/command-scheduler
```

Publish and run the migrations:

```bash
php artisan migrate
```

Publish the configuration file (optional):

```bash
php artisan vendor:publish --tag="command-scheduler-config"
```

---

## Defining Scheduled Commands

### 1. Attribute-Based Scheduling (Recommended)

Add the `#[Scheduled]` attribute to any Artisan command:

```php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use TLabsCo\CommandScheduler\Attributes\Scheduled;
use TLabsCo\CommandScheduler\Core\Scheduling\Domain\Enums\RunWindow;

#[Scheduled(
    cron: '*/15 * * * *',
    window: RunWindow::WORKING_HOURS,
    category: 'maintenance',
    priority: 10,
    description: 'Clean up temporary files during working hours'
)]
class PruneTempFilesCommand extends Command
{
    protected $signature = 'app:prune-temp';
    protected $description = 'Clean up temporary files';

    public function handle(): int
    {
        // ...
        return self::SUCCESS;
    }
}
```

### 2. Interface-Based Scheduling

Implement `SchedulableCommand` and use `IsSchedulable`:

```php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use TLabsCo\CommandScheduler\Contracts\SchedulableCommand;
use TLabsCo\CommandScheduler\Core\Scheduling\Domain\Enums\RunWindow;
use TLabsCo\CommandScheduler\Core\Scheduling\Domain\ValueObjects\ScheduleDefinition;
use TLabsCo\CommandScheduler\Traits\IsSchedulable;

class GenerateDailyReportCommand extends Command implements SchedulableCommand
{
    use IsSchedulable;

    protected $signature = 'report:daily';

    public function schedule(): ScheduleDefinition
    {
        return ScheduleDefinition::create()
            ->cron('0 8 * * 1-5')
            ->window(RunWindow::WORKING_HOURS)
            ->category('reports')
            ->priority(20);
    }

    public function handle(): int
    {
        // ...
        return self::SUCCESS;
    }
}
```

---

## Run Windows

Run windows restrict execution to specific time periods regardless of cron schedule. For a complete reference of all 14 run window types, see the [Usage Guide](USAGE.md#5-run-windows-reference).

| Window | Description |
|---|---|
| `RunWindow::ALWAYS` | No run window constraint (default) |
| `RunWindow::WORKING_HOURS` | Runs only during configured business working hours |
| `RunWindow::BEFORE_WORKING_HOURS` | Runs only before business start time |
| `RunWindow::AFTER_WORKING_HOURS` | Runs only after business end time |
| `RunWindow::WORKING_DAYS` | Runs on configured working days (e.g. Mon–Fri) |
| `RunWindow::REST_TIME` | Runs during configured lunch / rest interval |
| `RunWindow::WEEKEND` | Runs on Saturdays and Sundays |
| `RunWindow::HOLIDAY` | Runs on configured holiday dates |
| `RunWindow::TRADING` | Stock market trading hours |
| `RunWindow::PREMARKET` | Stock market pre-market hours |
| `RunWindow::AFTERHOURS` | Stock market after-hours |

---

## Artisan Commands

### Sync Discovered Commands
Scan command directories and sync definitions to the database:
```bash
php artisan scheduler:sync
```

### List Scheduled Tasks
Display all discovered tasks and their database runtime status:
```bash
php artisan scheduler:list
```

### Run a Task Manually
Trigger execution immediately, bypassing run window checks if desired:
```bash
php artisan scheduler:run app:prune-temp --force
```

### Prune Old Run Logs
Delete execution history older than configured retention days:
```bash
php artisan scheduler:prune-logs --days=30
```

---

## Dashboard Setup

For a complete step-by-step dashboard setup guide — including dependency installation, Vite asset building, layout customization, and troubleshooting — see the **[Dashboard Setup Guide](DASHBOARD-SETUP.md)**.

The dashboard is accessible by default at `/admin/scheduler` (and `/scheduler`).

### Quick Start — Authorization Gate
In your `AppServiceProvider` or `AuthServiceProvider`, define the permission gate:

```php
use Illuminate\Support\Facades\Gate;

public function boot(): void
{
    Gate::define('viewSchedulerDashboard', function ($user) {
        return $user->hasRole('admin');
    });
}
```

---

## Testing

Run the test suite using Pest:

```bash
composer test
```

Run static analysis:

```bash
composer analyse
```

---

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.

## Quality & Development Practices

This package is developed following Laravel best practices, with a strong focus on reliability, maintainability, and code quality.

- 🧩 **Laravel Best Practices:** Follows established Laravel conventions and recommended design principles.
- ✅ **Comprehensive Test Coverage:** Features are covered by unit and integration tests using Pest PHP.
- 🔍 **Static Analysis:** Validated with PHPStan Level 8 using Larastan to catch potential type errors and enforce strict typing.
- 🤖 **AI-Assisted Development:** AI assistants support development, analysis, and code quality improvements.
- 👨‍💻 **Senior Engineer Review:** All changes are carefully reviewed by a senior engineer before each release.

## Credits

- **Ty Huynh** ([@ty-huynh](https://github.com/ty-huynh)) — [tyhuynh.id.vn](https://tyhuynh.id.vn)  
  15+ years · Senior Software Engineer (PHP/Laravel) & Technical Lead
