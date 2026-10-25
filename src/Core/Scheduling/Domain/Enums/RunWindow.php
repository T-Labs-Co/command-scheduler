<?php

declare(strict_types=1);

namespace TLabsCo\CommandScheduler\Core\Scheduling\Domain\Enums;

/**
 * Run window defines when a scheduled task can be executed.
 */
enum RunWindow: string
{
    /**
     * Task can run at any time.
     */
    case ALWAYS = 'always';

    /**
     * Task runs only during trading hours.
     */
    case TRADING = 'trading';

    /**
     * Task runs only when market is closed.
     */
    case CLOSED = 'closed';

    /**
     * Task runs only during pre-market hours.
     */
    case PREMARKET = 'premarket';

    /**
     * Task runs only during after-hours.
     */
    case AFTERHOURS = 'afterhours';

    /**
     * Task runs only on trading days (excludes holidays).
     */
    case TRADING_DAYS = 'trading_days';

    /**
     * Task runs only on non-trading days (holidays/weekends).
     */
    case NON_TRADING_DAYS = 'non_trading_days';

    // Working hours run windows

    /**
     * Task runs only during configured working hours on working days.
     */
    case WORKING_HOURS = 'working_hours';

    /**
     * Task runs only before working hours start.
     */
    case BEFORE_WORKING_HOURS = 'before_working_hours';

    /**
     * Task runs only after working hours end.
     */
    case AFTER_WORKING_HOURS = 'after_working_hours';

    /**
     * Task runs only on configured working days (any time).
     */
    case WORKING_DAYS = 'working_days';

    /**
     * Task runs only during configured rest time (e.g. lunch break).
     */
    case REST_TIME = 'rest_time';

    /**
     * Task runs only on configured holiday dates.
     */
    case HOLIDAY = 'holiday';

    /**
     * Task runs only on weekends (Saturday or Sunday).
     */
    case WEEKEND = 'weekend';

    /**
     * Get human-readable label for the run window.
     */
    public function label(): string
    {
        return match ($this) {
            self::ALWAYS => 'Always',
            self::TRADING => 'Trading Hours',
            self::CLOSED => 'Market Closed',
            self::PREMARKET => 'Pre-Market',
            self::AFTERHOURS => 'After Hours',
            self::TRADING_DAYS => 'Trading Days Only',
            self::NON_TRADING_DAYS => 'Non-Trading Days',
            self::WORKING_HOURS => 'Working Hours',
            self::BEFORE_WORKING_HOURS => 'Before Working Hours',
            self::AFTER_WORKING_HOURS => 'After Working Hours',
            self::WORKING_DAYS => 'Working Days',
            self::REST_TIME => 'Rest Time',
            self::HOLIDAY => 'Holiday',
            self::WEEKEND => 'Weekend',
        };
    }

    /**
     * Get description for the run window.
     */
    public function description(): string
    {
        return match ($this) {
            self::ALWAYS => 'Runs at any time',
            self::TRADING => 'Runs only during market trading hours',
            self::CLOSED => 'Runs only when market is closed',
            self::PREMARKET => 'Runs only during pre-market hours (7:00-9:00 AM)',
            self::AFTERHOURS => 'Runs only during after-hours (3:00-5:00 PM)',
            self::TRADING_DAYS => 'Runs only on trading days (excludes holidays)',
            self::NON_TRADING_DAYS => 'Runs only on holidays and weekends',
            self::WORKING_HOURS => 'Runs only during configured working hours on working days',
            self::BEFORE_WORKING_HOURS => 'Runs only before working hours start',
            self::AFTER_WORKING_HOURS => 'Runs only after working hours end',
            self::WORKING_DAYS => 'Runs only on configured working days',
            self::REST_TIME => 'Runs only during configured rest time',
            self::HOLIDAY => 'Runs only on configured holiday dates',
            self::WEEKEND => 'Runs only on weekends',
        };
    }

    /**
     * Check if this is a trading-related window.
     */
    public function isTradingWindow(): bool
    {
        return in_array($this, [
            self::TRADING, self::CLOSED, self::PREMARKET,
            self::AFTERHOURS, self::TRADING_DAYS, self::NON_TRADING_DAYS,
        ], true);
    }

    /**
     * Check if this is a working-hours-related window.
     */
    public function isWorkingHoursWindow(): bool
    {
        return in_array($this, [
            self::WORKING_HOURS, self::BEFORE_WORKING_HOURS, self::AFTER_WORKING_HOURS,
            self::WORKING_DAYS, self::REST_TIME, self::HOLIDAY, self::WEEKEND,
        ], true);
    }

    /**
     * Get all available run windows.
     *
     * @return array<string, string>
     */
    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $window) => [$window->value => $window->label()])
            ->toArray();
    }
}
