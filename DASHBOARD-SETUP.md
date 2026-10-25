# Dashboard Setup Guide — MaryUI + Livewire 4 + Gate

**Package:** `t-labs-co/command-scheduler`  
**Applies to:** v1.0.0+

This guide explains how to set up the optional Scheduler Dashboard in your Laravel application using **MaryUI**, **Livewire 4**, and a **Laravel Gate** for access control. It also covers asset compilation with **Vite** and customizing the bundled `scheduler.blade.php` layout.

---

## Prerequisites

| Requirement | Version |
|-------------|---------|
| Laravel | 12, or 13 |
| PHP | 8.4+ |
| Livewire | ^4.2 |
| MaryUI | ^2.1 |
| Tailwind CSS | v4 |
| Vite | latest |

---

## Step 1 — Install Optional Dependencies

The dashboard requires Livewire and MaryUI. They are not pulled in automatically.

```bash
# Install Livewire 4
composer require livewire/livewire:"^4.2"

# Install MaryUI
composer require robsontenorio/mary:"^2.1"

# Install Heroicons (if not already present via MaryUI)
npm install @heroicons/vue
```

---

## Step 2 — Install the Package

```bash
composer require t-labs-co/command-scheduler
```

The service provider auto-discovers. You can confirm it registered by running:

```bash
php artisan package:discover
```

---

## Step 3 — Publish Configuration

```bash
php artisan vendor:publish --provider="TLabsCo\CommandScheduler\Providers\CommandSchedulerServiceProvider" --tag=command-scheduler-config
```

This creates `config/command-scheduler.php` in your application. Open it and configure the dashboard section:

```php
'dashboard' => [
    'enabled'      => env('COMMAND_SCHEDULER_DASHBOARD_ENABLED', true),
    'prefix_admin' => 'admin/scheduler',  // URL prefix for admin routes
    'prefix'       => 'scheduler',        // URL prefix for general routes
    'middleware'   => ['web', 'auth'],    // Add your own role/gate middleware here
    'layout'       => env('COMMAND_SCHEDULER_DASHBOARD_LAYOUT', 'scheduler'),
    'sync_gate'    => 'viewSchedulerDashboard',  // Gate used for sync permission
],
```

---

## Step 4 — Run Migrations

```bash
php artisan migrate
```

This creates two tables (names configurable in `config/command-scheduler.php`):
- `cs_scheduled_tasks` — discovered and persisted command metadata
- `cs_scheduled_command_runs` — execution audit trail

---

## Step 5 — Define the Gate

The dashboard uses a single Gate `viewSchedulerDashboard` to control who can access the sync action. Define it in your `app/Providers/AppServiceProvider.php`:

```php
use Illuminate\Support\Facades\Gate;
use App\Models\User;

public function boot(): void
{
    // Option A — Role-based (Spatie Laravel Permission)
    Gate::define('viewSchedulerDashboard', function (User $user): bool {
        return $user->hasAnyRole(['admin', 'super-admin']);
    });

    // Option B — Simple email allowlist
    Gate::define('viewSchedulerDashboard', function (User $user): bool {
        return in_array($user->email, config('scheduler.admin_emails', []), true);
    });

    // Option C — Always allow in local environment
    Gate::define('viewSchedulerDashboard', function (User $user): bool {
        return app()->isLocal() || $user->is_admin;
    });
}
```

---

## Step 6 — Configure Middleware for Dashboard Routes

By default the dashboard is accessible to any authenticated user (`['web', 'auth']`). To restrict it further, update the `middleware` key in `config/command-scheduler.php`:

```php
// Using Spatie roles
'middleware' => ['web', 'auth', 'role:admin'],

// Using a Gate (via can: middleware)
'middleware' => ['web', 'auth', 'can:viewSchedulerDashboard'],

// Using a custom middleware
'middleware' => ['web', 'auth', \App\Http\Middleware\RequireAdmin::class],
```

---

## Step 7 — Publish and Build Assets (Vite)

### 7.1 — Publish CSS and JS assets

```bash
php artisan vendor:publish --provider="TLabsCo\CommandScheduler\Providers\CommandSchedulerServiceProvider" --tag=command-scheduler-assets
```

This copies:
- `resources/css/scheduler.css` — Tailwind v4 + MaryUI/DaisyUI stylesheet
- `resources/js/scheduler.js` — Alpine/Livewire entrypoint

### 7.2 — Register in `vite.config.js`

```js
import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                // Add the scheduler assets
                'resources/css/scheduler.css',
                'resources/js/scheduler.js',
            ],
            refresh: true,
        }),
    ],
});
```

### 7.3 — Configure Tailwind source scanning

The published `resources/css/scheduler.css` already includes proper `@source` directives for scanning the package's Blade views. If you customise the file, ensure these lines remain:

```css
@import "tailwindcss";

/* Scan vendor package views */
@source '../../vendor/t-labs-co/command-scheduler/**/*.blade.php';
@source '../../vendor/t-labs-co/command-scheduler/**/*.js';

/* Scan published views (if you published them) */
@source '../views/vendor/command-scheduler/**/*.blade.php';

/* MaryUI components */
@source '../../vendor/robsontenorio/mary/src/View/Components/**/*.php';

/** daisyUI */
@plugin "daisyui" {
    themes: light --default;
}
```

### 7.4 — Build assets

```bash
# Development
npm run dev

# Production
npm run build
```

---

## Step 8 — Publish Views (Optional)

Publish views only if you need to customise them beyond the layout swap:

```bash
php artisan vendor:publish --provider="TLabsCo\CommandScheduler\Providers\CommandSchedulerServiceProvider" --tag=command-scheduler-views
```

Views are published to `resources/views/vendor/command-scheduler/`.

---

## Step 9 — Customise the Layout

The package ships a standalone `scheduler.blade.php` layout inside its own views. You have three customisation options.

### Option A — Use the Bundled Layout (Zero Config)

The bundled layout uses MaryUI's `x-mary-main` with a collapsible sidebar. Works out of the box after building assets.

The dashboard URL will be at `/admin/scheduler` (configurable via `prefix_admin`).

### Option B — Point to Your Application's Layout

If your app already has a MaryUI layout, point the package to it:

```bash
# In .env
COMMAND_SCHEDULER_DASHBOARD_LAYOUT=layouts.admin
```

This renders the dashboard pages inside your `resources/views/layouts/admin.blade.php` layout using `->layout('layouts.admin')`.

Your layout must:
1. Include Livewire scripts: `@livewireScripts`
2. Include the scheduler CSS and JS: `@vite(['resources/css/scheduler.css', 'resources/js/scheduler.js'])`
3. Render the slot: `{{ $slot }}`

Minimal compatible layout:

```blade
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Scheduler' }}</title>
    @vite(['resources/css/scheduler.css', 'resources/js/scheduler.js'])
</head>
<body class="min-h-screen bg-base-200">
    {{ $slot }}
    @livewireScripts
    <x-mary-toast />
</body>
</html>
```

### Option C — Publish and Fully Customise the Layout

```bash
php artisan vendor:publish --provider="TLabsCo\CommandScheduler\Providers\CommandSchedulerServiceProvider" --tag=command-scheduler-views
```

Then edit `resources/views/vendor/command-scheduler/layouts/scheduler.blade.php` freely — your published version takes precedence over the package's.

**Key sections to customise:**

```blade
{{-- BRAND —— Change app name --}}
<div class="ml-5 pt-5 font-bold">{{ config('app.name') }} · Scheduler</div>

{{-- MENU —— Add your own navigation items --}}
<x-mary-menu activate-by-route>
    <x-mary-menu-item title="Back to App" icon="o-arrow-left" link="/" />
    <x-mary-menu-separator />
    <x-mary-menu-item title="Dashboard" icon="o-home" link="{{ route('admin.scheduler.dashboard') }}" />
    <x-mary-menu-item title="Tasks"     icon="o-list-bullet" link="{{ route('admin.scheduler.tasks') }}" />
    <x-mary-menu-item title="Runs"      icon="o-clock" link="{{ route('admin.scheduler.runs') }}" />
</x-mary-menu>

{{-- USER —— Wire up your User model --}}
@if ($user = auth()->user())
    <x-mary-list-item :item="$user" value="name" sub-value="email" no-separator no-hover class="-mx-2 !-my-2 rounded">
        <x-slot:actions>
            <x-mary-button icon="o-power" class="btn-circle btn-ghost btn-xs" link="/logout" />
        </x-slot:actions>
    </x-mary-list-item>
@endif
```

---

## Step 10 — Sync Commands with Database

After installation, run the sync command to discover all schedulable commands and persist them to the database:

```bash
php artisan scheduler:sync
```

This discovers commands that either:
- Implement `TLabsCo\CommandScheduler\Core\Scheduling\Domain\Contracts\SchedulableCommand`
- Have a `#[Scheduled]` attribute (or any composed attribute)

Check what was found:

```bash
php artisan scheduler:list
```

---

## Step 11 — Register the Scheduler Kernel Hook

In `routes/console.php` (Laravel 12+) register the package's schedule registrar:

```php
// routes/console.php (Laravel 12+)
use Illuminate\Support\Facades\Schedule;
use TLabsCo\CommandScheduler\Core\Scheduling\Application\Services\ScheduleRegistrar;

Schedule::call(function () {
    // CommandSchedulerServiceProvider already calls ScheduleRegistrar::register()
    // during boot. No additional registration needed here.
})->everyMinute();
```

The package's service provider **automatically** calls `ScheduleRegistrar::register()` during boot. You only need to ensure Laravel's scheduler runs:

```bash
# Add to your server's crontab
* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
```

---

## Step 12 — Access the Dashboard

After completing setup:

| URL | Description |
|-----|-------------|
| `/admin/scheduler` | Dashboard overview + statistics |
| `/admin/scheduler/tasks` | All scheduled tasks with enable/disable toggle |
| `/admin/scheduler/runs` | Recent command run history |
| `/admin/scheduler/tasks/{id}` | Task detail + run timeline |

---

## Troubleshooting

### Dashboard shows 404

- Check `COMMAND_SCHEDULER_DASHBOARD_ENABLED=true` in `.env`
- Run `php artisan route:list | grep scheduler` to verify routes are registered
- Clear route cache: `php artisan route:clear`

### Assets not loading / unstyled page

- Run `npm run build` (production) or `npm run dev` (development)
- Confirm `scheduler.css` and `scheduler.js` are in `vite.config.js` inputs
- Verify `@source` directives in `scheduler.css` point to the correct vendor paths

### "Gate [viewSchedulerDashboard] not defined" error

- Define the gate in `AppServiceProvider::boot()`
- Clear config cache: `php artisan config:clear`

### Tables don't exist

- Run `php artisan migrate`
- The package migrations are loaded automatically via `loadMigrationsFrom()`

### Sync finds no commands

- Verify commands implement `SchedulableCommand` interface **or** have a `#[Scheduled]` attribute
- Check `config('command-scheduler.discovery_paths')` — add extra paths if needed
- Run `php artisan scheduler:list --all` to see all discovered commands before DB sync

---

## Complete `.env` Reference

```dotenv
# Enable/disable the entire scheduler
COMMAND_SCHEDULER_ENABLED=true

# Enable/disable the dashboard UI
COMMAND_SCHEDULER_DASHBOARD_ENABLED=true

# Layout name (default uses package's bundled layout)
COMMAND_SCHEDULER_DASHBOARD_LAYOUT=scheduler

# Cache TTL for task discovery (seconds)
COMMAND_SCHEDULER_CACHE_TTL=300

# Log pruning
COMMAND_SCHEDULER_PRUNE_LOGS_DAYS=30

# Failure notifications
COMMAND_SCHEDULER_NOTIFICATIONS_ENABLED=true
COMMAND_SCHEDULER_ADMIN_EMAIL=admin@example.com
COMMAND_SCHEDULER_SLACK_WEBHOOK_URL=https://hooks.slack.com/...

# Logging
COMMAND_SCHEDULER_LOGGING_ENABLED=false

# Working hours (for run-window constraints)
COMMAND_SCHEDULER_WORKING_TZ=UTC
COMMAND_SCHEDULER_WORK_START=08:00
COMMAND_SCHEDULER_WORK_END=17:00
COMMAND_SCHEDULER_REST_START=12:00
COMMAND_SCHEDULER_REST_END=13:00
```
