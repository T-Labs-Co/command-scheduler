<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Command Scheduler Enabled
    |--------------------------------------------------------------------------
    |
    | This option controls whether the command scheduler is enabled.
    | When disabled, no scheduled commands will be registered or executed.
    |
    */
    'enabled' => env('COMMAND_SCHEDULER_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Load Example Commands
    |--------------------------------------------------------------------------
    |
    | This option controls whether example commands should be loaded.
    | Set to false in production to avoid loading demonstration commands.
    |
    */
    'load_examples' => env('COMMAND_SCHEDULER_LOAD_EXAMPLES', false),

    /*
    |--------------------------------------------------------------------------
    | Dashboard UI
    |--------------------------------------------------------------------------
    |
    | Control whether the dashboard UI routes are enabled.
    | When disabled, the /scheduler routes will not be registered.
    |
    */
    'dashboard' => [
        'enabled' => env('COMMAND_SCHEDULER_DASHBOARD_ENABLED', true),
        'prefix_admin' => 'admin/scheduler',
        'prefix' => 'scheduler',
        'middleware' => ['web', 'auth'],
        'layout' => env('COMMAND_SCHEDULER_DASHBOARD_LAYOUT', 'scheduler'),
        'sync_gate' => 'viewSchedulerDashboard',
    ],

    /*
    |--------------------------------------------------------------------------
    | Discovery Paths
    |--------------------------------------------------------------------------
    |
    | Additional paths to scan for schedulable commands.
    | These paths will be scanned in addition to the default locations:
    | - app/Console/Commands
    | - packages/{package}/src/Console/Commands
    | - packages/{package}/src/Examples (if load_examples is enabled)
    |
    */
    'discovery_paths' => [
        // Add your project-specific discovery paths here after publishing the config:
        // php artisan vendor:publish --provider="TLabsCo\CommandScheduler\Providers\CommandSchedulerServiceProvider" --tag=command-scheduler-config
        //
        // Example: base_path('modules/*/Console/Commands'),
        // Example: app_path('Domains/*/Commands'),
        // Example: __DIR__.'/../../custom_commands',
    ],

    /*
    |--------------------------------------------------------------------------
    | Cache Time To Live
    |--------------------------------------------------------------------------
    |
    | The number of seconds to cache the enabled status of scheduled tasks.
    | This reduces database queries on every schedule run.
    |
    */
    'cache_ttl' => env('COMMAND_SCHEDULER_CACHE_TTL', 300), // 5 minutes

    /*
    |--------------------------------------------------------------------------
    | Database Tables
    |--------------------------------------------------------------------------
    |
    | The database table names used by the command scheduler package.
    |
    */
    'tables' => [
        'scheduled_tasks' => 'cs_scheduled_tasks',
        'scheduled_command_runs' => 'cs_scheduled_command_runs',
    ],

    /*
    |--------------------------------------------------------------------------
    | Prune Logs
    |--------------------------------------------------------------------------
    |
    | The number of days to keep the scheduled command run logs.
    |
    */
    'prune_logs_days' => env('COMMAND_SCHEDULER_PRUNE_LOGS_DAYS', 30),

    /*
    |--------------------------------------------------------------------------
    | Notification Settings
    |--------------------------------------------------------------------------
    |
    | Configure how the system should notify administrators about
    | scheduler failures and issues.
    |
    */
    'notifications' => [
        'enabled' => env('COMMAND_SCHEDULER_NOTIFICATIONS_ENABLED', true),
        'notify_after_consecutive_failures' => 3,
        'channels' => ['email'], // Available: email, slack
        'email' => [
            'recipients' => [env('COMMAND_SCHEDULER_ADMIN_EMAIL', 'admin@example.com')],
        ],
        'slack' => [
            'webhook_url' => env('COMMAND_SCHEDULER_SLACK_WEBHOOK_URL', null),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Working Hours Configuration
    |--------------------------------------------------------------------------
    |
    | Configure general business/working hours for run window constraints.
    | Used by: WORKING_HOURS, BEFORE_WORKING_HOURS, AFTER_WORKING_HOURS,
    |          WORKING_DAYS, REST_TIME, HOLIDAY, WEEKEND.
    |
    | working_days: ISO day-of-week numbers — 1 = Monday … 7 = Sunday.
    | holidays:     Explicit date list in Y-m-d format for the HOLIDAY window.
    |
    */
    'working_hours' => [
        'timezone' => env('COMMAND_SCHEDULER_WORKING_TZ', env('APP_TIMEZONE', 'UTC')),
        'working_days' => [1, 2, 3, 4, 5],
        'start' => env('COMMAND_SCHEDULER_WORK_START', '08:00'),
        'end' => env('COMMAND_SCHEDULER_WORK_END', '17:00'),
        'rest' => [
            'start' => env('COMMAND_SCHEDULER_REST_START', '12:00'),
            'end' => env('COMMAND_SCHEDULER_REST_END', '13:00'),
        ],
        'holidays' => [
            // List public holiday dates in Y-m-d format.
            // '2026-01-01', // New Year's Day
            // '2026-04-30', // Liberation Day (Vietnam)
            // '2026-05-01', // International Workers' Day
            // '2026-09-02', // National Day (Vietnam)
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Trading Hours Configuration
    |--------------------------------------------------------------------------
    |
    | Configure the trading hours for stock market operations.
    | These are used when scheduling tasks with trading hours scoping.
    |
    */
    'trading_hours' => [
        'timezone' => 'Asia/Ho_Chi_Minh',
        'premarket' => [
            'start' => '08:00',
            'end' => '09:00',
        ],
        'trading' => [
            'start' => '09:00',
            'end' => '15:00',
        ],
        'afterhours' => [
            'start' => '15:00',
            'end' => '18:00',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Log Channel Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for the dedicated 'command_scheduler' log channel.
    |
    */
    'logging' => [
        'enabled' => env('COMMAND_SCHEDULER_LOGGING_ENABLED', false),
        'driver' => env('COMMAND_SCHEDULER_LOGGING_ENABLED', false) ? 'daily' : 'silent',
        'path' => storage_path('logs/command-scheduler.log'),
        'level' => 'debug',
        'days' => 7,
    ],
];
