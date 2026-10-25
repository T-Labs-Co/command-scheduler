<?php

namespace TLabsCo\CommandScheduler\Core\Scheduling\Infrastructure\RunWindow;

use TLabsCo\CommandScheduler\Core\Scheduling\Domain\Contracts\RunWindowResolverInterface;
use TLabsCo\CommandScheduler\Core\Scheduling\Domain\Enums\RunWindow;

/**
 * Resolver for working-hours run windows using pure config + PHP time/date logic.
 *
 * Handles all 7 working-hours window types without any external service dependency.
 */
class WorkingHoursRunWindowResolver implements RunWindowResolverInterface
{
    /**
     * Determine if a task should run based on working hours configuration.
     */
    public function shouldRun(RunWindow $window): bool
    {
        return match ($window) {
            RunWindow::WORKING_HOURS => $this->isWithinWorkingHours(),
            RunWindow::BEFORE_WORKING_HOURS => $this->isBeforeWorkingHours(),
            RunWindow::AFTER_WORKING_HOURS => $this->isAfterWorkingHours(),
            RunWindow::WORKING_DAYS => $this->isWorkingDay(),
            RunWindow::REST_TIME => $this->isRestTime(),
            RunWindow::HOLIDAY => $this->isHoliday(),
            RunWindow::WEEKEND => $this->isWeekend(),
            default => true, // Trading or ALWAYS — not our concern
        };
    }

    // -------------------------------------------------------------------------

    protected function timezone(): string
    {
        return config('command-scheduler.working_hours.timezone', config('app.timezone', 'UTC'));
    }

    protected function isWithinWorkingHours(): bool
    {
        if (! $this->isWorkingDay()) {
            return false;
        }
        $now = now()->timezone($this->timezone())->format('H:i');
        $start = config('command-scheduler.working_hours.start', '08:00');
        $end = config('command-scheduler.working_hours.end', '17:00');

        return $now >= $start && $now < $end;
    }

    protected function isBeforeWorkingHours(): bool
    {
        $now = now()->timezone($this->timezone())->format('H:i');
        $start = config('command-scheduler.working_hours.start', '08:00');

        return $now < $start;
    }

    protected function isAfterWorkingHours(): bool
    {
        $now = now()->timezone($this->timezone())->format('H:i');
        $end = config('command-scheduler.working_hours.end', '17:00');

        return $now >= $end;
    }

    protected function isWorkingDay(): bool
    {
        $workingDays = config('command-scheduler.working_hours.working_days', [1, 2, 3, 4, 5]);
        $isoDay = now()->timezone($this->timezone())->dayOfWeekIso; // 1=Mon … 7=Sun

        return in_array($isoDay, $workingDays, true);
    }

    protected function isRestTime(): bool
    {
        $rest = config('command-scheduler.working_hours.rest', []);
        if (empty($rest['start']) || empty($rest['end'])) {
            return false;
        }
        $now = now()->timezone($this->timezone())->format('H:i');

        return $now >= $rest['start'] && $now < $rest['end'];
    }

    protected function isHoliday(): bool
    {
        $holidays = config('command-scheduler.working_hours.holidays', []);
        $today = now()->timezone($this->timezone())->toDateString(); // Y-m-d

        return in_array($today, $holidays, true);
    }

    protected function isWeekend(): bool
    {
        return now()->timezone($this->timezone())->isWeekend();
    }
}
