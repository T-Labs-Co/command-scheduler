# Usage Guide & Examples — `t-labs-co/command-scheduler`

**Package:** `t-labs-co/command-scheduler`  
**Applies to:** v1.0.0+

This guide covers all supported ways to register schedulable commands, configure run-windows, and use the scheduler's advanced features.

---

## Table of Contents

1. [Installation](#1-installation)
2. [Pattern A — PHP Attribute (Recommended)](#2-pattern-a--php-attribute-recommended)
3. [Pattern B — Interface Implementation](#3-pattern-b--interface-implementation)
4. [Pattern C — Composable Attributes](#4-pattern-c--composable-attributes)
5. [Run Windows Reference](#5-run-windows-reference)
6. [Working Hours Configuration](#6-working-hours-configuration)
7. [Custom Run Window Resolver](#7-custom-run-window-resolver)
8. [Database Controls](#8-database-controls)
9. [Artisan Commands Reference](#9-artisan-commands-reference)
10. [Events & Listeners](#10-events--listeners)
11. [Failure Notifications](#11-failure-notifications)
12. [Advanced — Options & Arguments](#12-advanced--options--arguments)
13. [Migration from Native Laravel Scheduler](#13-migration-from-native-laravel-scheduler)

---

## 1. Installation

```bash
composer require t-labs-co/command-scheduler
php artisan migrate
php artisan scheduler:sync
```

Ensure the Laravel scheduler runs every minute on your server:

```cron
* * * * * cd /path/to/project && php artisan schedule:run >> /dev/null 2>&1
```

---

## 2. Pattern A — PHP Attribute (Recommended)

Decorate any `Illuminate\Console\Command` with `#[Scheduled]`:

```php
<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use TLabsCo\CommandScheduler\Attributes\Scheduled;
use TLabsCo\CommandScheduler\Core\Scheduling\Domain\Enums\RunWindow;

#[Scheduled(
    expression: '0 2 * * *',           // Daily at 2 AM
    name: 'Nightly Report Generator',
    description: 'Generates daily performance reports',
    runWindow: RunWindow::ALWAYS,
    priority: 50,
    enabled: true,
    withoutOverlapping: true,
    onOneServer: true,
    environments: ['production', 'staging'],
    category: 'reports',
    tags: ['reports', 'nightly'],
    outputLog: 'logs/nightly-report.log',
)]
class GenerateNightlyReport extends Command
{
    protected $signature = 'reports:nightly';
    protected $description = 'Generates nightly performance reports';

    public function handle(): int
    {
        $this->info('Generating report...');
        // your logic here
        return self::SUCCESS;
    }
}
```

After adding the attribute, sync with the database:

```bash
php artisan scheduler:sync
```

---

## 3. Pattern B — Interface Implementation

Implement `SchedulableCommand` and define `scheduleConfig()` for a fluent, IDE-friendly experience:

```php
<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use TLabsCo\CommandScheduler\Core\Scheduling\Domain\Contracts\SchedulableCommand;
use TLabsCo\CommandScheduler\Core\Scheduling\Domain\ValueObjects\ScheduleDefinition;

class CleanExpiredSessions extends Command implements SchedulableCommand
{
    protected $signature = 'sessions:clean-expired';
    protected $description = 'Removes expired user sessions from the database';

    public function scheduleConfig(): ScheduleDefinition
    {
        return (new ScheduleDefinition('Clean Expired Sessions'))
            ->description('Removes expired sessions hourly during off-hours')
            ->everyHour()
            ->afterWorkingHours()
            ->priority(80)
            ->category('maintenance')
            ->tags(['cleanup', 'sessions'])
            ->withoutOverlapping()
            ->onlyInEnvironments(['production']);
    }

    public function handle(): int
    {
        $deleted = \DB::table('sessions')
            ->where('last_activity', '<', now()->subHours(24)->timestamp)
            ->delete();

        $this->info("Deleted {$deleted} expired sessions.");
        return self::SUCCESS;
    }
}
```

### `ScheduleDefinition` Fluent API

```php
(new ScheduleDefinition('My Task'))
    // Frequency
    ->everyMinute()
    ->everyNMinutes(15)
    ->everyFiveMinutes()
    ->everyTenMinutes()
    ->everyFifteenMinutes()
    ->everyThirtyMinutes()
    ->everyHour()
    ->daily()                    // midnight
    ->dailyAt('06:00')
    ->weeklyOn(1, '08:00')       // Monday at 8 AM
    ->monthly()
    ->cron('0 6 * * 1-5')        // custom cron expression

    // Run windows
    ->always()
    ->duringWorkingHours()
    ->beforeWorkingHours()
    ->afterWorkingHours()
    ->onWorkingDays()
    ->onWeekends()
    ->onHoliday()
    ->duringRestTime()

    // Constraints
    ->priority(50)               // lower = higher priority (default 100)
    ->enabled(true)
    ->timezone('Asia/Ho_Chi_Minh')
    ->withoutOverlapping()
    ->onOneServer()
    ->onlyInEnvironments(['production'])
    ->local()                    // shorthand for onlyInEnvironments(['local'])
    ->category('maintenance')
    ->tags(['tag1', 'tag2'])
    ->outputLog('logs/my-task.log')
    ->options(['--queue' => 'high'])
```

---

## 4. Pattern C — Composable Attributes

Compose multiple small attributes for a clean, readable declaration:

```php
<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use TLabsCo\CommandScheduler\Attributes\Scheduled;
use TLabsCo\CommandScheduler\Attributes\ScheduledWorkingHours;
use TLabsCo\CommandScheduler\Attributes\ScheduledWithoutOverlapping;
use TLabsCo\CommandScheduler\Attributes\ScheduledProductionOnly;
use TLabsCo\CommandScheduler\Attributes\ScheduledCategory;
use TLabsCo\CommandScheduler\Attributes\ScheduledTags;
use TLabsCo\CommandScheduler\Attributes\ScheduledName;

#[Scheduled(expression: '*/30 * * * *')]
#[ScheduledName('Sync Inventory Every 30 Min')]
#[ScheduledWorkingHours]
#[ScheduledWithoutOverlapping]
#[ScheduledProductionOnly]
#[ScheduledCategory('inventory')]
#[ScheduledTags(['sync', 'inventory', 'critical'])]
class SyncInventory extends Command
{
    protected $signature = 'inventory:sync';
    protected $description = 'Synchronises inventory from the external API';

    public function handle(): int
    {
        // ...
        return self::SUCCESS;
    }
}
```

### Available Composable Attributes

| Attribute | Effect |
|-----------|--------|
| `#[Scheduled(expression: '...')]` | Base attribute — sets cron expression |
| `#[ScheduledName('...')]` | Sets the display name |
| `#[ScheduledDaily]` | Shorthand for `0 0 * * *` |
| `#[ScheduledHourly]` | Shorthand for `0 * * * *` |
| `#[ScheduledMinute]` | Shorthand for `* * * * *` |
| `#[ScheduledWeekly]` | Shorthand for `0 0 * * 0` |
| `#[ScheduledWorkingHours]` | Restricts to working hours window |
| `#[ScheduledBeforeWorkingHours]` | Before start of working day |
| `#[ScheduledAfterWorkingHours]` | After end of working day |
| `#[ScheduledWorkingDays]` | Only on configured working days |
| `#[ScheduledWeekend]` | Only on Saturday/Sunday |
| `#[ScheduledHoliday]` | Only on configured holiday dates |
| `#[ScheduledRestTime]` | Only during lunch/rest break |
| `#[ScheduledWithoutOverlapping]` | Adds `withoutOverlapping()` |
| `#[ScheduledProductionOnly]` | Restricts to `production` environment |
| `#[ScheduledLocalOnly]` | Restricts to `local` environment |
| `#[ScheduledEnvironments(['staging', 'production'])]` | Custom environment list |
| `#[ScheduledCategory('name')]` | Sets category tag |
| `#[ScheduledTags(['a', 'b'])]` | Adds searchable tags |
| `#[ScheduledTimezone('UTC')]` | Sets cron timezone |
| `#[ScheduledTimezoneUTC]` | Sets timezone to UTC |
| `#[ScheduledTimezoneVietnam]` | Sets timezone to Asia/Ho_Chi_Minh |
| `#[ScheduledOutputLog('logs/my.log')]` | Appends output to log file |
| `#[ScheduledOptions(['--force'])]` | Passes CLI options to the command |
| `#[ScheduledRunWindow(RunWindow::ALWAYS)]` | Explicit run window override |

---

## 5. Run Windows Reference

Run windows let you restrict **when** a task is allowed to execute, evaluated at each scheduler tick.

| `RunWindow` value | When the task runs |
|-------------------|--------------------|
| `ALWAYS` | Every scheduled tick — no restriction |
| `WORKING_HOURS` | Only during configured work start–end on working days |
| `BEFORE_WORKING_HOURS` | Before `working_hours.start` on working days |
| `AFTER_WORKING_HOURS` | After `working_hours.end` on working days |
| `WORKING_DAYS` | Any time on configured working days (Mon–Fri by default) |
| `REST_TIME` | During configured lunch/rest window |
| `HOLIDAY` | On dates listed in `working_hours.holidays` |
| `WEEKEND` | Saturday or Sunday |
| `TRADING` | During stock market trading hours *(requires trading resolver)* |
| `CLOSED` | When market is closed *(requires trading resolver)* |
| `PREMARKET` | Pre-market session *(requires trading resolver)* |
| `AFTERHOURS` | After-hours session *(requires trading resolver)* |
| `TRADING_DAYS` | On trading days excluding holidays *(requires trading resolver)* |
| `NON_TRADING_DAYS` | On weekends/holidays *(requires trading resolver)* |

---

## 6. Working Hours Configuration

Configure business hours in `config/command-scheduler.php`:

```php
'working_hours' => [
    'timezone'     => env('COMMAND_SCHEDULER_WORKING_TZ', 'UTC'),
    'working_days' => [1, 2, 3, 4, 5],       // ISO: 1=Mon, 7=Sun
    'start'        => env('COMMAND_SCHEDULER_WORK_START', '08:00'),
    'end'          => env('COMMAND_SCHEDULER_WORK_END', '17:00'),
    'rest' => [
        'start' => env('COMMAND_SCHEDULER_REST_START', '12:00'),
        'end'   => env('COMMAND_SCHEDULER_REST_END', '13:00'),
    ],
    'holidays' => [
        '2026-01-01',  // New Year's Day
        '2026-04-30',  // Liberation Day
        '2026-05-01',  // International Workers' Day
        '2026-09-02',  // National Day
    ],
],
```

---

## 7. Custom Run Window Resolver

To add trading-hours windows (or any custom logic), implement `RunWindowResolverInterface` and bind it in your `AppServiceProvider`:

```php
<?php

namespace App\Scheduling;

use TLabsCo\CommandScheduler\Core\Scheduling\Domain\Contracts\RunWindowResolverInterface;
use TLabsCo\CommandScheduler\Core\Scheduling\Domain\Enums\RunWindow;
use TLabsCo\CommandScheduler\Models\ScheduledTask;

class MyCustomRunWindowResolver implements RunWindowResolverInterface
{
    public function shouldRun(ScheduledTask $task): bool
    {
        $window = $task->run_window;

        if ($window === RunWindow::ALWAYS) {
            return true;
        }

        if ($window === RunWindow::WORKING_HOURS) {
            return $this->isDuringWorkingHours();
        }

        // Delegate trading windows to a market service
        if ($window->isTradingWindow()) {
            return app(MyMarketCalendarService::class)->isOpenFor($window);
        }

        // Default: allow all other windows
        return true;
    }

    private function isDuringWorkingHours(): bool
    {
        $now = now()->timezone(config('command-scheduler.working_hours.timezone', 'UTC'));
        $start = config('command-scheduler.working_hours.start', '08:00');
        $end   = config('command-scheduler.working_hours.end', '17:00');

        return $now->format('H:i') >= $start && $now->format('H:i') <= $end;
    }
}
```

Bind in `AppServiceProvider::register()`:

```php
use TLabsCo\CommandScheduler\Core\Scheduling\Domain\Contracts\RunWindowResolverInterface;
use App\Scheduling\MyCustomRunWindowResolver;

$this->app->singleton(RunWindowResolverInterface::class, MyCustomRunWindowResolver::class);
```

---

## 8. Database Controls

### Enable or disable a task at runtime (no code deployment needed)

```php
use TLabsCo\CommandScheduler\Models\ScheduledTask;

// Find by signature
$task = ScheduledTask::query()->where('signature', 'reports:nightly')->first();

$task->disable();      // disables immediately
$task->enable();       // re-enables
$task->toggleStatus(); // flip current state
```

### Override the cron expression

```php
$task->expression = '0 3 * * *'; // run at 3 AM instead
$task->save();                    // persists + clears cache automatically
```

### Change the run window

```php
use TLabsCo\CommandScheduler\Core\Scheduling\Domain\Enums\RunWindow;

$task->run_window = RunWindow::AFTER_WORKING_HOURS;
$task->save();
```

All saves trigger `ScheduledTaskUpdated` → `ResetScheduledTaskCache` to invalidate the enabled-tasks cache.

---

## 9. Artisan Commands Reference

### `scheduler:sync`

Discovers all schedulable commands and upserts them in the database.

```bash
php artisan scheduler:sync           # standard sync
php artisan scheduler:sync --force   # force update even if unchanged
```

**When to run:**
- After deploying a new command
- After adding/changing `#[Scheduled]` attributes
- After installing or removing packages that add commands

### `scheduler:list`

Lists all discovered and/or registered commands.

```bash
php artisan scheduler:list
php artisan scheduler:list --enabled      # only enabled tasks
php artisan scheduler:list --disabled     # only disabled tasks
php artisan scheduler:list --tag=reports  # filter by tag
```

### `scheduler:run`

Manually trigger a specific command (bypasses run-window check by default).

```bash
php artisan scheduler:run reports:nightly
php artisan scheduler:run reports:nightly --check-window  # respects run window
```

### `scheduler:prune-logs`

Prune old command run records beyond retention period.

```bash
php artisan scheduler:prune-logs
php artisan scheduler:prune-logs --days=14  # override retention days
```

---

## 10. Events & Listeners

The package dispatches events you can listen to in your application.

### Available Events

| Event | When fired | Payload |
|-------|------------|---------|
| `ScheduledCommandExecuted` | After any command run | `ScheduledTask`, exit code, duration, memory |
| `ScheduledTaskUpdated` | When a `ScheduledTask` model is saved | Eloquent model |
| `SchedulerFailureEvent` | After consecutive failures exceed threshold | `ScheduledTask`, run record, count |

### Example Listener

```php
<?php

namespace App\Listeners;

use TLabsCo\CommandScheduler\Events\SchedulerFailureEvent;
use Illuminate\Contracts\Queue\ShouldQueue;

class AlertTeamOnSchedulerFailure implements ShouldQueue
{
    public function handle(SchedulerFailureEvent $event): void
    {
        $task     = $event->task;
        $failures = $event->consecutiveFailures;

        // Send to Slack, PagerDuty, etc.
        \Notification::route('slack', config('services.slack.webhook'))
            ->notify(new \App\Notifications\SchedulerFailureNotification($task, $failures));
    }
}
```

Register in `EventServiceProvider` or `AppServiceProvider`:

```php
use TLabsCo\CommandScheduler\Events\SchedulerFailureEvent;
use App\Listeners\AlertTeamOnSchedulerFailure;

Event::listen(SchedulerFailureEvent::class, AlertTeamOnSchedulerFailure::class);
```

---

## 11. Failure Notifications

Built-in failure notifications fire when a task fails `N` consecutive times (default: 3).

Configure in `config/command-scheduler.php`:

```php
'notifications' => [
    'enabled' => env('COMMAND_SCHEDULER_NOTIFICATIONS_ENABLED', true),
    'notify_after_consecutive_failures' => 3,
    'channels' => ['email'],    // 'email', 'slack'
    'email' => [
        'recipients' => [env('COMMAND_SCHEDULER_ADMIN_EMAIL', 'admin@example.com')],
    ],
    'slack' => [
        'webhook_url' => env('COMMAND_SCHEDULER_SLACK_WEBHOOK_URL', null),
    ],
],
```

To add a **custom notification channel**, listen to `SchedulerFailureEvent` (see above).

---

## 12. Advanced — Options & Arguments

Pass options and arguments to your commands:

### Via `#[Scheduled]` attribute

```php
#[Scheduled(
    expression: '0 1 * * *',
    options: ['--connection' => 'data_hub', '--chunk' => 500, '--force'],
)]
class PruneOldData extends Command
{
    protected $signature = 'data:prune {--connection=} {--chunk=100} {--force}';
    ...
}
```

### Via `ScheduleDefinition`

```php
public function scheduleConfig(): ScheduleDefinition
{
    return (new ScheduleDefinition('Prune Old Data'))
        ->daily()
        ->options([
            '--connection' => 'data_hub',
            '--chunk'      => 500,
            '--force',
        ]);
}
```

### Via database override

After syncing, you can override options in the database without redeployment:

```php
$task = ScheduledTask::query()->where('signature', 'data:prune')->first();
$task->options = ['--connection' => 'data_hub', '--chunk' => 200];
$task->save();
```

---

## 13. Migration from Native Laravel Scheduler

If you currently define schedules in `routes/console.php` (Laravel 12+), migration is straightforward.

### Before (native Laravel scheduler)

```php
// routes/console.php
Schedule::command('reports:nightly')
    ->daily()
    ->at('02:00')
    ->withoutOverlapping()
    ->onOneServer()
    ->environments(['production']);
```

### After (command-scheduler attribute)

```php
// In App\Console\Commands\GenerateNightlyReport
#[Scheduled(
    expression: '0 2 * * *',
    name: 'Nightly Report',
    withoutOverlapping: true,
    onOneServer: true,
    environments: ['production'],
)]
```

Then:

```bash
php artisan scheduler:sync
```

**Benefits of migrating:**
- ✅ Enable/disable without deployment
- ✅ Override cron expression at runtime via database/dashboard
- ✅ Full execution audit trail (run history, duration, memory, exit code)
- ✅ Failure notifications with consecutive-failure threshold
- ✅ Run-window restrictions (working hours, weekdays, holidays)
- ✅ Livewire dashboard for at-a-glance monitoring
