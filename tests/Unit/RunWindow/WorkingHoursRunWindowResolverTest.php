<?php

declare(strict_types=1);

use Carbon\Carbon;
use TLabsCo\CommandScheduler\Core\Scheduling\Domain\Enums\RunWindow;
use TLabsCo\CommandScheduler\Core\Scheduling\Infrastructure\RunWindow\WorkingHoursRunWindowResolver;

it('evaluates always window as true', function () {
    $resolver = new WorkingHoursRunWindowResolver;

    expect($resolver->shouldRun(RunWindow::ALWAYS))->toBeTrue();
});

it('evaluates weekend window', function () {
    $resolver = new WorkingHoursRunWindowResolver;

    // Saturday
    Carbon::setTestNow('2026-10-24 10:00:00');
    expect($resolver->shouldRun(RunWindow::WEEKEND))->toBeTrue()
        ->and($resolver->shouldRun(RunWindow::WORKING_DAYS))->toBeFalse();

    // Monday
    Carbon::setTestNow('2026-10-26 10:00:00');
    expect($resolver->shouldRun(RunWindow::WEEKEND))->toBeFalse()
        ->and($resolver->shouldRun(RunWindow::WORKING_DAYS))->toBeTrue();

    Carbon::setTestNow();
});

it('evaluates working hours window', function () {
    config()->set('command-scheduler.working_hours', [
        'timezone' => 'UTC',
        'working_days' => [1, 2, 3, 4, 5],
        'start' => '08:00',
        'end' => '17:00',
        'rest' => [
            'start' => '12:00',
            'end' => '13:00',
        ],
        'holidays' => [],
    ]);

    $resolver = new WorkingHoursRunWindowResolver;

    // Monday 09:00 UTC (during working hours)
    Carbon::setTestNow('2026-10-26 09:00:00');
    expect($resolver->shouldRun(RunWindow::WORKING_HOURS))->toBeTrue()
        ->and($resolver->shouldRun(RunWindow::BEFORE_WORKING_HOURS))->toBeFalse()
        ->and($resolver->shouldRun(RunWindow::AFTER_WORKING_HOURS))->toBeFalse();

    // Monday 07:00 UTC (before working hours)
    Carbon::setTestNow('2026-10-26 07:00:00');
    expect($resolver->shouldRun(RunWindow::WORKING_HOURS))->toBeFalse()
        ->and($resolver->shouldRun(RunWindow::BEFORE_WORKING_HOURS))->toBeTrue();

    // Monday 18:00 UTC (after working hours)
    Carbon::setTestNow('2026-10-26 18:00:00');
    expect($resolver->shouldRun(RunWindow::WORKING_HOURS))->toBeFalse()
        ->and($resolver->shouldRun(RunWindow::AFTER_WORKING_HOURS))->toBeTrue();

    // Monday 12:30 UTC (rest time)
    Carbon::setTestNow('2026-10-26 12:30:00');
    expect($resolver->shouldRun(RunWindow::REST_TIME))->toBeTrue();

    Carbon::setTestNow();
});
