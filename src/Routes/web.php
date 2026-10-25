<?php

use Illuminate\Support\Facades\Route;
use TLabsCo\CommandScheduler\Livewire\SchedulerDashboard;
use TLabsCo\CommandScheduler\Livewire\SchedulerRunList;
use TLabsCo\CommandScheduler\Livewire\SchedulerTaskDetail;
use TLabsCo\CommandScheduler\Livewire\SchedulerTaskList;

/*
|--------------------------------------------------------------------------
| Command Scheduler Web Routes
|--------------------------------------------------------------------------
|
| These routes handle the dashboard interface for managing scheduled commands.
| Prefix and middleware are configured via config/command-scheduler.php.
|
*/

Route::prefix(config('command-scheduler.dashboard.prefix_admin', 'admin/scheduler'))
    ->name('admin.scheduler.')
    ->group(function (): void {
        Route::get('/', SchedulerDashboard::class)->name('dashboard');
        Route::get('/runs', SchedulerRunList::class)->name('runs');
        Route::get('/tasks', SchedulerTaskList::class)->name('tasks');
        Route::get('/tasks/{task}', SchedulerTaskDetail::class)->name('tasks.show');
    });

Route::prefix(config('command-scheduler.dashboard.prefix', 'scheduler'))
    ->name('scheduler.')
    ->group(function (): void {
        Route::get('/', SchedulerDashboard::class)->name('dashboard');
        Route::get('/runs', SchedulerRunList::class)->name('runs');
        Route::get('/tasks/{task}', SchedulerTaskDetail::class)->name('tasks.show');
    });
